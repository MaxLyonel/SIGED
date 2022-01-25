<?php

namespace Sie\AppWebBundle\Controller;

use Sie\AppWebBundle\Entity\WfSolicitudTramite;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\PersonaAdministrativoInscripcion;
use Sie\AppWebBundle\Entity\MaestroInscripcion;
use Sie\AppWebBundle\Entity\Persona;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;


class PersonalHealthController extends Controller{



    public $session;
    public $idInstitucion;
    public $router;
    /**
     * the class constructor
     */
    public function __construct() {

        $this->session = new Session();
    } 
    public function index1Action(){
        
        return $this->render('SieAppWebBundle:PersonalHealth:index.html.twig', array(
                // ...
            ));    
    }  


    public function indexAction (Request $request) {
    	
      	$userId=$this->session->get('userId');
      	$userRol=$this->session->get('roluser');
      	$dataLugar=$this->getDatosUsuario($userId,$userRol);
      	
      	$swshowall = false;
      	$idDepto = false;
      	
      	if($userRol==8){
      		$swshowall = true;
      	}else{
      		$idDepto = $dataLugar['cod_dep'];
      	}

        return $this->render('SieAppWebBundle:PersonalHealth:index.html.twig', array(
        	'idDepto' => $idDepto
        ));
    }

public function lookforDeptoAction(Request $request){
	
	$response = new JsonResponse();
	$idDepto = $request->get('depto');

	$em = $this->getDoctrine()->getManager();

	$objDistritosReport = $em->getRepository('SieAppWebBundle:DistritoControlOperativoMenus')->findBy(array('departamentoTipoId'=>$idDepto));
	
	$arrDistritos = array();
	foreach ($objDistritosReport as  $value) {
		$arrDistritos[] = array(
			'id' => $value->getId(),
			'idDistrito' => $value->getDistritoTipo()->getId(),
			'distrito' => $value->getDistritoTipo()->getDistrito()
		);
		
	}

    $response->setStatusCode(200);
    $response->setData(array(
        'arrDistritos'=>$arrDistritos,
    ));

    return $response;	

}

    private function getDatosUsuario($userId,$userRol){
        
        $userId=($userId)?$userId:-1;
        $userRol=($userRol)?$userRol:-1;

        $user=NULL;
        $em = $this->getDoctrine()->getManager();
        $db = $em->getConnection();
        $query = '
        select *
        from (
        select b.rol_tipo_id,(select rol from rol_tipo where id=b.rol_tipo_id) as rol,a.persona_id,c.codigo as cod_dis,dt2.id as cod_dep,a.esactivo,a.id as user_id
        from usuario a 
          inner join usuario_rol b on a.id=b.usuario_id 
            inner join lugar_tipo c on b.lugar_tipo_id=c.id
               inner join distrito_tipo dt on c.codigo::INTEGER = dt.id
                inner join departamento_tipo dt2 on dt.departamento_tipo_id = dt2.id                
        where c.codigo not in (\'04\') and b.rol_tipo_id not in (2,3,9,29,26,21,14,39,6) and a.esactivo=\'t\'
        union all
        select f.rol_tipo_id,(select rol from rol_tipo where id=f.rol_tipo_id) as rol,a.persona_id,d.codigo as cod_dis,dt2.id as cod_dep,e.esactivo,e.id as user_id
        from maestro_inscripcion a
          inner join institucioneducativa b on a.institucioneducativa_id=b.id
            inner join jurisdiccion_geografica c on b.le_juridicciongeografica_id=c.id
              inner join lugar_tipo d on d.lugar_nivel_id=7 and c.lugar_tipo_id_distrito=d.id
                inner join usuario e on a.persona_id=e.persona_id
                  inner join usuario_rol f on e.id=f.usuario_id
                    inner join distrito_tipo dt on d.codigo::INTEGER = dt.id
                      inner join departamento_tipo dt2 on dt.departamento_tipo_id = dt2.id                  
        where a.gestion_tipo_id=2021 and cargo_tipo_id in (1,12) and periodo_tipo_id=1 and f.rol_tipo_id=9 and e.esactivo=\'t\') a
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



    

}
