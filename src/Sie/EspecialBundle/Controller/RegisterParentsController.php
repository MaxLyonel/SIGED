<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;
use Sie\AppWebBundle\Entity\BjpApoderadoInscripcion;
use Sie\AppWebBundle\Entity\BjpApoderadoInscripcionBeneficiarios;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

class RegisterParentsController extends Controller {


    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }

    public function indexAction(Request $request){
        // get the send values
        $idInscription = $request->get('idInscripcion');
        
        // get info about the inscription
        $arrStudent = $this->getInfoStudent($idInscription);
            
        if($this->session->get('pathSystem')=='SieEspecialBundle'){
            $swRegistryBJP = true;
            $message='';
            $message2='';
        }else{
            if($arrStudent['studentYearOld']<21){
                $message='';
                $message2='';
                $swRegistryBJP = true;
            }else{
                $message='Estudiante fuera de rango de edad para el registro Bono Juancito Pinto.';
                $message2=' Verificar su registro de FECHA DE NACIMIENTO.';
                $swRegistryBJP = false;
            }   
        }
        
        // get the years old about the student

        // list the apoderado
        //$listStudentsParents = $this->listStudentsParents($idInscription);
        return $this->render($this->session->get('pathSystem') . ':RegisterParents:index.html.twig', array(
                'idInscription' => $idInscription,
                'arrStudent' => $arrStudent,
                'swRegistryBJP' => $swRegistryBJP,
                'messageRegistryBJP' => $message,
                'messageRegistryBJP2' => $message2,
        ));    
    }

    private function getInfoStudent($idInscription){

        $em= $this->getDoctrine()->getManager();
        $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscription);     
        $sql = "select public.sp_obtener_edad(to_date('".$objStudentInscription->getEstudiante()->getFechaNacimiento()->format('Y-m-d')."','YYYY-MM-DD'),to_date('2022-12-31','YYYY-MM-DD'))";
        $query = $em->getConnection()->prepare($sql);
        $query->execute();
        $dataStudent = $query->fetch();
        // arrStudent
        $arrStudent = array(

            'rude'        =>$objStudentInscription->getEstudiante()->getCodigoRude(),
            'student'     =>$objStudentInscription->getEstudiante()->getPaterno().' '.$objStudentInscription->getEstudiante()->getMaterno().' '.$objStudentInscription->getEstudiante()->getNombre(),
            'fecNac'      =>$objStudentInscription->getEstudiante()->getFechaNacimiento(),
            'carnet'      =>$objStudentInscription->getEstudiante()->getCarnetIdentidad(),
            'complemento' =>$objStudentInscription->getEstudiante()->getComplemento(),
            'studentYearOld' => $dataStudent['sp_obtener_edad'],


            'nivel'=>$objStudentInscription->getInstitucioneducativaCurso()->getNivelTipo()->getNivel(),
            'grado'=>$objStudentInscription->getInstitucioneducativaCurso()->getGradoTipo()->getGrado(),
            'paralelo'=>$objStudentInscription->getInstitucioneducativaCurso()->getParaleloTipo()->getParalelo(),
            'turno'=>$objStudentInscription->getInstitucioneducativaCurso()->getTurnoTipo()->getTurno(),
            'matricula'=>$objStudentInscription->getEstadomatriculaTipo()->getEstadomatricula(),

        );      
        return $arrStudent;
    }


    public function loadDataAction( Request $request ){
        
        $response = new JsonResponse();
        $idInscription = $request->get('idInscription', null);
        

        $em = $this->getDoctrine()->getManager();
        // get parents list
        $listStudentsParents = $this->listStudentsParents($idInscription);
        // get person bjp
        $parentBjp = $this->listParentBjp($idInscription);

        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'arrParents'=>$listStudentsParents,
                'arrParentBJP'=>$parentBjp,
                'swConfirm'=>(sizeof($listStudentsParents)>=1)?true:false,
                'swExistParentBJP'=>(sizeof($parentBjp)>=1)?true:false,
            )
        ]);

    }    
    public function lookforPersonAction(Request $request){
        // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        // set the variables
        $ci = $request->get('ci');

        $complemento = ($request->get('complemento')!='')?mb_strtoupper($request->get('complemento'), "utf-8"):'';
        // conditio nto find the person
        $arrayCondition2['complemento'] = $complemento;
        $arrayCondition2['carnet'] = $ci;
        // to look for person wiht segip 1
        $arrayCondition2['segipId'] = 1;
        // set the response variables
        $existPerson = false;
        $valSegip = true;
        $arrPerson = array(

            'paterno'     =>'',
            'materno'     =>'',
            'nombre'      =>'',
            'fecNac'      =>'',
            'carnet'      =>'',
            'complemento' =>'',
            'personId' =>'',
            'valSegip'=>$valSegip,
            //'expedido'    =>'',

        );
        $message = 'No existe Data';
        // look for data person
        $objPerson = $em->getRepository('SieAppWebBundle:Persona')->findOneBy($arrayCondition2);  
        
        if($objPerson){
            $message = 'No existe Data';
            $existPerson = true;
            $valSegip = false;
            $arrPerson = array(

                'paterno'     =>$objPerson->getPaterno(),
                'materno'     =>$objPerson->getMaterno(),
                'nombre'      =>$objPerson->getNombre(),
                'fecNac'      =>$objPerson->getFechaNacimiento()->format('d-m-Y'),
                'carnet'      =>$objPerson->getCarnet(),
                'complemento' =>$objPerson->getComplemento(),
                'personId' =>$objPerson->getId(),
                'valSegip'=>$valSegip,
                //'expedido'    =>$objPerson->getPaterno(),

            );          
            /*$arrParametros = array(
                'complemento'=>$request->get('ci'),
                'primer_apellido'=>$request->get('ci'),
                'segundo_apellido'=>$request->get('ci'),
                'nombre'=>$request->get('ci'),
                'fecha_nacimiento'=>$request->get('ci')
            );  
            $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros,'prod', 'academico');             */
        
        }
      // get genero data
      $objGenero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
      $arrGenero = array();
      foreach ($objGenero as $value) {
          if($value->getId()<3){
              $arrGenero[] = array('generoId' => $value->getId(),'genero' => $value->getGenero());                
          }
      }
      // get parentesco data
      $objApoderadoTipo = $em->getRepository('SieAppWebBundle:BjpApoderadoTipo')->findAll();

      $arrApoderadoTipo = array();
      foreach ($objApoderadoTipo as $value) {
          //if(in_array( $value->getId(), array(1,2,8)) ){
              $arrApoderadoTipo[] = array('apoderadoId' => $value->getId(),'apoderado' => $value->getApoderado());                
          //}
      }                
 
        $searchActive = true;

        $response->setStatusCode(200);
        $response->setData(array(
            'existPerson'=>$existPerson,
            'arrPerson'=> $arrPerson,
            'arrGenero'=> $arrGenero,
            'arrApoderadoTipo'=> $arrApoderadoTipo,
            'message'=>$message,
            'searchActive'=>$searchActive
        ));

        return $response;        
    }

    public function saveParentsAction(Request $request){
        // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        // get the send values
        $personId= $request->get('personId');
        $ci= $request->get('ci');
        $carnet= $request->get('ci');
        $valSegip= $request->get('valSegip');
        $complemento= $request->get('complemento');
        $paterno= $request->get('paterno');
        $materno= $request->get('materno');
        $nombre= $request->get('nombre');
        $fecNac= $request->get('fecNac');
        $parentescoId= $request->get('parentescoId');
        $idInscription= $request->get('idInscription');
        $generoId= $request->get('generoId');
        $extranjero= $request->get('extranjero');


      // buil the person data
      $arrParametros = array(
        'complemento'=>$complemento,
        'primer_apellido'=>$paterno,
        'segundo_apellido'=>$materno,
        'nombre'=>$nombre,
        'fecha_nacimiento'=>$fecNac
      );
      
      if($extranjero == 1){
        $arrParametros['extranjero'] = 'e';
      }
      
      // do the validation on segip if the person is not in SIE DB
      $answerSegip = true;
      $message = "";
      if($valSegip == 'true'){
        $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros,'prod', 'academico');
        //dump($answerSegip);die;
        //$answerSegip = true;
        if($answerSegip){   
            $newPerson = $this->registerNewPerson($arrParametros, $carnet, $generoId, $extranjero);
            $personId = $newPerson->getId();
        }
        
      }else{
        // not thing to do 
      }


      if($answerSegip){
        //save the apoderado
        $nuevoApoderado = new BjpApoderadoInscripcion();
        $nuevoApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:BjpApoderadoTipo')->find($parentescoId));
        $nuevoApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($personId));
        $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscription));
        $nuevoApoderado->setObs('');
        $nuevoApoderado->setEsValidado(1);
        $nuevoApoderado->setEsEliminado(1);
        $nuevoApoderado->setFechaRegistro(new \DateTime('now'));
        $em->persist($nuevoApoderado);
        

        $objPerson = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array('carnet'=>$ci));
        $objPerson->setEsExtranjero($extranjero);
        $em->persist($objPerson);
        $em->flush();  
      }else{
        $message = "Validacion segip: Error en el registro de los datos personales";
      }

        // list the apoderado bjp
        $parentBjp = $this->listParentBjp($idInscription);
      
        // list the apoderado
        $listStudentsParents = $this->listStudentsParents($idInscription);

        $response->setStatusCode(200);
        $response->setData(array(
            'datos'=>array(
                'arrParents'=>$listStudentsParents,
                'swConfirm'=>(sizeof($listStudentsParents)>=1)?true:false,
                'arrParentBJP'=>$parentBjp,
                'swExistParentBJP'=>(sizeof($parentBjp)>=1)?true:false,
                'messagesegip'=>$message,
                'answerSegip'=>!$answerSegip,
            )

        ));

        return $response;           



    }

    private function registerNewPerson($data,$carnet, $generoId, $extranjero){
        // create db conexion
        $em = $this->getDoctrine()->getManager();
        // create new person object
            $newpersona = new Persona();            
            $newpersona->setPaterno(mb_strtoupper($data['primer_apellido'], "utf-8"));
            $newpersona->setMaterno(mb_strtoupper($data['segundo_apellido'], "utf-8"));    
            $newpersona->setNombre(mb_strtoupper($data['nombre'], "utf-8"));    
            $newpersona->setCarnet($carnet);  
            $newpersona->setComplemento(mb_strtoupper($data['complemento'], "utf-8"));
            $newpersona->setIdiomaMaterno($em->getRepository('SieAppWebBundle:IdiomaTipo')->find(0));
            $newpersona->setSangreTipo($em->getRepository('SieAppWebBundle:SangreTipo')->findOneById(0));
            $newpersona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById(0));

            $newpersona->setFechaNacimiento(new \DateTime($data['fecha_nacimiento']));            
            $newpersona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($generoId));
            $newpersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));
            $newpersona->setRda('0');
            $newpersona->setActivo('1');
            $newpersona->setSegipId('1');            
            $newpersona->setEsVigente('1');
            $newpersona->setCountEdit('1');
            $newpersona->setEsvigenteApoderado('1');
            $newpersona->setCedulaTipo($em->getRepository('SieAppWebBundle:CedulaTipo')->find(($extranjero)?2:1));
            
            // $newpersona->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($data['departamentoTipo']));
            $em->persist($newpersona);
            $em->flush();

            return $newpersona;

    }

    private function listStudentsParents($inscriptionId){
        
        $em = $this->getDoctrine()->getManager();

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
                            IDENTITY(ai.apoderadoTipo) AS apoderadoTipo,
                            ai.esEliminado
                        ')
                        ->from('SieAppWebBundle:BjpApoderadoInscripcion','ai')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','with','ai.estudianteInscripcion = ei.id')
                        ->innerJoin('SieAppWebBundle:Persona','p','with','ai.persona = p.id')

                        ->where('ei.id = :inscriptionId')
                        ->andWhere('p.segipId = 1')
                        ->setParameter('inscriptionId', $inscriptionId)
                        ->orderBy('ai.id','DESC');
        $parents = $parents           ->getQuery()
                        ->getResult();
        
        $arrayPersonas = [];
        foreach ($parents as $ap) {
            $arrayPersonas[] = array(
                "id" => $ap['id'],
                "carnet" => $ap['carnet'],
                "complemento" => $ap['complemento'],
                "paterno" => $ap['paterno'],
                "materno" => $ap['materno'],
                "nombre" => $ap['nombre'],
                "fechaNacimiento" => $ap['fechaNacimiento']->format('d-m-Y'),
                "apoderadoTipoId" => $ap['apoderadoTipo'] ,
                "personaid" => $ap['personaid'] ,
                "esEliminado" => $ap['esEliminado'] ,
                "apoderadoTipo" => $em->getRepository('SieAppWebBundle:BjpApoderadoTipo')->find($ap['apoderadoTipo'])->getApoderado() ,
            );
        }

        return $arrayPersonas ;

    }

    public function removeAction(Request $request){
        // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        // get the send values
        $idremove= $request->get('idremove');
        $idInscription= $request->get('idInscription');

        // remove the partent selected
        $removeApoderado = $em->getRepository('SieAppWebBundle:BjpApoderadoInscripcion')->find($idremove);          
        $em->remove($removeApoderado);
        $em->flush();

        
        // list the apoderado
        $listStudentsParents = $this->listStudentsParents($idInscription);
        
        // send response
        $response->setStatusCode(200);
        $response->setData(array(
            'datos'=>array(
                'arrParents'=>$listStudentsParents,
                'swConfirm'=>(sizeof($listStudentsParents)>=1)?true:false,
            )

        ));

        return $response;       
    }

    public function removeParentBJPAction(Request $request){
        // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        // get the send values
        $idremove= $request->get('idremoveBJP');
        $idInscription= $request->get('idInscription');

        // remove the partent selected
        $removeApoderadoBJP = $em->getRepository('SieAppWebBundle:BjpApoderadoInscripcionBeneficiarios')->find($idremove);          
        $em->remove($removeApoderadoBJP);
        $em->flush();

        
        // list the apoderado
        $listStudentsParents = $this->listStudentsParents($idInscription);

        // get person bjp
        $parentBjp = $this->listParentBjp($idInscription);      
        // send response
        $response->setStatusCode(200);
        return $response->setData([
            'status'=>'success',
            'datos'=>array(
                'arrParents'=>$listStudentsParents,
                'arrParentBJP'=>$parentBjp,
                'swConfirm'=>(sizeof($listStudentsParents)>=1)?true:false,
                'swExistParentBJP'=>(sizeof($parentBjp)>=1)?true:false,
            )
        ]); 

        return $response;       
    }


    public function assignParentBJPAction(Request $request){

        // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();

        $apoderadoInscription = $request->get('apoderadoInscription');
        $idStudentInscription = $request->get('idStudentInscription');
        // get the years old about person
        list($dayPer, $monthPer, $yearPer) = explode('-',$apoderadoInscription['fechaNacimiento']);
        $arrYearsOld = implode('-',array($yearPer,$monthPer,$dayPer));
        
        $sql = "select public.sp_obtener_edad(to_date('".$arrYearsOld."','YYYY-MM-DD'),to_date('2022-10-24','YYYY-MM-DD'))";
        $query = $em->getConnection()->prepare($sql);
        $query->execute();
        $datayearPerson = $query->fetch();
        //dump($datayearPerson['sp_obtener_edad']);die;

        // look for the bjp person 
        $obParentBjp = $em->getRepository('SieAppWebBundle:BjpApoderadoInscripcionBeneficiarios')->findOneBy(array('estudianteInscripcion'=>$idStudentInscription));
        $message = '';
        $itemColor='';
        if ($datayearPerson['sp_obtener_edad']>=18) {
            // code...
            if(sizeof($obParentBjp)>0){
                $message='Ya existe beneficiario BJP';
                $itemColor='#f0ae68';
            }else{
                //save the apoderado
                $obParentBjp = new BjpApoderadoInscripcionBeneficiarios();
                $obParentBjp->setApoderadoTipo($em->getRepository('SieAppWebBundle:BjpApoderadoTipo')->find($apoderadoInscription['apoderadoTipoId']));
                $obParentBjp->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($apoderadoInscription['personaid']));
                $obParentBjp->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idStudentInscription));
                $obParentBjp->setObs('');
                $obParentBjp->setEsValidado(1);
                $obParentBjp->setFechaRegistro(new \DateTime('now'));
                $em->persist($obParentBjp);
                $em->flush();   
                $message='Tutor de cobro para el Bono Juancito Pinto registrado!';  
                $itemColor = '#7bbf8b';         
            }
        }else{
            $message='Tutor de cobro para el Bono Juancito Pinto NO registrado. Menor de Edad';  
            $itemColor='#f0ae68';
        }
      

        // list the apoderado bjp
        $parentBjp = $this->listParentBjp($idStudentInscription);
        $swExistParentBJP = (sizeof($parentBjp)>=1)?true:false;
        
        $response->setStatusCode(200);
        $response->setData(array(
            'datos'=>array(
                //'swExistParentBJP'=>$swExistParentBJP,
                'arrParentBJP'=>$parentBjp,
                'swExistParentBJP'=> $swExistParentBJP,
                'itemColor'=> $itemColor,
                'message'=>$message
            )

        ));

        return $response;   



    }

    private function listParentBjp($inscriptionId){
        
        $em = $this->getDoctrine()->getManager();

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
                        ->setParameter('inscriptionId', $inscriptionId)
                        ->orderBy('ai.id','DESC');
        $parents = $parents           ->getQuery()
                        ->getResult();
        
        $arrayPersonas = [];
        foreach ($parents as $ap) {
            $arrayPersonas[] = array(
                "id" => $ap['id'],
                "carnet" => $ap['carnet'],
                "complemento" => $ap['complemento'],
                "paterno" => $ap['paterno'],
                "materno" => $ap['materno'],
                "nombre" => $ap['nombre'],
                "fechaNacimiento" => $ap['fechaNacimiento']->format('d-m-Y'),
                "apoderadoTipoId" => $ap['apoderadoTipo'] ,
                "personaid" => $ap['personaid'] ,
                "apoderadoTipo" => $em->getRepository('SieAppWebBundle:BjpApoderadoTipo')->find($ap['apoderadoTipo'])->getApoderado() ,
            );
        }

        return $arrayPersonas ;

    }   



}
