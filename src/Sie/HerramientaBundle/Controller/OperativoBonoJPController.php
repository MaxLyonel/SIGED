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
use ZipArchive;
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
		//dump($request); die;
		$esAjax=$request->isXmlHttpRequest();

		$form = $request->get('form');

		$request_sie = hex2bin($form['sie']);
		$request_sie = filter_var($request_sie,FILTER_SANITIZE_NUMBER_INT);
		$request_sie = is_numeric($request_sie)?$request_sie:-1;

		$request_gestion = hex2bin($form['gestion']);
		$request_gestion = filter_var($request_gestion,FILTER_SANITIZE_NUMBER_INT);
		$request_gestion = is_numeric($request_gestion)?$request_gestion:-1;

		/*dump($request_sie);
		dump($request_gestion);
		die;*/

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
		
		if(in_array($rol,[8,34,20]))//nacional
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
		$data['reglas'] = '13,8,15,20,63,60';
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
		//dump($gestion); die;
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
		//return $this->redirect($this->generateUrl('login'));
		//dump($this->session->get('pathSystem'));die; sieEspecialBundle
		return $this->render($this->session->get('pathSystem') .':BonoJP:fomularioCambiarTutor.html.twig');
	}

	public function buscarInscripcionesAction(Request $request)
	{


		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();

		$this->session = new Session();
        $sesinst = $request->getSession()->get('ie_id');
        // echo ">".$sesinst;exit();
		/*$sesion = $request->getSession();
        $id_usuario = $sesion->get('userI');

        echo ">".$id_usuario;exit();*/
		$whereregular = ' ';
        if($this->session->get('pathSystem')=='SieEspecialBundle'){
        	$idtipoInstitucion=4;
        }else{
        	$idtipoInstitucion=1;
        	$whereregular = 'and ei.estadomatricula_tipo_id in (4,5,55,11,10) and iec.nivel_tipo_id in (12,13,403,405,402,410,401,404,999) ';
        }


		$codigo_rude = $request->get('codigo_rude');
		$gestion = date('Y');
		$estado_matricula = 4;
		$swError = false;
		$messageError = false;
		if($idtipoInstitucion==4){
			//2023 bjp EXCEPTO SERVICIOS, INDIRECTA Y TALENTO Y DIF. APRENDIZAJE
			$query = "select iec.institucioneducativa_id
					from estudiante e
					inner join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
					inner join institucioneducativa_curso iec on ( ei.institucioneducativa_curso_id = iec.id)
					inner join institucioneducativa_curso_especial iece on ( iec.id = iece.institucioneducativa_curso_id )
					inner join institucioneducativa inst on (iec.institucioneducativa_id = inst.id)
					where e.codigo_rude= '".$codigo_rude."' and gestion_tipo_id = ".$this->session->get('currentyear')." and institucioneducativa_tipo_id = ".$idtipoInstitucion."	
					and iec.nivel_tipo_id <>410 and iece.especial_area_tipo_id in (1,2,3,4,10,12) and iece.especial_modalidad_tipo_id =1
					";
		}
		if($idtipoInstitucion==1){
			$query = "select iec.institucioneducativa_id
			from estudiante e
			inner join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
			inner join institucioneducativa_curso iec on ( ei.institucioneducativa_curso_id = iec.id)
			inner join institucioneducativa inst on (iec.institucioneducativa_id = inst.id)
			where e.codigo_rude= '".$codigo_rude."' and gestion_tipo_id = ".$this->session->get('currentyear')." and institucioneducativa_tipo_id = ".$idtipoInstitucion."	
			". $whereregular;
		}
		
		
		// dump($query);die();
		$query2 = $em->getConnection()->prepare($query);
		$query2->execute();
        $currentInscription = $query2->fetchAll();
        // dump(sizeof($currentInscription)); die();
		 //check if the student has current inscription and especial,conditioms
		if(sizeof($currentInscription)==0 && $idtipoInstitucion==4){
			$messageError = 'El estudiante no cumple las condiciones de cobro de BJP para Educación Especial';
	        $swError = true;
		}
         //check if the student has current inscription

        if(sizeof($currentInscription)>0){
         	// if the student is in the same UE
         	if($currentInscription[0]['institucioneducativa_id']!=$this->session->get('ie_id')){
         		if( ($this->session->get('roluser') == '7') || ($this->session->get('roluser') == '8')  || ($this->session->get('roluser') == '9')) {
         			$swError = false;
         		}else{
	         		$messageError = 'El estudiante no esta inscrito en esta UE';
	         		$swError = true;
         		}
         	} 
			 if(($this->session->get('roluser') == '7' || $this->session->get('roluser') == '8'  || $this->session->get('roluser') == '9' || $this->session->get('roluser') == '10') && $idtipoInstitucion==4){
					$swError = false;
			}  
         	/*if( ($this->session->get('roluser') == '7') || ($this->session->get('roluser') == '8')  || ($this->session->get('roluser') == '9')) {
         		// $messageError = 'roles...';
         		$swError = false;
         	}else{
	         	if($currentInscription[0]['institucioneducativa_id']!=$this->session->get('ie_id')){
	         		$messageError = 'El estudiante no esta inscrito en esta UE';
	         		$swError = true;
	         	}         		
         	}*/
        }else{
         	$messageError = 'El estudiante no cuenta con inscription';
         	$swError = true;
        }

		$dataInscriptionR = array();
		$dataInscriptionE= array();
		$tutoresActuales= array();
		$tutoresEliminados= array();

        if(!$swError){

			$em = $this->getDoctrine()->getManager();
			if($idtipoInstitucion == 1)
			{
				$query = $em->getConnection()->prepare("SELECT  * FROM sp_genera_estudiante_historial(?) where gestion_tipo_id_raep = ? AND estadomatricula_tipo_id_fin_r  IN (4,5,55,11,10) ");
				// $params = array($codigo_rude, $gestion, $estado_matricula);
				$params = array($codigo_rude, $gestion);

			} else {
				$query = $em->getConnection()->prepare("SELECT  * FROM sp_genera_estudiante_historial(?) where gestion_tipo_id_raep = ?");
				$params = array($codigo_rude, $gestion);
			}
 //educ especial
			if($idtipoInstitucion == 4)
			{
				$query = $em->getConnection()->prepare("SELECT  * FROM sp_genera_estudiante_historial_gen(?) where  gestion_tipo_id_raep = ? AND especial_modalidad_tipo_id_e=1 AND area_especial_id_e  IN (1,2,3,4,10,12) AND nivel_id_e<>410 ");
				// $params = array($codigo_rude, $gestion, $estado_matricula);
				$params = array($codigo_rude, $gestion);

			}

			$query->execute($params);
			$dataInscription = $query->fetchAll();
			// dump($dataInscription); exit();
			$dataInscriptionR = $dataInscriptionE = array();
			$inscriptionId =-1;
			foreach ($dataInscription as $key => $inscription)
			{
				// if ($inscription['institucioneducativa_id_raep']==$sesinst) {
				// 	# code...
				// }
				switch ($inscription['institucioneducativa_tipo_id_raep'])
				{
					case '1':
						$dataInscriptionR[$key] = $inscription;	
						$inscriptionId = $dataInscriptionR[$key]['estudiante_inscripcion_id_raep'];
					break;
					case '4':
						$dataInscriptionE[$key] = $inscription;
						$inscriptionId = $dataInscriptionE[$key]['estudiante_inscripcion_id_raep'];
					break;
				}
			}
			
			$tutoresActuales = $this->listarTutores($inscriptionId,array(1,2,4));
			$tutoresEliminados = $this->listarTutores($inscriptionId,array(3));
			//dump($inscriptionId);
			//dump($tutoresActuales);
			//dump($tutoresEliminados);
			//die;

        }
         // dump($dataInscriptionR);die;
		return $this->render($this->session->get('pathSystem') .':BonoJP:inscripcionesEstudianteBonoJP.html.twig', array(
			'inscripcionesRegular' => $dataInscriptionR,
			'inscripcionesEspecial' => $dataInscriptionE,
			'tutoresActuales' => $tutoresActuales,
			'tutoresEliminados' => $tutoresEliminados,
			'swError' => $swError,
			'messageError' => $messageError,

		));

	}

	public function buscarTutoresAction(Request $request,$inscripcion)
	{ 
	// dump($inscripcion); exit();
		/*$tutoresActuales = $this->listarTutores($inscripcion,1);
		$tutoresEliminados = $this->listarTutores($inscripcion,2);*/

		$tutoresActuales = $this->listarTutores($inscripcion,array(1,2,4));
		$tutoresEliminados = $this->listarTutores($inscripcion,array(3));
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

	public function listarTutores ($inscripcion, $estado)
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
						->innerJoin('SieAppWebBundle:BjpApoderadoTipo','ap','with','ap.id = beab.apoderadoTipo')
						->where('beab.estudianteInscripcionId = :inscriptionId')
						->andWhere('beab.segipIdTut = 1')
						->andWhere('beab.estadoId IN (:estado)')
						->andWhere('beab.fechaActualizacion is null')
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
		$ci = str_replace(' ', '', $request->get('ci'));
		$complemento = $request->get('complemento');
		// echo ">".$inscripcion.">".$ci;exit();
		$em = $this->getDoctrine()->getManager();
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('carnet' => $ci, 'complemento' => strtoupper($complemento)));

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
				    5 => $persona->getId(),
				    6 => $persona->getComplemento(),
				    7 => $persona->getSegipId()
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
        	$data = array(0=>0,1=>$mensaje,7=>0);
        }
        
   		return new JsonResponse($data);
	}
	public function operativo_bono_jp_cambiarEstadoTutoreAction(Request $request){ 
		$id = $request->get('id');
		$estado = $request->get('estado');
		$estudianteInscripcion = $request->get('estudianteInscripcion');

		$em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select * from Bjp_Apoderado_Tipo order by 1");
        $query->execute();
        $entity = $query->fetchAll();

		// echo ">".$id.">".$estado.">".$estudianteInscripcion;exit();
		return $this->render('SieHerramientaBundle:BonoJP:form_tutor_estudiante.html.twig',array(
			'bjp_Apoderado_Tipo' => $entity,
			'id' => $id,
			'estado' => $estado,
			'estudianteInscripcion' => $estudianteInscripcion));
	}
	public function operativo_bono_jp_cambiarEstadoTutore1Action(Request $request){ 
		$id = 0;
		$estado = $request->get('estado');
		$estudianteInscripcion = $request->get('estudianteInscripcion');
		// echo ">".$id.">".$estado.">".$estudianteInscripcion;exit();
		return $this->render('SieHerramientaBundle:BonoJP:form_tutor_estudiante.html.twig',array('id' => $id,'estado' => $estado,'estudianteInscripcion' => $estudianteInscripcion));
	}
	public function guardar_datos_tutorAction(Request $request){
		//dump("guardar tutorrr");die;
		$em = $this->getDoctrine()->getManager();

		$id_bjp_estudiante_apoderado_beneficiarios = $request->get('id');
		$estado = $request->get('estado');
		$inscripcionid = $request->get('estudianteInscripcion');

		// $caso = $request->get('caso');
		$obs = mb_strtoupper($request->get('obs'),'utf-8');

		$ci = str_replace(' ', '', $request->get('ci1'));
		$complemento1 = $request->get('complemento1');
		$form_idfecnac = $request->get('form_idfecnac');
		$paterno = mb_strtoupper($request->get('paterno'),'utf-8');
		$materno = mb_strtoupper($request->get('materno'),'utf-8');
		$nombre = mb_strtoupper($request->get('nombre'),'utf-8');
		$parentesco = $request->get('parentesco');
		$extranjero = $request->get('extranjero');
		$genero = $request->get('genero');
		$idpersona = $request->get('idpersona');
		$segipId = $request->get('segipId');
		// $tipo_cambio = $request->get('tipo_cambio');

		// echo ">".$ci.">".$form_idfecnac.">".$paterno.">".$materno.">".$nombre.">".$idpersona;exit();

        // $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('carnet' => $ci));

        // dump($persona); die;

        $data = array('error'=>0, 'message'=>'Datos registados');
        if($segipId==0){
        	$datos = array(
                'complemento'=>$complemento1,
                'primer_apellido'=>$paterno,
                'segundo_apellido'=>$materno,
                'nombre'=>$nombre,
                'fecha_nacimiento'=>$form_idfecnac
            );   
			if($extranjero == 1){
				$datos['extranjero'] = 'e';
			}

	        $resultadoPersona = ($segipId==1)?true:$this->get('sie_app_web.segip')->verificarPersonaPorCarnet($ci,$datos,'prod','academico');
	        // dump($resultadoPersona);exit();
	        if ($resultadoPersona) {
	        	
	        	// $updateBjpE2 = $em->getRepository('SieAppWebBundle:BjpEstudianteApoderadoBeneficiarios')->find($id_bjp_estudiante_apoderado_beneficiarios);
		        // $updateBjpE2->setEstadoId($estado);
		        // $em->persist($updateBjpE2);
	        	if( $idpersona == 0 ){
	        		$newPersona = new Persona();
	        	}else{
	        		$newPersona = $em->getRepository('SieAppWebBundle:Persona')->find($idpersona);
	        	}
	        	$newPersona->setCarnet($ci);
	        	$newPersona->setComplemento($complemento1);
	        	$newPersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find('0'));
	        	$newPersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($genero));
	        	$newPersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->find('0'));
	        	$newPersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->find('0'));
	        	$newPersona->setRda('0');
	        	$newPersona->setPaterno(mb_strtoupper($paterno, "utf-8"));
	        	$newPersona->setMaterno(mb_strtoupper($materno, "utf-8"));
	        	$newPersona->setNombre(mb_strtoupper($nombre, "utf-8"));
	        	$newPersona->setFechaNacimiento(new \DateTime($form_idfecnac));
	        	$newPersona->setSegipId('1');
	        	$newPersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find('0'));

            		$newPersona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find(($extranjero)?2:1));

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
        	$query = "select e.codigo_rude,iec.*
			from estudiante e
			inner join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
			inner join institucioneducativa_curso iec on ( ei.institucioneducativa_curso_id = iec.id)
			where ei.id= '".$inscripcionid."' and gestion_tipo_id = ".$this->session->get('currentyear')."	 ";
			 $query2 = $em->getConnection()->prepare($query);
			 $query2->execute();
	         $obj = $query2->fetch();
 			 //dump($obj['institucioneducativa_id']);
			 //dump($obj['codigo_rude']);
			 //dump($idpersona);
			 //dump($parentesco);
			//die;
	        $queryChange = "select * from sp_genera_transaccion_bono_juancito_pinto('".$obj['institucioneducativa_id']."','".$obj['codigo_rude']."','".$idpersona."','".$parentesco."')";


         	$query = $em->getConnection()->prepare($queryChange);
	        $query->execute();
	        $result2 = $query->fetchAll();
			//dump($result2);die;
	        // the errors on DB
	        $noTransfer = array(
				'0' =>' THIS IS OK',
				'1' =>' Problemas en los datos del estudiante, edad, estado, o no es publica',
				'1' =>' Problemas en los datos del estudiante, edad, estado, o no es publica',
				'2' =>' No corresponde ni a especial ni regular',
				'3' =>' No puede darse de baja, ya se realizó el pago o no esta en la base de beneficiarios.',
				'4' =>' No puede incorporarse, ya se encuentra registrado para pago.',
				'5' =>' No realizarce el cambio de tutor, mas de un registro activo',
				'6' =>' El tutor ya se encuentra registrado',
				'7' =>' El estudiante no cuenta con notas del 2 trimestre',
	        );
	        // check if the has an error on change
	        if($result2[0]['sp_genera_transaccion_bono_juancito_pinto']!=0){
	        	$data = array('error'=>1, 'message'=>$noTransfer[$result2[0]['sp_genera_transaccion_bono_juancito_pinto']]);
	        }else{	        	
	        	$datosInsert = array(
	                'complemento'=>$complemento1,
	                'primer_apellido'=>$paterno,
	                'segundo_apellido'=>$materno,
	                'nombre'=>$nombre,
	                'obs'=>$obs,
	                'fecha_nacimiento'=>$form_idfecnac
	            ); 

	             $this->get('funciones')->setLogTransaccion(
	               $inscripcionid,
	               'bjp_estudiante_apoderado_beneficiarios',
	               'I',
	               'cambio de tutor',
	               $datosInsert,
	               '',
	               'SIGED',
	               json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
	              );	        
	        }
	        
	        
        }
        // dump($data);die;
   		return new JsonResponse($data);
	}
	public function bajaTutoresBjpAction(Request $request){ 
		$em = $this->getDoctrine()->getManager();
		$inscripcionid = $request->get('estudianteInscripcionid');
    	$query = "select e.codigo_rude,iec.*
		from estudiante e
		inner join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
		inner join institucioneducativa_curso iec on ( ei.institucioneducativa_curso_id = iec.id)
		where ei.id= '".$inscripcionid."' and gestion_tipo_id = ".$this->session->get('currentyear')."	 ";
		 $query2 = $em->getConnection()->prepare($query);
		 $query2->execute();
         $obj = $query2->fetch();
		// dump($obj ); die();
        $queryChange = "select * from sp_genera_transaccion_bono_juancito_pinto('".$obj['institucioneducativa_id']."','".$obj['codigo_rude']."','','')";

     	$query = $em->getConnection()->prepare($queryChange);
        $query->execute();
        $result2 = $query->fetchAll();

        $noTransfer = array(
			'0' =>' THIS IS OK',
			'1' =>' Problemas en los datos del estudiante, edad, estado, o no es publica',
			'1' =>' Problemas en los datos del estudiante, edad, estado, o no es publica',
			'2' =>' No corresponde ni a especial ni regular',
			'3' =>' No puede darse de baja, ya se realizó el pago o no esta en la base de beneficiarios.',
			'4' =>' No puede incorporarse, ya se encuentra registrado para pago.',
			'5' =>' No realizarce el cambio de tutor, mas de un registro activo',
			'6' =>' El tutor ya se encuentra registrado',
	    );
	    // check if the has an error on change
	    if($result2[0]['sp_genera_transaccion_bono_juancito_pinto']!=0){
	    	$data = array('error'=>1,'message'=>$noTransfer[$result2[0]['sp_genera_transaccion_bono_juancito_pinto']]);
	    }else{
	    	$data = array('error'=>1,'message'=>'EXITOSAMENTE MODIFICADO');
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
	public function reporte_seguimiento_Bjp_pdfAction(){
		return $this->render('SieHerramientaBundle:ReporteSeguimientoBjpPdf:reporte_seguimiento_Bjp_pdf_index.html.twig');
	}
	public function imprimir_seguimiento_pdfAction(Request $request){
		$this->session = new Session();
		$ie_id=$this->session->get('ie_id');
		$gestion=date('Y');
		// echo ">".$ie_id.">>".$gestion;exit();
        // $pdf=$this->container->getParameter('urlreportweb') . 'reg_preins_formulario.rptdesign&__format=pdf'.'&preinscripcion='.$idTramite;
        $pdf=$this->container->getParameter('urlreportweb') . 'reg_lst_EstudianApod_Benef_Pagados_UnidadEducativa_v1_EEA.rptdesign&__format=pdf'.'&ue='.$ie_id.'&gestion='.$gestion;
        //$pdf='http://127.0.0.1:63170/viewer/preview?_report=D%3A\workspaces\workspace_especial\bono-bjp\reg_lst_EstudiantesApoderados_Benef_UnidadEducativa_v1_EEA.rptdesign&_format=pdf'.'&ue='.$sie.'&gestion='.$gestion;
        
        $status = 200;  
        $arch           = 'IMPRIMIR REPORTE DE SEGUIMIENTO-'.date('Y').'_'.date('YmdHis').'.pdf';
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

		public function preinspdfAction(Request $request, $idTramite){
            $pdf=$this->container->getParameter('urlreportweb') . 'reg_preins_formulario.rptdesign&__format=pdf'.'&preinscripcion='.$idTramite;
            //$pdf='http://127.0.0.1:63170/viewer/preview?_report=D%3A\workspaces\workspace_especial\bono-bjp\reg_lst_EstudiantesApoderados_Benef_UnidadEducativa_v1_EEA.rptdesign&_format=pdf'.'&ue='.$sie.'&gestion='.$gestion;
            
            $status = 200;  
            $arch           = 'DECLARACION_PREINSCRIPCIÓN-'.date('Y').'_'.date('YmdHis').'.pdf';
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

	public function _listarDatosDeArchivos()
	{
		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();

		$query = "
			select count(identificador) nro_archivos, identificador,string_agg(nombre_archivo,', ') archivos
			from
			(
				select split_part(ubicacion,'/',4) as archivo,split_part(split_part(ubicacion,'/',4),('_'||to_char(now(),'YYYY')) ,1) nombre_archivo ,to_char(now(),'YYYY')||replace(split_part(split_part(ubicacion,'/',4),('_'||to_char(now(),'YYYY')) ,2), '.csv', '') identificador
				from (
					select distinct ubicacion
					from bjp_estudiante_apoderado_enviados_sintesis
					where 
					--fecha_envio = (select max(fecha_envio) from bjp_estudiante_apoderado_enviados_sintesis) and 
					ubicacion is not null
				) as a
			) as b
			GROUP BY identificador
			ORDER BY identificador desc";

		$stmt = $db->prepare($query);
		$stmt->execute();
		$datos = $stmt->fetchAll();
		return $datos;
	}


	public function boton_generar_file_bonoJP_a_demandaAction()
	{
		if( $this->session->get('roluser') == 34 )
		{
			
			$em = $this->getDoctrine()->getManager();
			$db = $em->getConnection();
			$query = 'select sp_genera_archivo_txt_bjp();';
			$stmt = $db->prepare($query);
			$stmt->execute();
			$resultado = $stmt->fetch();
			
			return $this->redirect($this->generateUrl('operativo_bono_jp_GenerarFileCambioTutor'));
		}
		else
		{
			return $this->redirect($this->generateUrl('login'));
		}
	}

	public function operativo_bono_jp_GenerarFileCambioTutorAction()
	{
		if( $this->session->get('roluser') == 34 )
		{
			$datosArchivos = $this->_listarDatosDeArchivos();
			return $this->render('SieHerramientaBundle:GenerarFileBonoJP:operativo_bono_jp_GenerarFileCambioTutor.html.twig',array('datosArchivos' => $datosArchivos));
		}
		else
		{
			return $this->redirect($this->generateUrl('login'));
		}
	}

	public function _getFinalNombreDelArchivoGeneradoBonoJP2021()
	{
		$em = $this->getDoctrine()->getManager();
		$nombreArchivo = '';
		$query = $em->getConnection()->prepare("select max(fecha_envio) from bjp_estudiante_apoderado_enviados_sintesis");
		$query->execute();
		$nombreArchivo = $query->fetch();
		//return '2021-11-17_15_59_26';
		if(count($nombreArchivo)>0)
			//return str_replace([' ',':'], ['_','_'], $nombreArchivo['max']);
			return $nombreArchivo['max'];
		else
			return null;
	}

	public function getListadoArchivosGuardados($nombreArchivo,$directorioRaiz)
	{
		$arrayArchivos = array();
		try
		{
			if(is_dir($directorioRaiz))
			{
				chdir($directorioRaiz);
				$ruta = '../web/empfiles/bono_bjp';
				if(chdir($ruta))
				{
					$arrayArchivos = glob("*$nombreArchivo.csv");
					// $tmp = implode(', ', $nombreArchivos);
					// $arrayArchivos = glob("*{$tmp}.csv");
					//$arrayArchivos = glob("*2021-11-17_15_59_26.csv");
				}
				else
				{
					die('Ocurrio un error no se pudo descargar los archivos.');
				}
			}
			else
			{
				die('No existe el archivo.');
			}
		}
		catch (Exception $e)
		{
			die('Ocurrio un error no se pudo descargar el archivo.');
		}
		return $arrayArchivos;
	}

	public function _comprimirArchivosEnZip($listadoDeArchivos, $nombreArchivo, $directorioRaiz)
	{
		$msj = '';
		$filename = null;
		$nombreRutaDescarga = null;
		try
		{
			if(is_dir($directorioRaiz))
			{
				chdir($directorioRaiz);
				$ruta = '../web/empfiles/bono_bjp';
				if(chdir($ruta))
				{
					$zip = new ZipArchive;
					$filename = "Archivos-BJP-$nombreArchivo-".date('U.s').".zip";
					if ($zip->open($filename, ZipArchive::CREATE)!==TRUE)
					{
						$filename = null;
						$msj = 'Ocurrio un error no se pudo descargar el archivo.';
					}
					foreach($listadoDeArchivos as $file)
					{
						$zip->addfile($file);
					}
					$zip->close();
					$msj = 'Archivo listo para descarga.';
					$nombreRutaDescarga = realpath($filename);
				}
				else
				{
					$msj = 'Ocurrio un error no se pudo descargar los archivos.';
				}
			}
			else
			{
				$msj = 'No existe el archivo.';
			}
		}
		catch (Exception $e)
		{
			$filename = null;
			$msj = 'Ocurrio un error no se pudo descargar el archivo.';
		}
		return array($filename, $nombreRutaDescarga);
	}

	public function _generarArchivoZipBonoJP2021($identificador)
	{
		// la carpeta donde se descargarel archivo comprimido
		//$directorioArchivosBonoJP2021 = $this->get('kernel')->getRootDir() . '/../web/empfiles/bono_bjp/';
		$directorioArchivosBonoJP2021 = $this->get('kernel')->getRootDir();
		$msj = '';
		$estado = false;
		$nombreArchivo = '';
		$nombreFinalArchivo = '';
		try
		{
			// Comprimimos los archivos, obtenemos el nombre del archivo
			//$nombreArchivo = $this->_getFinalNombreDelArchivoGeneradoBonoJP2021();
			$nombreArchivo = $identificador;
			if($nombreArchivo != null)
			{
				$listadoDeArchivos = $this->getListadoArchivosGuardados($nombreArchivo,$directorioArchivosBonoJP2021);
				if(count($listadoDeArchivos)>0)
				{
					//$nombreFinalArchivo = "Archivos-BJP-$nombreArchivo-".date('U.s').".tar";
					list($nombreFinalArchivo, $nombreArchivo) = $this->_comprimirArchivosEnZip($listadoDeArchivos, $nombreArchivo, $directorioArchivosBonoJP2021);
					if($nombreFinalArchivo && $nombreArchivo)
					{
						$estado = true;
						$msj = 'Archivos descargados.';
					}
				}
				else
				{
					$msj = 'No existen archivos para descargar.';
				}
			}
			else
			{
				$msj = 'No existe el archivo, por favor vuelva a intentarlo.';
			}
		}
		catch (Exception $exc)
		{
			$msj = 'Ocurrio un error desconocido, por favor vuelva a intentarlo.';
		}
		return array($msj, $estado, $nombreArchivo,$nombreFinalArchivo);
	}

	public function boton_generar_file_bonoJPAction(Request $request, $identificador)
	{
		$directorioArchivosBonoJP2021 = $this->get('kernel')->getRootDir() . '/../web/empfiles/bono_bjp/';
		//list($msj, $estado, $nombreArchivo,$nombreFinalArchivo) = $this->_generarArchivoZipBonoJP2021();
		list($msj, $estado, $nombreArchivo,$nombreFinalArchivo) = $this->_generarArchivoZipBonoJP2021($identificador);
		if($estado)
		{
			try
			{
				$zip = $nombreFinalArchivo;
				header("Content-Description: File Transfer");
				header('Content-Type', 'application/zip');
				header('Content-disposition: attachment; filename="' . $nombreFinalArchivo . '"');
				header('Content-Length: ' . filesize($zip));
				readfile($zip);
				exit;
			}
			catch ( Exception $e )
			{
				die('Ocurrio un error desconocido, por favor vuelva a intentarlo..');
			}
		}
		else
		{
			die($msj);
		}
	}
	//70524638

	// VALIDACION BJP
	public function validaNoPagadosAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();  

		$sesion = $request->getSession();
		$ue = $sesion->get('ie_id');

		//$ue = 80640033;
		//dump($ue); die; 

		//valida si existe en la tabla bjp_titular_cobro_validacion_cierre
		$sql = "
		SELECT count(*) as existe
		FROM
			bjp_titular_cobro_validacion_cierre bjp 
		WHERE
			institucioneducativa_id = ".$ue; 			
		
		$stmt = $db->prepare($sql);
		$params = array();
		$stmt->execute($params);
		$validados = $stmt->fetchAll();
		$existe = $validados[0]['existe'];		
		if($existe == 0){
			//no existe se va al home
			return $this->redirectToRoute('principal_web');
		}
		
		$sql = "
		SELECT
			bjp.*, at.apoderado as apoderado_tipo
		FROM
			bjp_titular_cobro_validacion bjp
			INNER JOIN 
			bjp_apoderado_tipo at on at.id = bjp.bjp_apoderado_tipo_id
		WHERE
			institucioneducativa_id = ".$ue." ORDER BY 11, 5,6,7";

		$stmt = $db->prepare($sql);
		$params = array();
		$stmt->execute($params);
		$apoderados = $stmt->fetchAll();

		//dump($apoderados); die;

		$total_estudiantes = 0;
		for($i = 0; $i < sizeof($apoderados); $i++ ){
			$total_estudiantes += $apoderados[$i]['estudiantes_cobrados'];
		}

		// los ya validados
		$sql = "
		SELECT COUNT 
			(*) as total
		FROM
			bjp_titular_cobro_beneficiarios_validacion bjp 
		WHERE
			bjp_titular_cobro_validacion_id IN ( SELECT bjp.ID FROM bjp_titular_cobro_validacion bjp WHERE institucioneducativa_id = ".$ue." ) 
			AND es_pagado in (TRUE, FALSE) 
		";

		//dump($sql);die;
		$stmt = $db->prepare($sql);
		$params = array();
		$stmt->execute($params);
		$validados = $stmt->fetchAll();
		$total_estudiantes_validados = $validados[0]['total'];

		//ya esta cerrado ?
		$sql = "
		SELECT es_concluido
		FROM
			bjp_titular_cobro_validacion_cierre bjp 
		WHERE
			institucioneducativa_id = ".$ue; 			
		
		$stmt = $db->prepare($sql);
		$params = array();
		$stmt->execute($params);
		$validados = $stmt->fetchAll();
		$operation_status = $validados[0]['es_concluido'];		
	
		return $this->render($this->session->get('pathSystem') .':BonoJP:index_valida_no_pagados.html.twig', array(
            'apoderados' => $apoderados,
            'total_estudiantes' => $total_estudiantes,
            'total_estudiantes_validados' => $total_estudiantes_validados,
			'data'    => bin2hex(serialize($ue)),
			'operation_status' => $operation_status  //0: abierto 1: cerrado
        ));

        /*return $this->render('SieHerramientaBundle:bonoJP:index_valida_no_pagados.html.twig', array(
            'apoderados' => $apoderados,
            'total_estudiantes' => $total_estudiantes,
            'total_estudiantes_validados' => $total_estudiantes_validados,
			'data'    => bin2hex(serialize($ue)),
			'operation_status' => $operation_status  //0: abierto 1: cerrado
        ));*/
	}

	public function validaNoPagadosAlumnosAction(Request $request)
	{
		//$form = $request->get('form');
		//dump($request); die;		
		$id = $request->get('iduealtadem');		
		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();  
        
		$sql = "					
		SELECT
			bjp.*,
			n.nivel,
			g.id as grado,
			CASE 
				WHEN es_pagado = true  THEN 1
				WHEN es_pagado = false  THEN 2    
				ELSE 0
			END as opcion
		FROM
			bjp_titular_cobro_beneficiarios_validacion bjp
			INNER JOIN nivel_tipo n ON n.ID = bjp.nivel_tipo_id
			INNER JOIN grado_tipo G ON G.ID = bjp.grado_tipo_id 
		WHERE
			bjp_titular_cobro_validacion_id = ".$id." 
		ORDER BY
			nivel, grado, 
			7,
			8,9
		";

		$stmt = $db->prepare($sql);
		$params = array();
		$stmt->execute($params);
		$estudiantes = $stmt->fetchAll();


		//nombre del titular
		$sql = "					
		SELECT
			concat ( paterno, ' ', materno, ' ', nombre, '     ---     C.I.: ', carnet, complemento ) AS nombre_titular 
		FROM
			bjp_titular_cobro_validacion bjp 
		WHERE
		id = ".$id;
		
		//dump($sql); die;
		$stmt = $db->prepare($sql);
		$params = array();
		$stmt->execute($params);
		$datos = $stmt->fetchAll();
		$nombre_titular = $datos[0]['nombre_titular'];


		return $this->render($this->session->get('pathSystem') .':BonoJP:valida_no_pagados_alumnos.html.twig',array(
			'estudiantes' => $estudiantes,
			'nombre_titular' => $nombre_titular
            
        ));
		
		/*
        return $this->render('SieHerramientaBundle:bonoJP:valida_no_pagados_alumnos.html.twig', array(
			'estudiantes' => $estudiantes,
            
        ));*/
	}

	public function validaNoPagadosAlumnosSaveAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $response = new JsonResponse();

		
                
        //recibe todas las variables del form
        $form = $request->get('form');
		//$cheks = $request->get('ckbox');
		$cblists = $request->get('cbl');

		//dump($cheks); die;
		/*dump($form);
		dump($cblists); die;*/
		
		try {    

			
			foreach ($cblists as $clave => $valor) {     				 
				$id = $clave; 					
				$value = $valor;

				//dump($id); dump($value); die;
				
				if($value <> "0" ){							
					$pagado = 0;
					if($value == "1"){$pagado = 1;}
					$query ="update bjp_titular_cobro_beneficiarios_validacion set es_pagado = ? where id = ?";
					$stmt = $db->prepare($query);
					$params = array($pagado, $id);
					$stmt->execute($params);
				}else{
					$query ="update bjp_titular_cobro_beneficiarios_validacion set es_pagado = NULL where id = ?";
					$stmt = $db->prepare($query);
					$params = array($id);
					$stmt->execute($params);
				}
			}
			

            foreach ($form as $clave => $valor) {      
                $id = $clave; 
                $obs = $valor; 
				
				//dump(strlen($obs));  die; 
				if(strlen($obs) > 1){
					//update al registro, solo se cambia cantidad
					$query ="update bjp_titular_cobro_beneficiarios_validacion set observacion = ? where id = ?";
					$stmt = $db->prepare($query);
					$params = array($obs, $id);
					$stmt->execute($params);           
				}
            } 

            $msg  = 'Datos registrados correctamente';
            return $response->setData(array('estado' => true, 'msg' => $msg, 'cantidad' => 0));

        } catch (\Doctrine\ORM\NoResultException $ex) {           
            $msg  = 'Error al realizar el registro, intente nuevamente';
            return $response->setData(array('estado' => false, 'msg' => $msg, 'cantidad' => -1));
        } 


	}

	public function closeOpeAction(Request $request){
       
        //dump($request->get('ue')); die; 
		$ue_encrypt = $request->get('ue');
		$ue = $this->kdecrypt($ue_encrypt);
        
		
		//$ue = '80640033';
		//dump($ue); die;
      
        $data=null;
        $status= 404;
        $msj='Ocurrio un error, por favor vuelva a intentarlo.';
        $reporte = '';
        $observations = null;
        try{

            $observations = null;
            $em = $this->getDoctrine()->getManager();
            $db = $em->getConnection();
            $query = "
			select a.institucioneducativa_id,'No reporto validacion del '||(select apoderado from bjp_apoderado_tipo where id=a.bjp_apoderado_tipo_id)||':'||a.paterno||' '||a.materno||' '||a.nombre||', para el estudiante:'||b.codigo_rude||'-'||b.paterno||' '||b.materno||' '||b.nombre as observacion 
			from bjp_titular_cobro_validacion a
				inner join bjp_titular_cobro_beneficiarios_validacion b on a.id=b.bjp_titular_cobro_validacion_id 
			where institucioneducativa_id=".$ue." and es_pagado is null
			";
            $stmt = $db->prepare($query);
            //$params = array($ue);
            $stmt->execute();
            $observations = $stmt->fetchAll();
            //dump($observations); die;
			// are there observations?
            if($observations == null){
                $data=null;
                $status= 200;
                $msj='Cierre correcto! No existen inconsistencias en el cierre, proceda a la impresion del reporte: --->   ';
                // miss save data about the operative to the universite data
                // close oall year to the UnivSedeId
                /*$objOperative = $em->getRepository('SieAppWebBundle:UnivRegistroConsolidacion')->findBy(array('univSede'=>$dataform['sedeId']));
                if(sizeof($objOperative)>0){
                    foreach ($objOperative as $value) {
                        $value->setActivo(0);
                        $em->persist($value);
                    }
                    $em->flush();
                }*/
				$id_usuario = $this->session->get('userId');
				$obs = "Usuario: " . $id_usuario . " - Fecha: " . date('d/m/Y');
				//dump($obs); die;

				$query ="update bjp_titular_cobro_validacion_cierre set es_concluido = true, observacion = '".$obs ."', fecha_actualizacion = now()  where institucioneducativa_id = ?";
                $stmt = $db->prepare($query);
                $params = array($ue);
                $stmt->execute($params);           
            }
            else{
                $data=null;
                $status= 200;
                $msj='No se puedo cerrar el operativo, todavia tiene inconsistencias.';
            }
        }catch(Exception $e){
            $data=null;
            $status= 404;
            $msj='Ocurrio un error al cerrar el operativo, por favor vuelva a intentarlo.';
        }
        $response = new JsonResponse($data,$status);
        $response->headers->set('Content-Type', 'application/json');
        $allData = array('data'=>$data,'status'=>$status,'msj'=>$msj,'observations'=>$observations);
        // dump($allData);die;
        return $response->setData($allData);   ////////////////////////
    }    

	private function kdecrypt($data){
        $data = hex2bin($data);
        return unserialize($data);
    }

	public function impresionDDJJAction(Request $request) {
        $id_usuario = $this->session->get('userId');
        $username = $this->session->get('userName');

		$sesion = $request->getSession();
		$ue = $sesion->get('ie_id');
		//$ue = '80640033';

		//dump($ue); die;

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $arrSieInfo = array('id'=>$this->session->get('ie_id'), 'datainfo'=>$this->session->get('ie_nombre'));
        $gestion = 2022;//$this->gestionOperativo;
        

        $arch = 'DECLARACION_JURADA_BONO_BJP_' . $arrSieInfo['id'] . '_' . date('YmdHis') . '.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'reg_lst_ValidacionEstudiantesMaestro_pagador_v1_EEA.rptdesign&__format=pdf&&ue=' . $ue . '&gestion='.$gestion.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


	//solo para el tecnico departamental	
	public function seguimientoDeptoAction(Request $request){

		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();  
		$sesion = $request->getSession();

		$id_usuario = $this->session->get('userId');
		//$id_usuario = 92519630;// 13833357;//13833732; //13851223; //$this->session->get('userId');
        $username = $this->session->get('userName');
		
		//obtenemos el depto
		$sql = "select codigo from lugar_tipo where id in (select lugar_tipo_id from usuario_rol where rol_tipo_id = 7 and usuario_id = " . $id_usuario.")";		
		//dump($sql); die;
		$stmtaux = $em->getConnection()->prepare($sql);
		$stmtaux->execute();
        $currentdata = $stmtaux->fetchAll();
		$codigo_depto = $currentdata[0]['codigo'];

		$sql = "
			select
			'ABIERTO' as estado,
			bjp.* , u.*
				from bjp_titular_cobro_validacion_cierre  as bjp
					inner join (
				select a.cod_ue_id, a.desc_ue, a.cod_le_id, a.direccion, a.zona, a.cod_distrito, a.distrito,
				(case when a.tipo_area = 'R' then 'RURAL' when a.tipo_area = 'U' then 'URBANO' else '' end) as area,
				a.id_departamento, a.desc_departamento, a.id_provincia, a.desc_provincia, a.id_seccion, a.desc_seccion, a.id_canton, a.desc_canton, a.id_localidad, a.desc_localidad,
				a.cod_convenio_id, a.convenio,case when a.cod_dependencia_id <>3 then 'Publico' else 'Privado' end as dependencia_gen, a.cod_dependencia_id, a.dependencia, c.ini, c.pri, c.sec, c.epa, c.esa, c.eta, c.esp, c.perm,c.perm_tec,c.perm_otros, c.eja, a.fecha_last_update, a.estadoinstitucion,a.cordx,a.cordy, a.nro_resolucion, a.fecha_resolucion
				from 
				(select  a.id as cod_ue_id,a.institucioneducativa as desc_ue,a.orgcurricular_tipo_id,a.estadoinstitucion_tipo_id, h.estadoinstitucion, a.le_juridicciongeografica_id as cod_le_id,a.orgcurricular_tipo_id as cod_org_curr_id,f.orgcurricula
				,a.dependencia_tipo_id as cod_dependencia_id, e.dependencia,a.convenio_tipo_id as cod_convenio_id,g.convenio,d.cod_dep as id_departamento,d.des_dep as desc_departamento
				,d.cod_pro as id_provincia, d.des_pro as desc_provincia, d.cod_sec as id_seccion, d.des_sec as desc_seccion, d.cod_can as id_canton, d.des_can as desc_canton
				,d.cod_loc as id_localidad,d.des_loc as desc_localidad,d.area2001 as tipo_area,d.cod_dis as cod_distrito,d.des_dis as distrito,d.direccion,d.zona
				,d.cod_nuc,d.des_nuc,0 as usuario_id,current_date as fecha_last_update, a.fecha_creacion,d.cordx,d.cordy, a.nro_resolucion, a.fecha_resolucion
				from institucioneducativa a 
				inner join 
				(select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona,a.cordx,cordy
				from jurisdiccion_geografica a inner join 
				(select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001
				from (select id,codigo as cod_dep,lugar_tipo_id,lugar
				from lugar_tipo
				where lugar_nivel_id=1) a inner join (
				select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo
				where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id inner join (
				select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo
				where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id inner join (
				select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo
				where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id inner join (
				select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo
				where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id) b on a.lugar_tipo_id_localidad=b.id
				left join 
				(select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo
				where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id) d on a.le_juridicciongeografica_id=d.cod_le  and institucioneducativa_acreditacion_tipo_id=1 --and estadoinstitucion_tipo_id=10
				INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id
				INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id
				INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id
				INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id
				where a.institucioneducativa_acreditacion_tipo_id = 1) as a
				LEFT JOIN (
				select institucioneducativa_id
				,sum(case when nivel_tipo_id=11 then 1 else 0 end) as ini
				,sum(case when nivel_tipo_id=12 then 1 else 0 end) as pri
				,sum(case when nivel_tipo_id=13 then 1 else 0 end) as sec
				,sum(case when nivel_tipo_id=6 then 1 else 0 end) as esp
				,sum(case when nivel_tipo_id=8 then 1 else 0 end) as eja
				,sum(case when nivel_tipo_id=201 then 1 else 0 end) as epa
				,sum(case when nivel_tipo_id=202 then 1 else 0 end) as esa
				,sum(case when nivel_tipo_id in (203,204,205,206) then 1 else 0 end) as eta
				,sum(case when nivel_tipo_id in (207) then 1 else 0 end) as alfproy
				,sum(case when nivel_tipo_id in (208) then 1 else 0 end) as alfcam
				,sum(case when nivel_tipo_id in (211,212,213,214,215,216,217,218) then 1 else 0 end) as perm
				,sum(case when nivel_tipo_id in (219,220,224) then 1 else 0 end) as perm_tec
				,sum(case when nivel_tipo_id in (221,222,223) then 1 else 0 end) as perm_otros
				from institucioneducativa_nivel_autorizado
				group by institucioneducativa_id
				) as c on a.cod_ue_id=c.institucioneducativa_id 
				inner join dependencia_tipo d on a.cod_dependencia_id=d.id
					inner join orgcurricular_tipo e on e.id=a.cod_org_curr_id
						inner join convenio_tipo f on f.id=a.cod_convenio_id) u on u.cod_ue_id = bjp.institucioneducativa_id 
				WHERE
					id_departamento = '". $codigo_depto ."' AND 
					bjp.gestion_tipo_id = 2023 AND
					es_concluido is null
					order by desc_departamento, desc_provincia, distrito
		";

		//dump($sql); die; 

		$stmt = $db->prepare($sql);
		$params = array();
		$stmt->execute($params);
		$data = $stmt->fetchAll();


		// los cerrados
		$sql = "
			select
			'CERRADO' as estado,
			bjp.* , u.*
				from bjp_titular_cobro_validacion_cierre  as bjp
					inner join (
				select a.cod_ue_id, a.desc_ue, a.cod_le_id, a.direccion, a.zona, a.cod_distrito, a.distrito,
				(case when a.tipo_area = 'R' then 'RURAL' when a.tipo_area = 'U' then 'URBANO' else '' end) as area,
				a.id_departamento, a.desc_departamento, a.id_provincia, a.desc_provincia, a.id_seccion, a.desc_seccion, a.id_canton, a.desc_canton, a.id_localidad, a.desc_localidad,
				a.cod_convenio_id, a.convenio,case when a.cod_dependencia_id <>3 then 'Publico' else 'Privado' end as dependencia_gen, a.cod_dependencia_id, a.dependencia, c.ini, c.pri, c.sec, c.epa, c.esa, c.eta, c.esp, c.perm,c.perm_tec,c.perm_otros, c.eja, a.fecha_last_update, a.estadoinstitucion,a.cordx,a.cordy, a.nro_resolucion, a.fecha_resolucion
				from 
				(select  a.id as cod_ue_id,a.institucioneducativa as desc_ue,a.orgcurricular_tipo_id,a.estadoinstitucion_tipo_id, h.estadoinstitucion, a.le_juridicciongeografica_id as cod_le_id,a.orgcurricular_tipo_id as cod_org_curr_id,f.orgcurricula
				,a.dependencia_tipo_id as cod_dependencia_id, e.dependencia,a.convenio_tipo_id as cod_convenio_id,g.convenio,d.cod_dep as id_departamento,d.des_dep as desc_departamento
				,d.cod_pro as id_provincia, d.des_pro as desc_provincia, d.cod_sec as id_seccion, d.des_sec as desc_seccion, d.cod_can as id_canton, d.des_can as desc_canton
				,d.cod_loc as id_localidad,d.des_loc as desc_localidad,d.area2001 as tipo_area,d.cod_dis as cod_distrito,d.des_dis as distrito,d.direccion,d.zona
				,d.cod_nuc,d.des_nuc,0 as usuario_id,current_date as fecha_last_update, a.fecha_creacion,d.cordx,d.cordy, a.nro_resolucion, a.fecha_resolucion
				from institucioneducativa a 
				inner join 
				(select a.id as cod_le,cod_dep,des_dep,cod_pro,des_pro,cod_sec,des_sec,cod_can,des_can,cod_loc,des_loc,area2001,cod_dis,des_dis,a.cod_nuc,a.des_nuc,a.direccion,a.zona,a.cordx,cordy
				from jurisdiccion_geografica a inner join 
				(select e.id,cod_dep,a.lugar as des_dep,cod_pro,b.lugar as des_pro,cod_sec,c.lugar as des_sec,cod_can,d.lugar as des_can,cod_loc,e.lugar as des_loc,area2001
				from (select id,codigo as cod_dep,lugar_tipo_id,lugar
				from lugar_tipo
				where lugar_nivel_id=1) a inner join (
				select id,codigo as cod_pro,lugar_tipo_id,lugar from lugar_tipo
				where lugar_nivel_id=2) b on a.id=b.lugar_tipo_id inner join (
				select id,codigo as cod_sec,lugar_tipo_id,lugar from lugar_tipo
				where lugar_nivel_id=3) c on b.id=c.lugar_tipo_id inner join (
				select id,codigo as cod_can,lugar_tipo_id,lugar from lugar_tipo
				where lugar_nivel_id=4) d on c.id=d.lugar_tipo_id inner join (
				select id,codigo as cod_loc,lugar_tipo_id,lugar,area2001 from lugar_tipo
				where lugar_nivel_id=5) e on d.id=e.lugar_tipo_id) b on a.lugar_tipo_id_localidad=b.id
				left join 
				(select id,codigo as cod_dis,lugar_tipo_id,lugar as des_dis from lugar_tipo
				where lugar_nivel_id=7) c on a.lugar_tipo_id_distrito=c.id) d on a.le_juridicciongeografica_id=d.cod_le  and institucioneducativa_acreditacion_tipo_id=1 --and estadoinstitucion_tipo_id=10
				INNER JOIN dependencia_tipo e ON a.dependencia_tipo_id = e.id
				INNER JOIN orgcurricular_tipo f ON a.orgcurricular_tipo_id = f.id
				INNER JOIN convenio_tipo g ON a.convenio_tipo_id = g.id
				INNER JOIN estadoinstitucion_tipo h ON a.estadoinstitucion_tipo_id = h.id
				where a.institucioneducativa_acreditacion_tipo_id = 1) as a
				LEFT JOIN (
				select institucioneducativa_id
				,sum(case when nivel_tipo_id=11 then 1 else 0 end) as ini
				,sum(case when nivel_tipo_id=12 then 1 else 0 end) as pri
				,sum(case when nivel_tipo_id=13 then 1 else 0 end) as sec
				,sum(case when nivel_tipo_id=6 then 1 else 0 end) as esp
				,sum(case when nivel_tipo_id=8 then 1 else 0 end) as eja
				,sum(case when nivel_tipo_id=201 then 1 else 0 end) as epa
				,sum(case when nivel_tipo_id=202 then 1 else 0 end) as esa
				,sum(case when nivel_tipo_id in (203,204,205,206) then 1 else 0 end) as eta
				,sum(case when nivel_tipo_id in (207) then 1 else 0 end) as alfproy
				,sum(case when nivel_tipo_id in (208) then 1 else 0 end) as alfcam
				,sum(case when nivel_tipo_id in (211,212,213,214,215,216,217,218) then 1 else 0 end) as perm
				,sum(case when nivel_tipo_id in (219,220,224) then 1 else 0 end) as perm_tec
				,sum(case when nivel_tipo_id in (221,222,223) then 1 else 0 end) as perm_otros
				from institucioneducativa_nivel_autorizado
				group by institucioneducativa_id
				) as c on a.cod_ue_id=c.institucioneducativa_id 
				inner join dependencia_tipo d on a.cod_dependencia_id=d.id
					inner join orgcurricular_tipo e on e.id=a.cod_org_curr_id
						inner join convenio_tipo f on f.id=a.cod_convenio_id) u on u.cod_ue_id = bjp.institucioneducativa_id 
				WHERE
					id_departamento = '". $codigo_depto ."' AND 
					bjp.gestion_tipo_id = 2023 AND
					es_concluido = true
				order by desc_departamento, desc_provincia, distrito
		";

		$stmt = $db->prepare($sql);
		$params = array();
		$stmt->execute($params);
		$dataclosed = $stmt->fetchAll();

		$numopen = sizeof($data);
		$numclosed = sizeof($dataclosed);

		return $this->render($this->session->get('pathSystem') .':BonoJP:index_valida_depto.html.twig', array(
            'data' => $data,            
            'dataclosed' => $dataclosed,            
            'numopen' => $numopen,            
            'numclosed' => $numclosed,            
        ));
	}

	public function seguimientoOpenUeAction(Request $request)
	{
		$em      = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $response = new JsonResponse();

		// se recibe el id de la tabla bjp_titular_cobro_validacion_cierre
		$codigo_sie = $request->get('id');
		//dump($codigo_sie); die; 

		$query = "select observacion from bjp_titular_cobro_validacion_cierre where id = " . $codigo_sie;
		$stmtaux = $em->getConnection()->prepare($query);
		$stmtaux->execute();
        $currentdata = $stmtaux->fetchAll();
		$observacion = $currentdata[0]['observacion'];
		
		$fecha_actual = date("Y-m-d H:i:s");
		$observacion .= '-reopen:'. $fecha_actual;
		//dump($observacion); die; 

		$query ="update bjp_titular_cobro_validacion_cierre set es_concluido = NULL, observacion = ?, fecha_actualizacion = ? where id = ?";
		$stmt = $db->prepare($query);
		$params = array($observacion, $fecha_actual, $codigo_sie);
		$stmt->execute($params);
		$tmp=$stmt->fetchAll();


		$msg = 'La U.E. se ha aperturado correctamente !';
        return $response->setData(array('status' => 200, 'msg' => $msg, 'tipo' => 0));
		
	}

	public function seguimientoInfoUeAction(Request $request) {

		$em      = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $response = new JsonResponse();

		$query = "select institucioneducativa_id from bjp_titular_cobro_validacion_cierre where id = " . $request->get('id');
		$stmtaux = $em->getConnection()->prepare($query);
		$stmtaux->execute();
        $currentdata = $stmtaux->fetchAll();
		$codigo_sie = $currentdata[0]['institucioneducativa_id'];

		//$codigo_sie ='80590006'; //$request->get('id');
		 
		$query = $em->createQuery(
            'SELECT ie
        FROM SieAppWebBundle:Institucioneducativa ie
        WHERE ie.id = :id
        and ie.institucioneducativaAcreditacionTipo = :ieAcreditacion
        ORDER BY ie.id')
                    ->setParameter('id',  $codigo_sie )
                ->setParameter('ieAcreditacion', 1)
                ;
        
        $ue = $query->getResult();
		//dump($ue); die; 
		$sie= $ue[0]->getId();
		$nombreue = $ue[0]->getInstitucioneducativa();
		$dependencia = $ue[0]->getDependenciaTipo()->getDependencia();
		$tipo = $ue[0]->getInstitucioneducativaTipo()->getDescripcion();
		$depto = $ue[0]->getLeJuridicciongeografica()->getLugarTipoLocalidad()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugarTipo()->getLugar();
		$distrito = $ue[0]->getLeJuridicciongeografica()->getDistritoTipo()->getDistrito() ;
		$zona = $ue[0]->getLeJuridicciongeografica()->getZona();
		$direccion = $ue[0]->getLeJuridicciongeografica()->getDireccion();

		//datos del bono
		$sql = "
		SELECT
			bjp.*, at.apoderado as apoderado_tipo
		FROM
			bjp_titular_cobro_validacion bjp
			INNER JOIN 
			bjp_apoderado_tipo at on at.id = bjp.bjp_apoderado_tipo_id
		WHERE
			institucioneducativa_id = ".$codigo_sie." ORDER BY 11, 5,6,7";

		$stmt = $db->prepare($sql);
		$params = array();
		$stmt->execute($params);
		$apoderados = $stmt->fetchAll();

		$total_estudiantes = 0;
		for($i = 0; $i < sizeof($apoderados); $i++ ){
			$total_estudiantes += $apoderados[$i]['estudiantes_cobrados'];
		}

		// los ya validados
		$sql = "
		SELECT COUNT 
			(*) as total
		FROM
			bjp_titular_cobro_beneficiarios_validacion bjp 
		WHERE
			bjp_titular_cobro_validacion_id IN ( SELECT bjp.ID FROM bjp_titular_cobro_validacion bjp WHERE institucioneducativa_id = ".$codigo_sie." ) 
			AND es_pagado in (TRUE,FALSE) 
		";

		//dump($sql);die;
		$stmt = $db->prepare($sql);
		$params = array();
		$stmt->execute($params);
		$validados = $stmt->fetchAll();
		$total_estudiantes_validados = $validados[0]['total'];
		


		$msg = 'La U.E. se ha aperturado correctamente !';
        return $response->setData(array(
			'status' => 200, 
			'msg' => $msg, 
			'sie' => $sie,
			'nombreue' => $nombreue,
			'dependencia' => $dependencia,
			'depto' => $depto,
			'distrito' => $distrito,
			'total_estudiantes' => $total_estudiantes,
			'total_estudiantes_validados' => $total_estudiantes_validados
		));
	}


}
