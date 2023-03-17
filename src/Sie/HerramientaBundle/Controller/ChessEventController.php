<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\EstudiantePersonaDiplomatico;
use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\Institucioneducativa; 

class ChessEventController extends Controller{
    public $session;
    public function __construct() {
        $this->session = new Session();
    }       
    public function index1Action(){
        return $this->render('SieHerramientaBundle:ChessEvent:index.html.twig', array(
                // ...
            ));    }

    public function indexAction(){
        
        $ie_id=$this->session->get('ie_id');
        
            $swregistry = false;
            $id_usuario = $this->session->get('userId');
            if (!isset($id_usuario)) {
                return $this->redirect($this->generateUrl('login'));
            }        

            return $this->render('SieHerramientaBundle:ChessEvent:index.html.twig',array(
                
            ));        
    }

    public function findUEDataAction(Request $request){
        // get the vars send        
        $sie = $request->get('sie');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $objUE = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
        $arrLevel = array(
            array('id'=>12,'level'=>'Educación Primaria Comunitaria Vocacional'),
            array('id'=>13,'level'=>'Educación Secundaria Comunitaria Productiva'),
        );        
        $existUE = 0;
        $arrModalidades = array();
        if(sizeof($objUE)>0){
            $existUE = 1;
            $objModalidades = $em->getRepository('SieAppWebBundle:EveModalidadesTipo')->findAll();
            if(sizeof($objModalidades) > 0 ){
                foreach ($objModalidades as $value) {
                    $arrModalidades[]=array('id'=>$value->getId(), 'modalidad'=>$value->getDescripcion() );
                }
            }
            $arrResponse = array(
                'sie'                 => $objUE->getId(),
                'institucioneducativa'=> $objUE->getInstitucioneducativa(),
                'existUE'         => $existUE,                
                'arrModalidades'         => $arrModalidades,                
                'arrLevel'         => $arrLevel,                
            );               
        }else{
            $arrResponse = array(
                'sie'                 => '',
                'institucioneducativa'=> '',
                'existUE'         => $existUE,
                'arrModalidades'         => $arrModalidades,
                'arrLevel'         => $arrLevel,
            );   
        }
        


        
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;        
    }

    public function getInfoEventAction(Request $request){
        // get the vars send        
        $modalidadId = $request->get('modalidadId');
        $levelId = $request->get('levelId');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // get fases data
        $query = "SELECT * FROM eve_fase_tipo where es_vigente = true and   eve_modalidades_tipo_id = " .$modalidadId  ;              
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrFases = $statement->fetchAll();
        // get categories data
        $query = "SELECT * FROM eve_categorias_tipo where eve_modalidades_tipo_id = " .$modalidadId ." and nivel_tipo_id =".$levelId ;              
        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrCategories = $statement->fetchAll();        
        
        $arrResponse = array(
            'arrFases'         => $arrFases,
            'arrCategories'    => $arrCategories,
            'existUE'         => 1,
        ); 
                
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }

    public function dataSelectedAction(Request $request){
        
        // get the vars send        
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        $modalidadLabel = $em->getRepository('SieAppWebBundle:EveModalidadesTipo')->find($modalidadId);
        $faseLabel = $em->getRepository('SieAppWebBundle:EveFaseTipo')->find($faseId);
        $categorieLabel = $em->getRepository('SieAppWebBundle:EveCategoriasTipo')->find($categorieId);
        
        $genderRequest = (( trim(explode('(',$categorieLabel->getCategoria())[0] )) ==  'Varones' )?1:2;

        //get the allow grades
        preg_match_all('/[0-9]+/', $categorieLabel->getCategoria(), $rangeLevel);
        // check if the exist numbers
        if(sizeof($rangeLevel)>0){
            $arrRangeLevel = $rangeLevel[0];

            $arrAllowGrade = array();
            for ($i=$arrRangeLevel[0]; $i <= $arrRangeLevel[sizeof($arrRangeLevel)-1 ]; $i++) { 
                $arrAllowGrade[]=array('id'=>$i, 'grade'=>$i);
            }            
        }else{
            $arrAllowGrade=array();
        }


        // get students data
        $query = "SELECT * 
                FROM eve_estudiante_inscripcion_evento eeie
                inner join estudiante_inscripcion ei on (eeie.estudiante_inscripcion_id = ei.id)
                inner join institucioneducativa_curso iec on (ei.institucioneducativa_curso_id = iec.id)
                where 
                eeie.eve_categorias_tipo_id = $categorieId and  
                eeie.eve_fase_tipo_id = $faseId and  
                eeie.eve_modalidades_tipo_id = $modalidadId and  
                iec.institucioneducativa_id = " . $sie ;

        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrEveStudents = $statement->fetchAll();

        // dump($arrEveStudents);die;
               

        $arrResponse = array(
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'modalidadLabel' => $modalidadLabel->getDescripcion(),
            'faseLabel'      => $faseLabel->getDescripcion(),
            'categorieLabel' => $categorieLabel->getCategoria(),
            // 'categorieId' => $categorieId,
            'arrAllowGrade' => $arrAllowGrade,
            'genderRequest' => $genderRequest,
            'existSelectData'         => 1,    )
        ; 

        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }

