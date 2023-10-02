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
use Sie\AppWebBundle\Entity\EstudianteInscripcionExtranjero; 
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
        $rolusuario = $this->session->get('roluser');
        if($rolusuario != 8){
            return $this->redirect($this->generateUrl('login'));
        }
      

        $form = is_array(($request->get('form')))?$request->get('form'):false;
        if(!$form){
            $form = array(
                'idDetalle'=>'',
                'llave'=>'',
                'gestion'=>'',
                'institucioneducativa'=>'',
                'optionTodo'=>'',
            );
        }
     
        //validation if the user is logged
        if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }
        //dump($this->session->get('pathSystem'));die;
        return $this->render($this->session->get('pathSystem') .':UpdateSudentLevel:index.html.twig', array(
                'form' => $form
           
        ));
    }

    public function lookStudentDataAction(Request $request){ //dump($request);

        // get the send values
        $response = new JsonResponse();
        $codigoRude =  mb_strtoupper($request->get('codigoRude'),'utf-8');
        $em = $this->getDoctrine()->getManager(); 
        $swObs=false;
        
        $arrStudentExist = array();
        $dataInscriptionR = array();
        $code = 200;
        $message = "";
        $status = "";
        $existStudentData = false;
        $swUpdateLevelGradoIniPri = false;

        $arrCurrenteInscription = array();
        $arrLastInscription = array();
        $arrNextLevel = array();
        $arrParalelos = array();
        $arrLevel = array();
        $swObservation = false;
        $messageObservaation = '';
        
        if (in_array($this->session->get('roluser'), array(9))){ // el director solo puede trabajar su inscripcion
            //dump($codigoRude);
            $tuicionUe = $this->get('funciones')->getInscriptionToValidateTuicionUe($codigoRude, $this->currentyear);
            if($tuicionUe){
                //do change
            }
            else{
                $swObs=true;
                $mensaje = 'Usted no tiene Tuición sobre la inscripción del Estudiante ';
            }
        }

        $arrayCondition = array('codigoRude'=>$codigoRude);
        // get the students info
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findBy($arrayCondition);

        if( $objStudent==0 ){
            $swObs = true;
            $mensaje = 'Estudiante No existe o no cuenta con Historial';
        }
        // continue if exists
        if(sizeof($objStudent)>0 && $swObs==false){
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
                if($inscription['estadoMatriculaId']=='5' || $inscription['estadoMatriculaId']=='56' || $inscription['estadoMatriculaId']=='57' || $inscription['estadoMatriculaId']=='58' ){
                  $arrLastInscription = $inscription;
                  $sw=false;
                }

              next($inscriptions);
            }
            //dump($arrLastInscription);dump($sw);die;
             $arrayConditionInscription = array(
                    'codigoRude'=>$codigoRude,
                    'matriculaId'=>4,
                    'gestion'=>$this->currentyear,
                );
            $arrCurrenteInscription = $this->get('funciones')->getCurrentInscriptionByRudeAndGestionAndMatricula($arrayConditionInscription);
            
            if(sizeof($arrCurrenteInscription)>0){
                $arrCurrenteInscription = $arrCurrenteInscription[0];
                $arrNextLevel['studenInscriptionId']=$arrCurrenteInscription['studenInscriptionId'];
                $arrNextLevel['sie']=$arrCurrenteInscription['sie'];
                $arrNextLevel['gestion']=$arrCurrenteInscription['gestion'];
                $arrNextLevel['codigoRude']=$codigoRude;
                $arrNextLevel['oldDataInscription']=json_encode($arrCurrenteInscription) ;

            }else{
                $arrCurrenteInscription = array();
            }
            
            // thee student has history_?
            if(!$sw){ 
                $arrayConditionInscription = array(
                    'codigoRude'=>$codigoRude,
                    'matriculaId'=>4,
                    'gestion'=>$this->currentyear,
                );
                // $arrCurrenteInscription = $this->get('funciones')->getCurrentInscriptionByRudeAndGestionAndMatricula($arrayConditionInscription);

                if(sizeof($arrCurrenteInscription)>0){
                   
                    // $arrCurrenteInscription = $arrCurrenteInscription[0];

                    $arrNextLevelNow = $this->getInfoInscriptionStudent($arrLastInscription['nivelId'].$arrLastInscription['cicloId'].$arrLastInscription['gradoId'],$arrLastInscription['estadoMatriculaId']);
                    $arrNextLevel['nivelId'] = $arrNextLevelNow['nivelId'];
                    $arrNextLevel['cicloId'] = $arrNextLevelNow['cicloId'];
                    $arrNextLevel['gradoId'] = $arrNextLevelNow['gradoId'];

                    //if( $arrCurrenteInscription['nivelId']==$arrNextLevel['nivelId'] && $arrCurrenteInscription['cicloId']==$arrNextLevel['cicloId'] && $arrCurrenteInscription['gradoId']==$arrNextLevel['gradoId']){
                    if(false){
                        $swObservation = false;
                        $messageObservaation = 'No presenta Observación';
                        
                    }else{
                        $swObservation = true;
                        $swUpdateLevelGradoIniPri = true; 
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

                        $arrNextLevel['nivel']=$em->getRepository('SieAppWebBundle:NivelTipo')->find($arrNextLevel['nivelId'])->getNivel() ;
                        $arrNextLevel['grado']=$em->getRepository('SieAppWebBundle:GradoTipo')->find($arrNextLevel['gradoId'])->getGrado() ;

                        //the new add by krlos
                        $levelAllow = array(11,12,13);
                        $levelAndGrado = $arrCurrenteInscription['nivelId'];
                        if( in_array($levelAndGrado, $levelAllow) ){
                            // dump($arrNextLevel);die;
                            $swUpdateLevelGradoIniPri = true;  
                            $arrLevel = $this->getInfoUe($arrCurrenteInscription['sie']);
                            // $arrNextLevel['nivel']=$em->getRepository('SieAppWebBundle:NivelTipo')->find(1)->getNivel() ;
                            // $arrNextLevel['grado']=$em->getRepository('SieAppWebBundle:GradoTipo')->find(2)->getGrado() ;  
                        }
                        //dump($arrLevel);die;
                        $swObservation = false;
                        $messageObservaation = 'No presenta Observación';
                        $existStudentData = true;                        

                        
                    }

                    $existStudentData = true;                                    

                }else{

                    $swObservation = false;
                    $messageObservaation = 'No presenta Observación';
                    $existStudentData = true; 

                }

            }else{
                $levelAllow = array(11,12,13);
                $levelAndGrado = $arrCurrenteInscription['nivelId'];
                if( in_array($levelAndGrado, $levelAllow) ){                
                /*$levelAllow = array(111,112,121);
                $levelAndGrado = $arrCurrenteInscription['nivelId'].$arrCurrenteInscription['gradoId'];
                if( in_array($levelAndGrado, $levelAllow) ){*/
                    // dump($arrNextLevel);die;
                    $swUpdateLevelGradoIniPri = true;  
                    $arrLevel = $this->getInfoUe($arrCurrenteInscription['sie']);
                    // $arrNextLevel['nivel']=$em->getRepository('SieAppWebBundle:NivelTipo')->find(1)->getNivel() ;
                    // $arrNextLevel['grado']=$em->getRepository('SieAppWebBundle:GradoTipo')->find(2)->getGrado() ;  
                }
                $swObservation = false;
                $messageObservaation = 'No presenta Observación';
                $existStudentData = true; 

            }
                
        }else{
            // the studnet no exist
            $code = 200;
            $message = $mensaje;
            $status = "";
            $existStudentData = false;
            $swObservation = false;
        }

     $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'arrNextLevel' => $arrNextLevel,
        'arrParalelos' => $arrParalelos,
        'swObservation' => $swObservation,
        'dataInscriptionR' => $inscriptions,    
        'arrStudentExist' => $arrStudentExist,
        'existStudentData' => $existStudentData,        
        'messageObservaation' => $messageObservaation,
        'arrCurrenteInscription' => $arrCurrenteInscription,
        'swUpdateLevelGradoIniPri' => $swUpdateLevelGradoIniPri,        
        'arrLevel' => $arrLevel,        
      );
      //dump($arrResponse);die;
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }

    private function getCurrentInscritpion($data){

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
        $gestion = $this->currentyear;
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
        
        $arrData = json_decode($request->get('datos'),true);

        // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();    
        // get the send values
        $codigoRude = $arrData['codigoRude'];
        $nivelId = $arrData['nivelId'];
        // $cicloId = $arrData['cicloId'];
        $gradoId = $arrData['gradoId'];
        $paraleloId = $arrData['paraleloId'];
        $turnoId = $arrData['turnoId'];
        $sie = $arrData['sie'];
        $gestion = $arrData['gestion'];
        $studenInscriptionId = $arrData['studenInscriptionId'];
        $extranjero = $arrData['extranjero'];


        // condition to find the correct level to fix the observation
        $arrayCondition = array(
            'nivelTipo' => $nivelId,
            // 'cicloTipo' => $cicloId,
            'gradoTipo' => $gradoId,
            'paraleloTipo' => $paraleloId,
            'turnoTipo' => $turnoId,
            'institucioneducativa' => $sie,
            'gestionTipo' => $gestion,
        );

        try {
        
            $objStudentInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studenInscriptionId);
            $objStudentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy($arrayCondition));
            if($extranjero == 1){
                $objStudentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(19));    
            }
            $em->persist($objStudentInscription);
            
            $objValidacionProceso = $em->createQueryBuilder()
                                ->select('v')
                                ->from('SieAppWebBundle:ValidacionProceso','v')
                                ->where('v.esActivo = :active')
                                ->andWhere('v.validacionReglaTipo in (12, 15) ')
                                ->andwhere('v.llave = :codigoRude')
                                ->setParameter('codigoRude', $codigoRude)
                                ->setParameter('active', false)
                                ->getQuery()
                                ->getResult();
            
            if(sizeof($objValidacionProceso)>0){
                foreach ($objValidacionProceso as $value) {
                    $objValidacionProcesoUpdate = $em->getRepository('SieAppWebBundle:ValidacionProceso')->find($value->getId());
                    $objValidacionProcesoUpdate->setEsActivo('t');
                    $em->persist($objValidacionProcesoUpdate);
                }              
            }
            
            $em->flush();

         // save the file in case if exists
            // check if the file exists
            if(isset($_FILES['informe'])){
                $file = $_FILES['informe'];
                $type = $file['type'];
                $size = $file['size'];
                $tmp_name = $file['tmp_name'];
                $name = $file['name'];
                $extension = explode('.', $name);
                $extension = $extension[count($extension)-1];
                $new_name = $studenInscriptionId.'_'.date('YmdHis').'.'.$extension;
                // GUARDAMOS EL ARCHIVO
                $directorio = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/insExtranjeros/' .date('Y');

                if (!file_exists($directorio)) {
                    mkdir($directorio, 0775, true);
                }
                $directoriomove = $this->get('kernel')->getRootDir() . '/../web/uploads/archivos/insExtranjeros/' .date('Y').'/'.$studenInscriptionId;
                if (!file_exists($directoriomove)) {
                    mkdir($directoriomove, 0775, true);
                }

                $archivador = $directoriomove.'/'.$new_name;
                //unlink($archivador);
                if(!move_uploaded_file($tmp_name, $archivador)){
                    $em->getConnection()->rollback();
                    echo 'Excepción capturada: ', $ex->getMessage(), "\n";
                }

                  //move the file emp to the directory temp
                  // $file = $oFile->move($dirtmp, $originalName);
                  // $file = $oFile->move($dirtmp, $studentInscription->getId().'_'.$form['gestion']);
                  //save info extranjero inscription
                  $objEstudianteInscripcionExtranjero = new EstudianteInscripcionExtranjero();
                  $objEstudianteInscripcionExtranjero->setInstitucioneducativaOrigen('MODIFICACION NIVEL Y GRADO');
                  $objEstudianteInscripcionExtranjero->setCursoVencido('MODIFICACION NIVEL Y GRADO');
                  $objEstudianteInscripcionExtranjero->setRutaImagen($new_name);
                  $objEstudianteInscripcionExtranjero->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studenInscriptionId));
                  $objEstudianteInscripcionExtranjero->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find(0));
                  $em->persist($objEstudianteInscripcionExtranjero);
                  $em->flush();
            
            }            

            $arrNewDataInscription = array('istudenInscriptionId' => $objStudentInscription->getId(), 'institucioneducativaCursoId' => $objStudentInscription->getInstitucioneducativaCurso()->getId());

            $this->get('funciones')->setLogTransaccion(
                        $studenInscriptionId,
                        'estudiante_inscripcion',
                        'U',
                        '',
                        $arrNewDataInscription,
                        $arrData['oldDataInscription'],
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

    /**
     * get the levels by sie and year
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
    private function getInfoUe($id) {
        
        $em = $this->getDoctrine()->getManager();

        // $id = $request->get('sie');
        //get the tuicion
        //select * from get_ue_tuicion(137746,82480002)
        /*
          $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT)');
          $query->bindValue(':user_id', $this->session->get('userId'));
          $query->bindValue(':sie', $id);
          $query->execute();
          $aTuicion = $query->fetchAll();
     */
    
            //get the tuicion
            $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
            $query->bindValue(':user_id', $this->session->get('userId'));
            $query->bindValue(':sie', $id);
            $query->bindValue(':roluser', $this->session->get('roluser'));
            $query->execute();
            $aTuicion = $query->fetchAll();

        $aniveles = array();
         if ($aTuicion[0]['get_ue_tuicion']) {
        //get the IE
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        if($institucion){
            
            $em = $this->getDoctrine()->getManager();
            //get the Niveles

            $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
            $query = $entity->createQueryBuilder('iec')
                    ->select('(iec.nivelTipo)')
                    //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                    ->where('iec.institucioneducativa = :sie')
                    ->andwhere('iec.gestionTipo = :gestion')
                    //->andwhere('iec.nivelTipo != :nivel')
                    ->setParameter('sie', $id)
                    ->setParameter('gestion', $this->session->get('currentyear') )
                    //->setParameter('nivel', '13')
                    ->orderBy('iec.nivelTipo', 'ASC')
                    ->distinct()
                    ->getQuery();
            $aNiveles = $query->getResult();
            if($aNiveles){
                $aniveles = array();
                foreach ($aNiveles as $nivel) {
                    $aniveles[] = array('id'=> $nivel[1], 'level'=>$em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel());
                }
                $status = 'success';
                $code = 200;
                $message = "Datos encontrados";
                $swprocess = true; 
                $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "";
            }else{
                $status = 'error';
                $code = 400;
                $message = "Información no consolidada - no tiene level";
                $swprocess = false; 
                $nombreIE = false; 
            }
            

        }else{

            $status = 'error';
            $code = 400;
            $message = "Unidad Educativa no existe";
            $swprocess = false; 
            $nombreIE = false; 

        }
        
        
      } else {
          $message = 'Usuario No tiene Tuición';
           $status = 'error';
               $code = 400;
               //$message = "Unidad Educativa no existe";
               $swprocess = false;
               $nombreIE = false;

      } 

      return $aniveles;

      // $arrResponse = array(
      //   'status'          => $status,
      //   'code'            => $code,
      //   'message'         => $message,
      //   'swprocessue' => $swprocess,    
      //   'nombreue' => $nombreIE, 
      //   'aniveles' => $aniveles
      //   );
      
      // $response->setStatusCode(200);
      // $response->setData($arrResponse);

      // return $response;

        
      //   $response->setStatusCode(200);
      //   return $response->setData(array());
    }  
    
    /**
     * get grado
     * @param type $idnivel
     * @param type $sie
     * return list of grados
     */
    public function setGradoAction(Request  $request) {
        // ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        
        // get the send values
        $sie = $request->get('sie');
        $idnivel = $request->get('nivelId');
        $gestionselected = $request->get('gestion');;

        //get grado
        $agrados = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.gradoTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :idnivel')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('idnivel', $idnivel)
                ->setParameter('gestion', $gestionselected)
                ->distinct()
                ->orderBy('iec.gradoTipo', 'ASC')
                ->getQuery();
        $aGrados = $query->getResult();

        $agrados = array();
        
        foreach ($aGrados as $grado) {
            /*
            if($idnivel == 12 && $grado[1]==1){
                $agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );
            }
            if($idnivel == 11){
                $agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );
            }   
            */
            $agrados[$grado[1]] = array('id'=>$grado[1], 'grado'=>$em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado() );         
        }
      $arrResponse = array(
        'arrGrado' => $agrados
      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
        
    }

    /**
     * get the paralelos
     * @param type $idnivel
     * @param type $sie
     * @return type
     */
    public function setParaleloAction(Request $request) {
        // ini vars
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        // get the send values
        $sie = $request->get('sie');
        $nivel = $request->get('nivelId');
        $grado = $request->get('gradoId');
        $gestion = $request->get('gestion');;
        //get paralelo
        $aparalelos = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.paraleloTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('gestion', $gestion)
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
        $aParalelos = $query->getResult();
        $aparalelos = array();
        foreach ($aParalelos as $paralelo) {
            $aparalelos[$paralelo[1]] = array('id'=>$paralelo[1], 'paralelo'=>$em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo[1])->getParalelo() );    
        }

      $arrResponse = array(
        'arrParalelo' => $aparalelos
      );
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }              

}
