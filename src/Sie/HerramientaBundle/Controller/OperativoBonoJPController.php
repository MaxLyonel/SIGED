<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoModalidadAtencion;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoTextosEducativos;

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
			$observacionesBonpJP = $this->puedeCerrarOperativo($request_gestion,$request_sie);
			if(!$observacionesBonpJP)
			{
				if($esAjax && $request_sie >0 && $request_gestion >0)
				{
					list($data,$status,$msj)=$this->get('operativoutils')->cerrarOperativo($request_sie,$request_gestion,$request_estado);
					$reporte = $this->generateUrl('operativo_bono_jp_ddjj',array("sie"=>$request_sie,"gestion"=>$request_gestion));
				}
			}
			else
			{
				$status= 200;
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
		return $response->setData(array('data'=>$data,'status'=>$status,'msj'=>$msj,'urlReporte'=>$reporte,'observacionesBonpJP'=>$observacionesBonpJP));
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
		
		if(in_array($rol,[8]))//nacional
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
		$observacionesBonpJP=$stmt->fetchAll();

		return $observacionesBonpJP;
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

		$datos=$this->get('operativoutils')->getUnidadesEducativas($departamento,$distrito,$gestion,$this->estado);
		return $this->render('SieHerramientaBundle:BonoJP:mostrarDatosOperativoBonoJP.html.twig',array(
			'datos' => $datos,
			'periodo' => $ieTipo,
		));
	}

	public function ddjjAction(Request $request, $sie,$gestion)
	{
		//$pdf=$this->container->getParameter('urlreportweb') . 'reg_lst_textos_educativos_v1.rptdesign&__format=pdf'.'&codue='.$sie.'&gestion='.$gestion;
		$pdf='http://127.0.0.1:63020/viewer/preview?__report=D%3A\workspaces\workspace_especial\Reporte-Textos-Educativos\reg_lst_textos_educativos_v1.rptdesign&__format=pdf'.'&codue='.$sie.'&gestion='.$gestion;
		
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


}
