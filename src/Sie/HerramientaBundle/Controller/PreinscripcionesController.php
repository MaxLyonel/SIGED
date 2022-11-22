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

class PreinscripcionesController extends Controller
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

	/*
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
	
	public function ddjjAction(Request $request, $sie,$gestion)
	{
		$pdf=$this->container->getParameter('urlreportweb') . 'reg_lst_EstudiantesApoderados_Benef_UnidadEducativa_v1_EEA.rptdesign&__format=pdf'.'&ue='.$sie.'&gestion='.$gestion;
		//$pdf='http://127.0.0.1:63170/viewer/preview?__report=D%3A\workspaces\workspace_especial\bono-bjp\reg_lst_EstudiantesApoderados_Benef_UnidadEducativa_v1_EEA.rptdesign&__format=pdf'.'&ue='.$sie.'&gestion='.$gestion;
		
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
	*/

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
		else if(in_array($rol,[7]))//departamental
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

			$arrayUE=$this->getUEAltaDemanda($departamento,$distrito);
		}
		else if(in_array($rol,[9]))//Director
		{
			$tmpDepartamento= $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($departamento);
			$arrayDepartamentos[] = array('id'=>$tmpDepartamento->getId(),'codigo'=>$tmpDepartamento->getCodigo(),'depto'=>$tmpDepartamento->getDepartamento());

			$tmpDistrito = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($distrito);
			$arrayDistritos[] = array('id' =>$tmpDistrito->getId(),'distrito'=>$tmpDistrito->getDistrito());

			$ue = $this->session->get('ie_id');

			$arrayUE = $this->getUEAltaDemanda($departamento,$distrito,$ue);
		}
		else//ningun rol permitido
		{
			return $this->redirect($this->generateUrl('login'));
		}

		return $this->render('SieHerramientaBundle:PreInscripciones:segumientoPreinscripciones.html.twig',array
		(
			'rol' => $rol,
			'departamentos'=>$arrayDepartamentos,
			'distritos'=>$arrayDistritos,
			'ues'=>$arrayUE,
		));
	  }
	  else
	  {
		return $this->redirect($this->generateUrl('login'));
	  }
	}

	public function mostrarDatosAction(Request $request)
	{
		$form = $request->request->all();

		$departamento = $form['departamento'];
		$distrito = $form['distrito'];
		$gestion = $form['gestion'];
		$ue = $form['ue'];

		$sysName = $this->session->get('sysname');
		$sysName = strtolower(filter_var($sysName , FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
		$ieTipo=$this->get('operativoutils')->getTipoUE($sysName);

		$departamento = filter_var($departamento,FILTER_SANITIZE_NUMBER_INT);
		$distrito = filter_var($distrito,FILTER_SANITIZE_NUMBER_INT);
		$gestion = filter_var($gestion,FILTER_SANITIZE_NUMBER_INT);

		$departamento = is_numeric($departamento)?$departamento:-1;
		$distrito = is_numeric($distrito)?$distrito:-1;
		$gestion = is_numeric($gestion)?$gestion:-1;

		$datos=$this->getPreinscritos($departamento,$distrito,$ue,$gestion);
		return $this->render('SieHerramientaBundle:PreInscripciones:mostrarDatosPreinscripciones.html.twig',array(
			'datos' => $datos,
			'periodo' => $ieTipo,
		));
	}

    public function getUEAction($distrito)
    {
        $distrito=filter_var($distrito,FILTER_SANITIZE_NUMBER_INT);

        $departamento=substr($distrito,0,1);

        $ues_array=$this->getUEAltaDemanda($departamento,$distrito,-1);
        $response = new Response(json_encode($ues_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    private function getUEAltaDemanda($departamento=-1, $distrito=-1, $ueSelect=-1)
    {
        $operadorDepartamento=($departamento==-1)?' <> ':' = ';
        $operadorDistrito=($distrito==-1)?' <> ':' = ';
        $operadorUE=($ueSelect==-1)?' <> ':' = ';

        $ue=array();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $tipoUE=$this->session->get('sistemaid');
        $query ="
            select 
            institucioneducativa_id, institucioneducativa, dependencia
            from 
            (
              select distinct e.codigo as cod_dis,e.lugar as des_dis,c.id as institucioneducativa_id,c.institucioneducativa,(select UPPER(dependencia) from dependencia_tipo where id=c.dependencia_tipo_id) as dependencia
              from (institucioneducativa c 
                  inner join jurisdiccion_geografica d on c.le_juridicciongeografica_id=d.id
                    inner join lugar_tipo e on e.lugar_nivel_id=7 and d.lugar_tipo_id_distrito=e.id)
              --where c.estadoinstitucion_tipo_id=10 and c.institucioneducativa_acreditacion_tipo_id=1 and orgcurricular_tipo_id=1
              where c.id in (select institucioneducativa_id from preins_institucioneducativa_curso_cupo where gestion_tipo_id =2022)
            ) a
          where 
          cod_dis = ?
          and substring(cod_dis,1,1) = ?
          and institucioneducativa_id ".$operadorUE." ?
          group by cod_dis,des_dis,institucioneducativa_id,institucioneducativa,dependencia
        ";

        $stmt = $db->prepare($query);
        $params = array($distrito, $departamento, $ueSelect);
        $stmt->execute($params);
        $tmp=$stmt->fetchAll();

        if($tmp)
        {
            foreach ($tmp as $u)
            {
                $ue[]=array('id' =>$u['institucioneducativa_id'],'ue'=>$u['institucioneducativa'].' | '.$u['dependencia']);
            }
        }
        return $ue;
    }

	public function getPreinscritos($departamento, $distrito, $ue, $gestion)
	{
		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();

		$query ="
			SELECT pei.id, pe.carnet_identidad, pe.complemento, pe.paterno, pe.materno, pe.nombre, pe.fecha_nacimiento,nt.nivel, gt.grado,
			picc.nivel_tipo_id, picc.grado_tipo_id,pei.fecha_inscripcion
			from
			preins_estudiante_inscripcion pei
			INNER JOIN preins_estudiante pe on  pe.id = pei.preins_estudiante_id
			inner join preins_institucioneducativa_curso_cupo picc on picc.id = pei.preins_institucioneducativa_curso_cupo_id
			INNER JOIN institucioneducativa i on i.id = picc.institucioneducativa_id
			INNER JOIN nivel_tipo nt on nt.id = picc.nivel_tipo_id
			INNER JOIN grado_tipo gt on gt.id = picc.grado_tipo_id
			where i.id = ?
			AND picc.gestion_tipo_id = ?
			ORDER BY picc.nivel_tipo_id asc, picc.grado_tipo_id asc
		";

        $stmt = $db->prepare($query);
        $params = array($ue, $gestion);
        $stmt->execute($params);
        $tmp=$stmt->fetchAll();

        if(!$tmp)
        {
        	$tmp=array();
        }
        return $tmp;
	}

}
