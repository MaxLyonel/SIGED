<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEliminados;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
use Sie\AppWebBundle\Entity\EstudianteHistorialModificacion; 
use Sie\AppWebBundle\Entity\EstudianteInscripcion; 
use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\EstudianteDocumento; 
use Symfony\Component\Validator\Constraints\DateTime;

class UpdateSudentLevelController extends Controller{
    

    public $session;
    public $currentyear;
    public $userlogged;
    public $aCursos;
    public $em;

     /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->currentyear = $this->session->get('currentyear');
        $this->userlogged = $this->session->get('userId');
        $this->aCursos = $this->fillCursos();
        
    }
    // index method by krlos
    public function indexAction(Request $request){
     
        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render($this->session->get('pathSystem') .':UpdateSudentLevel:index.html.twig', array(
           
        ));
    }

    public function lookStudentDataAction(Request $request){

        // get the send values
        $codigoRude =  mb_strtoupper($request->get('codigoRude'),'utf-8');

        // set the ini var
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager(); 
        $arrStudentExist = array();
        $dataInscriptionR = array();
        $code = 200;
        $message = "";
        $status = "";
        $existStudentData = false;

        $arrCurrenteInscription = array();
        $arrLastInscription = array();
        $arrNextLevel = array();
        $arrParalelos = array();
        $swObservation = false;
        $messageObservaation = '';
        

        $arrayCondition = array('codigoRude'=>$codigoRude);
        // get the students info
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findBy($arrayCondition);
        // continue if exists
        if(sizeof($objStudent)>0){
            $objStudent = $objStudent[0];
            // the student exist
             $arrStudentExist = array(
                'paterno'=>$objStudent->getPaterno(),
                'materno'=>$objStudent->getMaterno(),
                'nombre'=>$objStudent->getNombre(),
                'carnet'=>$objStudent->getCarnetIdentidad(),
                'complemento'=>$objStudent->getComplemento(),
                'fecNac'=>$objStudent->getFechaNacimiento()->format('d-m-Y') ,
                'rude'=>$objStudent->getCodigoRude() ,
                'idStudent'=>$objStudent->getId() ,
            );

            $inscriptions =$this->get('funciones')->getAllInscriptionRegular($codigoRude);
             reset($inscriptions);
            $sw = true;
           
            //look for the next level inscrption if it has
            while($sw &&  ($inscription = current($inscriptions))){
                //get current inscripción estadoMatriculaId
                // if($inscription['gestion']==$this->currentyear && $inscription['estadoMatriculaId']==4){
                //     $arrCurrenteInscription = $inscription;
                // }                
                if($inscription['estadoMatriculaId']=='5' || $inscription['estadoMatriculaId']=='56' /*|| $inscription['estadoMatriculaId']=='57' || $inscription['estadoMatriculaId']=='58'*/ ){
                  $arrLastInscription = $inscription;
                  $sw=false;
                }

              next($inscriptions);
            }

            // thee student has history_?
            if(!$sw){
                $arrayConditionInscription = array(
                    'codigoRude'=>$codigoRude,
                    'matriculaId'=>4,
                    'gestion'=>$this->currentyear,
                );
                $arrCurrenteInscription = $this->get('funciones')->getCurrentInscriptionByRudeAndGestionAndMatricula($arrayConditionInscription);
                $arrCurrenteInscription = $arrCurrenteInscription[0];

                $arrNextLevel = $this->getInfoInscriptionStudent($arrLastInscription['nivelId'].$arrLastInscription['cicloId'].$arrLastInscription['gradoId'],$arrLastInscription['estadoMatriculaId']);

                if( $arrCurrenteInscription['nivelId']==$arrNextLevel['nivelId'] && $arrCurrenteInscription['cicloId']==$arrNextLevel['cicloId'] && $arrCurrenteInscription['gradoId']==$arrNextLevel['gradoId']){
                    $swObservation = false;
                    $messageObservaation = 'No presenta Observación';
                    
                }else{
                    $swObservation = true;
                    $messageObservaation = 'Presenta Observación';
                    
                    $objInfoCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findBy(array(
                        'nivelTipo'=>$arrNextLevel['nivelId'],
                        'cicloTipo'=>$arrNextLevel['cicloId'],
                        'gradoTipo'=>$arrNextLevel['gradoId'],
                        'institucioneducativa'=>$arrCurrenteInscription['sie'],
                        'gestionTipo'=>$this->currentyear,
                    ));
                    $arrParalelos = array();
                    foreach ($objInfoCurso as  $value) {
                        $arrParalelos[] = array('id'=>$value->getParaleloTipo()->getId(),'paralelo'=>$em->getRepository('SieAppWebBundle:ParaleloTipo')->find($value->getParaleloTipo()->getId())->getParalelo() );
                    }

                    $arrNextLevel['studenInscriptionId']=$arrCurrenteInscription['studenInscriptionId'];
                    $arrNextLevel['sie']=$arrCurrenteInscription['sie'];
                    $arrNextLevel['gestion']=$arrCurrenteInscription['gestion'];
                    $arrNextLevel['codigoRude']=$codigoRude;
                    $arrNextLevel['nivel']=$em->getRepository('SieAppWebBundle:NivelTipo')->find($arrNextLevel['nivelId'])->getNivel() ;
                    $arrNextLevel['grado']=$em->getRepository('SieAppWebBundle:GradoTipo')->find($arrNextLevel['gradoId'])->getGrado() ;
                    $arrNextLevel['oldDataInscription']=json_encode($arrCurrenteInscription) ;
                    

                }

             
                $existStudentData = true;                

            }else{
                $swObservation = false;
                $messageObservaation = 'No presenta Observación';
                $existStudentData = true; 
            }


                
        }else{
            // the studnet no exist
            $code = 200;
            $message = "Estudiante No existe";
            $status = "";
            $existStudentData = false;
            $swObservation = false;
        }

     $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'dataInscriptionR' => $inscriptions,    
        'arrStudentExist' => $arrStudentExist,
        'existStudentData' => $existStudentData,
        'swObservation' => $swObservation,
        'messageObservaation' => $messageObservaation,
        'arrParalelos' => $arrParalelos,
        'arrCurrenteInscription' => $arrCurrenteInscription,
        'arrNextLevel' => $arrNextLevel,


        
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function setTurnoAction(Request $request) {
        // ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        // get the send values
        $sie = $request->get('sie');
        $nivel = $request->get('nivelId');
        $grado = $request->get('gradoId');
        $paralelo = $request->get('paraleloId');
        $gestion = $request->get('gestion');
        //get turno
        $aturnos = array();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.turnoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andWhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
        $aTurnos = $query->getResult();
        foreach ($aTurnos as $turno) {
            $aturnos[] =array('id'=>$turno[1], 'turno'=> $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno());
        }

      $arrResponse = array(
        'arrTurno' => $aturnos
      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }    

    /**
     * get all info about the next level
     * @param type $nivel
     * @param type $grado
     */
    public function getInfoInscriptionStudent($currentLevelStudent, $matricula) {

        $sw = 1;
        $cursos = $this->aCursos;
        $keyLevel = ($matricula)?'':2;
        while (($acourses = current($cursos)) !== FALSE && $sw) {
            if ($acourses == $currentLevelStudent) {
                $keyLevel = key($cursos);
                $sw = 0;
            }
            next($cursos);
        }
        switch ($matricula) {
          case 4:
            # efectivo
            $levelStudent = -1;
            break;
          case 5:
          case 56:
          case 57:
          case 58:
            # promovido
            $levelStudent = $keyLevel + 1;
            break;
          default:
            # no next level
            $levelStudent = $keyLevel;
            break;
        }
        $levelId = substr($this->aCursos[$levelStudent], 0,-2);
        $cicloId = substr($this->aCursos[$levelStudent], 2,-1);
        $gradoId = substr($this->aCursos[$levelStudent], -1);
        $arrInfoLevel = array(
            'nivelId'=>$levelId,
            'cicloId'=>$cicloId,
            'gradoId'=>$gradoId,
        );
        return ($arrInfoLevel);
    }

    /**
     * build the cursos in a array
     * @param type $limitA
     * @param type $limitB
     * @param type $limitC
     * return array with the courses
     */
    private function fillCursos() {
        $this->aCursos = array(
            ('1111'),
            ('1112'),
            ('1211'),
            ('1212'),
            ('1213'),
            ('1224'),
            ('1225'),
            ('1226'),
            ('1311'),
            ('1312'),
            ('1323'),
            ('1324'),
            ('1335'),
            ('1336')
        );
        return($this->aCursos);
    }

    public function doUpdateAction(Request $request){

        // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();    
        // get the send values
        $codigoRude = $request->get('codigoRude');
        $nivelId = $request->get('nivelId');
        $cicloId = $request->get('cicloId');
        $gradoId = $request->get('gradoId');
        $paraleloId = $request->get('paraleloId');
        $turnoId = $request->get('turnoId');
        $sie = $request->get('sie');
        $gestion = $request->get('gestion');
        $studenInscriptionId = $request->get('studenInscriptionId');
        // condition to find the correct level to fix the observation
        $arrayCondition = array(
            'nivelTipo' => $nivelId,
            'cicloTipo' => $cicloId,
            'gradoTipo' => $gradoId,
            'paraleloTipo' => $paraleloId,
            'turnoTipo' => $turnoId,
            'institucioneducativa' => $sie,
            'gestionTipo' => $gestion,
        );

        try {
        
            $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studenInscriptionId);
            $objStudentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy($arrayCondition));
            $em->persist($objStudentInscription);
            $em->flush();

            $arrNewDataInscription = array('istudenInscriptionId' => $objStudentInscription->getId(), 'institucioneducativaCursoId' => $objStudentInscription->getInstitucioneducativaCurso()->getId());

            $this->get('funciones')->setLogTransaccion(
                        $studenInscriptionId,
                        'estudiante_inscripcion',
                        'U',
                        '',
                        $arrNewDataInscription,
                        $request->get('oldDataInscription'),
                        'SIGED',
                        json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                    );
            // Try and commit the transaction
            $em->getConnection()->commit();             

        } catch (Exception $ex) {
                    $em->getConnection()->rollback();
                    echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }


        
        // get all data to show the change
        $inscriptions =$this->get('funciones')->getAllInscriptionRegular($codigoRude);
        $swObservation = false;
        $messageObservaation = 'No presenta Observación';
        $existStudentData = true; 

        $arrResponse = array(
            'dataInscriptionR' => $inscriptions,
            'swObservation' => $swObservation,
            'messageObservaation' => $messageObservaation,
            'existStudentData' => $existStudentData,

          );
          $response->setStatusCode(200);
          $response->setData($arrResponse);

          return $response;

    }       

}
