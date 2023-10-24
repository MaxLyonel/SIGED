<?php

namespace Sie\ProcesosBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\NivelTipo;
use Sie\AppWebBundle\Entity\GradoTipo;
use Sie\AppWebBundle\Entity\ParaleloTipo;
use Sie\AppWebBundle\Entity\TurnoTipo;
use Sie\AppWebBundle\Entity\AsignaturaTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\CatalogoLibretaTipo;
use Sie\AppWebBundle\Entity\DistritoTipo;
use Sie\AppWebBundle\Entity\Tramite;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;


date_default_timezone_set('America/La_Paz');
error_reporting(E_ERROR | E_WARNING);
class RegularizacionPreBachilleratoController extends Controller
{
    public $session;
	public $path;
	// public $NRO_MAX_INSCRIPCIONES;
	public function __construct()
	{
		$this->session = new Session();
		/* Verificar login*/
		$id_usuario = $this->session->get('userId');
		if (!isset($id_usuario))
		//if(false)
		{
			return $this->redirect($this->generateUrl('login'));
		}
		// $this->NRO_MAX_INSCRIPCIONES = 12;
	}

	public function indexAction(Request $request)
	{
		$request_id = $request->get('id');
		$request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
		$request_id = is_numeric($request_id)?$request_id:-1;

		$request_tipo = $request->get('tipo');
		$request_tipo = filter_var($request_tipo,FILTER_SANITIZE_STRING);
		return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:dde_solicitud.html.twig',array(
			'flujo_id' => $request_id,
			'flujo_tipo' => $request_tipo
		));
	}

    
	public function buscarRudeAction(Request $request)
	{   
        /*Obtenemos los datos del formulario*/
		$em 		= $this->getDoctrine()->getManager();
		//$ci_rude 	= filter_var($form['ci_rude'],FILTER_SANITIZE_NUMBER_INT);
		$rude 	= $request->get('rude');
		$patron 	= "/[^A-Za-z0-9]/";
		$reemplazo 	= '';
		$rude 	= preg_replace($patron, $reemplazo, $rude);

        $gestion = $this->session->get('currentyear');
		
        $msg = '';

        if ($msg == ''){
            $query = $em->getConnection()->prepare("select e.codigo_rude, e.nombre, e.paterno, e.materno, e.carnet_identidad, e.complemento, e.segip_id, 
                    ic.institucioneducativa_id, i.institucioneducativa, 
                    ic.nivel_tipo_id, ic.grado_tipo_id, case when ic.nivel_tipo_id = 13 then 'Secundaria Comunitaria Productiva'
                    when ic.nivel_tipo_id = 12 then 'Primaria Comunitaria Vocacional' else '' end nivel, jg.distrito_tipo_id, dt.distrito 
                    from estudiante e
                    inner join estudiante_inscripcion ei on e.id = ei.estudiante_id 
                    inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id = ic.id 
                    inner join institucioneducativa i on ic.institucioneducativa_id = i.id
                    inner join jurisdiccion_geografica jg on i.le_juridicciongeografica_id = jg.id
                    inner join distrito_tipo dt on jg.distrito_tipo_id = dt.id
                    where ei.estadomatricula_tipo_id = 4
                    and ic.gestion_tipo_id = ".$gestion."
                    and ic.nivel_tipo_id in (12,13)
                    and e.codigo_rude ='".$rude."' ;");
            $query->execute();
            $estudiante = $query->fetchAll();	
            $estudianteDatos = $estudiante[0];
            
            if(count($estudiante)<>1)
            {	$msg = 'El estudiante no cumple con los requisitos para iniciar trámite Pre-Bachillerato, verifique si es estudiante efectivo en la gestión actual';
				return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:buscarEstudiante.html.twig',array(
				'estudiante'=>NULL,
				'msg' => $msg
				));
                
            }
            
            if (ltrim($estudianteDatos["segip_id"]) == '' or$estudianteDatos["segip_id"] == 0){
                $msg = 'El estudiante no cuenta con CI validado por SEGIP';
				return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:buscarEstudiante.html.twig',array(
					'estudiante'=>NULL,
					'msg' => $msg
					));
            } 
        }  
        
        /*
        * verificamos si tiene tuicion
        */
        if ($msg == ''){
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $estudianteDatos["institucioneducativa_id"]);
            $query->bindValue(':rolId', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();
            if($aTuicion[0]['get_ue_tuicion']==false)
            {	
                $msg = 'No tiene tuición sobre la Unidad Educativa, en la que el estudiante está como efectivo.';
				return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:buscarEstudiante.html.twig',array(
					'estudiante'=>NULL,
					'msg' => $msg
					));
            }
            
            $nivel_act = $estudianteDatos["nivel_tipo_id"];
            $grado_act = $estudianteDatos["grado_tipo_id"];

            if ($nivel_act == 13 and $grado_act == 1){
                $nivel = 12;
                $grado = 6;
            } else {
                $nivel = $nivel_act;
                $grado = $grado_act - 1;
            }

            if (($nivel_act == 12 and $grado_act == 1) or ($nivel == 12 and $grado == 1) ){
				$msg = 'El estudiante por el nivel y grado inscrito en la presente gestión no puede iniciar trámite Pre-Bachillerato.';
				return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:buscarEstudiante.html.twig',array(
					'estudiante'=>NULL,
					'msg' => $msg
					));
                
            }
        }
        if ($msg == ''){
            $query = $em->getConnection()->prepare("
                select e.codigo_rude, ic.institucioneducativa_id, ic.grado_tipo_id, ic.nivel_tipo_id 
                from estudiante e
                inner join estudiante_inscripcion ei on e.id = ei.estudiante_id 
                inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id = ic.id 
                where ei.estadomatricula_tipo_id = 5
                and ic.nivel_tipo_id = ".$nivel." 
                and ic.grado_tipo_id = ".$grado." 
                and e.codigo_rude = '".$rude."' ;");
            $query->execute();
            $estadomat_ant = $query->fetchAll();
             
            if(count($estadomat_ant) == 0)
            {
                $msg = 'El estudiante no cumple con los requisitos para iniciar trámite Pre-Bachillerato, verifique el último año de escolaridad con estado promovido.';
				return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:buscarEstudiante.html.twig',array(
					'estudiante'=>NULL,
					'msg' => $msg
					));
            }
        }

        if($msg<>'')
		{
			$estudianteDatos = '';
		}

		return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:buscarEstudiante.html.twig',array(
			'estudiante'=>$estudianteDatos,
			'msg' => $msg
		));
	}

    public function buscarNivelAction(Request $request)
	{
		$esAjax=$request->isXmlHttpRequest();
		$niveles = array();
		if($esAjax)
		{

			list($data,$status,$msj)=$this->obtenerNiveles(); 

			$response = new JsonResponse($data,$status);
			$response->headers->set('Content-Type', 'application/json');
			return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
		}
		else
		{
			return $this->redirect($this->generateUrl('login'));
		}
	}

    private function obtenerNiveles()
	{
		$em 	= $this->getDoctrine()->getManager();
		$qb 	= $em->createQueryBuilder();
			$niveles_por_gestion=array();
			
			$niveles_por_gestion=[12,13];
			
			$qb 		= $em->createQueryBuilder();
			$niveles 	= $qb->select('n')
			 ->from('SieAppWebBundle:NivelTipo', 'n')
			 ->where('n.id in (:niveles)')
			 ->setParameter('niveles', array_values( $niveles_por_gestion ))
			 ->getQuery()
			 ->getArrayResult();
			if($niveles)
			{
				$data=json_encode(array('niveles'=>$niveles));
				$status = 200;
				$msj 	= 'Niveles encontrados';
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'Acaba de ocurrir un error, no existen niveles de la géstion ';
			}
		
		return array($data,$status,$msj);
	}

    public function buscarGradoAction(Request $request,$nivel)
	{
		$esAjax=$request->isXmlHttpRequest();
		$grados = array();
		if($esAjax)
		{
			
			$request_nivel = filter_var($nivel,FILTER_SANITIZE_NUMBER_INT);

			list($data,$status,$msj)=$this->obtenerGrados($request_nivel);
			$response = new JsonResponse($data,$status);
			$response->headers->set('Content-Type', 'application/json');
			return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
		}
		else
		{
			return $this->redirect($this->generateUrl('login'));
		}
	}

    private function obtenerGrados($request_nivel,$grado=NULL)
	{
		$em 	= $this->getDoctrine()->getManager();
		$qb 	= $em->createQueryBuilder();
		$data 	= NULL;
		$status = 404;
		$msj 	= 'Acaba de ocurrir un error, debe seleccionar un nivel válido';
        $gestion = $this->session->get('currentyear');

		$operadorWhere =' = ';
		if($grado==NULL)
		{
			$operadorWhere =' <> ';
			$grado = '-1';
		}

			$grados_por_nivel=array();

			//Utilizamos el catalogo libreta para aseguramos que en cada gestion solo se seleccionen los grados correctos
			$gradoTmp 	= $qb->select('n.gradoTipoId')
			 ->from('SieAppWebBundle:CatalogoLibretaTipo', 'n')
			 ->where('n.gestionTipoId = :gestion')
			 ->andWhere('n.nivelTipoId = :nivel')
			 ->groupBy('n.gradoTipoId')
			 ->orderBy('n.gradoTipoId', 'asc')
			 ->setParameter('gestion', $gestion)
			 ->setParameter('nivel', $request_nivel)
			 ->getQuery()
			 //->getArrayResult();
			 ->getResult();

			if($gradoTmp)
			{
				foreach ($gradoTmp as $item)
				{
					$grados_por_nivel[]=$item['gradoTipoId'];
				}
			}
			else
			{
				$grados_por_nivel[]=-1;
			}

			$qb = $em->createQueryBuilder();
			$grados 	= $qb->select('g')
			 ->from('SieAppWebBundle:GradoTipo', 'g')
			 ->where('g.id in (:grados)')
			 ->andWhere('g.id '.$operadorWhere.' :grado')
			 ->setParameter('grados', array_values( $grados_por_nivel ))
			 ->setParameter('grado', $grado)
			 ->getQuery()
			 ->getArrayResult();
			if($grados)
			{
				$data=json_encode(array('grados'=>$grados));
				$status = 200;
				$msj 	= 'Grados encontrados';
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'Acaba de ocurrir un error, no existen grados para este nivel';
			}

		return array($data,$status,$msj);
	}

    public function verificarInscripcionAction(Request $request)
	{
		$form 		= $request->request->all();
        $patron 	= "/[^A-Za-z0-9]/";
		$reemplazo 	= '';
		$resultado 	= preg_replace($patron, $reemplazo, $form['register_rude']);
		if($resultado)
		$rude = $resultado;
		
		
		$nivel = filter_var($form['register_nivel'],FILTER_SANITIZE_NUMBER_INT);
		$nivel = is_numeric($nivel)?$nivel:-1;

		$grado = filter_var($form['register_grado'],FILTER_SANITIZE_NUMBER_INT);
		$grado = is_numeric($grado)?$grado:-1;

		$cantidadPreInscripciones = filter_var($form['register_inscripciones'],FILTER_SANITIZE_NUMBER_INT);
		$cantidadPreInscripciones = is_numeric($cantidadPreInscripciones)?$cantidadPreInscripciones:7777777;
        // dump($cantidadPreInscripciones);die;
		$em = $this->getDoctrine()->getManager();
		$query = $em->getConnection()->prepare("
                select e.codigo_rude, ic.institucioneducativa_id, ic.grado_tipo_id, ic.nivel_tipo_id 
                from estudiante e
                inner join estudiante_inscripcion ei on e.id = ei.estudiante_id 
                inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id = ic.id 
                where ei.estadomatricula_tipo_id = 4
                and e.codigo_rude = '".$rude."' ;");
        $query->execute();
        $estadomat = $query->fetchAll();
		$estmat = $estadomat[0];
        $grado_act = $estmat['grado_tipo_id'];
		$nivel_act = $estmat['nivel_tipo_id'];
		
        if ((intval($nivel)==$nivel_act and intval($grado) >= $grado_act) or (intval($nivel)>$nivel_act)){
			$data 	= NULL;
			$status = 404;
			$msj 	= 'No se puede crear la inscripción, es mayor o igual a la inscripción actual del estudiante';
		
		} else {
			$esValido = true;

			$esValido = $this->verificarUnaInscripcion($rude,$nivel,$grado,$cantidadPreInscripciones);
			$data 	= NULL;
			$status = 404;
			$msj 	= 'No se puede crear la inscripción, puesto que el estudiante ya tiene una inscripción con el grado y nivel seleccionado';

			if($esValido)
			{
				$data 	= 'Ok';
				$status = 200;
				$msj 	= 'Ok.';
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'No se puede crear la inscripción, puesto que el estudiante ya tiene una inscripción con el grado y nivel seleccionado';
			}
		}
		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,'status'=>$status,'data'=>$data));
	}

    private function verificarUnaInscripcion($rude,$nivel,$grado,$cantidadPreInscripciones)
	{
		$em = $this->getDoctrine()->getManager();

		$inscripcionValida = true;

		$query = $em->getConnection()->prepare("
                    select * 
                    from sp_genera_estudiante_historial(?) 
                    where nivel_tipo_id_r=? and grado_tipo_id_r=? 
                    and estadomatricula_tipo_id_fin_r in (5) 
                    order by gestion_tipo_id_raep desc, estudiante_inscripcion_id_raep desc;");
		$params = array($rude,$nivel,$grado);
		$query->execute($params);
		$dataInscription = $query->fetchAll();
		
		$totalInscripciones=count($dataInscription); 

		if($totalInscripciones>0)
			$inscripcionValida=false;

		return $inscripcionValida;
	}

    public function registrarSolicitudAction(Request $request)//DEPARTAMENTAL
	{
		$form = $request->request->all();
		$request_inscriptions = $form['request_inscriptions'];
		$request_fileDocsInteresado = $_FILES["request_fileDocsInteresado"];
		$request_fileInforme = $_FILES["request_fileInforme"];
        
		$data = NULL;
		$status	= 404;
		$msj = NULL;
		$urlReporte	= NULL;
        
		$tieneErrores = true;
		$inscripciones = array();
		$inscripciones_procesada = array();
        
		$request_flujo_id = $form['request_flujo_id'];
		$request_flujo_id = filter_var($request_flujo_id,FILTER_SANITIZE_NUMBER_INT);
		$request_flujo_id = is_numeric($request_flujo_id)?$request_flujo_id:-1;
        
		$request_flujo_tipo = $form['request_flujo_tipo'];
		$request_flujo_tipo = filter_var($request_flujo_tipo,FILTER_SANITIZE_STRING);
        
		$request_distrito = $form['request_distrito'];
		$request_distrito = filter_var($request_distrito,FILTER_SANITIZE_NUMBER_INT);
		$request_distrito = is_numeric($request_distrito)?$request_distrito:-1;
        
        $patron 	= "/[^A-Za-z0-9]/";
		$reemplazo 	= '';
		$resultado 	= preg_replace($patron, $reemplazo, $form['request_estudiante']);
		if($resultado)
            $rude = $resultado;
    
        $em = $this->getDoctrine()->getManager();
        $gestion = $this->session->get('currentyear');
		
		// $qb = $em->createQueryBuilder();
        $query = $em->getConnection()->prepare("select e.codigo_rude, e.nombre, e.paterno, e.materno, e.carnet_identidad, e.complemento, e.segip_id, ei.id inscripcion_id,
                ic.gestion_tipo_id, ic.institucioneducativa_id, i.institucioneducativa, 
                ic.nivel_tipo_id, ic.grado_tipo_id, case when ic.nivel_tipo_id = 13 then 'Secundaria Comunitaria Productiva'
                when ic.nivel_tipo_id = 12 then 'Primaria Comunitaria Vocacional' else '' end nivel, jg.distrito_tipo_id, dt.distrito 
                from estudiante e
                inner join estudiante_inscripcion ei on e.id = ei.estudiante_id 
                inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id = ic.id 
                inner join institucioneducativa i on ic.institucioneducativa_id = i.id
                inner join jurisdiccion_geografica jg on i.le_juridicciongeografica_id = jg.id
                inner join distrito_tipo dt on jg.distrito_tipo_id = dt.id
                where ei.estadomatricula_tipo_id = 4
                and ic.gestion_tipo_id = ".$gestion."
                and ic.nivel_tipo_id in (12,13)
                and e.codigo_rude ='".$rude."' ;");
        $query->execute();
        $estudiante = $query->fetchAll();	
        $estudianteDatos = $estudiante[0];

		$sie = $estudianteDatos['institucioneducativa_id'];
		try
		{
			$inscripciones=json_decode($request_inscriptions);
		}
		catch (Exception $e)
		{
			$inscripciones=array();
			$inscripciones_procesada= array();
		}
		//Validaciones necesarias para proseguir el registro
		if( $estudiante && $request_flujo_id>0 && $request_distrito>0 && ( isset($request_fileDocsInteresado) && isset($request_fileInforme ) ))
		{
			$em = $this->getDoctrine()->getManager();
			$tramite_id 	= -1;
			$rude 			= $estudianteDatos["codigo_rude"];
			$path=$this->path;
			if($rude != null && !empty($rude))
			{
				try
				{
					
						$nArcSolicitud = $this->guardarArch($sie,$rude,$gestion,'DDESOL', $request_fileDocsInteresado);
						$nArcInforme = $this->guardarArch($sie,$rude,$gestion,'DDEINF', $request_fileInforme);

						//Realizamos la verificacion de si los archvios fueron guardados
						if ($nArcSolicitud !== null && $nArcInforme !== null) 
						{   
							//$inscripciones 				= json_decode($request_inscriptions);
							$archivos = array(
								"solicitud" => $nArcSolicitud,
								"dde_sol_informe" => $nArcInforme
							);
							$datosJSONInscripcion 			= array(
								'estudiante'		=> $estudianteDatos,
								'inscripciones' 	=> $inscripciones,
								'informes' 			=> $archivos,
								// 'arc_informe' 			=> $informes,
							);
							$flujoTipo=$request_flujo_id;//Solicitud de regularización
							$tarea = $this->get('wftramite')->obtieneTarea($flujoTipo, 'idflujo');
							$tareaActual = $tarea['tarea_actual'];
							$tipoTramite = $em->getRepository('SieAppWebBundle:TramiteTipo')->findOneBy(array('obs'=>'RPB'));
							//Se utilizara este artificio para obtener el distrito
							$gestionArray=$nivelArray=$gradoArray=array();
							$departamento=$distrito=-1;
							list($gestionArray,$nivelArray,$gradoArray) = $this->formatearGestionNivelGrado($inscripciones);
							list($departamento,$distrito) = $this->obtenerDepartamentoDistritoUsuario();
							$distrito= $request_distrito;
                            $insc_id = $estudianteDatos["inscripcion_id"];
							$lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);
							$registroTramite = $this->get('wftramite')->guardarTramiteNuevo(
								$this->session->get('userId'),								//$usuario,
								$this->session->get('roluser'),								//$rol,
								$flujoTipo,													//$flujotipo,
								$tareaActual,												//$tarea,
								'institucioneducativa',										//$tabla,
								$sie,															//$id_tabla,
								'',															//$observacion,
								$tipoTramite->getId(),										//$tipotramite,
								'',															//$varevaluacion,
								'',															//$idtramite,
								json_encode($datosJSONInscripcion, JSON_UNESCAPED_UNICODE),	//$datos,
								'',															//$lugarTipoLocalidad_id,
								$lugarTipo['lugarTipoIdDistrito']							//$lugarTipoDistrito_id?
							);
                                
							if($registroTramite)
							{
								if($registroTramite['dato']===true && $registroTramite['tipo']==='exito')
								{   
										$data 			= $registroTramite['idtramite'];
										$status 		= 200;
										$msj 			= $registroTramite['msg'];
										$urlReporte 	= $this->generateUrl('regularizacion_pre_bachillerato_reporteDepartamental',array('idtramite' => $registroTramite['idtramite'])) ;
								}
								else
								{	
									$this->borrarArchivo($gestion, $sie, $rude, $nArcSolicitud);
									$this->borrarArchivo($gestion, $sie, $rude, $nArcInforme);
									$data 	= NULL;
									$status = 404;
									$msj 	= $registroTramite['msg'];
								}
							}
							else
							{
								$this->borrarArchivo($gestion, $sie, $rude, $nArcSolicitud);
								$this->borrarArchivo($gestion, $sie, $rude, $nArcInforme);		
								$data 	= NULL;
								$status = 404;
								$msj 	= 'Ocurrio un error al guardar los archivos adjuntos, por favor vuelva a intentarlo.';
							}	
						}
						else
						{
							//Esto ocurria si los archivos no pudieron ser guardados
							//por seguridad borramos los archivos, 
							$this->borrarArchivo($gestion, $sie, $rude, $nArcSolicitud);
							$this->borrarArchivo($gestion, $sie, $rude, $nArcInforme);	
							$data 	= NULL;
							$status = 404;
							$msj 	= 'Ocurrio un error al guardar los archivos adjuntos, por favor vuelva a intentarlo.';
						}
				}
				catch(Exception $e)
				{
					$data 	= NULL;
					$status = 404;
					$msj 	= 'Ocurrio un error al guardar el registro, por favor vuelva a intentarlo.';
				}
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'Ocurrio un error al guardar el registro, el estudiante no tiene codigo RUDE.';
			}
		}
		else
		{
			//Esto ocurrira si no fueron enviados los datos del formulario
			$data 	= NULL;
			$status = 404;
			$msj 	= 'Ocurrio un error debido a que las inscripciones no fueron registradas correctamente o los archivos adjuntos no fueron subidos o el estudiante no existe, por favor vuelva a intentarlo.';
		}

		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,'status'=>$status,'data'=>$data,'urlReporte'=>$urlReporte));
	}

	private function guardarArch($sie, $codigoRude, $gestion, $prefijo, $file)
    {
        if (isset($file)) {
            $type = $file['type'];
            $size = $file['size'];
            $tmp_name = $file['tmp_name'];
            $name = $file['name'];
            $extension = explode('.', $name);
            $extension = $extension[count($extension) - 1];
            $new_name = $prefijo.date('YmdHis') . '.' . $extension;
    
            // GUARDAR EL ARCHIVO
            $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/prebachillerato/'.$gestion.'/'.$codigoRude;
            if (!file_exists($directorio)) {
                mkdir($directorio, 0777, true);
            }
    
            $archivador = $directorio . '/' . $new_name;
    
            if (!move_uploaded_file($tmp_name, $archivador)) {
                return null;
            }
    
            // CREAR DATOS DEL ARCHIVO
            $informe = array(
                'name' => $name,
                'type' => $type,
                'tmp_name' => 'nueva_ruta',
                'size' => $size,
                'new_name' => $new_name
            );
    
            return $new_name;
        } else {
            return null;
        }
    }

	private function borrarArchivo($gestion, $sie, $codigoRude, $file_name)
	{
		$archivoBorrado=false;
		$carpeta = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/flujos/prebachillerato/'.$gestion.'/'. $codigoRude;

		if (file_exists($carpeta))
		{
			//mkdir($carpeta, 0775, true);
			$archivo=$carpeta.'/'. $file_name;

			if(unlink($archivo))
				$archivoBorrado=true;
			else
				$archivoBorrado=false;
		}
		return $archivoBorrado;
	}

    private function formatearGestionNivelGrado($inscripciones)
	{
		$gestionArray=array();
		$nivelArray=array();
		$gradoArray=array();

        $gestion = $this->session->get('currentyear');

		if($inscripciones && count($inscripciones)>0 && count($inscripciones)<=2)
		{
			foreach ($inscripciones as $ins)
			{
				if($ins && property_exists($ins,'gestion') && property_exists($ins,'nivel') && property_exists($ins,'grado') )
				{
					$gestionArray [] = $gestion;
					$nivelArray [] = filter_var($ins->nivel,FILTER_SANITIZE_NUMBER_INT);
					$gradoArray [] = filter_var($ins->grado,FILTER_SANITIZE_NUMBER_INT);
				}
				else
				{
					$gestionArray=array();
					$nivelArray=array();
					$gradoArray=array();
					break;
				}
			}
		}
		return array($gestionArray,$nivelArray,$gradoArray);
	}

    private function obtenerDepartamentoDistritoUsuario()
	{
		$departamento=-1;
		$distrito=-1;
		$userId=$this->session->get('userId');
		$userRol=$this->session->get('roluser');
        // dump($userId);
        // dump($userRol); die;
		$datosUsuario=$this->getDatosUsuario($userId,$userRol);
		if($datosUsuario)
		{
			$distrito = $datosUsuario['cod_dis'];
			$departamento = substr($distrito,0,1);
			if(is_numeric($distrito) )
			{
				if($distrito<=0)
				{
					$departamento=-1;
					$distrito=-1;
				}
			}
		}
        // dump($datosUsuario);
        // dump($distrito);die;
		return array($departamento,$distrito);
	}

    private function getDatosUsuario($userId,$userRol)
    {   
        $userId=($userId)?$userId:-1;
        $userRol=($userRol)?$userRol:-1;

        $user=NULL;
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = '
        select *
        from (
        select b.rol_tipo_id,(select rol from rol_tipo where id=b.rol_tipo_id) as rol,a.persona_id,c.codigo as cod_dis,a.esactivo,a.id as user_id
        from usuario a 
          inner join usuario_rol b on a.id=b.usuario_id 
            inner join lugar_tipo c on b.lugar_tipo_id=c.id
        where codigo not in (\'04\') and b.rol_tipo_id not in (2,3,9,29,26,21,14,39,6) and a.esactivo=\'t\'
        union all
        select f.rol_tipo_id,(select rol from rol_tipo where id=f.rol_tipo_id) as rol,a.persona_id,d.codigo as cod_dis,e.esactivo,e.id as user_id
        from maestro_inscripcion a
          inner join institucioneducativa b on a.institucioneducativa_id=b.id
            inner join jurisdiccion_geografica c on b.le_juridicciongeografica_id=c.id
              inner join lugar_tipo d on d.lugar_nivel_id=7 and c.lugar_tipo_id_distrito=d.id
                inner join usuario e on a.persona_id=e.persona_id
                  inner join usuario_rol f on e.id=f.usuario_id
        --where a.gestion_tipo_id='.date('Y').' and cargo_tipo_id in (1,12) and periodo_tipo_id=1 and f.rol_tipo_id=9 and e.esactivo=\'t\') a
        where a.gestion_tipo_id='.date('Y').' and cargo_tipo_id in (1,12) and e.esactivo=\'t\') a
        where user_id = ?
        and rol_tipo_id = ?
        ORDER BY cod_dis
        LIMIT 1
        ';
        $stmt = $db->prepare($query);
        $params = array($userId,$userRol);
        $stmt->execute($params);
        $user=$stmt->fetch();
        // dump($user);die;
        return $user;
    }

    

    /************************************************* REPORTES *************************************************/


	public function reporteDepartamentalAction(Request $request,$idtramite)
	{
		$em = $this->getDoctrine()->getManager();
		$request_id = $request->get('id');
		$request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
		$request_id = is_numeric($request_id)?$request_id:-1;

		$request_tipo = $request->get('tipo');
		$request_tipo = filter_var($request_tipo,FILTER_SANITIZE_STRING);

		$tramite_id = filter_var($idtramite,FILTER_SANITIZE_NUMBER_INT);
		$tramite_id = is_numeric($tramite_id)?$tramite_id:-1;
		
		//Verificamos que el id de trámite haya sido enviado, caso contrario redireccionamos a la anteriro pagina
		if($tramite_id>0)
		{
			//Buscamos el trámite segun el id enviado
			$tramite =$this->getTramiteReporte($tramite_id);
			//verificamos que el trámite exista
			if($tramite)
			{
				$datosJSONInscripcion = $tramite['datos'];
				
				if(strlen($datosJSONInscripcion)>0)//verificamos que el JSon existe
				{
					try
					{
						$datosJSONInscripcion_decode=json_decode($datosJSONInscripcion);
						$estudiante=property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;
						
						//verificamos que exista el estudiante
						if($estudiante)
						{
							if( $datosJSONInscripcion_decode && property_exists($datosJSONInscripcion_decode,'inscripciones'))
							{
								//Una vez verificado que el estudiante exista obtenemos los datos json de la inscripciones, documentos adjuntos delinteresado y el primer informe de la departamental
								$inscripciones = $datosJSONInscripcion_decode->inscripciones;
								
								$cantidadInscripciones= count($inscripciones);
								$tablaInscripciones=array();
								$tablaInscripciones=$datosJSONInscripcion_decode->inscripciones;
								// dump($tablaInscripciones);die;
								//continuamos con los documentos del interesado
								$adjuntosDocsInteresado = $datosJSONInscripcion_decode->informes->solicitud;
								
								//terminamos con los informes
								$adjuntosInformes 		= $datosJSONInscripcion_decode->informes;
								
								$datosInscripcion = base64_encode(json_encode($datosJSONInscripcion_decode->inscripciones));
								
								$etapa = "Inicio Departamental";
								$datosQR = "Etapa: ".$etapa."\n"."Tramite: ".$tramite_id."\n"."Solicitante: ".$rude."\n"."Fecha inicio de tramite: ".$tramite['fecha_tramite']."\n\n";
								$codigoQR = $this->getCodigoQR($datosQR,$datosInscripcion);
								$codigoQR=preg_replace('#^data:image/[^;]+;base64,#', '',base64_encode($codigoQR) );
								$html= $this->render('SieProcesosBundle:RegularizacionPreBachillerato:Reportes/departamental_reporte.html.twig',array(
									'estudiante'=>$estudiante,
									// 'path' => $this->path,
									'error' => $error,
									'tablaInscripciones' => $tablaInscripciones,
									'adjuntosDocsInteresado' => $adjuntosDocsInteresado,
									'adjuntosInformes' => $adjuntosInformes,
									"tramite" => $tramite_id,
									"codigoQR" => $codigoQR
								));
								$pdf=$this->generarPdf($html, $tramite_id, $rude,$descripcion="TRAMITE_DE_REGULARIZACION");
								$this->descargarPdf($pdf);

							}
							else
							{
								$error = 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
							}
						}
						else
						{
							$error='No se puede encontrar al estudiante, por favor verifique que el trámite fue realizado correctamente.';
						}
					}
					catch(Exception $e)
					{
						$error = 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
					}
				}
				else
				{
					$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
				}
			}
			else
			{
				$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
			}
			return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:Reportes/departamental_reporte.html.twig',array(
				'estudiante'=>$estudiante,
				'path' => $this->path,
				'error' => $error,
				'tablaInscripciones' => $tablaInscripciones,
				'adjuntosDocsInteresado' => $adjuntosDocsInteresado,
				'adjuntosInformes' => $adjuntosInformes,
				"tramite" => $tramite_id,
			));
		}
		else
		{
			return $this->redirect($this->generateUrl('login'));
		}
	}

    private function getTramiteReporte($tramite_id)
	{
		$em 		= $this->getDoctrine()->getManager();
		$db 		= $em->getConnection();

		$tramite= null;
		$tramite_id=is_numeric($tramite_id)?$tramite_id:-1;
		$query = '
			select t1.id as tramite_id, t2.id as tramite_detalle_id, t3.id as solicitud_tramite, t3.datos,t1.flujo_tipo_id,t1.tramite_tipo,to_char(t1.fecha_tramite,\'DD/MM/YYYY\') as fecha_tramite
			from tramite t1
			INNER JOIN tramite_detalle t2 on  (t2.tramite_id::INT) = (t1.id::INT)
			--INNER JOIN wf_solicitud_tramite t3 on (t3.tramite_detalle_id::INT)=(t2.tramite_detalle_id::INT)
			INNER JOIN wf_solicitud_tramite t3 on (t3.tramite_detalle_id::INT)=(t2.id::INT)
			where t1.id=?
			limit 1';
		$stmt = $db->prepare($query);
		$params = array($tramite_id);
		$stmt->execute($params);
		$tramite=$stmt->fetch();
		return $tramite;
	}

	private function getCodigoQR($datosQR,$datosInscripcion)
	{
		//$datosQR=$datosQR.$datosInscripcion;
		$barcodeobj = new \TCPDF2DBarcode($datosQR, 'QRCODE,Q');
		//$qr=$barcodeobj->getBarcodePNG(6, 6, array(0,0,0));
		$qr=$barcodeobj->getBarcodePngData();
		return $qr;
	}

	private function generarPdf($html,$tramite_id, $rude,$descripcion="")
	{
		$pdf = $this->container->get("white_october.tcpdf")->create(
			'PORTRAIT',
			PDF_UNIT,
			'LETTER',//PDF_PAGE_FORMAT,
			true,
			'UTF-8',
			false
		);
		$pdf->SetAuthor('Ministerio de Educación');
		$pdf->SetTitle('Informe de inicio de trámite');
		//$pdf->SetSubject('Your client');
		//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
		$pdf->setFontSubsetting(true);
		$pdf->SetFont('helvetica', '', 11, '', true);

		// set default header data
		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
		//$pdf->setFooterData(array(0,64,0), array(0,64,128));
		$pdf->setFooterData(array(0,32,0), array(0,32,64));

		// set header and footer fonts
		//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setPrintHeader(false);
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
		$pdf->SetMargins(12, 10, 12);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		$pdf->SetFooterMargin(10);

		// set auto page breaks
		//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

		// set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

		$style = array(
			'position' => $this->rtl?'R':'L',
			'align' => $this->rtl?'R':'L',
			'stretch' => false,
			'fitwidth' => true,
			'cellfitalign' => '',
			'border' => false,
			'padding' => 0,
			'fgcolor' => array(0,0,0),
			'bgcolor' => false,
			'text' => false
		);
		$pdf->AddPage();

		$content=null;
		if($html)
			$content=$html->getContent();

		$pdf->writeHTMLCell(
			$w = 0,
			$h = 0,
			$x = '',
			$y = '',
			$content,
			$border = 0,
			$ln = 1,
			$fill = 0,
			$reseth = true,
			$align = '',
			$autopadding = true
		);
		
		//$pdf->writeHTML($content, true, false, true, false, '');
		$nombrePdf=$tramite_id."-".$rude."-".date('d.m.Y-H.i.s.v');
		return $pdf->Output($descripcion.$nombrePdf.".pdf", 'I');
	}

	private function descargarPdf($pdf,$tramite_id, $rude,$descripcion="")
	{
		$status 		= 200;
		$nombrePdf 		= $tramite_id."-".$rude."-".date('d.m.Y-H.i.s.v');
		$arch           = $descripcion.$nombrePdf.".pdf";
		$response       = new Response();
		$response->headers->set('Content-type', 'application/pdf');
		$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
		$response->setContent($pdf);
		$response->setStatusCode(file_get_contents($pdf));
		$response->setStatusCode($status);
		$response->headers->set('Content-Transfer-Encoding', 'binary');
		$response->headers->set('Pragma', 'no-cache');
		$response->headers->set('Expires', '0');
		return $response;
	}

    // private function listarUEValidas($departamento,$distrito,$nivelArray,$gradoArray,$ue=-1)
	// {
	// 	$ueTmp = $this->listarUECumplenRequerimientos($nivelArray,$gradoArray);

	// 	$gestionArray = implode(',',$gestionArray);
	// 	$nivelArray = implode(',',$nivelArray);
	// 	$gradoArray = implode(',',$gradoArray);

    //     $em = $this->getDoctrine()->getManager();
    //     $db = $em->getConnection();

    //     $operadorDepartamento = ($departamento>0)? ' = ':' <> ';
    //     $operadorDistrito = ($distrito>0)? ' = ':' <> ';

    //     $whereUE =($ue==-1)?'':'and d.id = '.$ue;

    //     $tieneErrores = true;
    //     $ue_validas = array();
    //     $query = '
	// 	select DISTINCT d.id ue_id,d.institucioneducativa ue_nombre, j.id dept_id, j.departamento dept_nombre, f.cod_dis, f.des_dis
	// 	--, c.gestion_tipo_id, c.nivel_tipo_id,c.grado_tipo_id
	// 	from  institucioneducativa d
	// 			inner join institucioneducativa_curso c on d.id=c.institucioneducativa_id
	// 	        inner join jurisdiccion_geografica e on d.le_juridicciongeografica_id=e.id
	// 	        inner join (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) f on e.lugar_tipo_id_distrito=f.id
	// 	        INNER JOIN departamento_tipo j on j.id=CAST(substring(f.cod_dis,1,1) as INTEGER)
	// 	where d.dependencia_tipo_id in (1,2,3) --unidad educativa fiscal
	// 	and LENGTH(d.id::VARCHAR) >= 8
	// 	and d.id in('.$ueTmp.')
	// 	and j.id '.$operadorDepartamento.' ?
	// 	and f.cod_dis '.$operadorDistrito.' ?
	// 	and (c.gestion_tipo_id in ( '.$gestionArray.' )) 
	// 	and (c.nivel_tipo_id in ( '.$nivelArray.' )) 
	// 	and (c.grado_tipo_id  in ( '.$gradoArray.' )) '
	// 	.$whereUE.
	// 	'order by j.id, f.cod_dis asc';

    //     $stmt = $db->prepare($query);
    //     $params = array($departamento,$distrito);
    //     $stmt->execute($params);
    //     $ue_validas=$stmt->fetchAll();
    //     if($ue_validas)
    //     	$tieneErrores= false;
    //     return array($tieneErrores,$ue_validas);
	// }

    // private function listarUECumplenRequerimientos($gestionArray,$nivelArray,$gradoArray)
	// {
	// 	$lenGestionArray = count($gestionArray);
	// 	$lenNivelArray = count($nivelArray);
	// 	$lenGradoArray = count($gradoArray);
	// 	$query = '';
	// 	if(count($gestionArray)>0 && count($nivelArray)>0 && count($gradoArray)>0 && $lenGestionArray == $lenNivelArray && $lenNivelArray == $lenGradoArray)
	// 	{
	// 		for($i=0;$i<$lenGestionArray;$i++)
	// 		{
	// 			$query.= "select institucioneducativa_id from institucioneducativa_curso where gestion_tipo_id='".$gestionArray[$i]."' and nivel_tipo_id='".$nivelArray[$i]."' and grado_tipo_id='".$gradoArray[$i]."'";
	// 			if($lenGestionArray>1 && $i<$lenGestionArray-1)
	// 			{
	// 				$query.=' union all ';
	// 			}
	// 		}
	// 	}
	// 	/*if(strlen($query)>0)
	// 	{
	// 		$em = $this->getDoctrine()->getManager();
	// 		$db = $em->getConnection();
	// 		$stmt = $db->prepare($query);
	// 		//$params = array($userId,$userRol);
	// 		$stmt->execute();
	// 		$ues=$stmt->fetch();
	// 	}*/

	// 	return $query;
	// }

    // private function renombrarDirectorio($path,$tramite_id_tmp,$tramite_id)
	// {
	// 	$directorioNameOld = $this->get('kernel')->getRootDir() .$path.'/'.$tramite_id_tmp;
	// 	$directorioNameNew = $this->get('kernel')->getRootDir() .$path.'/'.$tramite_id;
	// 	return rename($directorioNameOld ,$directorioNameNew);
	// }

    // private function obtenerNombresYValidarDatosDeInscripciones($inscripciones)
	// {
	// 	$datosInscripciones_procesada = array();
	// 	$tieneErrores = false;
	// 	if($inscripciones)
	// 	{
	// 		$tmpInscripciones=$inscripciones;
	// 		foreach ($tmpInscripciones as $inscripcion)
	// 		{
	// 			//if( property_exists($inscripcion,'id') && property_exists($inscripcion,'gestion') && property_exists($inscripcion,'nivel') && property_exists($inscripcion,'grado') && property_exists($inscripcion,'paralelo') && property_exists($inscripcion,'turno') && property_exists($inscripcion,'materias') )
	// 			if( $inscripcion && property_exists($inscripcion,'id') && property_exists($inscripcion,'nivel') && property_exists($inscripcion,'grado') )
	// 			{
	// 				$nivel = null;
	// 				$grado = null;
	// 				try
	// 				{
	// 					$tmpGestion 	= filter_var($inscripcion->gestion,FILTER_SANITIZE_NUMBER_INT);
	// 					$tmpNivel 		= filter_var($inscripcion->nivel,FILTER_SANITIZE_NUMBER_INT);
	// 					$tmpGrado 		= filter_var($inscripcion->grado,FILTER_SANITIZE_NUMBER_INT);

	// 					list($dataNivel,$statusNivel,$msjNivel) = 	$this->obtenerNiveles();
	// 					list($dataGrado,$statusGrado,$msjGrado) = 	$this->obtenerGrados($tmpNivel,$tmpGrado);

	// 					$nivel = json_decode($dataNivel);
	// 					$grado = json_decode($dataGrado);

	// 					if($nivel && $grado && property_exists($nivel,'niveles') && property_exists($grado,'grados') )
	// 					{
	// 						//obtenemos el primer y unico elemento de nivel y grado
	// 						$nivel=$nivel->niveles;
	// 						$grado=$grado->grados;
	// 						if(count($nivel)==1 && count($grado)==1 )
	// 						{
	// 							$nivel=$nivel[0];
	// 							$grado=$grado[0];
	// 							if($nivel && $grado && property_exists($nivel,'nivel') && property_exists($grado,'grado'))
	// 							{
	// 								$tmp=array();
	// 								$tmp=array(
	// 									'id'		=> $inscripcion->id,
	// 									'nivel' 	=> $nivel->nivel,
	// 									'grado' 	=> $grado->grado,
	// 								);
	// 								$datosInscripciones_procesada[]=$tmp;
	// 							}
	// 							else
	// 							{
	// 								$datosInscripciones_procesada= array();
	// 								$tieneErrores = true;
	// 								break;
	// 							}
	// 						}
	// 						else
	// 						{
	// 							$datosInscripciones_procesada= array();
	// 							$tieneErrores = true;
	// 							break;
	// 						}
	// 					}
	// 					else
	// 					{
	// 						$datosInscripciones_procesada= array();
	// 						$tieneErrores = true;
	// 						break;
	// 					}
	// 				}
	// 				catch(Exception $e)
	// 				{
	// 					$datosInscripciones_procesada= array();
	// 					$tieneErrores = true;
	// 					break;
	// 				}
	// 			}
	// 			else
	// 			{
	// 				$datosInscripciones_procesada= array();
	// 				$tieneErrores = true;
	// 				break;
	// 			}
	// 		}
	// 	}
	// 	else
	// 	{
	// 		$tieneErrores = false;
	// 	}
	// 	return array($tieneErrores,json_decode(json_encode($datosInscripciones_procesada)));
	// }
    
    private function obtenerNombresDeInscripciones($inscripciones)
	{
		$em = $this->getDoctrine()->getManager();
        $datosInscripciones_procesada = array();
		$tieneErrores = false;
		if($inscripciones)
		{
			$tmpInscripciones=$inscripciones;
			foreach ($tmpInscripciones as $inscripcion)
			{
				if( $inscripcion && property_exists($inscripcion,'id') && property_exists($inscripcion,'nivel') && property_exists($inscripcion,'grado') )
				{
					$nivel = null;
					$grado = null;
					try
					{   
						$tmpNivel 		= filter_var($inscripcion->nivel,FILTER_SANITIZE_NUMBER_INT);
						$tmpGrado 		= filter_var($inscripcion->grado,FILTER_SANITIZE_NUMBER_INT);

                        $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->find($tmpNivel);
                        $grado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($tmpGrado);
                        
						if($nivel && $grado )
						{
							//obtenemos el primer y unico elemento de nivel y grado
							$nivel=$nivel->getNivel();
							$grado=$grado->getGrado();

							if(count($nivel)==1 && count($grado)==1 )
							{
								$datosInscripciones_procesada[]=array(
									'id'		=> $inscripcion->id,
									'nivel' 	=> $nivel,
									'grado' 	=> $grado,
								);
							}
							else
							{
								$datosInscripciones_procesada= array();
								$tieneErrores = true;
								break;
							}
						}
						else
						{
							$datosInscripciones_procesada= array();
							$tieneErrores = true;
							break;
						}
					}
					catch(Exception $e)
					{
						$datosInscripciones_procesada= array();
						$tieneErrores = true;
						break;
					}
				}
				else
				{
					$datosInscripciones_procesada= array();
					$tieneErrores = true;
					break;
				}
			}
		}
		else
		{
			$tieneErrores = false;
		}
		return $datosInscripciones_procesada;
	}

    private function verificarasignacionUe($institucioneducativa_id, $inscripciones)
	{   
		$em = $this->getDoctrine()->getManager();
        $gestion=$this->session->get('currentyear');

        $conditions = [];

        $tmpInscripciones=$inscripciones;
        // dump($tmpInscripciones);
		foreach ($tmpInscripciones as $inscripcion)
		{
            $condition = " (ic.nivel_tipo_id = ".$inscripcion->nivel." and ic.grado_tipo_id = ". $inscripcion->grado .") ";
            $conditions[] = $condition;
        }
        $whereClause = implode(" OR ", $conditions);
        $query = $em->getConnection()->prepare("select distinct ic.gestion_tipo_id, ic.institucioneducativa_id, ic.nivel_tipo_id, ic.grado_tipo_id 
                    from institucioneducativa_curso ic 
                    where ic.gestion_tipo_id = ".$gestion." 
                    and ic.institucioneducativa_id = ".$institucioneducativa_id."
                    and ( ".$whereClause." );");
        $query->execute();
        $cursos = $query->fetchAll();

        $ue_asignada = false;

        if (count($tmpInscripciones) == count($cursos)){
            $ue_asignada = true;
        } 
        return $ue_asignada;        
    }
    /**
	 * Esta funcion devuelve UNA SOLICITUD que recien paso por la departamental
	 *
	 **/
	public function solicitudesRecibidasDistritalAction(Request $request)
	{   
        $em 		= $this->getDoctrine()->getManager();
		$db 		= $em->getConnection();
		$form 		= $request->request->all();
        
		$request_id = $request->get('id');
		$request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
		$request_id = is_numeric($request_id)?$request_id:-1;
        
		$request_tipo = $request->get('tipo');
		$request_tipo = filter_var($request_tipo,FILTER_SANITIZE_STRING);
        
        
        // dump($request);die;
		$tramite_id = $request_id;
		$tramite_id = is_numeric($tramite_id)?$tramite_id:-1;
		$error 		= NULL;
		$tablaInscripciones = array();
		$adjuntosDocsInteresado = array();
		$adjuntosInformes = array();

		//Verificamos que el id de trámite haya sido enviado, caso contrario redireccionamos a la anteriro pagina
		if($tramite_id>0)
		{
			//Buscamos el trámite segun el id enviado
			$tramite =$this->getTramite($tramite_id);

			//verificamos que el trámite exista
			if($tramite)
			{
				$datosJSONInscripcion = $tramite['datos'];

				if(strlen($datosJSONInscripcion)>0)//verificamos que el JSon existe
				{
					try
					{
						$datosJSONInscripcion_decode=json_decode($datosJSONInscripcion);
						$rude=property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;
                        $estudiante = $datosJSONInscripcion_decode->estudiante;

						//verificamos que exista el estudiante
						if($estudiante)
						{
							if( $datosJSONInscripcion_decode && property_exists($datosJSONInscripcion_decode,'inscripciones'))
							{
								//Una vez verificado que el estudiante exista obtenemos los datos json de la inscripciones, documentos adjuntos delinteresado y el primer informe de la departamental
								$inscripciones = $datosJSONInscripcion_decode->inscripciones;
								
								$cantidadInscripciones= count($inscripciones);
								$tablaInscripciones=array();
								$tablaInscripciones = $this->obtenerNombresDeInscripciones($datosJSONInscripcion_decode->inscripciones);
								//continuamos con los documentos del interesado
								$adjuntosDocsInteresado = $datosJSONInscripcion_decode->docsInteresado;
								//terminamos con los informes
								$adjuntosInformes 		= $datosJSONInscripcion_decode->informes;
                                //terminamos con los informes
                                $ueasignada = $this->verificarasignacionUe($estudiante->institucioneducativa_id,$datosJSONInscripcion_decode->inscripciones);
                                
							}
							else
							{
								$error = 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
							}
						}
						else
						{
							$error='No se puede encontrar al estudiante, por favor verifique que el trámite fue realizado correctamente.';
						}
					}
					catch(Exception $e)
					{
						$error = 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
					}
				}
				else
				{
					$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
				}
			}
			else
			{
				$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
			}
            // dump($estudiante);die;

			return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:distrital_detalleSolicitudesRecibidas.html.twig',array(
				'estudiante'=>$estudiante,
				'path' => $this->path,
				'error' => $error,
				'tablaInscripciones' => $tablaInscripciones,
                'ueasignada' => $ueasignada,
				'adjuntosDocsInteresado' => $adjuntosDocsInteresado,
				'adjuntosInformes' => $adjuntosInformes,
				"tramite" => $tramite_id,
			));
		}
		else
		{
			dump('ver otra opcion');die;
            //return $this->redirect($this->generateUrl('regularizacion_estudiantesPostBachillerato_getSolicitudeRecibidasDistrital'));
		}
	}
    
    private function getTramite($tramite_id)
	{
		$em 		= $this->getDoctrine()->getManager();
		$db 		= $em->getConnection();

		$tramite= null;
		$tramite_id=is_numeric($tramite_id)?$tramite_id:-1;
		$query = '
			select t1.id as tramite_id, t2.id as tramite_detalle_id, t3.id as solicitud_tramite, t3.datos,t1.flujo_tipo_id,t1.tramite_tipo,to_char(t1.fecha_tramite,\'DD/MM/YYYY\') as fecha_tramite
			from tramite t1
			INNER JOIN tramite_detalle t2 on  (t2.id::INT) = (t1.tramite::INT)
			INNER JOIN wf_solicitud_tramite t3 on (t3.tramite_detalle_id::INT)=(t2.tramite_detalle_id::INT)
			--INNER JOIN wf_solicitud_tramite t3 on (t3.tramite_detalle_id::INT)=(t2.id::INT)
			where t1.id=?
			limit 1';
		$stmt = $db->prepare($query);
		$params = array($tramite_id);
		$stmt->execute($params);
		$tramite=$stmt->fetch();
		return $tramite;
	}

    /**
	 * Esta funcion recupera una lista de UE validas, es decir aquellas que tengan la gestion, el nivel y grado requeridos
	 *
	 **/
	public function buscaListaUEValidasAction(Request $request)
	{
		$form = $request->request->all();
		$request_tramite = filter_var($form['tramite'],FILTER_SANITIZE_NUMBER_INT);
		$request_tramite = is_numeric($request_tramite)?$request_tramite:-1;
		$trámite 	= null;
		$data 		= NULL;
		$status 	= 404;
		$msj 		= NULL;
		
		if($request_tramite>0)
		{
			//Buscamos el trámite segun el id enviado
			$tramite_id=$request_id;
			
			$tramite =$this->getTramite($request_tramite);
			//verificamos que el trámite exista
			if($tramite)
			{   
				//luego obtenemos el Json del trámite
				$datosJSONInscripcion = $tramite['datos'];
				if(strlen($datosJSONInscripcion)>0)//verificamos que el JSon existe
				{
					try
					{
						$datosJSONInscripcion_decode=json_decode($datosJSONInscripcion);
						if( $datosJSONInscripcion_decode && property_exists($datosJSONInscripcion_decode,'inscripciones'))
						{
							$inscripciones = $datosJSONInscripcion_decode->inscripciones;
							// if($cantidadInscripciones>0 && $cantidadInscripciones<=2)
							// {
								$gestionArray=array();
								$nivelArray=array();
								$gradoArray=array();
                                
								// list($gestionArray,$nivelArray,$gradoArray) = $this->formatearGestionNivelGrado($inscripciones);
								if(count($inscripciones)>0)
								{
									$departamento=-1;
									$distrito=-1;
									
									list($departamento,$distrito) = $this->obtenerDepartamentoDistritoUsuario();
                                    list($tieneErrores,$unidades_educativas)= $this->listarUEValidas($departamento,$distrito,$inscripciones);
									if(!$tieneErrores)
									{
                                        $data=json_encode(array('unidades_educativas'=>$unidades_educativas));
										$status = 200;
									}
									else
									{
                                        $msj='No xisten unidades educativas con los requerimientos del trámite';
									}
								}
								else
								{
									$msj='El trámite no es valido';
								}
							// }
							// else
							// {
							// 	$msj='El trámite no es valido';
							// }
						}
						else
						{
							$msj='El trámite no tiene materias';
						}
					}
					catch(Exception $e)
					{
						$msj='No existe datos validos';
					}
				}
				else
				{
					$msj='El trámite no existe';
				}
			}
			else
			{
				$msj='El trámite no existe';
			}
		}
		else
		{
			$msj='El trámite no existe';
		}

		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,'status'=>$status,'data'=>$data));
	}

    private function listarUEValidas($departamento,$distrito,$inscripciones)
	{
		// $ueTmp = $this->listarUECumplenRequerimientos($gestionArray,$nivelArray,$gradoArray);
        $gestion=$this->session->get('currentyear');
        $em = $this->getDoctrine()->getManager();
        // $db = $em->getConnection();

        $tmpInscripciones=$inscripciones;

		foreach ($tmpInscripciones as $inscripcion)
		{
            $nivel_wr[] = $inscripcion->nivel;
            $grado_wr[] = $inscripcion->grado;
        }
        $whereClauseNivel = implode(",", $nivel_wr);
		$whereClauseGrado = implode(",", $grado_wr);

        $tieneErrores = true;
        $ue_validas = array();
        $query = $em->getConnection()->prepare("
		select DISTINCT d.id ue_id,d.institucioneducativa ue_nombre, j.id dept_id, j.departamento dept_nombre, f.cod_dis, f.des_dis
		--, c.gestion_tipo_id, c.nivel_tipo_id,c.grado_tipo_id
		from  institucioneducativa d
				inner join institucioneducativa_curso c on d.id=c.institucioneducativa_id
		        inner join jurisdiccion_geografica e on d.le_juridicciongeografica_id=e.id
		        inner join (select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo where lugar_nivel_id=7) f on e.lugar_tipo_id_distrito=f.id
		        INNER JOIN departamento_tipo j on j.id=CAST(substring(f.cod_dis,1,1) as INTEGER)
		where d.dependencia_tipo_id = 1 --unidad educativa fiscal
		and j.id = ".$departamento." 
		and f.cod_dis = '".$distrito."'
		and (c.gestion_tipo_id in ( ".$gestion." )) 
		and (c.nivel_tipo_id in (".$whereClauseNivel."))
		and (c.grado_tipo_id in (".$whereClauseGrado.")) 
		order by j.id, f.cod_dis asc");
		$query->execute();
		$ue_validas = $query->fetchAll();
        if($ue_validas)
        	$tieneErrores= false;
        return array($tieneErrores,$ue_validas);
	}

	public function regularizacionBuscarUEAction(Request $request)
	{
		$em 		= $this->getDoctrine()->getManager();
		$form 		= $request->request->all();
		$sie 		= $form['sie'];
		$sieTmp 	= filter_var($sie,FILTER_SANITIZE_NUMBER_INT);
		$sieTmp 	= is_numeric($sieTmp)?$sieTmp:-1;
		$nombreUE 	= null;
		$status 	= 404;
		$ue 		= $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sieTmp);

		if($ue)
		{
			$nombreUE = $ue->getInstitucioneducativa();
			$status=200;
		}
		else
		{
			$nombreUE = "";
			$status=404;
		}
		$response = new JsonResponse($data=null,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('nombreUE'=>$nombreUE,'status'=>$status));
	}

	public function enviarDddSolicitudUeAction(Request $request)
	{	
		$em 		= $this->getDoctrine()->getManager();
		$db 		= $em->getConnection();
		$form = $request->request->all();
		
		$tramite_id 			= filter_var($form['request_tramite'],FILTER_SANITIZE_NUMBER_INT);
		$sie 					= filter_var($form['request_sie'],FILTER_SANITIZE_NUMBER_INT);
		
		$tramite_id 			= is_numeric($tramite_id)?$tramite_id:-1;
		$sie 					= is_numeric($sie)?$sie:-2;
		$gestion				= $this->session->get('currentyear');
		
		$request_fileInforme 	= $_FILES["request_fileInforme"];
		
		//Buscamos el trámite apartir del codigo de trámite enviado en la peticion
		$tramite = $this->getTramite($tramite_id);
		$institucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
		
		//verificamos que exista el trámite y la unidad educativa
		if( $sie>0 && $tramite && $institucionEducativa )
		{
			//luego obtenemos el Json del trámite
			$datosJSONInscripcion = $tramite['datos'];
			
			//decodificamos el json para volverlo en objeto y poder procesarlos
			$datosJSONInscripcion_decode=json_decode($datosJSONInscripcion);
			
			$estudianteData=property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;
			// dump($estudianteData);
			$rude = $estudianteData->codigo_rude;
			$sieAnt = $estudianteData->institucioneducativa_id;
			// dump($sieAnt);
			$qb = $em->createQueryBuilder();
			$estudiante=$qb->select('e')
			->from('SieAppWebBundle:Estudiante', 'e')
			->where('e.codigoRude = :codigoRude')
			->setParameters(array('codigoRude'=>$rude))
			->getQuery()
			->getSingleResult();
			
			//verificamos que el JSon existe
			if(strlen($datosJSONInscripcion)>0)
			{
				//verificamos que el informe haya sido subido
				if(isset($request_fileInforme))
				{
					$nArcInforme = $this->guardarArch($sieAnt,$rude,$gestion,'DDDINF', $request_fileInforme);
					try //al decodificar la cadena JSON nos aseguramos que sea correcta
					{	
						
						//Realizamos la verificacion de si el archivo fue guardado
						
						if(isset($nArcInforme))
						{
							// $datosJSONInscripcion_decode->informes['informe_ddd'] = $nArcInforme;
							$datosJSONInscripcion_decode->informes->informe_ddd = $nArcInforme;
							
							$inscripciones = $datosJSONInscripcion_decode->inscripciones;
							$sieAsingada = $sieAnt;
							$ueasignada = $this->verificarasignacionUe($sieAnt, $inscripciones);
							
							if (!$ueasignada){
								$ueasignada = $this->verificarasignacionUe($sie, $inscripciones);
								$sieAsingada = $sie;
							}
							if($ueasignada)
							{
								list($departamento,$distrito) = $this->obtenerDepartamentoDistritoUsuario();
								// list($tieneErrores,$unidad_educativa)= $this->listarUEValidas($departamento,$distrito,$gestionArray,$nivelArray,$gradoArray,$sie);
								
								if(isset($sieAsingada))
								{	
									//añadimos el campo sie a la cadena JSON
									$datosJSONInscripcion_decode->sie=$sieAsingada;
									$institucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sieAsingada);
									// dump($datosJSONInscripcion_decode);die;
									$flujoTipo=$tramite['flujo_tipo_id'];//Solicitud de regularización
									//$tarea = $this->get('wftramite')->obtieneTarea($flujoTipo, 'idflujo');
									$tarea = $this->get('wftramite')->obtieneTarea($tramite['tramite_id'], 'idtramite');
									
									$tareaActual = $tarea['tarea_actual'];
									$tareaSiguiente = $tarea['tarea_siguiente'];
									$idTramite=$tramite['tramite_id'];

									$gestion=$this->session->get('currentyear');
									$lugarTipo = $this->get('wftramite')->lugarTipoUE($sieAsingada, $gestion);

									//volvemos a codificar la cadena JSON y actualizamos el campo del trámite

									//se utilizao esto para asignarle al tramite la undiad educativa
									$tramiteArtificio 	= $em->getRepository('SieAppWebBundle:Tramite')->find($idTramite);
									$tramiteArtificio->setInstitucioneducativa($institucionEducativa);
									$em->persist($tramiteArtificio);
									$em->flush();
									

									$registroTramite = $this->get('wftramite')->guardarTramiteEnviado(
										$this->session->get('userId'),										//$usuario,
										$this->session->get('roluser'),										//$rol,
										$flujoTipo,															//$flujotipo,
										$tareaActual,														//$tarea,
										'institucioneducativa',												//$tabla,
										$sie,																//$id_tabla,
										'',																	//$observacion,
										'',																	//$varevaluacion,
										$idTramite,															//$idtramite,
										json_encode($datosJSONInscripcion_decode, JSON_UNESCAPED_UNICODE),	//$datos,
										'',																	//$lugarTipoLocalidad_id,
										$lugarTipo['lugarTipoIdDistrito']									//$lugarTipoDistrito_id
									);
									
									if($registroTramite)
									{
										if($registroTramite['dato']===true && $registroTramite['tipo']==='exito')
										{
											//dump(json_encode($datosJSONInscripcion));
											$data 	= $registroTramite['idtramite'];
											$status = 200;
											$msj 	= $registroTramite['msg'];
										}
										else
										{
											$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
											// $this->borrarArchivo($this->path,$nombre,$tramite_id);
											$data 	= NULL;
											$status = 404;
											$msj 	= $registroTramite['msg'];
										}
									}
									else
									{
										$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
										$data 	= NULL;
										$status = 404;
										$msj 	= 'Ocurrio un error al guardar los archivos adjuntos, por favor vuelva a intentarlo.';
									}

								}
								else
								{
									//Esto ocurria si los archivos no pudieron ser guardados
									//por seguridad borramos los archivos,
									$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
									$data 	= NULL;
									$status = 404;
									$msj 	= 'Ocurrio un error debido a que la unidad educativa no cumple los requisitos requeridos o posiblemente no pertenece a su distrito.';
								}
							}
							else
							{
								//Esto ocurria si los archivos no pudieron ser guardados
								//por seguridad borramos los archivos,
								$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
								$data 	= NULL;
								$status = 404;
								$msj 	= 'Ocurrio un error debido a que la cantidad de inscripciones no es correcta.';
							}
						}
						else
						{
							//Esto ocurria si los archivos no pudieron ser guardados
							//por seguridad borramos los archivos,
							$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
							$data 	= NULL;
							$status = 404;
							$msj 	= 'Ocurrio un error al guardar el archivo adjunto, por favor vuelva a intentarlo.';
						}
					}
					catch(Excepción $e)
					{
						//Esto ocurria si los archivos no pudieron ser guardados
						$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
						$data 	= NULL;
						$status = 404;
						$msj 	= 'Ocurrio un error al guardar el registro, por favor vuelva a intentarlo.';
					}
				}
				else
				{
					//Esto ocurrira si no fueron enviados los archivos del formulario
					$data 	= NULL;
					$status = 404;
					$msj 	= 'Ocurrio un error debido a que el archivo adjunto no fue subido, por favor vuelva a intentarlo.';
				}
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
			}
		}
		else
		{
			$data 	= NULL;
			$status = 404;
			$msj 	= 'Ocurrio un error debido a que no existe el trámite seleccionado o la unidad educativa no existe, por favor vuelva a intentarlo.';
		}

		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,'status'=>$status,'data'=>$data));
	}

	public function solicitudesRecibidasUEAction(Request $request)
	{	
		$em 					= $this->getDoctrine()->getManager();
		$form 					= $request->request->all();
		$tramite_id				= isset($form['tramite_id'])?$form['tramite_id']:-1;
		$error					= NULL;
		$tablaInscripciones		= array();
		$adjuntosDocsInteresado = array();
		$adjuntosInformes		= array();
		
		
		$request_id = $request->get('id');
		$request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
		$request_id = is_numeric($request_id)?$request_id:-1;
		
		$request_tipo = $request->get('tipo');
		$request_tipo = filter_var($request_tipo,FILTER_SANITIZE_STRING);
		
		$tramite_id=$request_id;
		if($tramite_id>0)
		{
			//Buscamos el trámite segun el id enviado
			//verificamos que el trámite exista
			$tramite =$this->getTramite($tramite_id);
			if($tramite)
			{
				$datosJSONInscripcion = $tramite['datos'];
				if(strlen($datosJSONInscripcion)>0)//verificamos que el JSon existe
				{
					try
					{
						//intentamos decodificar la cadena
						$datosJSONInscripcion_decode=json_decode($datosJSONInscripcion);
						// dump($datosJSONInscripcion_decode);die;
						$estudiantejson=property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;
						$rude = $estudiantejson->codigo_rude;
						// dump($rude);die;
						$qb = $em->createQueryBuilder();
						// $estudiante=$qb->select('e')
						//  ->from('SieAppWebBundle:Estudiante', 'e')
						//  ->where('e.codigoRude = :codigoRude')
						//  ->setParameters(array('codigoRude'=>$rude))
						//  ->getQuery()
						//  ->getSingleResult();
						$estudiante = $datosJSONInscripcion_decode->estudiante;
						//verificamos que exista el estudiante
						if($estudiante)
						{
							if( $datosJSONInscripcion_decode && property_exists($datosJSONInscripcion_decode,'inscripciones'))
							{
								$inscripciones = $datosJSONInscripcion_decode->inscripciones;
								//Una vez verificado que el estudiante exista obtemos los datos json de la inscripciones, documentos adjuntos delinteresado y el primer informe de la departamental

								//empezamos con las inscripciones
								//Ahora procedemos a procesar los datos
								$inscripcionesDecode 	= $datosJSONInscripcion_decode;//json_decode($inscripciones);

								$tablaInscripciones 	= array();
								$tablaInscripciones = $this->obtenerNombresDeInscripciones($datosJSONInscripcion_decode->inscripciones);
							}
							else
							{
								$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
							}
						}
						else
						{
							$error='No se puede encontrar al estudiante, por favor verifique que el trámite fue realizado correctamente.';
						}
					}
					catch(Exception $e)
					{
						$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
					}
				}
				else
				{
					$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
				}
			}
			else
			{
				$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
			}
			// dump($this->path);die;
			return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:ue_detalleSolicitudesRecibidas.html.twig',array(
				'estudiante'=>$estudiante,
				'path' => $this->path,
				'error' => $error,
				'tablaInscripciones' => $tablaInscripciones,
				// 'adjuntosDocsInteresado' => $adjuntosDocsInteresado,
				// 'adjuntosInformes' => $adjuntosInformes,
				"tramite" => $tramite_id,
			));
		}
		else
		{
			return $this->redirectToRoute('wf_tramite_index', [
                'tipo' => 2,
            ]);
		}
	}

	public function getDetalleInscripcionAction(Request $request)
	{	
		$em 				= $this->getDoctrine()->getManager();
		$qb 				= $em->createQueryBuilder();
		$db 				= $em->getConnection();

		$form 				= $request->request->all();
		$_tramite_id 		= isset($form['tramite'])? 	filter_var($form['tramite'], FILTER_SANITIZE_NUMBER_INT ):-1;
		$_inscripcion_id 	= isset($form['id'])?		filter_var($form['id'], FILTER_SANITIZE_NUMBER_INT ):-1;

		$inscripcion_retornar = null;
		$paralelos = array();
		$turnos= array();
		$materias = array();

		$data	= null;
		$status	= 404;
		$msj	= null;

		if($_tramite_id >0 && $_inscripcion_id>0)
		{
			$tramite_id=$_tramite_id;
			//Buscamos el trámite apartir del codigo de trámite enviado en la peticion
			$tramite= $this->getTramite($tramite_id);
			//verificamos que exista el trámite
			if($tramite)
			{
				//ahora obtenemos el campo json del trámite
				$datosJSONInscripcion=$tramite['datos'];
				//verificamos que el JSon existe
				if(strlen($datosJSONInscripcion)>0)
				{
					try //al decodificar la cadena JSON nos aseguramos que sea correcta
					{
						//decodificamos el json para volverlo en objeto y poder procesarlos
						$datosJSONInscripcion_decode 	= json_decode($datosJSONInscripcion);
						$inscripciones 					= $datosJSONInscripcion_decode->inscripciones;
						$sie 							= $datosJSONInscripcion_decode->sie;
						
						foreach ($inscripciones as $i)
						{
							if($i->id == $_inscripcion_id)
							{
								$inscripcion_retornar = $i;
								break;
							}
						}
						if($inscripcion_retornar != null && property_exists($inscripcion_retornar,'nivel') && property_exists($inscripcion_retornar,'grado'))
						{
							//sanitizamos los datos
							// $gestion = filter_var($inscripcion_retornar->gestion,FILTER_SANITIZE_NUMBER_INT);
							$gestion=$this->session->get('currentyear');
							$nivel = filter_var($inscripcion_retornar->nivel,FILTER_SANITIZE_NUMBER_INT);
							$grado = filter_var($inscripcion_retornar->grado,FILTER_SANITIZE_NUMBER_INT);

							$gestion = is_numeric($gestion)?$gestion:-1;
							$nivel = is_numeric($nivel)?$nivel:-1;
							$grado = is_numeric($grado)?$grado:-1;
							//Obtener el curso oferta
							$query = ' 
								select id as id
								from institucioneducativa_curso 
								where 
								institucioneducativa_id = ?
								and gestion_tipo_id = ?
								and nivel_tipo_id = ?
								and grado_tipo_id = ?
								limit 1
							';
							$stmt = $db->prepare($query);
							$params = array($sie,$gestion,$nivel,$grado);
							$stmt->execute($params);
							$cursoOferta = $stmt->fetch();
							$cursoOferta = isset($cursoOferta['id'])?$cursoOferta['id']:-1;
							
							
							//obtenemos los ID de materias
							$query = 'select string_agg(asignatura_tipo_id::VARCHAR,\',\') as ids_materias from institucioneducativa_curso_oferta where insitucioneducativa_curso_id =? ';
							$stmt = $db->prepare($query);
							$params = array($cursoOferta);
							$stmt->execute($params);
							$idMaterias = $stmt->fetch();
							$idMaterias = isset($idMaterias['ids_materias'])?$idMaterias['ids_materias']:-1;
							
							//obtenemos los paralelos y turnos en base a la inscripcion recibida (en base a gestion, nivel y grado)
							$query = ' 
								select string_agg(paralelo_tipo_id,\'|\') as paralelos
								from institucioneducativa_curso 
								where 
								institucioneducativa_id = ?
								and gestion_tipo_id = ?
								and nivel_tipo_id = ?
								and grado_tipo_id = ?
							';
							$stmt = $db->prepare($query);
							$params = array($sie,$gestion,$nivel,$grado);
							$stmt->execute($params);
							$requisitos=$stmt->fetch();
							
							
							$paralelosTmp= $requisitos['paralelos'];
							if(isset($paralelosTmp))
							{
								$status 	= 200;
								$msj 		= 'Ok';

								$query = $em->createQuery(
								        'SELECT DISTINCT tt.id,tt.turno
								        FROM SieAppWebBundle:InstitucioneducativaCurso iec
								        JOIN iec.institucioneducativa ie
								        JOIN iec.turnoTipo tt
								        WHERE ie.id = :id
								        AND iec.gestionTipo = :gestion
								        ORDER BY tt.id')
								        ->setParameter('id', $sie)
								        ->setParameter('gestion', $gestion);
								$turnos = $query->getResult();
								
								$data = json_encode(array(
									'inscripcion' => $inscripcion_retornar,
									// 'paralelos'=> $paralelos,
									'turnos'=> $turnos,
									// 'materias'=> $materias,
								));
							}
							else
							{
								$data 	= NULL;
								$status = 404;
								$msj 	= 'No existen paralelos con los requisitos requeridos por la inscripción.';
							}
						}
						else
						{
							$data 	= NULL;
							$status = 404;
							$msj 	= 'No se encontro la inscripción seleccionada.';
						}
					}
					catch(Excepción $e)
					{
						//Por si se produjera un error
						$data 	= NULL;
						$status = 404;
						$msj 	= 'Ocurrio un error al obtener el registro, por favor vuelva a intentarlo.';
					}
				}
				else
				{
					$data 	= NULL;
					$status = 404;
					$msj 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
				}
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'Ocurrio un error debido a que no existe el trámite seleccionado, por favor vuelva a intentarlo.';
			}
		}
		else
		{
			$data 	= NULL;
			$status = 404;
			$msj 	= 'Ocurrio un error debido a que no existe el trámite o la inscripción seleccionada no existe, por favor vuelva a intentarlo.';
		}
		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,'status'=>$status,'data'=>$data));
	}

	public function getTurnosApartirDeParaleloAction(Request $request)//NUEVA FUNCION
	{
		$em 				= $this->getDoctrine()->getManager();
		$qb 				= $em->createQueryBuilder();
		$db 				= $em->getConnection();

		$form 				= $request->request->all();
		$_tramite_id 		= filter_var($form['tramite'], FILTER_SANITIZE_NUMBER_INT);
		$_inscripcion_id 	= filter_var($form['id'], FILTER_SANITIZE_NUMBER_INT);
		$_paralelo_id 		= filter_var($form['paralelo'], FILTER_SANITIZE_NUMBER_INT );

		$_tramite_id 		= is_numeric($_tramite_id)?$_tramite_id:-1;
		$_inscripcion_id 	= is_numeric($_inscripcion_id)?$_inscripcion_id:-1;
		$_paralelo_id 		= is_numeric($_paralelo_id)?$_paralelo_id:-1;

		$inscripcion_retornar = null;
		$paralelos = array();

		$data	= null;
		$status	= 404;
		$msj	= null;

		//Buscamos el trámite apartir del codigo de trámite enviado en la peticion
		if($_tramite_id >0 && $_inscripcion_id>0 && $_paralelo_id>=0)
		{
			$tramite_id=$_tramite_id;
			
			//verificamos que exista el trámite
			$tramite = $this->getTramite($tramite_id);
			if($tramite)
			{
				//ahora obtenemos el campo json del trámite
				$datosJSONInscripcion=$tramite['datos'];
				if(strlen($datosJSONInscripcion)>0)//verificamos que el JSon existe
				{
					//al decodificar la cadena JSON nos aseguramos que sea correcta
					try
					{
						//decodificamos el json para volverlo en objeto y poder procesarlos
						$datosJSONInscripcion_decode = json_decode($datosJSONInscripcion);
						$inscripciones = $datosJSONInscripcion_decode->inscripciones;
						$sie 		= $datosJSONInscripcion_decode->sie;
						
						foreach ($inscripciones as $i)
						{
							if($i->id == $_inscripcion_id)
							{
								$inscripcion_retornar = $i;
								break;
							}
						}
						if($inscripcion_retornar != null && property_exists($inscripcion_retornar,'nivel') && property_exists($inscripcion_retornar,'grado'))
						{
							//sanitizamos los datos
							$gestion=$this->session->get('currentyear');
							$nivel = filter_var($inscripcion_retornar->nivel,FILTER_SANITIZE_NUMBER_INT);
							$grado = filter_var($inscripcion_retornar->grado,FILTER_SANITIZE_NUMBER_INT);

							$gestion = is_numeric($gestion)?$gestion:-1;
							$nivel = is_numeric($nivel)?$nivel:-1;
							$grado = is_numeric($grado)?$grado:-1;

							//obtenemos los paralelos en base a la inscripcion recibida (en base a gestion, nivel y grado y turno)
							$query = ' 
								select string_agg(turno_tipo_id::VARCHAR,\'|\') as turnos
								from institucioneducativa_curso 
								where 
								institucioneducativa_id = ?
								and gestion_tipo_id = ?
								and nivel_tipo_id = ?
								and grado_tipo_id = ?
								and paralelo_tipo_id = ?
							';

							$stmt = $db->prepare($query);
							$params = array($sie,$gestion,$nivel,$grado,$_paralelo_id);
							$stmt->execute($params);
							$requisitos=$stmt->fetch();
							
							$turnosTmp= $requisitos['turnos'];
							if(isset($turnosTmp))
							{
								$status 	= 200;
								$msj 		= 'Ok';

								//en base a los los datos de la inscripcion encontrada, se buscara los turnos
								$qb 		= $em->createQueryBuilder();
								$turnos 	= $qb->select('t')
											 ->from('SieAppWebBundle:TurnoTipo', 't')
											 ->where('t.id in (:turnos)')
											 ->setParameter('turnos', array_values( explode('|',$turnosTmp) ))
											 ->getQuery()
											 ->getArrayResult();

								$data = json_encode(array(
									'turnos'=> $turnos,
								));
							}
							else
							{
								$data 	= NULL;
								$status = 404;
								$msj 	= 'No existen turnos con los requisitos requeridos por la inscripción.';
							}
						}
						else
						{
							$data 	= NULL;
							$status = 404;
							$msj 	= 'No se encontro la inscripción seleccionada1.';
						}
					}
					catch(Excepción $e)
					{
						//Por si se produjera un error
						$data 	= NULL;
						$status = 404;
						$msj 	= 'Ocurrio un error al obtener el registro, por favor vuelva a intentarlo.';
					}
				}
				else
				{
					$data 	= NULL;
					$status = 404;
					$msj 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
				}
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'Ocurrio un error debido a que no existe el trámite seleccionado, por favor vuelva a intentarlo.';
			}
		}
		else
		{
			$data 	= NULL;
			$status = 404;
			$msj 	= 'Ocurrio un error debido a que no existe el trámite o la inscripción seleccionada no existe, por favor vuelva a intentarlo.';
		}
		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,'status'=>$status,'data'=>$data));
	}

	public function getParalelosApartirDeTurnoAction(Request $request)//NUEVA FUNCION
	{
		$em 				= $this->getDoctrine()->getManager();
		$qb 				= $em->createQueryBuilder();
		$db 				= $em->getConnection();

		$form 				= $request->request->all();
		$_tramite_id 		= filter_var($form['tramite'], FILTER_SANITIZE_NUMBER_INT);
		$_inscripcion_id 	= filter_var($form['id'], FILTER_SANITIZE_NUMBER_INT);
		$_turno_id 		= filter_var($form['turno'], FILTER_SANITIZE_NUMBER_INT );
		$_tramite_id 		= is_numeric($_tramite_id)?$_tramite_id:-1;
		$_inscripcion_id 	= is_numeric($_inscripcion_id)?$_inscripcion_id:-1;
		$_turno_id 		= is_numeric($_turno_id)?$_turno_id:-1;
		
		$inscripcion_retornar = null;
		$paralelos = array();
		

		$data	= null;
		$status	= 404;
		$msj	= null;

		//Buscamos el trámite apartir del codigo de trámite enviado en la peticion
		if($_tramite_id >0 && $_inscripcion_id>0 && $_turno_id>=0)
		{
			$tramite_id=$_tramite_id;
			
			//verificamos que exista el trámite
			$tramite = $this->getTramite($tramite_id);
			
			if($tramite)
			{
				//ahora obtenemos el campo json del trámite
				$datosJSONInscripcion=$tramite['datos'];
				if(strlen($datosJSONInscripcion)>0)//verificamos que el JSon existe
				{
					//al decodificar la cadena JSON nos aseguramos que sea correcta
					try
					{
						//decodificamos el json para volverlo en objeto y poder procesarlos
						$datosJSONInscripcion_decode = json_decode($datosJSONInscripcion);
						$inscripciones = $datosJSONInscripcion_decode->inscripciones;
						$sie 		= $datosJSONInscripcion_decode->sie;
						foreach ($inscripciones as $i)
						{
							if($i->id == $_inscripcion_id)
							{
								$inscripcion_retornar = $i;
								break;
							}
						}
						if($inscripcion_retornar != null && property_exists($inscripcion_retornar,'nivel') && property_exists($inscripcion_retornar,'grado'))
						{
							//sanitizamos los datos
							$gestion=$this->session->get('currentyear');
							$nivel = filter_var($inscripcion_retornar->nivel,FILTER_SANITIZE_NUMBER_INT);
							$grado = filter_var($inscripcion_retornar->grado,FILTER_SANITIZE_NUMBER_INT);

							$gestion = is_numeric($gestion)?$gestion:-1;
							$nivel = is_numeric($nivel)?$nivel:-1;
							$grado = is_numeric($grado)?$grado:-1;

							$query = $em->createQuery(
							        'SELECT DISTINCT pt.id,pt.paralelo
							        FROM SieAppWebBundle:InstitucioneducativaCurso iec
							        JOIN iec.institucioneducativa ie
							        JOIN iec.paraleloTipo pt
							        WHERE ie.id = :id
							        AND iec.gestionTipo = :gestion
							        AND iec.turnoTipo = :turno
							        AND iec.nivelTipo = :nivel
							        AND iec.gradoTipo = :grado
							        ORDER BY pt.id')
							        ->setParameter('id', $sie)
							        ->setParameter('gestion', $gestion)
							        ->setParameter('turno', $_turno_id)
							        ->setParameter('nivel', $nivel)
							        ->setParameter('grado', $grado);
							$paralelos = $query->getResult();

							if($paralelos)
							{
								$status 	= 200;
								$msj 		= 'Ok';
								$data = json_encode(array(
									'paralelos'=> $paralelos,
								));
							}
							else
							{
								$data 	= NULL;
								$status = 404;
								$msj 	= 'No existen paralelos con los requisitos requeridos por la inscripción.';
							}
						}
						else
						{
							$data 	= NULL;
							$status = 404;
							$msj 	= 'No se encontro la inscripción seleccionada1.';
						}
					}
					catch(Excepción $e)
					{
						//Por si se produjera un error
						$data 	= NULL;
						$status = 404;
						$msj 	= 'Ocurrio un error al obtener el registro, por favor vuelva a intentarlo.';
					}
				}
				else
				{
					$data 	= NULL;
					$status = 404;
					$msj 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
				}
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'Ocurrio un error debido a que no existe el trámite seleccionado, por favor vuelva a intentarlo.';
			}
		}
		else
		{
			$data 	= NULL;
			$status = 404;
			$msj 	= 'Ocurrio un error debido a que no existe el trámite o la inscripción seleccionada no existe, por favor vuelva a intentarlo.';
		}
		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,'status'=>$status,'data'=>$data));
	}

	public function getMateriasAction(Request $request)
	{
		$em 				= $this->getDoctrine()->getManager();
		$qb 				= $em->createQueryBuilder();
		$db 				= $em->getConnection();

		$form 				= $request->request->all();
		$_tramite_id 		= isset($form['tramite'])? 	filter_var($form['tramite'], FILTER_SANITIZE_NUMBER_INT ):-1;
		$_inscripcion_id 	= isset($form['id'])?		filter_var($form['id'], FILTER_SANITIZE_NUMBER_INT ):-1;
		$_turno 			= isset($form['turno'])? 	filter_var($form['turno'], FILTER_SANITIZE_NUMBER_INT ):-1;
		$_paralelo 			= isset($form['paralelo'])? 	filter_var($form['paralelo'], FILTER_SANITIZE_NUMBER_INT ):-1;
		
		$inscripcion_retornar = null;
		$materias = array();

		$data	= null;
		$status	= 404;
		$msj	= null;

		if($_tramite_id >0 && $_inscripcion_id>0)
		{
			$tramite_id=$_tramite_id;
			//Buscamos el trámite apartir del codigo de trámite enviado en la peticion
			$tramite= $this->getTramite($tramite_id);
			
			//verificamos que exista el trámite
			if($tramite)
			{
				//ahora obtenemos el campo json del trámite
				$datosJSONInscripcion=$tramite['datos'];
				
				if(strlen($datosJSONInscripcion)>0)
				{
					try //al decodificar la cadena JSON nos aseguramos que sea correcta
					{
						//decodificamos el json para volverlo en objeto y poder procesarlos
						$datosJSONInscripcion_decode 	= json_decode($datosJSONInscripcion);
						$inscripciones 					= $datosJSONInscripcion_decode->inscripciones;
						$sie 							= $datosJSONInscripcion_decode->sie;
						
						foreach ($inscripciones as $i)
						{
							if($i->id == $_inscripcion_id)
							{
								$inscripcion_retornar = $i;
								break;
							}
						}
						if($inscripcion_retornar != null && property_exists($inscripcion_retornar,'nivel') && property_exists($inscripcion_retornar,'grado'))
						{
							//sanitizamos los datos
							$gestion=$this->session->get('currentyear');
							$nivel = filter_var($inscripcion_retornar->nivel,FILTER_SANITIZE_NUMBER_INT);
							$grado = filter_var($inscripcion_retornar->grado,FILTER_SANITIZE_NUMBER_INT);

							$gestion = is_numeric($gestion)?$gestion:-1;
							$nivel = is_numeric($nivel)?$nivel:-1;
							$grado = is_numeric($grado)?$grado:-1;
							
							$qb = $em->createQueryBuilder();
							$curso=$qb->select('c')
							 ->from('SieAppWebBundle:InstitucioneducativaCurso', 'c')
							 ->where('c.institucioneducativa = :institucioneducativa')
							 ->andWhere('c.gestionTipo = :gestionTipo')
							 ->andWhere('c.turnoTipo = :turnoTipo')
							 ->andWhere('c.nivelTipo = :nivelTipo')
							 ->andWhere('c.gradoTipo = :gradoTipo')
							 ->andWhere('c.paraleloTipo = :paraleloTipo')
							 ->setParameter('institucioneducativa',$sie)
							 ->setParameter('gestionTipo',$gestion)
							 ->setParameter('turnoTipo',$_turno)
							 ->setParameter('nivelTipo',$nivel)
							 ->setParameter('gradoTipo',$grado)
							 ->setParameter('paraleloTipo',$_paralelo)
							 ->getQuery()
							 ->getSingleResult();
							 
							if($curso)
							{
								$idCurso = $curso->getId();
								$materias = $this->get('areas')->getAreas($idCurso);
								
								if($materias && isset($materias['cursoOferta']))
								{
									
									//****NO EXISTE TTG - TTE EN POSTBACHILLERATO***/
									if ($nivel == 13 && $grado >= 3){ 
										foreach ( $materias['cursoOferta'] as $key => $item) {
											if ((isset($item['idAsignatura']) && $item['idAsignatura'] == 1038)|| (isset($item['idAsignatura']) && $item['idAsignatura'] == 1039)) {
												unset($materias['cursoOferta'][$key]) ;
											}
										}
									}
									
									//*****/
									$data = json_encode(array(
										'inscripcion' => $inscripcion_retornar,
										//'paralelos'=> $paralelos,
										'turnos'=> $_turno,
										'materias'=> $materias['cursoOferta'],
									));
									$status 	= 200;
									$msj 		= 'Ok';
								}
								else
								{
									$data 	= NULL;
									$status = 404;
									$msj 	= 'No existen asignaturas con los requisitos requeridos por la inscripción.';
								}
							}
							else
							{
								$data 	= NULL;
								$status = 404;
								$msj 	= 'No existen asignaturas con los requisitos requeridos por la inscripción.';
							}
						}
						else
						{
							$data 	= NULL;
							$status = 404;
							$msj 	= 'No se encontro la inscripción seleccionada2.';
						}
					}
					catch(Excepción $e)
					{
						//Por si se produjera un error
						$data 	= NULL;
						$status = 404;
						$msj 	= 'Ocurrio un error al obtener el registro, por favor vuelva a intentarlo.';
					}
				}
				else
				{
					$data 	= NULL;
					$status = 404;
					$msj 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
				}
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'Ocurrio un error debido a que no existe el trámite seleccionado, por favor vuelva a intentarlo.';
			}
		}
		else
		{
			$data 	= NULL;
			$status = 404;
			$msj 	= 'Ocurrio un error debido a que no existe el trámite o la inscripción seleccionada no existe, por favor vuelva a intentarlo.';
		}
		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,'status'=>$status,'data'=>$data));
	}

	function enviarSolicitudDistritalUeAction(Request $request)
	{
		$em 				= $this->getDoctrine()->getManager();
		$qb 				= $em->createQueryBuilder();

		$form = $request->request->all();
		$tramite_id 			= filter_var($form['request_tramite'],FILTER_SANITIZE_NUMBER_INT);
		$tramite_id 			= is_numeric($tramite_id)?$tramite_id:-1;
		
		$request_materias 		= isset($form['request_materias'])?$form['request_materias']:array( );
		$request_fileInforme 	= $_FILES["request_fileInforme"];
		
		$data 					= NULL;
		$status 				= 404;
		$msj 					= NULL;
		$urlReporte 			= NULL;
		$urlActaSupletoria 		= NULL;

		//Buscamos el trámite apartir del codigo de trámite enviado en la peticion
		$tramite = $this->getTramite($tramite_id);
				
		//verificamos que exista el trámite y la unidad educativa
		if($tramite)
		{
			//luego obtenemos el Json del trámite
			$datosJSONInscripcion = $tramite['datos'];
			
			//verificamos que el JSon existe
			if(strlen($datosJSONInscripcion)>0 )
			{
				//verificamos que las materias y el informe haya sido subido
				if(count($request_materias)>0 && isset($request_fileInforme))
				{
					//al decodificar la cadena JSON nos aseguramos que sea correcta
					try 
					{
						//decodificamos el json para volverlo en objeto y poder procesarlos
						$datosJSONInscripcion_decode = json_decode($datosJSONInscripcion);
						$estudiante = property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;
						
						$rude 		= $estudiante->codigo_rude;
						$gestion 	= $estudiante->gestion_tipo_id;
						$sieAnt 	= $estudiante->institucioneducativa_id;

						if($estudiante)
						{
							//nos aseguramos que las inscripciones enviadas sean igual a las inscripcion datosJSONInscripcion
							
							list($observado,$materias)=$this->verificarInscripcionesRecibidas($datosJSONInscripcion_decode->inscripciones,$request_materias);
							if(!$observado && count($materias)>0)
							{
								// list($archivoGuardado,$nombre) = $this->guardarArchivo($request_fileInforme,$tramite_id,$estudiante->getCodigoRude(),$this->path,'Informe-UE');
								$nArcInforme = $this->guardarArch($sieAnt,$rude,$gestion,'UEINF', $request_fileInforme);
								
								//Realizamos la verificacion de si el archivo fue guardado
								if(isset($nArcInforme))
								{
									//Asignamos el informe en la cadena JSON
									// $informes=$datosJSONInscripcion_decode->informes;
									
									// $informes[]=json_decode(json_encode(array('informe'=>$nombre)));
									$datosJSONInscripcion_decode->informes->informes_ue=$nArcInforme;
									
									//ahora añadimos las notas a las materias 
									$tmpInscripciones = $datosJSONInscripcion_decode->inscripciones;
									$error=false;
									foreach ($tmpInscripciones as $ins)
									{
										$datos=$materias[$ins->id];
										// dump($materias[$ins->id]);
										//verificamos que todas las materias tenga la propiedad paralelo, turno y materia y de ser asi la añadimos a la cadena json 
										if( $datos && property_exists($datos,'paralelo') && property_exists($datos,'turno') && property_exists($datos,'materias') )
										{
											$ins->paralelo=$datos->paralelo;
											$ins->turno=$datos->turno;
											$ins->materias=$datos->materias;
										}
										else
										{
											$error=true;
											break;
										}
									}
									//verificamos que todo las materias tenga una calificacion
									if($error==false)
									{
										//Obtenemos el SIE de la unidad educativa
										$sie = $datosJSONInscripcion_decode->sie;
										$flujoTipo=$tramite['flujo_tipo_id'];//Solicitud de regularización
										//$tarea = $this->get('wftramite')->obtieneTarea($flujoTipo, 'idflujo');
										$tarea = $this->get('wftramite')->obtieneTarea($tramite['tramite_id'], 'idtramite');
										$tareaActual = $tarea['tarea_actual'];
										$tareaSiguiente = $tarea['tarea_siguiente'];
										
										$idTramite=$tramite['tramite_id'];
										
										$gestion=$this->session->get('currentyear');
										$lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);
										$registroTramite = $this->get('wftramite')->guardarTramiteEnviado(
											$this->session->get('userId'),										//$usuario,
											$this->session->get('roluser'),										//$rol,
											$flujoTipo,															//$flujotipo,
											$tareaActual,														//$tarea,
											'institucioneducativa',												//$tabla,
											$sie,																//$id_tabla,
											'',																	//$observacion,
											'',																	//$varevaluacion,
											$idTramite,															//$idtramite,
											json_encode($datosJSONInscripcion_decode, JSON_UNESCAPED_UNICODE),	//$datos,
											'',																	//$lugarTipoLocalidad_id,
											$lugarTipo['lugarTipoIdDistrito']									//$lugarTipoDistrito_id
										);

										if($registroTramite)
										{
											if($registroTramite['dato']===true && $registroTramite['tipo']==='exito')
											{
												//dump(json_encode($datosJSONInscripcion));
												$data 				= $registroTramite['idtramite'];
												$status 			= 200;
												$msj 				= $registroTramite['msg'];
												$urlReporte 		= $this->generateUrl('regularizacion_estudiantesPostBachillerato_reporteUE',array('idtramite' => $registroTramite['idtramite'])) ;
												$urlActaSupletoria 	= $this->generateUrl('regularizacion_estudiantesPostBachillerato_reporteUEActaSupletoria',array('idtramite' => $registroTramite['idtramite'])) ;
											}
											else
											{
												$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
												$data 	= NULL;
												$status = 404;
												$msj 	= $registroTramite['msg'];
											}
										}
										else
										{
											$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
											$data 	= NULL;
											$status = 404;
											$msj 	= 'Ocurrio un error al guardar los archivos adjuntos, por favor vuelva a intentarlo.';
										}
									}
									else
									{
										//Esto ocurria si los archivos no pudieron ser guardados
										//$em->getConnection()->rollback();
										//por seguridad borramos los archivos,
										$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
										$data 	= NULL;
										$status = 404;
										$msj 	= 'Ocurrio un error al procesar las notas de materias de las inscripciones, por favor vuelva a intentarlo.';
									}
								}
								else
								{
									//Esto ocurria si los archivos no pudieron ser guardados
									//$em->getConnection()->rollback();
									//por seguridad borramos los archivos,
									$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
									$data 	= NULL;
									$status = 404;
									$msj 	= 'Ocurrio un error al guardar el archivo adjunto, por favor vuelva a intentarlo.';
								}
							}
							else
							{
								$data 	= NULL;
								$status = 404;
								$msj 	= 'Ocurrio un error debido a que las inscripciones registradas no son correctas, por favor vuelva a intentarlo.';
							}
						}
						else
						{
							$data 	= NULL;
							$status = 404;
							$msj 	= 'Ocurrio un error debido a que no existe el esatudiante que solicito la regularización.';
						}
					}
					catch(Excepción $e)
					{
						//Esto ocurria si los archivos no pudieron ser guardados
						$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
						$data 	= NULL;
						$status = 404;
						$msj 	= 'Ocurrio un error al guardar el registro, por favor vuelva a intentarlo.';
					}
				}
				else
				{
					//Esto ocurrira si no fueron enviados los archivos del formulario
					$data 	= NULL;
					$status = 404;
					$msj 	= 'Ocurrio un error debido a que las notas de las inscripciones no fueron registradas correctamente o el archivo adjunto no fue subido, por favor vuelva a intentarlo.';
				}
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
			}
		}
		else
		{
			$data 	= NULL;
			$status = 404;
			$msj 	= 'Ocurrio un error debido a que no existe el trámite seleccionado, por favor vuelva a intentarlo.';
		}

		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,'status'=>$status,'data'=>$data,'urlReporte'=>$urlReporte,'urlActaSupletoria'=>$urlActaSupletoria ));
	}

	function verificarInscripcionesRecibidas($inscripciones,$materias)
	{
		$observado=false;
		$listaMaterias = array();

		if($inscripciones != null && count($inscripciones)>0)
		{
			foreach ($inscripciones as $i)
			{
				if(array_key_exists ($i->id,$materias))
				{
					try
					{
						$m=json_decode($materias[$i->id]);
						$listaMaterias[$i->id]=$m->{$i->id};
						$observado=false;
					}
					catch(Exception $e)
					{
						$observado=true;
						$listaMaterias= array();
						break;
					}
				}
				else
				{
					$observado=true;
					$listaMaterias= array();
					break;
				}
			}
		}
		return array($observado,$listaMaterias);
	}

	public function reporteUEAction(Request $request,$idtramite)
	{
		$em = $this->getDoctrine()->getManager();
		$request_id = $request->get('id');
		$request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
		$request_id = is_numeric($request_id)?$request_id:-1;

		$request_tipo = $request->get('tipo');
		$request_tipo = filter_var($request_tipo,FILTER_SANITIZE_STRING);

		$tramite_id = filter_var($idtramite,FILTER_SANITIZE_NUMBER_INT);
		$tramite_id = is_numeric($tramite_id)?$tramite_id:-1;


		$determinarAprobacionOReprobacionSolicitud = false;
		//Verificamos que el id de trámite haya sido enviado, caso contrario redireccionamos a la anteriro pagina
		if($tramite_id>0)
		{
			//Buscamos el trámite segun el id enviado
			$tramite =$this->getTramiteReporte($tramite_id);

			if($tramite)
			{
				$datosJSONInscripcion = $tramite['datos'];

				if(strlen($datosJSONInscripcion)>0)//verificamos que el JSon existe
				{
					try
					{
						//intentamos decodificar la cadena
						$datosJSONInscripcion_decode=json_decode($datosJSONInscripcion);
						$rude=property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;

						$qb = $em->createQueryBuilder();
						$estudiante=$qb->select('e')
						 ->from('SieAppWebBundle:Estudiante', 'e')
						 ->where('e.codigoRude = :codigoRude')
						 ->setParameters(array('codigoRude'=>$rude))
						 ->getQuery()
						 ->getSingleResult();

						//verificamos que exista el estudiante
						if($estudiante)
						{
							if( $datosJSONInscripcion_decode && property_exists($datosJSONInscripcion_decode,'inscripciones'))
							{
								//Ahora procedemos a procesar los datos
								$inscripcionesDecode 	= $datosJSONInscripcion_decode;

								list($tieneErrores,$tablaInscripciones_procesada)=$this->obtenerNombresDeInscripciones($inscripcionesDecode);

								$institucioneducativa=null;
								if($inscripcionesDecode && property_exists($inscripcionesDecode,'sie'))
								{
									//obtenemos con la unidad educativa
									$institucioneducativaTMP=$em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $inscripcionesDecode->sie));
									$institucioneducativa= $institucioneducativaTMP->getInstitucioneducativa();
								}
								if($tieneErrores==false && $institucioneducativa)
								{
									//verificamos que la inscripcion tenga los campos inscripciones, docsInteresadoe informess
									$tablaInscripciones=array();
									if($tablaInscripciones_procesada && property_exists($tablaInscripciones_procesada,'inscripciones'))
									{
										//empezamos con las inscripciones
										$tablaInscripciones = $tablaInscripciones_procesada->inscripciones;
										$determinarAprobacionOReprobacionSolicitud = $this->determinarAprobacionOReprobacionSolicitud($tablaInscripciones);
									}
									$adjuntosDocsInteresado=array();
									if($inscripcionesDecode && property_exists($inscripcionesDecode,'docsInteresado'))
									{
										//continuamos con los documentos del interesado
										$adjuntosDocsInteresado = $inscripcionesDecode->docsInteresado;
									}

									$adjuntosInformes=array();
									if($inscripcionesDecode && property_exists($inscripcionesDecode,'informes'))
									{
										//continuamos con los informes
										$adjuntosInformes = $inscripcionesDecode->informes;
									}

									$datosInscripcion = base64_encode(json_encode($datosJSONInscripcion_decode->inscripciones));

									$etapa = "Evaluacion Unidad Educativa";
									$datosQR = "Etapa: ".$etapa."\n"."Tramite: ".$tramite_id."\n"."Solicitante: ".$rude."\n"."Fecha inicio de tramite: ".$tramite['fecha_tramite']."\n\n";
									$codigoQR = $this->getCodigoQR($datosQR,$datosInscripcion);
									$codigoQR=preg_replace('#^data:image/[^;]+;base64,#', '',base64_encode($codigoQR) );

									$html= 
									$this->render($this->session->get('pathSystem') . ':Regularizacion_EstudiantesPostBachillerato:Reportes/ue_reporte.html.twig',array(
										'estudiante'=>$estudiante,
										'path' => $this->path,
										'error' => $error,
										'tablaInscripciones' => $tablaInscripciones,
										'adjuntosDocsInteresado' => $adjuntosDocsInteresado,
										'adjuntosInformes' => $adjuntosInformes,
										'institucioneducativa' => $institucioneducativaTMP,
										"tramite" => $tramite_id,
										"codigoQR" => $codigoQR
									));
									$pdf=$this->generarPdf($html, $tramite_id, $rude,$descripcion="EVALUACION_UE_");
									$this->descargarPdf($pdf,$tramite_id, $rude,$descripcion="EVALUACION_UE_");
								}
								else
								{
									$tablaInscripciones = array();
									$adjuntosDocsInteresado = array();
									$adjuntosInformes = array();
									$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
								}
							}
							else
							{
								$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
							}
						}
						else
						{
							$error='No se puede encontrar al estudiante, por favor verifique que el trámite fue realizado correctamente.';
						}
					}
					catch(Exception $e)
					{
						$tablaInscripciones = array();
						$adjuntosDocsInteresado = array();
						$adjuntosInformes = array();
						$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
					}
				}
				else
				{
					$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
				}
			}
			else
			{
				$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
			}

			return $this->render($this->session->get('pathSystem') . ':Regularizacion_EstudiantesPostBachillerato:distrital_detalleSolicitudesRecibidasPorConcluir.html.twig',array(
				'estudiante'=>$estudiante,
				'path' => $this->path,
				'error' => $error,
				'tablaInscripciones' => $tablaInscripciones,
				'adjuntosDocsInteresado' => $adjuntosDocsInteresado,
				'adjuntosInformes' => $adjuntosInformes,
				'institucioneducativa' => $institucioneducativa,
				"tramite" => $tramite_id,
			));
		}
		else
		{
			return $this->redirect($this->generateUrl('login'));
		}
	}

	public function reporteUEActaSupletoriaAction(Request $request,$idtramite)
	{
		$em = $this->getDoctrine()->getManager();
		$request_id = $request->get('id');
		$request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
		$request_id = is_numeric($request_id)?$request_id:-1;

		$request_tipo = $request->get('tipo');
		$request_tipo = filter_var($request_tipo,FILTER_SANITIZE_STRING);

		$tramite_id = filter_var($idtramite,FILTER_SANITIZE_NUMBER_INT);
		$tramite_id = is_numeric($tramite_id)?$tramite_id:-1;

		$determinarAprobacionOReprobacionSolicitud= false;

		if($tramite_id>0)
		{
			//Buscamos el trámite segun el id enviado
			$tramite =$this->getTramiteReporte($tramite_id);

			if($tramite)
			{
				$datosJSONInscripcion = $tramite['datos'];

				if(strlen($datosJSONInscripcion)>0)//verificamos que el JSon existe
				{
					try
					{
						//intentamos decodificar la cadena
						$datosJSONInscripcion_decode=json_decode($datosJSONInscripcion);
						$rude=property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;

						$qb = $em->createQueryBuilder();
						$estudiante=$qb->select('e')
						 ->from('SieAppWebBundle:Estudiante', 'e')
						 ->where('e.codigoRude = :codigoRude')
						 ->setParameters(array('codigoRude'=>$rude))
						 ->getQuery()
						 ->getSingleResult();

						//verificamos que exista el estudiante
						if($estudiante)
						{
							if( $datosJSONInscripcion_decode && property_exists($datosJSONInscripcion_decode,'inscripciones'))
							{
								//Ahora procedemos a procesar los datos
								$inscripcionesDecode 	= $datosJSONInscripcion_decode;

								list($tieneErrores,$tablaInscripciones_procesada)=$this->obtenerNombresDeInscripciones($inscripcionesDecode);

								$institucioneducativa=null;
								if($inscripcionesDecode && property_exists($inscripcionesDecode,'sie'))
								{
									//obtenemos con la unidad educativa
									$institucioneducativaTMP=$em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $inscripcionesDecode->sie));
									$institucioneducativa= $institucioneducativaTMP->getInstitucioneducativa();
								}
								if($tieneErrores==false && $institucioneducativa)
								{
									//verificamos que la inscripcion tenga los campos inscripciones, docsInteresadoe informess
									$tablaInscripciones=array();
									if($tablaInscripciones_procesada && property_exists($tablaInscripciones_procesada,'inscripciones'))
									{
										//empezamos con las inscripciones
										$tablaInscripciones = $tablaInscripciones_procesada->inscripciones;
										$determinarAprobacionOReprobacionSolicitud = $this->determinarAprobacionOReprobacionSolicitud($tablaInscripciones);
									}
									$adjuntosDocsInteresado=array();
									if($inscripcionesDecode && property_exists($inscripcionesDecode,'docsInteresado'))
									{
										//continuamos con los documentos del interesado
										$adjuntosDocsInteresado = $inscripcionesDecode->docsInteresado;
									}

									$adjuntosInformes=array();
									if($inscripcionesDecode && property_exists($inscripcionesDecode,'informes'))
									{
										//continuamos con los informes
										$adjuntosInformes = $inscripcionesDecode->informes;
									}

									$pdf=$this->container->getParameter('urlreportweb') . 'reg_est_cert_cal_solicitud_tramite_acta_supletoria_V1_eea.rptdesign&__format=pdf'.'&tramite_id='.$tramite_id;
									//$pdf='http://127.0.0.1:63020/viewer/preview?__report=D%3A\workspaces\workspace_especial\Regularizacion_EstudiantesPostBachillerato\reg_est_cert_cal_solicitud_tramite_acta_supletoria_V1_eea.rptdesign&__format=pdf'.'&tramite_id='.$tramite_id;
									
									$status = 200;
									$arch           = 'ACTA_SUPLETORIA-'.$tramite_id.'-'.$estudiante->getCodigoRude().'.pdf';
									$response       = new Response();
									$response->headers->set('Content-type', 'application/pdf');
									$response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
									$response->setContent(file_get_contents($pdf));
									$response->setStatusCode($status);
									$response->headers->set('Content-Transfer-Encoding', 'binary');
									$response->headers->set('Pragma', 'no-cache');
									$response->headers->set('Expires', '0');
									return $response;

								}
								else
								{
									$tablaInscripciones = array();
									$adjuntosDocsInteresado = array();
									$adjuntosInformes = array();
									$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
								}
							}
							else
							{
								$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
							}
						}
						else
						{
							$error='No se puede encontrar al estudiante, por favor verifique que el trámite fue realizado correctamente.';
						}
					}
					catch(Exception $e)
					{
						$tablaInscripciones = array();
						$adjuntosDocsInteresado = array();
						$adjuntosInformes = array();
						$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
					}
				}
				else
				{
					$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
				}
			}
			else
			{
				$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
			}

			return $this->render($this->session->get('pathSystem') . ':Regularizacion_EstudiantesPostBachillerato:distrital_detalleSolicitudesRecibidasPorConcluir.html.twig',array(
				'estudiante'=>$estudiante,
				'path' => $this->path,
				'error' => $error,
				'tablaInscripciones' => $tablaInscripciones,
				'adjuntosDocsInteresado' => $adjuntosDocsInteresado,
				'adjuntosInformes' => $adjuntosInformes,
				'institucioneducativa' => $institucioneducativa,
				"tramite" => $tramite_id,
			));
		}
		else
		{
			return $this->redirect($this->generateUrl('login'));
		}
	}

	public function postAprobacionDistritalAction(Request $request)
	{
		$em 					= $this->getDoctrine()->getManager();
		$qb 					= $em->createQueryBuilder();
		$form 					= $request->request->all();
		$tramite_id				= isset($form['tramite_id'])?$form['tramite_id']:-1;
		$error					= NULL;
		$tablaInscripciones		= array();
		$adjuntosDocsInteresado = array();
		$adjuntosInformes		= array();

		$request_id = $request->get('id');
		$request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
		$request_id = is_numeric($request_id)?$request_id:-1;

		$request_tipo = $request->get('tipo');
		$request_tipo = filter_var($request_tipo,FILTER_SANITIZE_STRING);

		$tramite_id=$request_id;

		//verificamos que el trámite exista
		if($tramite_id>0)
		{
			//Buscamos el trámite segun el id enviado
			$tramite =$this->getTramite($tramite_id);

			if($tramite)
			{
				$datosJSONInscripcion = $tramite['datos'];
				
				if(strlen($datosJSONInscripcion)>0)//verificamos que el JSon existe
				{
					try
					{
						//intentamos decodificar la cadena
						$datosJSONInscripcion_decode=json_decode($datosJSONInscripcion);
						$rude=property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;
						$estudiante = $datosJSONInscripcion_decode->estudiante;
						$institucioneducativaTMP=$em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $datosJSONInscripcion_decode->sie));
						$ueDesignada = $institucioneducativaTMP->getInstitucioneducativa().' - '.$datosJSONInscripcion_decode->sie;
						//verificamos que exista el estudiante
						if($estudiante)
						{
							if( $datosJSONInscripcion_decode && property_exists($datosJSONInscripcion_decode,'inscripciones'))
							{
								//Ahora procedemos a procesar los datos
								$inscripciones = $datosJSONInscripcion_decode->inscripciones;
								$tablaInscripciones=array();
								$tablaInscripciones = $this->obtenerNombresDeMaterias($inscripciones);
								$adjuntosDocsInteresado = $datosJSONInscripcion_decode->docsInteresado;
								//terminamos con los informes
								$adjuntosInformes 		= $datosJSONInscripcion_decode->informes;
                                //terminamos con los informes
                                $ueasignada = $this->verificarasignacionUe($estudiante->institucioneducativa_id,$datosJSONInscripcion_decode->inscripciones);
							}
							else
							{
								$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
							}
						}
						else
						{
							$error='No se puede encontrar al estudiante, por favor verifique que el trámite fue realizado correctamente.';
						}
					}
					catch(Exception $e)
					{
						$tablaInscripciones = array();
						$adjuntosDocsInteresado = array();
						$adjuntosInformes = array();
						$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
					}
				}
				else
				{
					$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
				}
			}
			else
			{
				$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
			}
			return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:distrital_detalleSolicitudesRecibidasAprobar.html.twig',array(
				'estudiante'=>$estudiante,
				'error' => $error,
				'ue_designada' => $ueDesignada,
				'tablaInscripciones' => $tablaInscripciones,
				'adjuntosDocsInteresado' => $adjuntosDocsInteresado,
				'adjuntosInformes' => $adjuntosInformes,
				'institucioneducativa' => $institucioneducativa,
				"tramite" => $tramite_id,
			));
		}
		else
		{
			return $this->redirect($this->generateUrl('regularizacion_estudiantesPostBachillerato_getSolicitudeRecibidasUE'));
		}
	}

	private function obtenerNombresDeMaterias($inscripciones)
	{	
		$em = $this->getDoctrine()->getManager();
        $datosInscripciones_procesada = array();
		$tieneErrores = false;
		if($inscripciones)
		{
			$tmpInscripciones=$inscripciones;
			foreach ($tmpInscripciones as $inscripcion)
			{
				if( $inscripcion && property_exists($inscripcion,'id') && property_exists($inscripcion,'nivel') && property_exists($inscripcion,'grado') )
				{
					$nivel = null;
					$grado = null;
					try
					{   
						$tmpNivel 		= filter_var($inscripcion->nivel,FILTER_SANITIZE_NUMBER_INT);
						$tmpGrado 		= filter_var($inscripcion->grado,FILTER_SANITIZE_NUMBER_INT);

                        $nivel = $em->getRepository('SieAppWebBundle:NivelTipo')->find($tmpNivel);
                        $grado = $em->getRepository('SieAppWebBundle:GradoTipo')->find($tmpGrado);
                        
						if($nivel && $grado )
						{
							//obtenemos el primer y unico elemento de nivel y grado
							$nivel=$nivel->getNivel();
							$grado=$grado->getGrado();

							if(count($nivel)==1 && count($grado)==1 )
							{
								$datosInscripciones_procesada[]=array(
									'id'		=> $inscripcion->id,
									'nivel' 	=> $nivel,
									'grado' 	=> $grado,
								);
							}
							else
							{
								$datosInscripciones_procesada= array();
								$tieneErrores = true;
								break;
							}
						}
						else
						{
							$datosInscripciones_procesada= array();
							$tieneErrores = true;
							break;
						}
					}
					catch(Exception $e)
					{
						$datosInscripciones_procesada= array();
						$tieneErrores = true;
						break;
					}
				}
				else
				{
					$datosInscripciones_procesada= array();
					$tieneErrores = true;
					break;
				}
			}
		}
		else
		{
			$tieneErrores = false;
		}
		return $datosInscripciones_procesada;
	}

	public function postEnviarAprobarDistritalAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$qb = $em->createQueryBuilder();
		$form = $request->request->all();

		$tramite_id 			= filter_var($form['request_tramite'],FILTER_SANITIZE_NUMBER_INT);
		$tramite_id 			= is_numeric($tramite_id)?$tramite_id:-1;

		$request_fileInforme 	= $_FILES["request_fileInforme"];
		//Buscamos el trámite apartir del codigo de trámite enviado en la peticion
		$tramite = $this->getTramite($tramite_id);
		
		//verificamos que exista el trámite y la unidad educativa
		if($tramite)
		{
			//luego obtenemos el Json del trámite
			$datosJSONInscripcion = $tramite['datos'];
			
			if(strlen($datosJSONInscripcion)>0)//verificamos que el JSon existe
			{
				//verificamos que el informe haya sido subido
				if(isset($request_fileInforme))
				{
					try //al decodificar la cadena JSON nos aseguramos que sea correcta
					{
						//decodificamos el json para volverlo en objeto y poder procesarlos
						$datosJSONInscripcion_decode = json_decode($datosJSONInscripcion);
						// dump($datosJSONInscripcion_decode);die;
						$estudiante=property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;

						$rude		= $estudiante->codigo_rude;
						$gestion	= $estudiante->gestion_tipo_id;
						$sieAnt		= $estudiante->institucioneducativa_id;

						if($estudiante)
						{
							$nArcInforme = $this->guardarArch($sieAnt,$rude,$gestion,'DDDINFAUTORIZA', $request_fileInforme);

							//Realizamos la verificacion de si el archivo fue guardado
							if(isset($nArcInforme))
							{
								$datosJSONInscripcion_decode->informes->inf_aprueba_ddd=$nArcInforme;
								//Asignamos el informe en la cadena JSON
								$sie = $datosJSONInscripcion_decode->sie;
								$flujoTipo=$tramite['flujo_tipo_id'];//Solicitud de regularización
								//$tarea = $this->get('wftramite')->obtieneTarea($flujoTipo, 'idflujo');
								$tarea = $this->get('wftramite')->obtieneTarea($tramite['tramite_id'], 'idtramite');

								$tareaActual = $tarea['tarea_actual'];
								$tareaSiguiente = $tarea['tarea_siguiente'];
								$idTramite=$tramite['tramite_id'];

								$lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);
								
								$registroTramite = $this->get('wftramite')->guardarTramiteEnviado(
									$this->session->get('userId'),										//$usuario,
									$this->session->get('roluser'),										//$rol,
									$flujoTipo,															//$flujotipo,?
									$tareaActual,														//$tarea,
									'institucioneducativa',												//$tabla,?
									$sie,																//$id_tabla,?
									'',																	//$observacion,
									'',																	//$varevaluacion,
									$idTramite,															//$idtramite,
									json_encode($datosJSONInscripcion_decode, JSON_UNESCAPED_UNICODE),	//$datos,
									'',																	//$lugarTipoLocalidad_id,
									$lugarTipo['lugarTipoIdDistrito']									//$lugarTipoDistrito_id?
								);

								if($registroTramite)
								{
									if($registroTramite['dato']===true && $registroTramite['tipo']==='exito')
									{
										//dump(json_encode($datosJSONInscripcion));
										$data 	= $registroTramite['idtramite'];
										$status = 200;
										$msj 	= $registroTramite['msg'];
									}
									else
									{
										$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
										$data 	= NULL;
										$status = 404;
										$msj 	= $registroTramite['msg'];
									}
								}
								else
								{
									$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
									$data 	= NULL;
									$status = 404;
									$msj 	= 'Ocurrio un error al guardar los archivos adjuntos, por favor vuelva a intentarlo.';
								}
							}
							else
							{
								//Esto ocurria si los archivos no pudieron ser guardados
								//por seguridad borramos los archivos,
								$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
								$data 	= NULL;
								$status = 404;
								$msj 	= 'Ocurrio un error al guardar el archivo adjunto, por favor vuelva a intentarlo.';
							}
						}
						else
						{
							$data 	= NULL;
							$status = 404;
							$msj 	= 'Ocurrio un error debido a que no existe el esatudiante que solicito la regularización.';
						}

					}
					catch(Excepción $e)
					{
						//Esto ocurria si los archivos no pudieron ser guardados
						$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
						$data 	= NULL;
						$status = 404;
						$msj 	= 'Ocurrio un error al guardar el registro, por favor vuelva a intentarlo.';
					}
				}
				else
				{
					//Esto ocurrira si no fueron enviados los archivos del formulario
					$data 	= NULL;
					$status = 404;
					$msj 	= 'Ocurrio un error debido a que el archivo adjunto no fue subido, por favor vuelva a intentarlo.';
				}
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
			}
		}
		else
		{
			$data 	= NULL;
			$status = 404;
			$msj 	= 'Ocurrio un error debido a que no existe el trámite seleccionado, por favor vuelva a intentarlo.';
		}

		$response = new JsonResponse($data=null,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,'status'=>$status));
	}

	public function postAprobacionDepartamentalAction(Request $request)
	{
		$em 					= $this->getDoctrine()->getManager();
		$qb 					= $em->createQueryBuilder();
		$form 					= $request->request->all();
		$tramite_id				= isset($form['tramite_id'])?$form['tramite_id']:-1;
		$error					= NULL;
		$tablaInscripciones		= array();
		$adjuntosDocsInteresado = array();
		$adjuntosInformes		= array();

		$request_id = $request->get('id');
		$request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
		$request_id = is_numeric($request_id)?$request_id:-1;

		$request_tipo = $request->get('tipo');
		$request_tipo = filter_var($request_tipo,FILTER_SANITIZE_STRING);

		$tramite_id=$request_id;
		
		if($tramite_id>0)
		{
			//Buscamos el trámite segun el id enviado
			$tramite =$this->getTramite($tramite_id);
			
			//verificamos que el trámite exista
			if($tramite)
			{
				$datosJSONInscripcion = $tramite['datos'];
				if(strlen($datosJSONInscripcion)>0)
				{
					try
					{
						//intentamos decodificar la cadena
						$datosJSONInscripcion_decode=json_decode($datosJSONInscripcion);
						//Ahora procedemos a procesar los datos
						// $inscripcionesDecode 	= $datosJSONInscripcion_decode;

						// $rude=property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;
						$rude=property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;
						$estudiante = $datosJSONInscripcion_decode->estudiante;
						$institucioneducativaTMP=$em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $datosJSONInscripcion_decode->sie));
						$ueDesignada = $institucioneducativaTMP->getInstitucioneducativa().' - '.$datosJSONInscripcion_decode->sie;

						// $qb = $em->createQueryBuilder();
						// $estudiante=$qb->select('e')
						//  ->from('SieAppWebBundle:Estudiante', 'e')
						//  ->where('e.codigoRude = :codigoRude')
						//  ->setParameters(array('codigoRude'=>$rude))
						//  ->getQuery()
						//  ->getSingleResult();

						//verificamos que exista el estudiante
						if($estudiante)
						{
							if( $datosJSONInscripcion_decode && property_exists($datosJSONInscripcion_decode,'inscripciones'))
							{
								//Ahora procedemos a procesar los datos
								$inscripciones = $datosJSONInscripcion_decode->inscripciones;
								$tablaInscripciones=array();
								$tablaInscripciones = $this->obtenerNombresDeMaterias($inscripciones);
								$adjuntosDocsInteresado = $datosJSONInscripcion_decode->docsInteresado;
								//terminamos con los informes
								$adjuntosInformes 		= $datosJSONInscripcion_decode->informes;
                                //terminamos con los informes
                                $ueasignada = $this->verificarasignacionUe($estudiante->institucioneducativa_id,$datosJSONInscripcion_decode->inscripciones);
							}
							else
							{
								$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
							}
						}
						else
						{
							$error='No se puede encontrar al estudiante, por favor verifique que el trámite fue realizado correctamente.';
						}
					}
					catch(Exception $e)
					{
						$tablaInscripciones = array();
						$adjuntosDocsInteresado = array();
						$adjuntosInformes = array();
						$error 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
					}
				}
				else
				{
					$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
				}
			}
			else
			{
				$error='No se pudo encontrar el trámite, por favor verifique que el trámite fue realizado correctamente.';
			}

			return $this->render('SieProcesosBundle:RegularizacionPreBachillerato:departamental_detalleSolicitudesRecibidasAprobar.html.twig',array(
				'estudiante'=>$estudiante,
				'error' => $error,
				'ue_designada' => $ueDesignada,
				'tablaInscripciones' => $tablaInscripciones,
				'adjuntosDocsInteresado' => $adjuntosDocsInteresado,
				'adjuntosInformes' => $adjuntosInformes,
				'institucioneducativa' => $institucioneducativa,
				"tramite" => $tramite_id,
			));
		}
		else
		{
			return $this->redirect($this->generateUrl('regularizacion_estudiantesPostBachillerato_getSolicitudeRecibidasUE'));
		}
	}

	public function postFinalizarDepartamentalAction(Request $request)
	{
		$em 					= $this->getDoctrine()->getManager();
		
		$data 								= NULL;
		$status 							= 404;
		$msj 								= 'Acaba de ocurrir un error, por favor vuelva a intentarlo.';
		$urlCertificacionConclusionEstudios = NULL;
		
		$form 					= $request->request->all();
		$tramite_id 			= isset($form['request_tramite'])? filter_var($form['request_tramite'],FILTER_SANITIZE_NUMBER_INT) :-1;
		$request_fileInforme 	= $_FILES["request_fileInforme"];
		
		$request_procede 		= filter_var($form['request_procede'],FILTER_SANITIZE_NUMBER_INT);
		$request_procede 		= is_numeric($request_procede)?$request_procede:-1;
		
		$request_observacion 	= '';
		
		$determinarAprobacionOReprobacionSolicitud= false;
		if($request_procede>=0)
		{
			if($request_procede==0)//no procede
			{
				$_FILES['request_fileInforme'] 	= array();
				$request_fileInforme 			= $_FILES["request_fileInforme"];
				$request_observacion 			= strtoupper(filter_var($form['request_observacion'],FILTER_SANITIZE_STRING));
			}
			else if($request_procede==1) // si procede
			{
				$form['request_observacion']='';
			}
			else
			{
				unset($_FILES['request_fileInforme']);
				$form['request_observacion']='';
				$request_procede =-1; // algun error
			}
		}
		
	
		//Buscamos el trámite apartir del codigo de trámite enviado en la peticion

		$tramite =$this->getTramite($tramite_id);

		if($tramite && ($request_procede>=0 && $request_procede<=1) )//verificamos que exista el trámite y la unidad educativa
		{
			//luego obtenemos el Json del trámite
			$datosJSONInscripcion = $tramite['datos'];
			
			if(strlen($datosJSONInscripcion)>0 )//verificamos que el JSon existe
			{
				try //al decodificar la cadena JSON nos aseguramos que sea correcta
				{
					//decodificamos el json para volverlo en objeto y poder procesarlos
					$datosJSONInscripcion_decode = json_decode($datosJSONInscripcion);
					
					$estudiante=property_exists($datosJSONInscripcion_decode,'estudiante')?$datosJSONInscripcion_decode->estudiante:-1;
					$qb = $em->createQueryBuilder();
					
					$rude		= $estudiante->codigo_rude;
					$gestion	= $estudiante->gestion_tipo_id;
					$sieAnt		= $estudiante->institucioneducativa_id;

					//verificamos que exista el estudiante
					if($estudiante)
					{	
						
						if( $datosJSONInscripcion_decode && property_exists($datosJSONInscripcion_decode,'inscripciones'))
						{
							$sie = $datosJSONInscripcion_decode->sie;
							$varevaluacion='NO';
							$archivoGuardado=false;
							
							if($request_procede==1) //SI procede
							{
								$varevaluacion='SI';
								// list($archivoGuardado,$nombre) = $this->guardarArchivo($request_fileInforme,$tramite_id,$estudiante->getCodigoRude(),$this->path,'Informe-Departamental-2');
								$nArcInforme = $this->guardarArch($sieAnt,$rude,$gestion,'DDEINFAUTORIZA', $request_fileInforme);
								
								//Realizamos la verificacion de si el archivo fue guardado
								if(isset($nArcInforme))
								{
									$archivoGuardado=true;
									//Asignamos el informe en la cadena JSON
									$datosJSONInscripcion_decode->informes->inf_aprueba_dde=$nArcInforme;
									$informes[]=json_decode(json_encode(array('informe'=>$nombre)));
									// $datosJSONInscripcion_decode->informes=$informes;
									$datosJSONInscripcion_decode->observacion_dde='Sin Observaciones';
									list($tieneErrores,$msjError) = $this->inscribirMaterias($datosJSONInscripcion_decode);
									if($tieneErrores==true)
									{
										$datosActualizados=false;
										$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
										$data 	= NULL;
										$status = 404;
										$msj 	= $msjError;
									}
									else
									{
										$datosActualizados=true;
									}
								}
								else
								{
									//por seguridad borramos los archivos,
									$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
									$data 	= NULL;
									$status = 404;
									$msj 	= 'Ocurrio un error al guardar el archivo adjunto, por favor vuelva a intentarlo.';
								}
							}
							else
							{	
								$varevaluacion='NO';
								$archivoGuardado=false;
								if(strlen($request_observacion)>0)
									$datosJSONInscripcion_decode->observacion=$request_observacion;
							}
							
							if(($varevaluacion==='NO' && strlen($request_observacion)>0) || ($varevaluacion==='SI' && $archivoGuardado===true && $datosActualizados===true))
							{	
								$flujoTipo 	=	$tramite['flujo_tipo_id'];//Solicitud de regularización
								
								//$tarea = $this->get('wftramite')->obtieneTarea($flujoTipo, 'idflujo');
								$tareaTmp = $this->get('wftramite')->obtieneTarea($tramite['tramite_id'], 'idtramite');
								if($request_procede==0)//No procede
									$tarea = $tareaTmp[1];
								else //Si procede
									//$tarea = $tareaTmp[1];
									$tarea = $tareaTmp[0];

								$tareaActual = $tarea['tarea_actual'];
								$tareaSiguiente = $tarea['tarea_siguiente'];
								$idTramite=$tramite['tramite_id'];
								$gestion=$this->session->get('currentyear');
								$lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);
								$obs = $datosJSONInscripcion_decode->observacion;
								$registroTramite = $this->get('wftramite')->guardarTramiteEnviado(
									$this->session->get('userId'),										//$usuario,
									$this->session->get('roluser'),										//$rol,
									$flujoTipo,															//$flujotipo,
									$tareaActual,														//$tarea,
									'institucioneducativa',												//$tabla,
									$sie,																//$id_tabla,
									$obs,																//$observacion,
									$varevaluacion,														//$varevaluacion,
									$idTramite,															//$idtramite,
									json_encode($datosJSONInscripcion_decode, JSON_UNESCAPED_UNICODE),	//$datos,
									'',//$lugarTipoLocalidad,											//$lugarTipoLocalidad_id,
									$lugarTipo['lugarTipoIdDistrito']									//$lugarTipoDistrito_id?
								);

								
								//$registroTramite['dato']=true;
								//$registroTramite['tipo']='exito';
								
								if($registroTramite)
								{
									if($registroTramite['dato']==true && $registroTramite['tipo']=='exito')
									{
										//volvemos a enviar el tramite para finalizarlo
										// $flujoTipo=$tramite['flujo_tipo_id'];
										
										// $tareaTmp = $this->get('wftramite')->obtieneTarea($tramite['tramite_id'], 'idtramite');
										// if($request_procede==0)//No procede
										// 	//$tarea = $tareaTmp[1];
										// 	$tarea = $tareaTmp[0];
										// else //Si procede
										// 	$tarea = $tareaTmp[0];

										// $tareaActual = $tarea['tarea_actual'];
										// $tareaSiguiente = $tarea['tarea_siguiente'];

										$idTramite=$tramite['tramite_id'];
										$lugarTipo = $this->get('wftramite')->lugarTipoUE($sie, $gestion);

										//No olvidar este paso
										$recibirTramite = $this->get('wftramite')->guardarTramiteRecibido(
											$this->session->get('userId'),
											$tareaSiguiente,
											$idTramite
										);
										$finalizar='SI';
										$datosJSONInscripcion_decode->finaliza=$finalizar;
										// $obs = $datosJSONInscripcion_decode->observacion;
										$registroTramite = $this->get('wftramite')->guardarTramiteEnviado(
											$this->session->get('userId'),										//$usuario,
											$this->session->get('roluser'),										//$rol,
											$flujoTipo,															//$flujotipo,?
											$tareaSiguiente,													//$tarea,
											'institucioneducativa',												//$tabla,?
											$sie,																//$id_tabla,?
											$obs,																//$observacion,
											$finalizar,														//$varevaluacion,
											$idTramite,															//$idtramite,
											json_encode($datosJSONInscripcion_decode, JSON_UNESCAPED_UNICODE),	//$datos,
											'',//$lugarTipoLocalidad,											//$lugarTipoLocalidad_id,
											$lugarTipo['lugarTipoIdDistrito']									//$lugarTipoDistrito_id?
										);
										//dump($registroTramite);die();
										// $data 	= $registroTramite['idtramite'];
										// $status = 200;
										// $msj 	= $registroTramite['msg'];

										if ($request_procede==0)
											$msj = 'El trámite Nº '. $idTramite. ' fué finalizado. NO FUE CONSOLIDADO';
										else 
											$msj = 'El trámite Nº '. $idTramite. ' fué CONSOLIDADO CORRECTAMENTE';
										// $em->getConnection()->commit();
										$data 	= NULL;
										$status = 200;
										

										// list($tieneErrores,$tablaInscripciones_procesada)=$this->obtenerNombresDeInscripciones($datosJSONInscripcion_decode);
										// $tablaInscripciones = $tablaInscripciones_procesada->inscripciones;
										// $determinarAprobacionOReprobacionSolicitud = $this->determinarAprobacionOReprobacionSolicitud($tablaInscripciones);
										
										// if($request_procede==1 && $determinarAprobacionOReprobacionSolicitud==true)// procede
										// 	$urlCertificacionConclusionEstudios = $this->generateUrl('regularizacion_estudiantesPostBachillerato_reporteDepartamentalPorConcluirConclusionEstudios',array('idtramite' => $registroTramite['idtramite'])) ;
										// else
										// 	$urlCertificacionConclusionEstudios = NULL;
									}
									else
									{
										$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
										$data 	= NULL;
										$status = 404;
										$msj 	= $registroTramite['msg'];
									}
								}
								else
								{
									$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
									$data 	= NULL;
									$status = 404;
									$msj 	= 'Ocurrio un error al guardar los archivos adjuntos, por favor vuelva a intentarlo.';
								}
							}
							else
							{
								if($varevaluacion==='SI')
									$msj 	= 'Ocurrio un error al guardar los archivos adjuntos, por favor vuelva a intentarlo.';
								else
									$msj 	= 'Debe describir una razón de por que no procede la solicitud.';
							}
						}
						else
						{
							$data 	= NULL;
							$status = 404;
							$msj 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
						}
					}
					else
					{
						$data 	= NULL;
						$status = 404;
						$msj='No se puede encontrar al estudiante, por favor verifique que el trámite fue realizado correctamente.';
					}
				}
				catch(Excepción $e)
				{
					//Esto ocurria si los archivos no pudieron ser guardados
					$this->borrarArchivo($gestion, $sieAnt, $rude, $nArcInforme);
					$data 	= NULL;
					$status = 404;
					$msj 	= 'Ocurrio un error al guardar el registro, por favor vuelva a intentarlo.';
				}
			}
			else
			{
				$data 	= NULL;
				$status = 404;
				$msj 	= 'Ocurrio un error debido a que no existen datos de las inscripciones, por favor verifique que el trámite sea correcto.';
			}
		}
		else
		{
			$data 	= NULL;
			$status = 404;
			$msj 	= 'Ocurrio un error debido a que no existe el trámite seleccionado o no se determino si procede o no la solicitud, por favor vuelva a intentarlo.';
		}

		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,
										'status'=>$status,
										'data'=>$data
										// 'urlCertificacionConclusionEstudios'=>$urlCertificacionConclusionEstudios
									));
	}

	private function inscribirMaterias($datosJSONInscripcion_decode)
	{
		$em 			= $this->getDoctrine()->getManager();
		$tieneErrores 	= false;
		$msj 			= '';

		$tieneEstudiante 		= property_exists($datosJSONInscripcion_decode,'estudiante');
		$tieneSie 				= property_exists($datosJSONInscripcion_decode,'sie');
		$tieneInscripciones 	= property_exists($datosJSONInscripcion_decode,'inscripciones');
		
		if($tieneEstudiante && $tieneSie && $tieneInscripciones)
		{
			$estudiante		 		= $datosJSONInscripcion_decode->estudiante;
			$estudiante_rude		= $estudiante->codigo_rude;
			$sie 					= $datosJSONInscripcion_decode->sie;
			$inscripciones 			= $datosJSONInscripcion_decode->inscripciones;
			$institucionEducativa 	= $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id'=>$sie));
			if($institucionEducativa)
			{
				if(count($inscripciones)>0)
				{
					$em->getConnection()->beginTransaction();
					try
					{	
						$gestion = $estudiante->gestion_tipo_id;
						foreach($inscripciones as $i)
						{
							$materias 	= $i->materias;
							// $gestion 	= $i->gestion;
							$nivel 		= $i->nivel;
							$grado 		= $i->grado;
							$paralelo 	= $i->paralelo;
							$turno 		= $i->turno;
							foreach($materias as $m)
							{
								$institucioneducativaCurso 	= $this->getInstitucionEducativaCurso($institucionEducativa,$gestion,$nivel,$grado,$paralelo,$turno);
								$estudianteInscripcion 		= $this->getEstudianteInscripcion($estudiante_rude,$institucioneducativaCurso,$em);
								$asignaturaTipo = $em->getRepository('SieAppWebBundle:AsignaturaTipo')->findOneBy(array('id'=>$m->id));
								
								//Creamos la institucion educativa Cursos o usamos uno que ya existe
								//insert into institucioneducativa_curso_oferta (asignatura_tipo_id,horasmes,insitucioneducativa_curso_id) values (3,0,145568796)-
								$institucioneducativaCursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')
																	  ->findOneBy(array('asignaturaTipo' =>$asignaturaTipo->getId(), 'insitucioneducativaCurso'=>$institucioneducativaCurso->getId()));
								if($institucioneducativaCursoOferta==null)
								{
									$institucioneducativaCursoOferta=new InstitucioneducativaCursoOferta();
									$institucioneducativaCursoOferta->setAsignaturaTipo($asignaturaTipo);
									$institucioneducativaCursoOferta->setHorasMes(0);
									$institucioneducativaCursoOferta->setInsitucioneducativaCurso($institucioneducativaCurso);
									$em->persist($institucioneducativaCursoOferta);
								}

								$gestionTipo 				= $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestion ));
								//Creamos la materia o usamos  uno que ya existe
								//insert into estudiante_asignatura (gestion_tipo_id,estudiante_inscripcion_id,institucioneducativa_curso_oferta_id) values (2009,155990498,69500824);--ADD 
								$estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
									'gestionTipo'=> $gestionTipo->getId(),
									'estudianteInscripcion'=>$estudianteInscripcion->getId(),
									'institucioneducativaCursoOferta'=>$institucioneducativaCursoOferta->getId()
								));
								if($estudianteAsignatura==null)
								{
									$estudianteAsignatura = new EstudianteAsignatura();
									$estudianteAsignatura->setGestionTipo($gestionTipo);
									$estudianteAsignatura->setFechaRegistro(new \DateTime(date('Y-m-d')));
									$estudianteAsignatura->setEstudianteInscripcion($estudianteInscripcion);
									$estudianteAsignatura->setInstitucioneducativaCursoOferta($institucioneducativaCursoOferta);
									$em->persist($estudianteAsignatura);
								}

								//CREAMOS LA NOTAS
								//insert into estudiante_nota (nota_tipo_id,estudiante_asignatura_id,nota_cuantitativa,nota_cualitativa,usuario_id) values(6,895550836,70,'ESTO ES LA NOTA CUALITATIVA',1);--ADD --POSIBLEMENTE NOTA_TIPO=9
								$promedioTipo =9;
								$notaTipo = $em->getRepository('SieAppWebBundle:NotaTipo')->findOneBy(array('id'=>$promedioTipo));
								$estudianteNota 	= new EstudianteNota();
								$estudianteNota->setNotaTipo($notaTipo);
								$estudianteNota->setEstudianteAsignatura($estudianteAsignatura);
								$estudianteNota->setNotaCuantitativa($m->nota);
								$estudianteNota->setNotaCualitativa('');
								$estudianteNota->setUsuarioId($this->session->get('userId'));
								$estudianteNota->setFechaRegistro(new \DateTime(date('Y-m-d')));
								$estudianteNota->setFechaModificacion(new \DateTime(date('Y-m-d')));
								$em->persist($estudianteNota);
							}
							$em->flush();
						}
						$em->getConnection()->commit();
						$tieneErrores = false;
						$msj = 'Los datos fueron guardados correctamente.';
					}
					catch(Exception $e)
					{
						$em->getConnection()->rollback();
						$tieneErrores = true;
						$msj = 'Ocurrio un problema al guardar las inscripciones, por favor vuelva a intentarlo.';
					}
				}
				else
				{
					$msj = 'La inscripción no es valida, por que no tiene materias.';
					$tieneErrores = true;
				}
			}
			else
			{
				$msj = 'La inscripción no es valida, por que no tiene una unidad educativa asignada.';
				$tieneErrores = true;
			}
		}
		else
		{
			$msj = 'La inscripción no es valida, por que no tiene todos los datos requeridos.';
			$tieneErrores = true;
		}
		return array($tieneErrores,$msj);
	}

	private function getInstitucionEducativaCurso($institucionEducativa,$gestion,$nivel,$grado,$paralelo,$turno)
	{
		$em = $this->getDoctrine()->getManager();
		$institucioneducativaCurso = null;
		$gestion 	= filter_var($gestion,FILTER_SANITIZE_NUMBER_INT);
		$nivel 		= filter_var($nivel,FILTER_SANITIZE_NUMBER_INT);
		$grado 		= filter_var($grado,FILTER_SANITIZE_NUMBER_INT);
		$paralelo 	= filter_var($paralelo,FILTER_SANITIZE_NUMBER_INT);
		$turno 		= filter_var($turno,FILTER_SANITIZE_NUMBER_INT);

		$gestion 	= is_numeric($gestion)?$gestion:-1;
		$nivel 		= is_numeric($nivel)?$nivel:-1;
		$grado 		= is_numeric($grado)?$grado:-1;
		$paralelo 	= is_numeric($paralelo)?$paralelo:-1;
		$turno 		= is_numeric($turno)?$turno:-1;

		$gestionTipo 	= $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestion ));
		$nivelTipo 		= $em->getRepository('SieAppWebBundle:NivelTipo')->findOneBy(array('id' => $nivel ));
		$gradoTipo 		= $em->getRepository('SieAppWebBundle:GradoTipo')->findOneBy(array('id' => $grado ));
		$paraleloTipo 	= $em->getRepository('SieAppWebBundle:ParaleloTipo')->findOneBy(array('id' => $paralelo ));
		$turnoTipo 		= $em->getRepository('SieAppWebBundle:TurnoTipo')->findOneBy(array('id' => $turno ));
		//select * from institucioneducativa_curso  where  institucioneducativa_id='82480024'  AND gestion_tipo_id='2009'  and nivel_tipo_id='3'  and grado_tipo_id='4'  and paralelo_tipo_id='1'  and turno_tipo_id='0' limit 10 --145568796
		$institucioneducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(
			array(
				'institucioneducativa'	=> $institucionEducativa,
				'gestionTipo'			=> $gestionTipo,
				'nivelTipo'				=> $nivelTipo,
				'gradoTipo'				=> $gradoTipo,
				'paraleloTipo'			=> $paraleloTipo,
				'turnoTipo'				=> $turnoTipo,
			)
		);

		return $institucioneducativaCurso;
	}

	private function getEstudianteInscripcion($estudiante_rude,$institucioneducativaCurso,$em)
	{
		//$em = $this->getDoctrine()->getManager();
		$estudianteInscripcion = null;
		$estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$estudiante_rude));
		if($estudiante && $institucioneducativaCurso)
		{
			$estudianteInscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(
				array(
					'estudiante'=>$estudiante->getId(),
					'institucioneducativaCurso'=>$institucioneducativaCurso->getId()
			));
			if($estudianteInscripcion==null)
			{	
				$estudianteInscripcion= $this->createEstudianteInscripcion($estudiante,$institucioneducativaCurso,$em);
			}
		}
		return $estudianteInscripcion;
	}

	public function createEstudianteInscripcion($estudiante,$institucioneducativaCurso,$em)
	{
		//$em 		= $this->getDoctrine()->getManager();
		$sie 		= $institucioneducativaCurso->getInstitucioneducativa();
		$gestion 	= $institucioneducativaCurso->getGestionTipo();
		$estudianteInscripcion = null;

		if($institucioneducativaCurso && $institucioneducativaCurso->getInstitucioneducativa())
		{
			$estudianteInscripcion = new EstudianteInscripcion();
			$estudianteInscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));
			$estudianteInscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
			//HAY QUE VERIFICAR ESTO DEL ESTADO DE LA MATRICULA se creara la matricula ID (106) con estadomatricula:PROMOVIDO POR PRE-BACHILLERATO
			$estudianteInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(106));
			$estudianteInscripcion->setEstudiante($estudiante);
			$estudianteInscripcion->setFechaInscripcion(new \DateTime('now'));
			$estudianteInscripcion->setOperativoId(1);
			$estudianteInscripcion->setCodUeProcedenciaId($institucioneducativaCurso->getInstitucioneducativa()->getId());
			$estudianteInscripcion->setFechaRegistro(new \DateTime('now'));
			$estudianteInscripcion->setInstitucioneducativaCurso($institucioneducativaCurso);
			$estudianteInscripcion->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(1));
			//$estudianteInscripcion->setObservacion(1);

			$em->persist($estudianteInscripcion);
			$em->flush();
		}

		return $estudianteInscripcion;
	}
}