<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoModalidadAtencion;
use Sie\AppWebBundle\Entity\InstitucioneducativaModalidadAtencion;
use Sie\AppWebBundle\Entity\NivelTipo;
use Sie\AppWebBundle\Entity\TurnoTipo;
use Sie\AppWebBundle\Entity\GradoTipo;
use Sie\AppWebBundle\Entity\ParaleloTipo;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\Estudiante;


/**
 * InstitucionModalidadAte controller.
 * author = krlos
 */

class InstitucionModalidadAteController extends Controller{


	public $session;
	public $month;
	public function __construct(){
		$this->session = new Session();
		/* Verificar login*/
		$id_usuario = $this->session->get('userId');
		if (!isset($id_usuario))
		//if(false)
		{
			return $this->redirect($this->generateUrl('login'));
		}
	}
/**
 * index methos to get access by rol
 * author = krlos
 */	
    public function indexAction(Request $request){

 
    	if(!$this->session->get('roluser')){
    		return $this->redirect($this->generateUrl('login'));
    	}
    	$userId = $this->session->get('userId');
    	$userRol = $this->session->get('roluser');

        return $this->render($this->session->get('pathSystem').':InstitucionModalidadAte:index.html.twig', array(
            'rol' => $userRol,
            'currentyear'=> $this->session->get('currentyear')
            ));    
   	}

    public function getMainInfoAction(Request $request){
        $response = new JsonResponse();             

   	// create db conexion
    	$em = $this->getDoctrine()->getManager();
    	// set the place info
    	$departamento = 1;
    	$distrito = 1;
    	// get the user data
    	$userId = $this->session->get('userId');
    	// $userRol = $this->session->get('roluser');
    	$userRol = $request->get('userRol');

		$datosUser=$this->getDatosUsuario($userId,$userRol);
		if($datosUser){
	        $departamentoDistrito=$datosUser['cod_dis'];
	        list($departamento,$distrito)=$this->getDepartamentoDistrito($departamentoDistrito);		

	    	// get info about the user
	    	switch ($userRol) {
	    		case 8://Nacional
		            $arrayDepartamentos = $this->getDepartamentos();
		            $arrayDistritos = array();
		            $arrayUE = array();
				break;
	    		case 7://depto
		            $tmpDepartamento= $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($departamento);
		            $arrayDepartamentos[] = array('id'=>$tmpDepartamento->getId(),'codigo'=>$tmpDepartamento->getCodigo(),'depto'=>$tmpDepartamento->getDepartamento());

		            $arrayDistritos = $this->getDistritos($departamento);
		            $arrayUE = array();
				break;
	    		case 10://distrito
		            $tmpDepartamento= $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findOneById($departamento);
		            $arrayDepartamentos[] = array('id'=>$tmpDepartamento->getId(),'codigo'=>$tmpDepartamento->getCodigo(),'depto'=>$tmpDepartamento->getDepartamento());

		            $tmpDistrito = $em->getRepository('SieAppWebBundle:DistritoTipo')->findOneById($distrito);
		            $arrayDistritos[] = array('id' =>$tmpDistrito->getId(),'distrito'=>$tmpDistrito->getDistrito());
				break;
	    		
	    		default:
	    			return $this->redirect($this->generateUrl('login'));
	    			break;
	    	}			

		}else{
			return $this->redirect($this->generateUrl('login'));
		}

    	// dump($arrayDepartamentos);die;

        // $arrData = array('sedeId'=> $request->get('sedeId'));
        // $arrOperative = $this->get('univfunctions')->getAllOperative($arrData);
          $arrResponse = array(             
            'rol' => $userRol,
            'departamentos'=>$arrayDepartamentos,
            'distritos'=>$arrayDistritos,
            'currentyear'=> $this->session->get('currentyear')            
          );
          
          $response->setStatusCode(200);
          $response->setData($arrResponse);        
          return $response;        
    }   

   public function getDistritomainAction(Request $request){
        $response = new JsonResponse();
        $departamento=filter_var($request->get('deptoSelected'),FILTER_SANITIZE_NUMBER_INT);
        
        $distritos_array= $this->getDistritos($departamento);        


          $arrResponse = array(
            'distritos'=>$distritos_array,
            'currentyear'=> $this->session->get('currentyear')            
          );
          
          $response->setStatusCode(200);
          $response->setData($arrResponse);        
          return $response;

    }    

    public function getAllUEAction(Request $request){
    	$response = new JsonResponse();

        

        $departamento = $request->get('sendDepto');
        $distrito = $request->get('sendDistrito');
        //$ue = $form['ue'];
        $gestion = $request->get('gestion');

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
        $arrUEs=$this->getUnidadesEducativas($departamento,$distrito,$gestion);

        $answerResponse = (sizeof($arrUEs)>0)?true:false;
        $em = $this->getDoctrine()->getManager();
        $objModalidad = $em->getRepository('SieAppWebBundle:ModalidadAtencionTipo')->findAll();
        $arrModalidad = array();
        foreach ($objModalidad as $value) {
        	$arrModalidad[]=array('id'=>$value->getId(), 'modalidad'=>$value->getModalidadAtencion());
        }
      $arrResponse = array(
        'arrUEs'=>$arrUEs,
        'arrModalidad'=>$arrModalidad,
        'today'=> date("d-m-Y"),
        'currentyear'=> $gestion,
        'answerResponse'=> $answerResponse
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);        
      return $response;




    }

