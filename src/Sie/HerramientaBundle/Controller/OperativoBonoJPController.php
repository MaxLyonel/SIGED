<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;

use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\BjpEstudianteApoderadoBeneficiarios;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoModalidadAtencion;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoTextosEducativos;
use Sie\AppWebBundle\Entity\ApoderadoTipo;

class OperativoBonoJPController extends Controller
{
	public $session;
	public $estado;
	public function __construct()
	{
		$this->session = new Session();
		$this->estado = 14;
		/* Verificar login*/
		$id_usuario = $this->session->get('userId');
		//if (!isset($id_usuario))
		if(false)
		{
			return $this->redirect($this->generateUrl('login'));
		}
	}

	public function cerrarAction(Request $request)
	{
		$esAjax=$request->isXmlHttpRequest();

		$form = $request->get('form');

		$request_sie = $form['sie'];
		$request_sie = filter_var($request_sie,FILTER_SANITIZE_NUMBER_INT);
		$request_sie = is_numeric($request_sie)?$request_sie:-1;

		$request_gestion = $form['gestion'];
		$request_gestion = filter_var($request_gestion,FILTER_SANITIZE_NUMBER_INT);
		$request_gestion = is_numeric($request_gestion)?$request_gestion:-1;

		$request_estado = $this->estado; // si existe algun regsitro con el nro 14 en el estado_menu, eso signifca que el operativo de esa unidad educativa ya fue cerrada

		$data=null;
		$status= 404;
		$msj='Ocurrio un error, por favor vuelva a intentarlo.';
		$reporte = '';
		$observacionesBonpJP = null;
		try
		{
			list($observacionesBonpJP, $observacionesControlCalidad) = $this->puedeCerrarOperativo($request_gestion,$request_sie);
			if(!$observacionesBonpJP && !$observacionesControlCalidad)
			{
				if($esAjax && $request_sie >0 && $request_gestion >0)
				{
					list($data,$status,$msj)=$this->get('operativoutils')->cerrarOperativo($request_sie,$request_gestion,$request_estado);
					if($status == 200)
					{
						//$this->registrarDatosBonoBJP($request_sie, $request_gestion);
						$reporte = $this->generateUrl('operativo_bono_jp_ddjj',array("sie"=>$request_sie,"gestion"=>$request_gestion));
					}
				}
			}
			else
			{
				$data=null;
				$status= 200;
				$msj='No se puedo cerrar el operativo, todavia tiene inconsistencias.';
			}
		}
		catch(Exception $e)
		{
			$data=null;
			$status= 404;
			$msj='Ocurrio un error al cerrar el operativo, por favor vuelva a intentarlo.';
		}
		$observaciones = array_merge($observacionesBonpJP,$observacionesControlCalidad);
		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj,'urlReporte'=>$reporte,'observacionesBonpJP'=>$observaciones));
	}

	public function abrirAction(Request $request,$id)
	{
		$esAjax=$request->isXmlHttpRequest();

		$form = $request->get('form');

		$request_id = $id;
		$request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
		$request_id = is_numeric($request_id)?$request_id:-1;

		$request_estado = $this->estado; // si existe algun regsitro con el nro 14 en el estado_menu, eso signifca que el operativo de esa unidad educativa ya fue cerrada

		$data=null;
		$status= 404;
		$msj='Ocurrio un error, por favor vuelva a intentarlo.';
		try
		{
			if($esAjax && $request_id >0 && $request_estado>0)
			{
				list($data,$status,$msj)=$this->get('operativoutils')->abrirOperativo($request_id,$request_estado);
			}
		}
		catch(Exception $e)
		{
			$data=null;
			$status= 404;
			$msj=$e->getMessage();
		}
		
		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
	}

	public function seguimientoAction(Request $request)
	{

	  $em = $this->getDoctrine()->getManager();
	  $departamento=-1;
	  $distrito=-1;
	  $userId=$this->session->get('userId');
	  $userRol=$this->session->get('roluser');
	  $datosUser=$this->get('operativoutils')->getDatosUsuario($userId,$userRol);
	  if($datosUser)
	  {
		$departamentoDistrito=$datosUser['cod_dis'];
		list($departamento,$distrito)=$this->get('operativoutils')->getDepartamentoDistrito($departamentoDistrito);

		$arrayDepartamentos = array();
		$arrayDistritos = array();
		$arrayUE = array();

		$rol= $datosUser['rol_tipo_id'];
		
		if(in_array($rol,[8,34]))//nacional
		{
			$arrayDepartamentos = $this->get('operativoutils')->getDepartamentos();
			$arrayDistritos = array();
			$arrayUE = array();
		}
		else if(in_array($rol,[7,9]))//departamental
		{
			$tmpDepartamento= $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($departamento);
			$arrayDepartamentos[] = array('id'=>$tmpDepartamento->getId(),'codigo'=>$tmpDepartamento->getCodigo(),'depto'=>$tmpDepartamento->getDepartamento());

			$arrayDistritos = $this->get('operativoutils')->getDistritos($departamento);
			$arrayUE = array();
		}
		else if(in_array($rol,[10]))//distrital
		{
			$tmpDepartamento= $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($departamento);
			$arrayDepartamentos[] = array('id'=>$tmpDepartamento->getId(),'codigo'=>$tmpDepartamento->getCodigo(),'depto'=>$tmpDepartamento->getDepartamento());

			$tmpDistrito = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($distrito);
			$arrayDistritos[] = array('id' =>$tmpDistrito->getId(),'distrito'=>$tmpDistrito->getDistrito());

			//$arrayUE=$this->getUnidadesEducativas($departamento,$distrito);
		}
		else//ningun rol permitido
		{
			return $this->redirect($this->generateUrl('login'));
		}

		return $this->render('SieHerramientaBundle:BonoJP:segumientoOperativoBonoJP.html.twig',array
		(
			'rol' => $rol,
			'departamentos'=>$arrayDepartamentos,
			'distritos'=>$arrayDistritos,
			//'ues'=>$arrayUE,
		));
	  }
	  else
	  {
		return $this->redirect($this->generateUrl('login'));
	  }
	}

	public function puedeCerrarOperativo($gestion,$sie)
	{
		//select * from sp_validacion_bono_juancito_pinto_web('2021','81400072');
		$observacionesBonpJP = null;
		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();
		$query = 'select * from sp_validacion_bono_juancito_pinto_web(?,?);';
		$stmt = $db->prepare($query);
		$params = array($gestion,$sie);
		$stmt->execute($params);

		$observacionesBonpJP = $stmt->fetchAll();
		$observacionesControlCalidad = $this->getObservacionesControlCalidad(array('gestion' => $gestion,'sie' => $sie));

		if($observacionesBonpJP == null)
			$observacionesBonpJP = [];
		
		if($observacionesControlCalidad == null)
			$observacionesControlCalidad = [];

		return array($observacionesBonpJP, $observacionesControlCalidad);
	}

	private function getObservacionesControlCalidad($data)
	{
		$data['reglas'] = '12,13,26,24,25,8,15,20,37,63,60,61,62';
		// added to 2021 about qa
		// $years = $data['gestion'].' ,'.$data['gestion'];
		$years = $this->session->get('currentyear');
		$em = $this->getDoctrine()->getManager();
		$query = $em->getConnection()->prepare("
		select vp.obs as observacion
		from validacion_proceso vp
		where vp.institucion_educativa_id = '".$data['sie']."' and vp.gestion_tipo_id in (".$years.")
		and vp.validacion_regla_tipo_id in (".$data['reglas'].")
		and vp.es_activo = 'f'
		");
		$query->execute();
		$objobsQA = $query->fetchAll();

		return $objobsQA;
	}

	public function mostrarDatosAction(Request $request)
	{
		$form = $request->request->all();

		$departamento = $form['departamento'];
		$distrito = $form['distrito'];
		$gestion = $form['gestion'];

		$sysName = $this->session->get('sysname');
		$sysName = strtolower(filter_var($sysName , FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
		$ieTipo=$this->get('operativoutils')->getTipoUE($sysName);

		$departamento = filter_var($departamento,FILTER_SANITIZE_NUMBER_INT);
		$distrito = filter_var($distrito,FILTER_SANITIZE_NUMBER_INT);
		$gestion = filter_var($gestion,FILTER_SANITIZE_NUMBER_INT);

		$departamento = is_numeric($departamento)?$departamento:-1;
		$distrito = is_numeric($distrito)?$distrito:-1;
		$gestion = is_numeric($gestion)?$gestion:-1;

		$datos=$this->get('operativoutils')->getUnidadesEducativas($departamento,$distrito,$gestion,$this->estado,$tipoUENopermitidos=array(3));
		return $this->render('SieHerramientaBundle:BonoJP:mostrarDatosOperativoBonoJP.html.twig',array(
			'datos' => $datos,
			'periodo' => $ieTipo,
		));
	}

	public function ddjjAction(Request $request, $sie,$gestion)
	{
		$pdf=$this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesApoderados_Benef_UnidadEducativa_v1_EEA.rptdesign&__format=pdf'.'&ue='.$sie.'&gestion='.$gestion;
		//$pdf='http://127.0.0.1:63170/viewer/preview?__report=D%3A\workspaces\workspace_especial\bono-bjp\reg_lst_EstudiantesApoderados_Benef_UnidadEducativa_v1_EEA.rptdesign&__format=pdf'.'&ue='.$sie.'&gestion='.$gestion;
		
		$status = 200;	
		$arch           = 'DECLARACION_JURADA_BONO_JUANCITO_PINTO-'.date('Y').'_'.date('YmdHis').'.pdf';
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

	public function registrarDatosBonoBJP($sie, $gestion)
	{
		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();

		$query = '
		INSERT INTO public.bjp_estudiante_apoderado_beneficiarios(
			institucioneducativa_id, nivel_tipo_id, grado_tipo_id, paralelo_tipo_id,
			turno_tipo_id, estudiante_inscripcion_id, estudiante_id, codigo_rude,
			carnet_est, complemento_est, paterno_est, materno_est, nombre_est,
			fecha_nacimiento_est, persona_id, carnet_tut, complemento_tut,
			paterno_tut, materno_tut, nombre_tut, apoderado_tipo_id, segip_id_tut,
			fecha_registro, fecha_actualizacion, observacion,estadomatricula_tipo_id)
		 select a.institucioneducativa_id,a.nivel_tipo_id,a.grado_tipo_id,a.paralelo_tipo_id,a.turno_tipo_id,c.estudiante_inscripcion_id,b.estudiante_id,e.codigo_rude,e.carnet_identidad,e.complemento,e.paterno,e.materno,e.nombre,e.fecha_nacimiento,c.persona_id
		 ,d.carnet,d.complemento,d.paterno,d.materno,d.nombre,c.apoderado_tipo_id,d.segip_id,current_date,null as fecha_actualizacion,null as observacion,b.estadomatricula_tipo_id
		 from institucioneducativa_curso a
		 inner join estudiante_inscripcion b on a.id=b.institucioneducativa_curso_id
		 inner join bjp_apoderado_inscripcion_beneficiarios c on b.id=c.estudiante_inscripcion_id
		 inner join persona d on c.persona_id=d.id
		 inner join estudiante e on b.estudiante_id=e.id
		 where a.gestion_tipo_id= ?
		 and a.institucioneducativa_id = ?;
		';
		$stmt = $db->prepare($query);
		$params = array($gestion,$sie);
		$stmt->execute($params);
		$requisitos=$stmt->fetch();
	}
















	public function formularioCambiarTutorAction()
	{
		return $this->render('SieHerramientaBundle:BonoJP:fomularioCambiarTutor.html.twig');
	}

	public function buscarInscripcionesAction(Request $request)
	{
		$codigo_rude = $request->get('codigo_rude');
		$gestion = date('Y');
		$estado_matricula = 4;
		
		$em = $this->getDoctrine()->getManager();
		$query = $em->getConnection()->prepare("SELECT  * FROM sp_genera_estudiante_historial(?) where gestion_tipo_id_raep = ? AND estadomatricula_tipo_id_fin_r = ?");
		$params = array($codigo_rude, $gestion, $estado_matricula);
		$query->execute($params);
		$dataInscription = $query->fetchAll();

		$dataInscriptionR = $dataInscriptionE = array();
		foreach ($dataInscription as $key => $inscription)
		{
			switch ($inscription['institucioneducativa_tipo_id_raep'])
			{
				case '1':
					$dataInscriptionR[$key] = $inscription;	
					$inscriptionId = $dataInscriptionR[$key]['estudiante_inscripcion_id_raep'];
				break;
				case '4':
					$dataInscriptionE[$key] = $inscription;
					$inscriptionId = $dataInscriptionR[$key]['estudiante_inscripcion_id_raep'];
				break;
			}
		}


		$tutoresActuales = $this->listarTutores($inscriptionId,1);
		$tutoresEliminados = $this->listarTutores($inscriptionId,2);

		return $this->render('SieHerramientaBundle:BonoJP:inscripcionesEstudianteBonoJP.html.twig', array(
			'inscripcionesRegular' => $dataInscriptionR,
			'inscripcionesEspecial' => $dataInscriptionE,
			'tutoresActuales' => $tutoresActuales,
			'tutoresEliminados' => $tutoresEliminados
		));
	}

	public function buscarTutoresAction(Request $request,$inscripcion)
	{ 
	// dump($inscripcion); exit();
		$tutoresActuales = $this->listarTutores($inscripcion,1);
		$tutoresEliminados = $this->listarTutores($inscripcion,2);
		/*
		$status = 200;
		$msj = '';

		$data =  array(
			'tutoresActuales' => $this->render('SieHerramientaBundle:BonoJP:listarTutores.html.twig',array('tutores' => $tutoresActuales)),
			'tutoresEliminados' => $this->render('SieHerramientaBundle:BonoJP:listarTutores.html.twig',array('tutores' => $tutoresEliminados))
		);*/
		//$response = new JsonResponse($data,$status);
		//$response->headers->set('Content-Type', 'application/json');
		//return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));

		return $this->render('SieHerramientaBundle:BonoJP:listarTutores.html.twig',array('inscripcionid' => $inscripcion,'tutoresActuales' => $tutoresActuales,'tutoresEliminados' => $tutoresEliminados));
	}

	public function listarTutores ($inscripcion, $estado = 1)
	{
		$em = $this->getDoctrine()->getManager();
		/*
		$parents = $em->createQueryBuilder()
						->select('
							ai.id,
							p.id as personaid,
							p.carnet,
							p.complemento,
							p.paterno, 
							p.materno, 
							p.nombre as nombre,
							p.fechaNacimiento,
							IDENTITY(ai.apoderadoTipo) AS apoderadoTipo
						')
						->from('SieAppWebBundle:BjpApoderadoInscripcionBeneficiarios','ai')
						->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ai.estudianteInscripcion = ei.id')
						->innerJoin('SieAppWebBundle:Persona','p','with','ai.persona = p.id')

						->where('ei.id = :inscriptionId')
						->andWhere('p.segipId = 1')
						->setParameter('inscriptionId', $inscripcion)
						->orderBy('ai.id','DESC');
		*/
		$parents = $em->createQueryBuilder()
						->select('
							beab.id,
							beab.personaId as personaid,
							beab.carnetTut as carnet,
							beab.complementoTut as complemento,
							beab.paternoTut as paterno, 
							beab.maternoTut as materno, 
							beab.nombreTut  as nombre,
							p.fechaNacimiento as fechaNacimiento,
							IDENTITY(beab.apoderadoTipo) AS apoderadoTipo,
							ap.apoderado as apoderado,
							beab.estudianteInscripcionId as estudianteInscripcion,
							beab.fechaActualizacion as fechaActualizacion
						')
						->from('SieAppWebBundle:BjpEstudianteApoderadoBeneficiarios','beab')
						->innerJoin('SieAppWebBundle:Persona','p','with','p.id = beab.personaId')
						->innerJoin('SieAppWebBundle:ApoderadoTipo','ap','with','ap.id = beab.apoderadoTipo')
						->where('beab.estudianteInscripcionId = :inscriptionId')
						->andWhere('beab.segipIdTut = 1')
						->andWhere('beab.estadoId = :estado')
						->setParameter('inscriptionId', $inscripcion)
						->setParameter('estado', $estado)
						->orderBy('beab.id','DESC');
		$parents = $parents->getQuery()->getResult();
		
		return $parents;
	}

	public function cambiarEstadoTutoresAction(Request $request,$id,$estado)
	{
		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();
		$esAjax=$request->isXmlHttpRequest();

		$request_id = $id;
		$request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
		$request_id = is_numeric($request_id)?$request_id:-1;

		$request_estado = $estado;
		$request_estado = filter_var($request_estado,FILTER_SANITIZE_NUMBER_INT);
		$request_estado = is_numeric($request_estado)?$request_estado:-1;


		$data=null;
		$status= 404;
		$msj='Ocurrio un error, por favor vuelva a intentarlo';

		if($esAjax && $request_id >0)
		{
			$query ="update bjp_estudiante_apoderado_beneficiarios set estado_id = ? where id = ?";
			$stmt = $db->prepare($query);
			$params = array($request_estado, $request_id);
			$stmt->execute($params);
			$tmp=$stmt->fetchAll();

			$borrado = $em->getRepository('SieAppWebBundle:BjpEstudianteApoderadoBeneficiarios')->findOneBy(array('id' => $request_id, 'estadoId' => $request_estado));

			if($borrado!=null)
			{
				$query ="update bjp_estudiante_apoderado_beneficiarios set fecha_actualizacion = ?";
				$stmt = $db->prepare($query);
				//$params = array(new \DateTime(date('Y-m-d')));
				$params = array(date('Y-m-d'));
				$stmt->execute($params);
				$tmp=$stmt->fetchAll();

				$data='ok';
				$status= 200;
				$msj='Los datos fueron eliminados correctamente';
			}
			else
			{
				$data=null;
				$status= 404;
				$msj='Ocurrio un error, por favor vuelva a intentarlo';
			}
		}
		else
		{
			$data=null;
			$status= 404;
			$msj='Ocurrio un error, por favor vuelva a intentarlo';
		}
		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj));
	}
	public function buscar_validar_persona_ci_segipAction(Request $request){
		$inscripcion = $request->get('inscripcionid');
		$ci = $request->get('ci');
		$complemento = $request->get('complemento');
		// echo ">".$inscripcion.">".$ci;exit();
		$em = $this->getDoctrine()->getManager();
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('carnet' => $ci, 'complemento' => $complemento));
        $estado = false;
        $mensaje = "";
        // dump($persona); exit();
        if($persona){
        	$parents = $em->createQueryBuilder()
				->select('
					beab.id
				')
				->from('SieAppWebBundle:BjpEstudianteApoderadoBeneficiarios','beab')
				->innerJoin('SieAppWebBundle:Persona','p','with','p.id = beab.personaId')
				->innerJoin('SieAppWebBundle:ApoderadoTipo','ap','with','ap.id = beab.apoderadoTipo')
				->where('beab.carnetTut = :carnet')
				->andWhere('beab.estudianteInscripcionId = :inscriptionId')
				->andWhere('beab.segipIdTut = 1')
				->setParameter('inscriptionId', $inscripcion)
				->setParameter('carnet', $ci);
				$parents = $parents->getQuery()->getResult();
			// dump($parents); exit();
        	if ($parents==true) {
        		$mensaje = "Tutor ya se encuntra registrado ACTIVO o en la tabla de ELIMINADOS. ";
        		$data = array( 0 => 1,1=>$mensaje);
        	}else{
        		$data = array(
	        		0 => $ci,
				    1 => $persona->getPaterno(),
				    2 => $persona->getMaterno(),
				    3 => $persona->getNombre(),
				    4 => $persona->getFechaNacimiento()->format('d-m-Y'),
				    5 => $persona->getId()
				);
				/*$datos = array(
	                'complemento'=>$persona->getComplemento(),
	                'primer_apellido'=>$persona->getPaterno(),
	                'segundo_apellido'=>$persona->getMaterno(),
	                'nombre'=>$persona->getNombre(),
	                'fecha_nacimiento'=>$persona->getFechaNacimiento()->format('d-m-Y')
	            );       
		        $resultadoPersona = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($ci,$datos,'prod','academico');
		        // dump($resultadoPersona);exit();
		        if($resultadoPersona){ 
		        	$data = array(
		        		0 => $ci,
					    1 => $persona->getPaterno(),
					    2 => $persona->getMaterno(),
					    3 => $persona->getNombre(),
					    4 => $persona->getFechaNacimiento()->format('d-m-Y'),
					    5 => $persona->getId()
					);		
		        } else {
		            $mensaje = "No se realizó la validación con SEGIP. Debe actualizar la información a través del módulo: Modificación de Datos.";
		        	$data = array(0=>2,1=>$mensaje);
		        } 	*/ 
        	}
        }else{
        	$data = array(0=>0,1=>$mensaje);
        }
   		return new JsonResponse($data);
	}
	public function operativo_bono_jp_cambiarEstadoTutoreAction(Request $request){ 
		$id = $request->get('id');
		$estado = $request->get('estado');
		$estudianteInscripcion = $request->get('estudianteInscripcion');
		// echo ">".$id.">".$estado.">".$estudianteInscripcion;exit()
		return $this->render('SieHerramientaBundle:BonoJP:form_tutor_estudiante.html.twig',array('id' => $id,'estado' => $estado,'estudianteInscripcion' => $estudianteInscripcion));
	}
	public function guardar_datos_tutorAction(Request $request){
		$em = $this->getDoctrine()->getManager();

		$id_bjp_estudiante_apoderado_beneficiarios = $request->get('id');
		$estado = $request->get('estado');
		$inscripcionid = $request->get('estudianteInscripcion');

		// $caso = $request->get('caso');
		$obs = mb_strtoupper($request->get('obs'),'utf-8');

		$ci = $request->get('ci1');
		$complemento1 = $request->get('complemento1');
		$form_idfecnac = $request->get('form_idfecnac');
		$paterno = mb_strtoupper($request->get('paterno'),'utf-8');
		$materno = mb_strtoupper($request->get('materno'),'utf-8');
		$nombre = mb_strtoupper($request->get('nombre'),'utf-8');
		$parentesco = $request->get('parentesco');
		$extranjero = $request->get('extranjero');
		$genero = $request->get('genero');
		$idpersona = $request->get('idpersona');

		// echo ">".$ci.">".$form_idfecnac.">".$paterno.">".$materno.">".$nombre.">".$idpersona;exit();

        // $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('carnet' => $ci));
        // dump($persona); die;
        $data = array('error'=>0, 'message'=>'Datos registados');
        if( $idpersona ==0 ){
        	$datos = array(
                'complemento'=>$complemento1,
                'primer_apellido'=>$paterno,
                'segundo_apellido'=>$materno,
                'nombre'=>$nombre,
                'fecha_nacimiento'=>$form_idfecnac
            );   
	        $resultadoPersona = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet($ci,$datos,'prod','academico');
	        // dump($resultadoPersona);exit();
	        if ($resultadoPersona) {
	        	$newPersona = new Persona();
	        	$newPersona->setCarnet($ci);
	        	$newPersona->setComplemento($complemento1);
	        	$newPersona->setPaterno($paterno);
	        	$newPersona->setMaterno($materno);
	        	$newPersona->setNombre($nombre);
	        	$newPersona->setFechaNacimiento(new \DateTime($form_idfecnac));
	        	$em->persist($newPersona);
	        	// $newPersona->setMaterno($carnet);
	        	$idpersona = $newPersona->getId();
		        // save all data
	            $em->flush();  
	        	
	        }else{
	        	$data = array('error'=>1, 'message'=>'Error con la verificacion segip');
	        }
        }
        if(!$data['error']){
        	
	        $edotarBJP = $em->getRepository('SieAppWebBundle:BjpEstudianteApoderadoBeneficiarios')->find($id_bjp_estudiante_apoderado_beneficiarios);
	        $edotarBJP->setObservacion($obs);
	        $edotarBJP->setEstadoId(2);
	        $em->persist($edotarBJP);
	        $em->flush();

			$obj=$this->mostra_datos_fer($inscripcionid);        
        	///existe tabla persona los datos
	        ////////////////////////////////////////
            // this is to the new  bjp_estudiante_apoderado_beneficiarios////
            ////////////////////////////////////////
            $newEstudianteInscTutor = new BjpEstudianteApoderadoBeneficiarios();
            // dump($newEstudianteInscTutor); exit();
            $newEstudianteInscTutor->setNivelTipoId($obj['nivel_tipo_id']);
            $newEstudianteInscTutor->setGradoTipoId($obj['grado_tipo_id']);
            $newEstudianteInscTutor->setParaleloTipoId($obj['paralelo_tipo_id']);
            $newEstudianteInscTutor->setTurnoTipoId($obj['turno_tipo_id']);
            $newEstudianteInscTutor->setEstudianteInscripcionId($inscripcionid);
            $newEstudianteInscTutor->setEstudianteId($obj['estudiante_id']);
            $newEstudianteInscTutor->setCodigoRude($obj['codigo_rude']);
            $newEstudianteInscTutor->setCarnetEst($obj['carnet_identidad']);
            $newEstudianteInscTutor->setInstitucioneducativaId($obj['institucioneducativa_id']);
            $newEstudianteInscTutor->setComplementoEst($obj['complemento']);
            $newEstudianteInscTutor->setPaternoEst($obj['paterno']);
            $newEstudianteInscTutor->setMaternoEst($obj['materno']);
            $newEstudianteInscTutor->setNombreEst($obj['nombre']);
            $newEstudianteInscTutor->setFechaNacimientoEst(new \DateTime($obj['fecha_nacimiento']));

            // $newEstudianteInscTutor->setPersonaId($persona->getId());
            $newEstudianteInscTutor->setPersonaId($idpersona);
            $newEstudianteInscTutor->setCarnetTut($ci);
            $newEstudianteInscTutor->setComplementoTut($complemento1);
            $newEstudianteInscTutor->setPaternoTut($paterno);
            $newEstudianteInscTutor->setMaternoTut($materno);
            $newEstudianteInscTutor->setNombreTut($nombre);
            $newEstudianteInscTutor->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($parentesco));
            $newEstudianteInscTutor->setSegipIdTut(1);
            $newEstudianteInscTutor->setFechaRegistro(new \DateTime('now'));
            $newEstudianteInscTutor->setFechaActualizacion(new \DateTime('now'));
            $newEstudianteInscTutor->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($obj['estadomatricula_tipo_id']));
            $newEstudianteInscTutor->setEstadoId(1);
            // dump($newEstudianteInscTutor); exit();
            $em->persist($newEstudianteInscTutor);
	        // save all data        
	        /*dump($data);
			exit();*/
	  		// $em->getConnection()->beginTransaction();
	    	$em->flush();
        }
   		return new JsonResponse($data);
	}
	public function mostra_datos_fer($inscripcionid){
		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();
		$query = 'SELECT a.institucioneducativa_id, a.nivel_tipo_id, a.grado_tipo_id, a.paralelo_tipo_id, a.turno_tipo_id, 	b.id, b.estudiante_id, 	e.codigo_rude, e.carnet_identidad, e.complemento, 	e.paterno, e.materno, e.nombre, e.fecha_nacimiento,b.estadomatricula_tipo_id, e.id as estudiante_id FROM institucioneducativa_curso AS a
		INNER JOIN estudiante_inscripcion AS b ON a.id = b.institucioneducativa_curso_id
		INNER JOIN estudiante AS e ON b.estudiante_id = e.id
		WHERE b.id = ? ';
		// dump($query); exit();
		$stmt = $db->prepare($query);
		$params = array($inscripcionid);
		$stmt->execute($params);
		return $stmt->fetch();
	}
	public function cambiarEstadoTutores_restablecerAction(Request $request){
		$id_bjp_estudiante_apoderado_beneficiarios = $request->get('id');
		$estado = $request->get('estado');
		$inscripcion = $request->get('inscripcion');
		// echo ">".$id.">".$estado.">".$inscripcion;exit();
		$valor=$this->validar_tutor_active($inscripcion);
		if ($valor) {
			$data = array(0=>1);
		}else{
			$em = $this->getDoctrine()->getManager();
			$db = $em->getConnection();
			$query ="update bjp_estudiante_apoderado_beneficiarios set estado_id = ? where id = ?";
			$stmt = $db->prepare($query);
			$params = array($estado, $id_bjp_estudiante_apoderado_beneficiarios);
			$stmt->execute($params);
			$tmp=$stmt->fetchAll();
			$data = array(0=>0);
		}
   		return new JsonResponse($data);
	}
	function validar_tutor_active($inscripcion){
		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();
		$query = 'SELECT estado_id FROM bjp_estudiante_apoderado_beneficiarios WHERE  estudiante_inscripcion_id=? AND estado_id=1 ';
		// dump($query); exit();
		$stmt = $db->prepare($query);
		$params = array($inscripcion);
		$stmt->execute($params);
		return $stmt->fetch();
	}
	

}
