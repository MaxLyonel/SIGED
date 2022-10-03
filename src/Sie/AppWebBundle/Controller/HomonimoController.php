<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante; 
use Symfony\Component\Validator\Constraints\DateTime;
use Sie\AppWebBundle\Entity\EstudianteHomonimo;

 /**
     * Modulo para la Unificación de Homónimos
    */
class HomonimoController extends Controller{

    private $session;

    public function __construct() {
        $this->session = new Session();
       
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
    }  

    public function indexAction(Request $request){
        
        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }        
        // db conexion
        $em = $this->getDoctrine()->getManager();
        // ini vars
         $arrStudent = array( 
                            'rudea' => '',
                            'rudeb' => '',
                            'messageqa' => '',
                        ); 
        //get info 
        $form = $request->get('form');
        if($form){
            $arrObs = array(24,26);
            if(in_array($form['idRegla'], $arrObs)){
                $arrRudes = explode('|',  $form['rude']);
                
                $arrStudent = array( 
                    'rudea' => $arrRudes[0],
                    'rudeb' => $arrRudes[1],
                    'messageqa' => '',   
                );

            }else{

                $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['rude']));
                    if(!$student){
                        $arrStudent['messageqa'] = 'Rudes no encontrados.';
                        $vala = '';
                        $valb = '';
                    }else{
                        $students = $em->createQueryBuilder()
                                    ->select('DISTINCT e.codigoRude')
                                    ->from('SieAppWebBundle:Estudiante','e')
                                    ->where('e.paterno = :paterno')
                                    ->andWhere('e.materno = :materno')
                                    ->andWhere('e.nombre = :nombre')
                                    ->andWhere('e.fechaNacimiento = :fechaNacimiento')
                                    ->setParameter('paterno', $student->getPaterno())
                                    ->setParameter('materno', $student->getMaterno())
                                    ->setParameter('nombre', $student->getNombre())
                                    ->setParameter('fechaNacimiento', $student->getFechaNacimiento())
                                    ->getQuery()
                                    ->getResult();

                        if(sizeof($students)<=1){
                            $arrStudent = array( 
                                'rudea' => '',
                                'rudeb' => '',   
                                'messageqa' => 'No existen dos rudes homonimos.',
                            ); 
                        }else{
                             $arrStudent = array( 
                                'rudea' => $students[0]['codigoRude'],
                                'rudeb' => $students[1]['codigoRude'],
                                'messageqa' => '',   
                            );
                        }
                }
            }
        }

        return $this->render($this->session->get('pathSystem').':Homonimo:index.html.twig', array(
                'rudeStudent' => $arrStudent
            ));    
    }

    public function datosAction(Request $request){
        // ini vars
        $response = new JsonResponse();
        // get the send data
        $rudea = mb_strtoupper($request->get('rudea')) ;
        $rudeb = mb_strtoupper($request->get('rudeb')) ;
        // creete db conexion
         $em = $this->getDoctrine()->getManager();
         $swresponse=true;
         $swhistory=true;
         $message=false;
         $status='success';
         $code = 200;
         $student = array();
         $arrMessage = array();
        // validate the cod RUDE
        $rudea = trim($rudea);
        $rudeb = trim($rudeb);
        if ($rudea == $rudeb) {            
             $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'Los códigos rudes deben ser distintos.',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );

            $response->setStatusCode(200);
            $response->setData($arrResponse);
            return $response;

        }


        // get the data students by rudes
        $studenta = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudea));
        if(!$studenta){
            $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'Estudiante con código Rude: '.$rudea.' no existe',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );

            $response->setStatusCode(200);
            $response->setData($arrResponse);
            return $response;
        }else{
            $arrStudenta = array(
                'id' => $studenta->getId(),
                'codigoRude' => $studenta->getCodigoRude(),
                'paterno' => $studenta->getPaterno(),
                'materno' => $studenta->getMaterno(),
                'nombre' => $studenta->getNombre(),
                'ci' => $studenta->getCarnetIdentidad(),
                'complemento' => $studenta->getNombre(),
                'complemento' => $studenta->getComplemento(),
                'fechaNac' => $studenta->getfechaNacimiento()->format('d-m-Y'),
                
            );
        }
        $studentb = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rudeb));
        if(!$studentb){
            $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'Estudiante con código Rude: '.$rudeb.' no existe',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );

            $response->setStatusCode(200);
            $response->setData($arrResponse);
            return $response;            
        }else{
            $arrStudentb = array(
                'id' => $studentb->getId(),
                'codigoRude' => $studentb->getCodigoRude(),
                'paterno' => $studentb->getPaterno(),
                'materno' => $studentb->getMaterno(),
                'nombre' => $studentb->getNombre(),
                'ci' => $studentb->getCarnetIdentidad(),
                'complemento' => $studentb->getComplemento(),
                'fechaNac' => $studentb->getfechaNacimiento()->format('d-m-Y'),
            );
        }

        // get the students history
        $dataInscriptiona = $this->getHistorial($rudea);
        $dataInscriptionb = $this->getHistorial($rudeb);

        // validate the histories info inscription
        if(sizeof($dataInscriptiona)==0 || sizeof($dataInscriptionb)==0){

             $arrResponse = array(
            'status'          => 'error',
            'code'            => 400,
            'message'         => 'No presentan historial de Inscripciones',
            'swresponse' => false,
            'swhistory' => false,
            'dataHistoryA' => array(),
            'dataHistoryB' => array(),
            );

            $response->setStatusCode(200);
            $response->setData($arrResponse);
            return $response;

        }


        $arrResponse = array(
            'status'          => $status,
            'code'            => $code,
            'message'         => $message,
            'swresponse' => $swresponse,
            'swhistory' => $swhistory,
            'dataHistoryA' => $dataInscriptiona,
            'dataHistoryB' => $dataInscriptionb,
            'studentA' => $arrStudenta,
            'studentB' => $arrStudentb,
            'rudea' => $rudea,
            'rudeb' => $rudeb,           
            'currentyear' => $this->currentyear,           
            'arrMessage' => $arrMessage,           
        );

      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
  
    }

    public function guardarAction(Request $request){
        $response = new JsonResponse();
        $arrDatos = json_decode($request->get('datos'), true);
        $estudianteA = $arrDatos['estudiantea'];
        $estudianteB = $arrDatos['estudianteb'];
        $justificacion = $arrDatos['justificacion'];
        $em = $this->getDoctrine()->getManager();
        //dump($arrDatos);
        //dump($_FILES['informe']);die;
        $archivador = '';
        if(isset($_FILES['informe'])){
            $file = $_FILES['informe'];

            $type = $file['type'];
            $size = $file['size'];
            $tmp_name = $file['tmp_name'];
            $name = $file['name'];
            $extension = explode('.', $name);
            $extension = $extension[count($extension)-1];
            $new_name = date('YmdHis').'.'.$extension;

            // GUARDAMOS EL ARCHIVO
            $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/homonimo/' .date('Y');
            if (!file_exists($directorio)) {
                mkdir($directorio, 0775, true);
            }

            $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/homonimo/' .date('Y').'/'.$estudianteA;
            if (!file_exists($directoriomove)) {
                mkdir($directoriomove, 0775, true);
            }

            $archivador = $directoriomove.'/'.$new_name;
            if(!move_uploaded_file($tmp_name, $archivador)){
                $em->getConnection()->rollback();
                        $response->setStatusCode(500);
                        return $response;
                    }
            }else{                    
                $archivador = 'empty';
        }   
            //unlink($archivador);
               
                $existeHomonimo = $em->getRepository('SieAppWebBundle:EstudianteHomonimo')->findOneBy(array(
                        'estudiante' => $em->getRepository('SieAppWebBundle:Estudiante')->find($estudianteA),
                        'estudianteHomonimo' => $em->getRepository('SieAppWebBundle:Estudiante')->find($estudianteB),
                    ));
                    
                if($existeHomonimo){
                    $status = 'info';
                    $code = 400;
                    $message = "Los datos ya existen como Homónimos";
                    $swinscription = true; 
                }else{

                $objEstudiante = new EstudianteHomonimo();
                $objEstudiante->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($estudianteA));
                $objEstudiante->setEstudianteHomonimo($em->getRepository('SieAppWebBundle:Estudiante')->find($estudianteB));
                $objEstudiante->setJustificacion($justificacion);
                $objEstudiante->setFechaRegistro(new \DateTime('now'));
                $objEstudiante->setArchivo($archivador);
                $objEstudiante->setUsuarioId($this->session->get('userId'));
                $em->persist($objEstudiante);
                $em->flush();
              //  $em->getConnection()->commit();

                $status = 'success';
                $code = 200;
                $message = "Se registraron los datos Correctamente";
                $swinscription = true; 
            }

           
            $arrResponse = array(
            'status'          => $status,
            'code'            => $code,
            'message'         => $message,
            'swDoneUnification'   => $swinscription,       
               
          );
          
          $response->setStatusCode(200);
          $response->setData($arrResponse);
    
          return $response;
        
    }
    public function getHistorial($rude){
        $dataInscriptionR = array();
        $dataInscriptionA = array();
        $dataInscriptionE = array();
        $dataInscriptionP = array();

        $dataInscriptionAll = array();
        
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rude . "') order by gestion_tipo_id_raep desc, estudiante_inscripcion_id_raep desc;");
        $query->execute();
        $dataInscription = $query->fetchAll();

        foreach ($dataInscription as $key => $inscription) {
            switch ($inscription['institucioneducativa_tipo_id_raep']) {
                case '1':
                    $dataInscriptionR[] = $inscription;
                    break;
                case '2':
                    $dataInscriptionA[] = $inscription;
                    break;
                case '4':
                    $dataInscriptionE[] = $inscription;
                    break;
                case '5':
                    if(($inscription['bloque_p'] == 1 && $inscription['parte_p'] == 1) || $inscription['parte_p'] == 14)$bloquep ='Segundo';
                    if(($inscription['bloque_p'] == 1 && $inscription['parte_p'] == 2) || $inscription['parte_p'] == 15)$bloquep = 'Tercero';
                    if(($inscription['bloque_p'] == 2 && $inscription['parte_p'] == 1) || $inscription['parte_p'] == 16)$bloquep = 'Quinto';
                    if(($inscription['bloque_p'] == 2 && $inscription['parte_p'] == 2) || $inscription['parte_p'] == 17)$bloquep = 'Sexto';
                    $dataInscriptionP[] = array(
                      'gestion'=> $inscription['gestion_tipo_id_raep'],
                      'institucioneducativa'=> $inscription['institucioneducativa_raep'],
                      'partp'=> ($inscription['parte_p']==1 ||$inscription['parte_p']==2)?'Antiguo':'Actual',
                      'bloquep'=> $bloquep,
                      'fini'=> $inscription['fech_ini_p'],
                      'ffin'=> $inscription['fech_fin_p'],
                      'curso'=> $inscription['institucioneducativa_curso_id_raep'],
                      'matricula'=> $inscription['estadomatricula_p'],
                    );
                    break;
            }
        }

        $dataInscriptionR = (sizeof($dataInscriptionR)>0)?$dataInscriptionR:false;
        $dataInscriptionA = (sizeof($dataInscriptionA)>0)?$dataInscriptionA:false;
        $dataInscriptionE = (sizeof($dataInscriptionE)>0)?$dataInscriptionE:false;
        $dataInscriptionP = (sizeof($dataInscriptionP)>0)?$dataInscriptionP:false;


        $dataInscriptionAll = array(

            'dataInscriptionR' => $dataInscriptionR,
            'dataInscriptionA' => $dataInscriptionA,
            'dataInscriptionE' => $dataInscriptionE,
            'dataInscriptionP' => $dataInscriptionP

        );

        
        return $dataInscriptionAll;
    }
    
    

}