    public function registerUEModalityAction(Request $request){
    	// set var ini
    	$response = new JsonResponse();
    	$em = $this->getDoctrine()->getManager();
    	// get the send data
    	$sendDepto = $request->get('sendDepto');
    	$sendDistrito = $request->get('sendDistrito');
    	$gestion = $request->get('gestion');
    	$sie = $request->get('sie');
    	$modalidad = $request->get('modalidad');


        $allInstitucionModalidad = $this->getInstModalidadbyUE($sie);
        
        $answerHistory = false;
        if(sizeof($allInstitucionModalidad)>0){
        	$answerHistory = true;
        }

        $objInstitucionEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);

        $arrInstitucionE = array('sie' => $objInstitucionEducativa->getId(), 'unidadEducativa' => $objInstitucionEducativa->getInstitucioneducativa() ); 
        
      $arrResponse = array(
        // 'arrUEs'=>$arrUEs,
        'historyModalidad'=>$allInstitucionModalidad,
        'arrInstitucionE'=>$arrInstitucionE,
        'today'=> date("d-m-Y"),
        'currentyear'=> $gestion,
        'answerHistory'=> $answerHistory
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);        
      return $response;
    }    

    public function saveUEModalityAction(Request $request){
    	// set var ini
    	$response = new JsonResponse();
    	$em = $this->getDoctrine()->getManager();
    	// get the send data
    	$sendDepto = $request->get('sendDepto');
    	$sendDistrito = $request->get('sendDistrito');
    	$gestion = $request->get('gestion');
    	$sie = $request->get('sie');
    	$modalidad = $request->get('modalidad');

		$repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaModalidadAtencion');
        $query = $repository->createQueryBuilder('iema')
                ->where('iema.institucioneducativa = :institucion')
                ->andWhere('iema.fechaModificacion = :fechaRegistro')
                ->setParameter('institucion', $sie)
                ->setParameter('fechaRegistro', new \DateTime('now'))
                // ->setParameter('fechaNacimiento', new \DateTime($formulario['fecha']))
                ->getQuery();

        $modalidadToday = $query->getResult();   

        if(sizeof($modalidadToday)>0){
        	$objInstModalidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaModalidadAtencion')->find($modalidadToday[0]->getId());
        	// $objInstModalidad->setModalidadAtencionTipo($em->getRepository('SieAppWebBundle:ModalidadAtencionTipo')->find($modalidad));
        	// $objInstModalidad->setFechaModificacion(new \DateTime('now'));
        }else{
        	$objInstModalidad =  new InstitucioneducativaModalidadAtencion();
        	$objInstModalidad->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));
        	$objInstModalidad->setFechaRegistro(new \DateTime('now'));        
        }

    	$objInstModalidad->setModalidadAtencionTipo($em->getRepository('SieAppWebBundle:ModalidadAtencionTipo')->find($modalidad));
    	$objInstModalidad->setFechaModificacion(new \DateTime('now'));        
    	$em->persist($objInstModalidad);
        $em->flush();        	

        $allInstitucionModalidad = $this->getInstModalidadbyUE($sie);

        $answerHistory = false;
        if(sizeof($allInstitucionModalidad)>0){
        	$answerHistory = true;
        }

        
      $arrResponse = array(
        // 'arrUEs'=>$arrUEs,
        'historyModalidad'=>$allInstitucionModalidad,
        'today'=> date("d-m-Y"),
        'currentyear'=> $gestion,
        'answerHistory'=> $answerHistory
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);        
      return $response;
    }
    private function getInstModalidadbyUE($sie){
    	
    	$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaModalidadAtencion');
        $query = $repository->createQueryBuilder('iema')
                ->where('iema.institucioneducativa = :institucion')
                ->setParameter('institucion', $sie)
                ->getQuery();

        $modalidadToday = $query->getResult();  

        $arryUEMod = array();
        foreach ($modalidadToday as $value) {
        	$arryUEMod[] = array('id'=>$value->getId(), 'sie'=>$value->getInstitucioneducativa()->getId(), 'fecha'=>$value->getFechaRegistro()->format('d-m-Y'),'modalidad'=>$value->getModalidadAtencionTipo()->getModalidadAtencion());
        	# code...
        }

        return $arryUEMod;

    }

    private function getDepartamentoDistrito($numero){
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
    private function getDatosUsuario($userId,$userRol){
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

    public function getTipoUE($sysName){

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
        
   private function getUnidadesEducativas($departamento=-1,$distrito=-1,$gestion){
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



    public function getDepartamentos(){
      $em = $this->getDoctrine()->getManager();
      $departamentos =  $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();
      $departamentos_array = array();
      foreach ($departamentos as $value)
      {
        $departamentos_array[] = array('id'=>$value->getId(),'codigo'=>$value->getCodigo(),'depto'=>$value->getDepartamento());
      }
      return $departamentos_array;
    }
    public function getDistritos($departamento){

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




}
