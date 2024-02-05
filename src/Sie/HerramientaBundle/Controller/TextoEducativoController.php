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
use Sie\AppWebBundle\Entity\NivelTipo;
use Sie\AppWebBundle\Entity\TurnoTipo;
use Sie\AppWebBundle\Entity\GradoTipo;
use Sie\AppWebBundle\Entity\ParaleloTipo;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\Estudiante;

/**
 * ModalidadCurso controller.
 *
 */
class TextoEducativoController extends Controller
{

	public $session;
	public $month;
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
	}

	public function indexAction(Request $request)
	{
		$em 				= $this->getDoctrine()->getManager();
		$db = $em->getConnection();
		$infoUe 			= $request->get('infoUe');
		$aInfoUeducativa 	= unserialize($infoUe);
		$cursoId 			= $aInfoUeducativa['ueducativaInfoId']['iecId'];
		$institucioneducativaCurso 			= $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoId); 
		$estudiantes 		= null;
		$sie= -1;
		$gestion=$this->session->get('currentyear');
		$listadoEntregas 	=array();
		if($institucioneducativaCurso)
		{
			$sie 		= $institucioneducativaCurso->getInstitucioneducativa()->getId();
			$gestion 	= $institucioneducativaCurso->getGestionTipo()->getId();
			$nivel 		= $institucioneducativaCurso->getNivelTipo()->getId();
			$grado 		= $institucioneducativaCurso->getGradoTipo()->getId();
			$paralelo 	= $institucioneducativaCurso->getParaleloTipo()->getId();
			$turno 		= $institucioneducativaCurso->getTurnoTipo()->getId();

			//select * from nota_tipo limit 10
			//select * from institucioneducativa_sucursal where gestion_tipo_id='2020' and institucioneducativa_id='40730008' limit 10
			//select * from periodo_tipo limit 10
			$estudiantes 		= null;
			$tipoPeriodo 		= '';
			$cantidadPeriodo 	= -1;
			$notaTipo 			= array();
			if($sie && $gestion && $nivel && $grado && $paralelo && $turno)
			{
				$estudiantes 	= $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);
				list($cantidadPeriodo,$tipoPeriodo) = $this->obtenerNotaTipo($institucioneducativaCurso->getInstitucioneducativa());


				$institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id'=>$sie));

				$gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id'=>$request->getSession()->get('currentyear')));
				$institucioneducativaSucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array(
					'gestionTipo'=>$gestionTipo,
					'institucioneducativa'=>$institucioneducativaCurso->getInstitucioneducativa()
				));


				$listadoEntregas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoTextosEducativos')->findBy(array(
					'institucioneducativaCurso'=>$institucioneducativaCurso,
					'periodoTipo'=> $institucioneducativaSucursal->getPeriodoTipoId()
				));

			}
			else
			{
				$estudiantes = null;
			}
		}
		
		//$objInstitucioneducativaCursoModalidadAtencion = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoModalidadAtencion')->findby(array('institucioneducativaCurso'=>$cursoId, 'mes'=>3));
		
		return $this->render('SieHerramientaBundle:TextoEducativo:index.html.twig', array
		(
			'curso' => $institucioneducativaCurso,
			'estudiantes' => $estudiantes,
			'cantidadPeriodo' => $cantidadPeriodo,
			'tipoPeriodo' => $tipoPeriodo,
			'sie' => $sie,
			'listadoEntregas' => $listadoEntregas,
			'gestion' => $gestion
		));
	}

	public function saveAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$form = $request->request->all();
		
		$request_sie 				= $form["request_sie"];
		$request_sie 				= filter_var($request_sie, FILTER_SANITIZE_NUMBER_INT);
		$request_sie 				= is_numeric($request_sie)?$request_sie:-1;

		$request_curso 				= $form["request_curso"];
		$request_curso 				= filter_var($request_curso, FILTER_SANITIZE_NUMBER_INT);
		$request_curso 				= is_numeric($request_curso)?$request_curso:-1;

		$request_temporada 			= $form["request_temporada"];
		$request_temporada 			= filter_var($request_temporada, FILTER_SANITIZE_NUMBER_INT);
		$request_temporada 			= is_numeric($request_temporada)?$request_temporada:-1;

		$request_recibio 			= $form["request_recibio"];
		$request_recibio 			= filter_var($request_recibio, FILTER_SANITIZE_NUMBER_INT);
		$request_recibio 			= is_numeric($request_recibio)?$request_recibio:-1;

		$request_cantidad_textos 	= $form["request_cantidad_textos"];
		$request_cantidad_textos 	= filter_var($request_cantidad_textos, FILTER_SANITIZE_NUMBER_INT);
		$request_cantidad_textos 	= is_numeric($request_cantidad_textos)?$request_cantidad_textos:-1;

		$request_fecha_registro 	= $form["request_fecha_registro"];

		$request_observacion 		= $form["request_observacion"];
		$request_observacion 		= filter_var($request_observacion, FILTER_SANITIZE_STRING);

		$estudiantes = 0;
		$data 	= NULL;
		$dataId = -1;
		$status = 404;
		$msj 	= 'Ocurrio un error, por favor vuelva a intentarlo.';
		$gestion=$this->session->get('currentyear');

		$institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id'=>$request_sie));
		$institucioneducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('id'=>$request_curso));

		if($institucioneducativa && $institucioneducativaCurso)
		{

			$sie 		= $institucioneducativaCurso->getInstitucioneducativa()->getId();
			$gestion 	= $institucioneducativaCurso->getGestionTipo()->getId();
			$nivel 		= $institucioneducativaCurso->getNivelTipo()->getId();
			$grado 		= $institucioneducativaCurso->getGradoTipo()->getId();
			$paralelo 	= $institucioneducativaCurso->getParaleloTipo()->getId();
			$turno 		= $institucioneducativaCurso->getTurnoTipo()->getId();
			$estudiantes 	= $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);

			$gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id'=>$request->getSession()->get('currentyear')));
			$institucioneducativaSucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array(
				'gestionTipo'=>$gestionTipo,
				'institucioneducativa'=>$institucioneducativa
			));


			$entregaTexto = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoTextosEducativos')->findOneBy(array(
				'institucioneducativaCurso'=>$institucioneducativaCurso,
				'periodoTipo'=> $institucioneducativaSucursal->getPeriodoTipoId(),
				'trimestreSemestre' =>$request_temporada
			));

			if($request_recibio==0 && $entregaTexto)
			{
				$em->remove($entregaTexto);
				$em->flush();
				$entregaTexto=null;
			}

			if($entregaTexto==null)
			{
				$entregaTexto = new InstitucioneducativaCursoTextosEducativos();
			}
			
			$periodoTipo = $em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneBy(array('id'=>$institucioneducativaSucursal->getPeriodoTipoId() ));

			$entregaTexto->setInstitucioneducativaCurso($institucioneducativaCurso);
			$entregaTexto->setTrimestreSemestre($request_temporada);
			$entregaTexto->setPeriodoTipo($periodoTipo);
			$entregaTexto->setObservacion($request_observacion);
			if($request_recibio==1)
			{
				$entregaTexto->setCantidad($request_cantidad_textos);
				$tmpFecha1=explode('-',$request_fecha_registro);
				list($d1,$m1,$y1)=$tmpFecha1;
				$entregaTexto->setfechaEntrega(new \DateTime($y1.'-'.$m1.'-'.$d1));
			}
			$entregaTexto->setRecibido($request_recibio);
			$entregaTexto->setFechaRegistro(new \DateTime(date('Y-m-d')));
			$em->persist($entregaTexto);
			$em->flush();

			$listadoEntregas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoTextosEducativos')->findBy(array(
				'institucioneducativaCurso'=>$institucioneducativaCurso,
				'periodoTipo'=> $institucioneducativaSucursal->getPeriodoTipoId()
			));

			$idCreadoModificado=($entregaTexto)?$entregaTexto->getId():-1;

			list($cantidadPeriodo,$tipoPeriodo) = $this->obtenerNotaTipo($institucioneducativaCurso->getInstitucioneducativa());
			$datosTexto= $this->renderView('SieHerramientaBundle:TextoEducativo:listadoEntregas.html.twig',array(
				'listadoEntregas'=>$listadoEntregas,
				'cantidadPeriodo'=>$cantidadPeriodo,
				'tipoPeriodo'=>$tipoPeriodo,
				'estudiantes' => $estudiantes,
				'sie' =>$sie,
				'gestion'=>$gestion,
			));

			$data=$datosTexto;
			$dataId= $idCreadoModificado;
			$status = 200;
			$msj='Datos registrados correctamente.';
		}
		else
		{
			$data 	= NULL;
			$dataId = -1;
			$status = 404;
			$msj='La unidad educativa no existe.';
		}

		$response = new JsonResponse($data,$status);
		$response->headers->set('Content-Type', 'application/json');
		return $response->setData(array('msj'=>$msj,'status'=>$status,'data'=>$data,'dataId'=>$dataId));
	}


    public function deleteAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $esAjax=$request->isXmlHttpRequest();

        $request_id = $id;
        $request_id = filter_var($request_id,FILTER_SANITIZE_NUMBER_INT);
        $request_id = is_numeric($request_id)?$request_id:-1;

        $data=null;
        $status= 404;
        $msj='Ocurrio un error, por favor vuelva a intentarlo';

        if($esAjax && $request_id >0)
        {
            $query ="delete from institucioneducativa_curso_textos_educativos where id=?";
            $stmt = $db->prepare($query);
            $params = array($request_id);
            $stmt->execute($params);
            $tmp=$stmt->fetchAll();

            $borrado = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoTextosEducativos')->findOneBy(array('id' => $request_id));

            if($borrado==null)
            {
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

	private function obtenerNotaTipo($institucioneducativa)
	{
		//select * from nota_tipo limit 10
		//select * from institucioneducativa_sucursal where gestion_tipo_id='2020' and institucioneducativa_id='40730008' limit 10
		//select * from periodo_tipo limit 10
		$em = $this->getDoctrine()->getManager();
		$db = $em->getConnection();
		$gestionTipo = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id'=>2022));
		$institucioneducativaSucursal = $em->getRepository('SieAppWebBundle:InstitucioneducativaSucursal')->findOneBy(array(
			'gestionTipo'=>$gestionTipo,
			'institucioneducativa'=>$institucioneducativa
		));
		$tipoPeriodo 		= '';
		$cantidadPeriodo 	= -1;
		if($institucioneducativaSucursal)
		{
			$periodoTipo = $institucioneducativaSucursal->getPeriodoTipoId();

			if($periodoTipo==1)
			{
				$tipoPeriodo ='Trimestre';
				$cantidadPeriodo= 3;
			}
			else if(2<=$periodoTipo && $periodoTipo <=4 )
			{
				$tipoPeriodo ='Semestre';
				$cantidadPeriodo= 2;
			}
		}
		return array($cantidadPeriodo,$tipoPeriodo);
	}


	public function seguimientoAction()
	{
      $em = $this->getDoctrine()->getManager();
      $departamento=-1;
      $distrito=-1;
      $userId=$this->session->get('userId');
      $userRol=$this->session->get('roluser');
      $datosUser=$this->getDatosUsuario($userId,$userRol);
      if($datosUser)
      {
        $departamentoDistrito=$datosUser['cod_dis'];
        list($departamento,$distrito)=$this->getDepartamentoDistrito($departamentoDistrito);

        $arrayDepartamentos = array();
        $arrayDistritos = array();
        $arrayUE = array();

        $rol= $datosUser['rol_tipo_id'];
        
        if(in_array($rol,[8]))//nacional
        {
            $arrayDepartamentos = $this->getDepartamentos();
            $arrayDistritos = array();
            $arrayUE = array();
        }
        else if(in_array($rol,[7]))//departamental
        {
            $tmpDepartamento= $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($departamento);
            $arrayDepartamentos[] = array('id'=>$tmpDepartamento->getId(),'codigo'=>$tmpDepartamento->getCodigo(),'depto'=>$tmpDepartamento->getDepartamento());

            $arrayDistritos = $this->getDistritos($departamento);
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

        return $this->render('SieHerramientaBundle:TextoEducativo:segumientoOperativoTextosEducativos.html.twig',array
        (
            'rol' => $rol,
            'departamentos'=>$arrayDepartamentos,
            'distritos'=>$arrayDistritos,
            'currentyear'=> $this->session->get('currentyear')
            //'ues'=>$arrayUE,
        ));
      }
      else
      {
        return $this->redirect($this->generateUrl('login'));
      }
	}


    private function getDepartamentoDistrito($numero)
    {
      $departamento=-1;
      $distrito=-1;
      if($numero==0)
      {
        $departamento=-1;
        $distrito=-1;
      }
      else
      {
        if($numero>0 && $numero<=9)
        {
          $departamento=$numero;
          $distrito=-1;
        }
        else
        {
          if($numero > 10 and strlen($numero)==4)
          {
            $departamento=substr($numero,0,1);
            $distrito=$numero;
          }
          else
          {
            $departamento=-1;
            $distrito=-1;
          }
        }
      }
      return array($departamento,$distrito);
    }

    public function getDepartamentos()
    {
      $em = $this->getDoctrine()->getManager();
      $departamentos =  $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();
      $departamentos_array = array();
      foreach ($departamentos as $value)
      {
        $departamentos_array[] = array('id'=>$value->getId(),'codigo'=>$value->getCodigo(),'depto'=>$value->getDepartamento());
      }
      return $departamentos_array;
    }

    private function getDatosUsuario($userId,$userRol)
    {
        $userId=($userId)?$userId:-1;
        $userRol=($userRol)?$userRol:-1;
        $gestion = date('Y');
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
        --where a.gestion_tipo_id='.$gestion.' and cargo_tipo_id in (1,12) and periodo_tipo_id=1 and f.rol_tipo_id=9 and e.esactivo=\'t\') a
        where a.gestion_tipo_id='.$gestion.' and cargo_tipo_id in (1,12) and e.esactivo=\'t\') a
        where user_id = ?
        and rol_tipo_id = ?
        ORDER BY cod_dis
        LIMIT 1
        ';
        $stmt = $db->prepare($query);
        $params = array($userId,$userRol);
        $stmt->execute($params);
        $user=$stmt->fetch();
        return $user;
    }

    public function mostrarDatosAction(Request $request)
    {
        $form = $request->request->all();

        $departamento = $form['departamento'];
        $distrito = $form['distrito'];
        //$ue = $form['ue'];
        $gestion = $form['gestion'];

        $sysName = $this->session->get('sysname');
        $sysName = strtolower(filter_var($sysName , FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
        $ieTipo=$this->getTipoUE($sysName);

        $departamento = filter_var($departamento,FILTER_SANITIZE_NUMBER_INT);
        $distrito = filter_var($distrito,FILTER_SANITIZE_NUMBER_INT);
        //$ue = filter_var($ue,FILTER_SANITIZE_NUMBER_INT);
        $gestion = filter_var($gestion,FILTER_SANITIZE_NUMBER_INT);

        $departamento = is_numeric($departamento)?$departamento:-1;
        $distrito = is_numeric($distrito)?$distrito:-1;
        $gestion = is_numeric($gestion)?$gestion:-1;

        //$datos=$this->getUnidadesEducativasDetalle($departamento,$distrito,$ue,$gestion);
        $datos=$this->getUnidadesEducativas($departamento,$distrito,$gestion);
        //dump($datos);die();
        return $this->render('SieHerramientaBundle:TextoEducativo:mostrarDatosSeguimientoOperativoTextosEducativos.html.twig',array(
            'datos' => $datos,
            'periodo' => $ieTipo,
        ));
    }

    private function getUnidadesEducativas($departamento=-1,$distrito=-1,$gestion)
    {
        $operadorDepartamento=($departamento==-1)?' <> ':' = ';
        $operadorDistrito=($distrito==-1)?' <> ':' = ';

        $ue=array();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $tipoUE=$this->session->get('sistemaid');
        /*$query ="
            select 
            institucioneducativa_id,institucioneducativa
            from 
            (
              select distinct e.codigo as cod_dis,e.lugar as des_dis,c.id as institucioneducativa_id,c.institucioneducativa,(select dependencia from dependencia_tipo where id=c.dependencia_tipo_id) as dependencia
              from (institucioneducativa c 
                  inner join jurisdiccion_geografica d on c.le_juridicciongeografica_id=d.id
                    inner join lugar_tipo e on e.lugar_nivel_id=7 and d.lugar_tipo_id_distrito=e.id)
              where c.estadoinstitucion_tipo_id=10 and c.institucioneducativa_acreditacion_tipo_id=1 and orgcurricular_tipo_id=1
            ) a
          where 
          cod_dis = ?
          and substring(cod_dis,1,1) = ?
          group by cod_dis,des_dis,institucioneducativa_id,institucioneducativa,dependencia";*/

        $query ="
            select 
            institucioneducativa_id_target,
            institucioneducativa,
            dependencia,
            (select count(*)
			from (
			select c.id, count(i.trimestre_semestre)
			from institucioneducativa_curso  c
				inner join turno_tipo d on  d.id = c.turno_tipo_id
					inner join nivel_tipo e on e.id = c.nivel_tipo_id 
						inner join ciclo_tipo f on f.id = c.ciclo_tipo_id 
						INNER JOIN grado_tipo g on g.id = c.grado_tipo_id
							INNER JOIN paralelo_tipo h on h.id = c.paralelo_tipo_id
								left JOIN institucioneducativa_curso_textos_educativos i on i.institucioneducativa_curso_id = c.id
			where institucioneducativa_id = institucioneducativa_id_target and gestion_tipo_id = ? 
			group by c.id
			having count(i.trimestre_semestre)>=1
			) as tmp) as cursos_registrados,
			(select count(distinct c.id)
			from institucioneducativa_curso  c
				inner join turno_tipo d on  d.id = c.turno_tipo_id
					inner join nivel_tipo e on e.id = c.nivel_tipo_id 
						inner join ciclo_tipo f on f.id = c.ciclo_tipo_id 
						INNER JOIN grado_tipo g on g.id = c.grado_tipo_id
							INNER JOIN paralelo_tipo h on h.id = c.paralelo_tipo_id
								left JOIN institucioneducativa_curso_textos_educativos i on i.institucioneducativa_curso_id = c.id
			where institucioneducativa_id = institucioneducativa_id_target and gestion_tipo_id = ?) as nro_cursos
            from 
            (
              select distinct e.codigo as cod_dis,e.lugar as des_dis,c.id as institucioneducativa_id_target,c.institucioneducativa,(select dependencia from dependencia_tipo where id=c.dependencia_tipo_id ) as dependencia
              from (institucioneducativa c 
                  inner join jurisdiccion_geografica d on c.le_juridicciongeografica_id=d.id
                    inner join lugar_tipo e on e.lugar_nivel_id=7 and d.lugar_tipo_id_distrito=e.id)
              where c.estadoinstitucion_tipo_id=10 and c.institucioneducativa_acreditacion_tipo_id=1 and orgcurricular_tipo_id=1 
            ) a
          where 
          cod_dis = ?
          and substring(cod_dis,1,1) = ?
          group by cod_dis,des_dis,institucioneducativa_id_target,institucioneducativa,dependencia
        ";

        $stmt = $db->prepare($query);
        $params = array($gestion,$gestion,$distrito,$departamento);
        $stmt->execute($params);
        $tmp=$stmt->fetchAll();

        if(!$tmp)
        {
        	$tmp=array();
        }
        return $tmp;
    }

    private function getUnidadesEducativasDetalle($departamento,$distrito,$ueSeleccionado,$gestion)
    {
        $operadorDepartamento=($departamento==-1)?' <> ':' = ';
        $operadorDistrito=($distrito==-1)?' <> ':' = ';
        $operadorUe=($ueSeleccionado==-1)?' = ':' = ';

        $ue=array();
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();


        $queryRegular ="
			select distinct c.id as idcurso,turno, nivel,grado, paralelo, e.id,g.id, h.id , d.id , f.id, (select count(institucioneducativa_curso_id) from institucioneducativa_curso_textos_educativos where institucioneducativa_curso_id = c.id  ) nro_registros
			from institucioneducativa_curso  c
				inner join turno_tipo d on  d.id = c.turno_tipo_id
					inner join nivel_tipo e on e.id = c.nivel_tipo_id 
						inner join ciclo_tipo f on f.id = c.ciclo_tipo_id 
						INNER JOIN grado_tipo g on g.id = c.grado_tipo_id
							INNER JOIN paralelo_tipo h on h.id = c.paralelo_tipo_id
			where institucioneducativa_id ".$operadorUe." ? and gestion_tipo_id=?
			order by  e.id asc,g.id asc, h.id asc,c.id asc, d.id asc, f.id asc
        ";

        $sysName = $this->session->get('sysname');
        $sysName = strtolower(filter_var($sysName , FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
        $ieTipo=$this->getTipoUE($sysName);

        if($ieTipo==2)
        {
            $query=$queryRegular;
        }
        else
        {
            $query=$queryRegular;
        }

        $stmt = $db->prepare($query);
        //$params = array($gestion,$gestion,$gestion,$gestion,$distrito,$departamento);
        $params = array($ueSeleccionado,$gestion);
        $stmt->execute($params);
        $tmp=$stmt->fetchAll();

        if($tmp)
        {
            foreach ($tmp as $u)
            {
                $ue[]=array(
                    'ue'=>$ueSeleccionado,
                    'id' =>$u['idcurso'],
                    'turno'=>$u['turno'],
                    'nivel'=>$u['nivel'],
                    'grado'=>$u['grado'],
                    'paralelo'=>$u['paralelo'],
                    'periodo' => $ieTipo,
                    'nro_registros'=>$u['nro_registros'],
                );
            }
        }


        return $ue;
    }

    public function getDistritos($departamento)
    {
        $em = $this->getDoctrine()->getManager();
        //$departamento=filter_var($request->get('departamento'),FILTER_SANITIZE_NUMBER_INT);
        $distritos_array=array();
        $em = $this->getDoctrine()->getManager();
        $distritos = $em->getRepository('SieAppWebBundle:DistritoTipo')->findBy(array('departamentoTipo'=>$departamento));
        
        foreach ($distritos as $d)
        {
            $distritos_array[]=array('id' =>$d->getId(),'distrito'=>$d->getDistrito());
        }
        //$response = new Response(json_encode($distritos_array));
        //$response->headers->set('Content-Type', 'application/json');
        //return $response;
        return $distritos_array;
    }

    public function getUnidadesEducativasAction($distrito)
    {
        $distrito=filter_var($distrito,FILTER_SANITIZE_NUMBER_INT);

        $departamento=substr($distrito,0,1);

        $ues_array=$this->getUnidadesEducativas($departamento,$distrito);
        $response = new Response(json_encode($ues_array));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function getTipoUE($sysName)
    {
        $tmpTipoUE = strtolower(filter_var($sysName , FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
        $tipoUE =-1;
        if(strpos($tmpTipoUE,'regular')>0)
        {
            $tipoUE=1;
        }
        elseif(strpos($tmpTipoUE,'alternativa')>0)
        {
            $tipoUE=2;
        }
        elseif(strpos($tmpTipoUE,'especial')>0)
        {
            $tipoUE=4;
        }
        else
        {
            $tipoUE=-1;
        }
        return $tipoUE;
    }

    public function generarReporteAction(Request $request, $sie,$gestion)
    {
		$pdf=$this->container->getParameter('urlreportweb') . 'reg_lst_textos_educativos_v1.rptdesign&__format=pdf'.'&codue='.$sie.'&gestion='.$gestion;
		//$pdf='http://127.0.0.1:63020/viewer/preview?__report=D%3A\workspaces\workspace_especial\Reporte-Textos-Educativos\reg_lst_textos_educativos_v1.rptdesign&__format=pdf'.'&codue='.$sie.'&gestion='.$gestion;
		
		$status = 200;
		$arch           = 'REPORTE_REGISTRO_DE_TEXTOS_EDUCATIVOS-'.date('Y').'_'.date('YmdHis').'.pdf';
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





	public function asignacionParaleloAction(Request $request) {
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //get the info ue
        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        // dump($aInfoUeducativa); die();
        $sie = $aInfoUeducativa['requestUser']['sie'];
        $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];

        $turno1 = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno)->getTurno();
        $grado1 = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado)->getGrado();
        $nivel1 = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel)->getNivel();
        $paralelo1 = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo)->getParalelo();
        $gestion_a = $gestion-1;


        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('gestionTipo'=>$gestion, 'institucioneducativa'=>$sie, 'nivelTipo'=>$nivel, 'gradoTipo'=>$grado, 'paraleloTipo'=>$paralelo ));
        $id_inst_curso=$entity->getId();
       
        //dump($nivel, $grado, $id_inst_curso );die;
        $query = $em->getConnection()->prepare("create temporary table estudiantes_regular_seleccionables_".$sie."_".$nivel."_".$grado." as select * from sp_regular_listado_inscripcion_ig('".$gestion_a."','".$gestion."','".$sie."','".$nivel."','".$grado."','".$id_inst_curso."') order by paterno,materno,nombre");
		    $query->execute();


        $query1 = $em->getConnection()->prepare("select * from estudiantes_regular_seleccionables_".$sie."_".$nivel."_".$grado." ");
		    $query1->execute();
        $dataInscription = $query1->fetchAll();
        //dump($dataInscription);die;
        if (!$dataInscription) {
          $query3 = $em->getConnection()->prepare("drop table if exists estudiantes_regular_seleccionables_".$sie."_".$nivel."_".$grado." ");
          $query3->execute();
        }
		// dump($dataInscription); die();


        /*$query = $em->getConnection()->prepare("select * from sp_regular_listado_inscripcion_ig('".$gestion_a."','".$gestion."','".$sie."','".$nivel."','".$grado."','".$id_inst_curso."')");
		$query->execute();
		$dataInscription = $query->fetchAll();*/
        // $institucioneducativaCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId); 

        return $this->render($this->session->get('pathSystem') . ':AsignarParalelo:asignarparalelo_index.html.twig', array(
            'dataInscription' => $dataInscription,
            'nivel' => $nivel1,
            'grado' => $grado1,
            'gestion' => $gestion,
            'paralelo' => $paralelo1,
            'turno' => $turno1,
            'id_inst_curso' => $id_inst_curso,
            'sie' => $sie,
            'nivelid' => $nivel,
            'gradoid' => $grado
        ));
    }


    public function guardar_datos_asignacion_paraleloAction(Request $request){
    	  $em = $this->getDoctrine()->getManager();
    	  $response = new JsonResponse();
        $gradoid = $request->get('gradoid');
        $nivelid = $request->get('nivelid');
        $sie = $request->get('sie');
        $id_inst_curso = $request->get('id_inst_curso');
        $gestion = $request->get('gestion');
        $estudiante_id = $request->get('estudiante_id');
        $id_usuario = $this->session->get('userId');

        // dump("-----------------------"); 
        if(is_array($estudiante_id)){
        	// $em->getConnection()->beginTransaction();
			try {
				// $datos = array();
				for($i=0;$i<count($estudiante_id);$i++){	
					// $datos[]=$estudiante_id[$i];

          // $inscriptionsGestionSelected = $em->createQueryBuilder()
          // ->select('ei.id as idInscripcion')
          // ->from('SieAppWebBundle:EstudianteInscripcion','ei')
          // ->innerJoin('SieAppWebBundle:Estudiante','e','with','ei.estudiante = e.id')
          // ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ei.institucioneducativaCurso = iec.id')
          // ->innerJoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
          // ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = iec.gestionTipo')
          // ->where('e.id = :idEstudiante')
          // ->andWhere('ei.estadomatriculaTipo = 4')
          // ->andWhere('gt.id = :gestion')
          // ->andWhere('i.institucioneducativaTipo = :tipoue')
          // ->setParameter('idEstudiante', $estudiante_id[$i])
          // ->setParameter('gestion',  $gestion)
          // ->setParameter('tipoue', 1)
          // ->getQuery()
          // ->getResult();

            $query = $em->getConnection()->prepare("
              SELECT e0_.id AS id00 FROM estudiante_inscripcion e0_ 
              INNER JOIN estudiante e1_ ON (e0_.estudiante_id = e1_.id) 
              INNER JOIN institucioneducativa_curso i2_ ON (e0_.institucioneducativa_curso_id = i2_.id) 
              INNER JOIN institucioneducativa i3_ ON (i2_.institucioneducativa_id = i3_.id) 
              WHERE e1_.id = :estudianteId AND e0_.estadomatricula_tipo_id in (4,6,9,10,11,55) AND i2_.gestion_tipo_id = :gestionId::double precision AND i3_.institucioneducativa_tipo_id = 1
            ");
            $query->bindValue(':estudianteId', $estudiante_id[$i]);
            $query->bindValue(':gestionId', $gestion);
            $query->execute();
            $inscriptionsGestionSelected = $query->fetchAll();

            // dump("-----------------------"); 
            // dump($inscriptionsGestionSelected); die;
            //check if the student has more than one inscription




            if(count($inscriptionsGestionSelected)>0){
                //already register
               
            }else{

					      $estInscripcion = new EstudianteInscripcion();
					      $estInscripcion->setNumMatricula(0);
	              $estInscripcion->setObservacionId(0);
	              $estInscripcion->setObservacion(0);
	              $estInscripcion->setFechaInscripcion(new \DateTime('now'));
	              $estInscripcion->setApreciacionFinal('');
	              $estInscripcion->setOperativoId(1);
	              $estInscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById(4));
	              $estInscripcion->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->findOneById($estudiante_id[$i]));
	              $estInscripcion->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($id_inst_curso));
	              $estInscripcion->setFechaRegistro(new \DateTime('now'));
                $estInscripcion->setCodUeProcedenciaId($sie);
                $estInscripcion->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(1));
                $estInscripcion->setUsuarioId($id_usuario);
	              $em->persist($estInscripcion);
                $em->flush();
                
                //add the areas to the student
                //$query = $em->getConnection()->prepare('SELECT * from sp_genera_estudiante_asignatura(:estudiante_inscripcion_id::VARCHAR)');
                //$query->bindValue(':estudiante_inscripcion_id', $estInscripcion->getId());
                //$query->execute();
                $query = $em->getConnection()->prepare('SELECT * from sp_crea_estudiante_asignatura_regular(:sie::VARCHAR, :estudiante_inscripcion_id::VARCHAR)');
                $query->bindValue(':estudiante_inscripcion_id', $estInscripcion->getId());
                $query->bindValue(':sie', $sie);
                $query->execute();
                    
              }
				}	
				// $dato_string=implode(",",$datos);
				/*$query = $em->getConnection()->prepare("update estudiantes_regular_seleccionables_".$sie."_".$nivelid."_".$gradoid." set  es_seleccionado='t' where estudiante_id IN (".$dato_string.") ");
				$query->execute();*/
				// $em->getConnection()->commit();
				/*$query3 = $em->getConnection()->prepare("drop table if exists estudiantes_regular_seleccionables_".$sie."_".$nivelid."_".$gradoid." ");
				$query3->execute();*/
				$dat=array('0'=>1);
			} catch (Exception $e) {
	            // $em->getConnection()->rollback();
	            $dat=array('0'=>0);
	        }  
		}else{
			$dat=array('0'=>0);
		}
		return $response->setData($dat);
    
    } 

}