    public function getParalelosAction(Request  $request){
        // get the send values
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        $levelId = $request->get('levelId');
        $gradeId = $request->get('gradeId');
        $genderRequest = $request->get('genderRequest');
        $year = $this->session->get('currentyear')-1;

        // create db conexion
        $em=$this->getDoctrine()->getManager();
        $query="
            select ic.turno_tipo_id ,  ic.paralelo_tipo_id, pt.paralelo 
            from institucioneducativa_curso ic
            inner join paralelo_tipo pt on (ic.paralelo_tipo_id = pt.id)
            where ic.institucioneducativa_id = $sie and ic.nivel_tipo_id =$levelId and grado_tipo_id = $gradeId and gestion_tipo_id = $year 
            order by ic.paralelo_tipo_id 
        ";

        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrParallels = $statement->fetchAll();        

        // dump($arrParallels);die;

        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrParallels' => $arrParallels,
            'existParall'         => 1,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;          
    }

    public function showStudentsAction(Request $request){
        
        // get the send values
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        $levelId = $request->get('levelId');
        $gradeId = $request->get('gradeId');
        $parallelId = $request->get('parallelId');
        $genderRequest = $request->get('genderRequest');
        $year = $this->session->get('currentyear')-1;

        // create db conexion
        $em=$this->getDoctrine()->getManager();
        $query="
                select e.carnet_identidad, e.complemento ,e.codigo_rude ,e.paterno ,e.materno ,e.nombre ,e.genero_tipo_id , ei.id as inscriptionId, gt.genero
                from estudiante e 
                inner join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
                inner join institucioneducativa_curso ic on (ei.institucioneducativa_curso_id=ic.id)
                inner join genero_tipo gt on (e.genero_tipo_id=gt.id)
                where ic.nivel_tipo_id =$levelId and ic.grado_tipo_id =$gradeId and ic.gestion_tipo_id =$year and e.genero_tipo_id = $genderRequest and ic.paralelo_tipo_id = '".$parallelId."' and ic.institucioneducativa_id =$sie
        ";


        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrStudents = $statement->fetchAll();        
// dump($arrStudents);die;


        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrStudents' => $arrStudents,
            'existStudent'         => 1,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;          
    }

    public function doInscriptionAction(Request $request){
        dump($request);die;
    }





    public function findStudentAction(Request $request){
        
        // get the vars send        
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        
        
        // get students data
        $yearIns = $this->session->get('currentyear') - 1;
        $query = "
                select
                e.codigo_rude, e.carnet_identidad ,
                e.nombre, e.paterno, e.materno, ei.id as studentId, nt.nivel, gt.grado, pt.paralelo  
                from estudiante e 
                inner join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
                inner join institucioneducativa_curso ic on (ei.institucioneducativa_curso_id=ic.id)
                inner join nivel_tipo nt on (ic.nivel_tipo_id=nt.id) 
                inner join grado_tipo gt on (ic.grado_tipo_id=gt.id)
                inner join paralelo_tipo pt on (ic.paralelo_tipo_id=pt.id)
                where ic.gestion_tipo_id = ".$yearIns." and ic.institucioneducativa_id = $sie and e.codigo_rude ='".$request->get('codigoRude')."' 
                " ;

        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrStudents = $statement->fetchAll();

        if(sizeof($arrStudents)>0 ){
            $existStudent=1;
            $arrStudents=$arrStudents[0];
        }else{
            $existStudent=0;
            $arrStudents=array();
        }

       

        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrStudents' => $arrStudents,
            'existStudent'         => 1,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;          
    }










}
 