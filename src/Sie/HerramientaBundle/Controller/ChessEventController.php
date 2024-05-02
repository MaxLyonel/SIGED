<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\EstudiantePersonaDiplomatico;
use Sie\AppWebBundle\Entity\Estudiante; 
use Sie\AppWebBundle\Entity\Institucioneducativa; 
use Sie\AppWebBundle\Entity\EveEstudianteInscripcionEvento; 
use Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLog; 

class ChessEventController extends Controller{
    public $session;
    public $limitDay;
    public function __construct() {
        $this->session = new Session();
        $this->limitDay = '07-05-2024';
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
            $disableElement=0;
            if(in_array($this->session->get('roluser'),array(9))){
                $sie=$ie_id=$this->session->get('ie_id');
                $disableElement=1;
            }else{
                if(in_array($this->session->get('roluser'),array(7,8,10))){
                    $sie='';
                }
            }


            return $this->render('SieHerramientaBundle:ChessEvent:index.html.twig',array(
                'codsie'=>$sie,
                'disableElement'=>$disableElement,
                
            ));        
    }

    public function findUEDataAction(Request $request){
        // get the vars send        
        $sie = $request->get('sie');
        // create db conexion
        $em = $this->getDoctrine()->getManager();

        $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :roluser::INT)');
        $query->bindValue(':user_id', $this->session->get('userId'));
        $query->bindValue(':sie', $sie);
        $query->bindValue(':roluser', $this->session->get('roluser'));
        $query->execute();
        $aTuicion = $query->fetchAll();
        
        $existUE = 0;
        $arrModalidades = array();        
        $arrLevel = array(
            array('id'=>12,'level'=>'Educación Primaria Comunitaria Vocacional'),
            array('id'=>13,'level'=>'Educación Secundaria Comunitaria Productiva'),
        ); 

        $swcloseevent =  (is_object($this->checkOperativeChees($sie)))?1:0;   
        // start this secction validate the last day to report the inscription INFO
        $swcloseevent = ($swcloseevent)?1:$this->getLastDayRegistryOpeCheesEventStatus($this->limitDay);          
        // end this secction validate the last day to report the inscription INFO                     

        if ($aTuicion[0]['get_ue_tuicion'] == true){
        //if (1){
            $objUE = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie);
            
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
                    'swcloseevent'         => $swcloseevent,
                    'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
                );               
            }else{
                $arrResponse = array(
                    'sie'                 => '',
                    'institucioneducativa'=> 'No tiene tuición sobre la institución educatia o no existe codigo SIE ',
                    'existUE'             => $existUE,
                    'arrModalidades'      => $arrModalidades,
                    'arrLevel'            => $arrLevel,
                    'swcloseevent'            => $swcloseevent,
                    'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
                );   
            }            
        }else{
                $arrResponse = array(
                    'sie'                 => '',
                    'institucioneducativa'=> 'N',
                    'existUE'         => $existUE,
                    'arrModalidades'         => $arrModalidades,
                    'arrLevel'         => $arrLevel,
                    'swcloseevent'         => $swcloseevent,
                    'urlreporte'=> ($swcloseevent)?$this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie)):''
                ); 
        }

        
        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;        
    }

    private function getLastDayRegistryOpeCheesEventStatus($limitDay){
        $today = date('d-m-Y');
        $swcloseevent =  (strtotime($today) >= strtotime($limitDay))?1:0;  
        return $swcloseevent;
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

        $swcloseevent =  (is_object($this->checkOperativeChees($sie)))?1:0;            
        // start this secction validate the last day to report the inscription INFO
        $swcloseevent = ($swcloseevent)?1:$this->getLastDayRegistryOpeCheesEventStatus($this->limitDay);  
        // end this secction validate the last day to report the inscription INFO        
        // get students data
        $arrEveStudents = $this->getAllRegisteredInscription( $categorieId,$faseId,$modalidadId,$sie);
        // dump($arrEveStudents);die;
        $arrResponse = array(
            'modalidadId'     => $modalidadId,
            'faseId'          => $faseId,
            'categorieId'     => $categorieId,
            'modalidadLabel'  => $modalidadLabel->getDescripcion(),
            'faseLabel'       => $faseLabel->getDescripcion(),
            'categorieLabel'  => $categorieLabel->getCategoria(),
            'arrEveStudents'  => $arrEveStudents,
            'arrAllowGrade'   => $arrAllowGrade,
            'genderRequest'   => $genderRequest,
            'existSelectData' => 1,    
            'swcloseevent'    => $swcloseevent,    
        )
        ; 

        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }

    private function getAllRegisteredInscription($categorieId,$faseId,$modalidadId,$sie){
        $year = $this->session->get('currentyear');
        $em = $this->getDoctrine()->getManager();
        $query = "SELECT e.codigo_rude,e.paterno,e.materno,e.nombre,e.carnet_identidad,e.complemento,nt.nivel,gt.grado,pt.paralelo, eeie.id as eveinscriptionid 
                FROM eve_estudiante_inscripcion_evento eeie
                inner join estudiante_inscripcion ei on (eeie.estudiante_inscripcion_id = ei.id)
                inner join estudiante e on (ei.estudiante_id = e.id)
                inner join institucioneducativa_curso iec on (ei.institucioneducativa_curso_id = iec.id)
                inner join nivel_tipo nt on (iec.nivel_tipo_id=nt.id)
                inner join grado_tipo gt on (iec.grado_tipo_id=gt.id)
                inner join paralelo_tipo pt on (iec.paralelo_tipo_id=pt.id)
                where 
                iec.gestion_tipo_id = $year and  
                eeie.eve_categorias_tipo_id = $categorieId and  
                eeie.eve_fase_tipo_id = $faseId and  
                eeie.eve_modalidades_tipo_id = $modalidadId and  
                iec.institucioneducativa_id = " . $sie ;

        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrEveStudents = $statement->fetchAll();
        // dump($arrEveStudents);
        // die;
        return $arrEveStudents;        

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
        $year = $this->session->get('currentyear');
        //$year = 2023;

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
        $year = $this->session->get('currentyear');
        //$year = 2023;

        // create db conexion
        $em=$this->getDoctrine()->getManager();
        $query="
                select e.carnet_identidad, e.complemento ,e.codigo_rude ,e.paterno ,e.materno ,e.nombre ,e.genero_tipo_id , ei.id as inscriptionId, gt.genero
                from estudiante e 
                inner join estudiante_inscripcion ei on (e.id = ei.estudiante_id)
                inner join institucioneducativa_curso ic on (ei.institucioneducativa_curso_id=ic.id)
                inner join genero_tipo gt on (e.genero_tipo_id=gt.id)
                where ic.nivel_tipo_id =$levelId and ic.grado_tipo_id =$gradeId and ic.gestion_tipo_id =$year and e.genero_tipo_id = $genderRequest and ic.paralelo_tipo_id = '".$parallelId."' and ic.institucioneducativa_id =$sie and  ei.estadomatricula_tipo_id = 4
        ";


        $statement = $em->getConnection()->prepare($query);
        $statement->execute();
        $arrStudents = $statement->fetchAll();
        $arrStudents2 = array();
        foreach ($arrStudents  as $value) {
            $query = "SELECT * 
                    FROM eve_estudiante_inscripcion_evento eeie
                    where 
                    eeie.eve_categorias_tipo_id = $categorieId and  
                    eeie.eve_fase_tipo_id = $faseId and  
                    eeie.eve_modalidades_tipo_id = $modalidadId and  
                    eeie.estudiante_inscripcion_id = " . $value['inscriptionid'] ;


                $statement = $em->getConnection()->prepare($query);
                $statement->execute();
                $arrRegStudent = $statement->fetchAll();  
                if(sizeof(($arrRegStudent))>0){
                }else{
                    $arrStudents2[]=$value;
                }
             }     

        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrStudents' => $arrStudents2,
            'existStudent'         => 1,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;          
    }

    public function doInscriptionAction(Request $request){
        // get the send values
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        $levelId = $request->get('levelId');
        $gradeId = $request->get('gradeId');
        $parallelId = $request->get('parallelId');
        $inscriptionid = $request->get('inscriptionid');
        $genderRequest = $request->get('genderRequest');
        $year = $this->session->get('currentyear');

        // create db conexion
        $em=$this->getDoctrine()->getManager();
        
        $objEveStudentInscription = new EveEstudianteInscripcionEvento();
        $objEveStudentInscription->setFechaRegistro(new \DateTime('now'));
        $objEveStudentInscription->setEsVigente(1);
        $objEveStudentInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscriptionid));
        $objEveStudentInscription->setEveCategoriasTipo($em->getRepository('SieAppWebBundle:EveCategoriasTipo')->find($categorieId));
        $objEveStudentInscription->setEveFaseTipo($em->getRepository('SieAppWebBundle:EveFaseTipo')->find($faseId));
        $objEveStudentInscription->setEveModalidadesTipo($em->getRepository('SieAppWebBundle:EveModalidadesTipo')->find($modalidadId));

        $em->persist($objEveStudentInscription);
        $em->flush();

        $arrEveStudents = $this->getAllRegisteredInscription( $categorieId,$faseId,$modalidadId,$sie);

        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrEveStudents' => $arrEveStudents,
            'existStudent'         => 1,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }


    public function removeInscriptionCheesNextLevelAction(Request $request){

        // get the send values
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        $levelId = $request->get('levelId');
        $gradeId = $request->get('gradeId');
        $parallelId = $request->get('parallelId');
        // $inscriptionid = $request->get('inscriptionid');
        $remoinscriptionid = $request->get('remoinscriptionid');
        $genderRequest = $request->get('genderRequest');
        $year = $this->session->get('currentyear');

        // create db conexion
        $em=$this->getDoctrine()->getManager();
        
        $existRemoveStudent=0;
        $objEveStudentInscription = $em->getRepository('SieAppWebBundle:EveEstudianteInscripcionEvento')->find($remoinscriptionid);
        
        if(is_object($objEveStudentInscription) ){
            $em->remove($objEveStudentInscription);
            // $em->persist($objEveStudentInscription);
            $em->flush();
            $existRemoveStudent=1;
        }

            $query = "
                    select * 
                    from eve_fase_tipo  
                    where eve_modalidades_tipo_id = $modalidadId and descripcion = 'Fase II. Distrital'
            ";
            $statement = $em->getConnection()->prepare($query);
            $statement->execute();
            $objFase = $statement->fetchAll();   

        $faseIdddis = (sizeof($objFase)>0)?$objFase[0]['id']:2;

        $arrEveStudentsClassified = $this->getAllRegisteredInscription( $categorieId,$faseIdddis,$modalidadId,$sie);
        
        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrEveStudentsClassified' => $arrEveStudentsClassified,
            'existRemoveStudent'       => $existRemoveStudent,    
            'swRegistryClassi'         => (sizeof($arrEveStudentsClassified)==2)?0:1            

        ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }

    public function removeInscriptionCheesAction(Request $request){


        // get the send values
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        $levelId = $request->get('levelId');
        $gradeId = $request->get('gradeId');
        $parallelId = $request->get('parallelId');
        // $inscriptionid = $request->get('inscriptionid');
        $remoinscriptionid = $request->get('remoinscriptionid');
        $genderRequest = $request->get('genderRequest');
        $year = $this->session->get('currentyear');

        // create db conexion
        $em=$this->getDoctrine()->getManager();
        
        $existRemoveStudent=0;
        $objEveStudentInscription = $em->getRepository('SieAppWebBundle:EveEstudianteInscripcionEvento')->find($remoinscriptionid);
        // dump($remoinscriptionid);
        // dump($objEveStudentInscription);
        // die;
        if(is_object($objEveStudentInscription) ){
            $em->remove($objEveStudentInscription);
            // $em->persist($objEveStudentInscription);
            $em->flush();
            $existRemoveStudent=1;
        }
        
        $arrEveStudents = $this->getAllRegisteredInscription( $categorieId,$faseId,$modalidadId,$sie);

        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrEveStudents' => $arrEveStudents,
            'existRemoveStudent'         => $existRemoveStudent,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }

    public function closeEventCheesAction(Request $request) {
        // DUMP($request);die;
        // get the send values
        $sie = $request->get('sie');        
        //conexion to DB
        $em = $this->getDoctrine()->getManager();
        // $em->getConnection()->beginTransaction();
        try {
          //save the log data
          
          // dump(is_object($objDownloadFilenewOpe));die;
          $objDownloadFilenewOpe = $this->checkOperativeChees($sie);
          if(!is_object($objDownloadFilenewOpe)){
            $objDownloadFilenewOpe = new InstitucioneducativaOperativoLog();
          }
          
          $objDownloadFilenewOpe->setInstitucioneducativaOperativoLogTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLogTipo')->find(11));
          $objDownloadFilenewOpe->setGestionTipoId($this->session->get('currentyear'));
          $objDownloadFilenewOpe->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->find(1));
          $objDownloadFilenewOpe->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));
          $objDownloadFilenewOpe->setInstitucioneducativaSucursal(0);
          $objDownloadFilenewOpe->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(0));
          $objDownloadFilenewOpe->setDescripcion('AJEDREZ');
          $objDownloadFilenewOpe->setEsexitoso('t');
          $objDownloadFilenewOpe->setEsonline('t');
          $objDownloadFilenewOpe->setUsuario($this->session->get('userId'));
          $objDownloadFilenewOpe->setFechaRegistro(new \DateTime('now'));
          $objDownloadFilenewOpe->setClienteDescripcion($_SERVER['HTTP_USER_AGENT']);
          $em->persist($objDownloadFilenewOpe);
          $em->flush();
           // $em->getConnection()->commit();

          $swcloseevent = 1;

        } catch (Exception $e) {
            $swcloseevent = 0;
          $em->getConnection()->rollback();
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }   

        $arrResponse = array(
            'sie'          => $sie,
            'swcloseevent' => $swcloseevent,
            'swcloseevent' => $swcloseevent,
            'urlreporte'=> $this->generateUrl('cheesevent_reportChessInscription', array('sie'=>$sie))
        ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;                
    }


    private function checkOperativeChees($sie){
        // create db conexion
        $em=$this->getDoctrine()->getManager();

          $objDownloadFilenewOpe = $em->getRepository('SieAppWebBundle:InstitucioneducativaOperativoLog')->findOneBy(array(
            'institucioneducativa'=>$sie,
            'institucioneducativaOperativoLogTipo'=>11,
            'gestionTipoId'=>$this->session->get('currentyear')
          ));

        return $objDownloadFilenewOpe;        
    }

    public function reportChessInscriptionAction(Request $request, $sie){

        $response = new Response();
        $gestion = $this->session->get('currentyear');
        $codigoQR = 'EVEAJE'.$sie.'|'.$gestion;

        $data = $this->session->get('userId').'|'.$gestion.'|'.$sie;
        //$link = 'http://'.$_SERVER['SERVER_NAME'].'/sie/'.$this->getLinkEncript($codigoQR);
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'eventChees'.$sie.'_'.$this->session->get('currentyear'). '.pdf'));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') .'reg_lst_registro_ajedrez_v1_EEA_ue.rptdesign&institucioneducativa_id='.$sie.'&&__format=pdf&'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
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
        $yearIns = $this->session->get('currentyear');
        $query = "
                select
                e.codigo_rude, e.carnet_identidad ,
                e.nombre, e.paterno, e.materno, ei.id as studentId, nt.nivel, gt.grado, pt.paralelo, ei.id as estInscId
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

        $existStudent=0;
        if(sizeof($arrStudents)>0 ){
            $arrStudents=$arrStudents[0];

            /*$query = "
                    select * 
                    from eve_estudiante_inscripcion_evento eeie 
                    where eeie.estudiante_inscripcion_id = '".$arrStudents['estinscid']."' and eeie.eve_categorias_tipo_id = '".$request->get('categorieId')."' and eeie.eve_fase_tipo_id = '".$request->get('faseId') ."' and eeie.eve_modalidades_tipo_id = '".$request->get('modalidadId') ."'        
            ";*/
            $faseanterior = $request->get('faseId') -1;

            $query = "
                    select * 
                    from eve_estudiante_inscripcion_evento eeie 
                    where eeie.estudiante_inscripcion_id = '" . $arrStudents['estinscid'] . "' and eeie.eve_categorias_tipo_id = '" . $request->get('categorieId') . "' and eeie.eve_fase_tipo_id = '" . $faseanterior . "' and eeie.eve_modalidades_tipo_id = '" . $request->get('modalidadId') . "'        
            ";

            //dump($query); die; 

            
            $statement = $em->getConnection()->prepare($query);
            $statement->execute();
            $arrInscription = $statement->fetchAll();

            if(sizeof($arrInscription)>0){
                $existStudent=1;
            }else{
                $existStudent=0;
                $arrStudents=array();                
            }
          
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
            'existStudentpre'         => $existStudent,    ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;          
    }


    public function registerClassifiedAction(Request $request){

        // get the send values
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        $levelId = $request->get('levelId');
        $gradeId = $request->get('gradeId');
        $parallelId = $request->get('parallelId');
        $inscriptionid = $request->get('inscriptionidCla');
        $genderRequest = $request->get('genderRequest');
        $year = $this->session->get('currentyear');

        // create db conexion
        $em=$this->getDoctrine()->getManager();

            $query = "
                    select * 
                    from eve_fase_tipo  
                    where eve_modalidades_tipo_id = $modalidadId and descripcion = 'Fase IV. Nacional'
            ";
            $statement = $em->getConnection()->prepare($query);
            $statement->execute();
            $objFase = $statement->fetchAll();   

        //$faseIdddis = (sizeof($objFase)>0)?$objFase[0]['id']:2;
        $faseIdddis = $objFase[0]['id'];


        $objexistInscription = $em->getRepository('SieAppWebBundle:EveEstudianteInscripcionEvento')->findOneBy(array(
            'estudianteInscripcion' =>$inscriptionid,
            'eveCategoriasTipo' =>$categorieId,
            'eveFaseTipo' => $faseIdddis,
            'eveModalidadesTipo' =>$modalidadId,

        ));

        $existStudentpre = 1;
        if(sizeof($objexistInscription)>0){
            $existStudentpre = 0;
        }else{

            $objEveStudentInscription = new EveEstudianteInscripcionEvento();
            $objEveStudentInscription->setFechaRegistro(new \DateTime('now'));
            $objEveStudentInscription->setEsVigente(1);
            $objEveStudentInscription->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscriptionid));
            $objEveStudentInscription->setEveCategoriasTipo($em->getRepository('SieAppWebBundle:EveCategoriasTipo')->find($categorieId));
            $objEveStudentInscription->setEveFaseTipo($em->getRepository('SieAppWebBundle:EveFaseTipo')->find($faseIdddis));
            $objEveStudentInscription->setEveModalidadesTipo($em->getRepository('SieAppWebBundle:EveModalidadesTipo')->find($modalidadId));

            $em->persist($objEveStudentInscription);
            $em->flush();
        }


        $arrEveStudentsClassified = $this->getAllRegisteredInscription( $categorieId,$faseIdddis,$modalidadId,$sie);
        
        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrEveStudentsClassified' => $arrEveStudentsClassified,
            'existStudentpre'         => $existStudentpre,    
            'swRegistryClassi'         => (sizeof($arrEveStudentsClassified)==2)?0:1
        );


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }


    public function startInscriptionAction(Request $request){

        // get the send values
        $sie = $request->get('sie');
        $institucioneducativa = $request->get('institucioneducativa');
        $modalidadId = $request->get('modalidadId');
        $faseId = $request->get('faseId');
        $categorieId = $request->get('categorieId');
        $levelId = $request->get('levelId');
        $gradeId = $request->get('gradeId');
        $parallelId = $request->get('parallelId');
        $inscriptionid = $request->get('inscriptionidCla');
        $genderRequest = $request->get('genderRequest');
        $year = $this->session->get('currentyear');

        // create db conexion
        $em=$this->getDoctrine()->getManager();

            $query = "
                    select * 
                    from eve_fase_tipo  
                    where eve_modalidades_tipo_id = $modalidadId and descripcion = 'Fase IV. Nacional'
            ";
            $statement = $em->getConnection()->prepare($query);
            $statement->execute();
            $objFase = $statement->fetchAll();

        //$faseIdddis = (sizeof($objFase)>0)?$objFase[0]['id']:2;

        $faseIdddis = $objFase[0]['id'] ;

        $arrEveStudentsClassified = $this->getAllRegisteredInscription( $categorieId,$faseIdddis,$modalidadId,$sie);
// dump( sizeof($arrEveStudentsClassified) );die;
        $arrResponse = array(
            'sie'            => $sie,
            'modalidadId'    => $modalidadId,
            'faseId'         => $faseId,
            'categorieId'    => $categorieId,
            'arrEveStudentsClassified' => $arrEveStudentsClassified,
            'existStudentClassi'         => 1,    
            'swRegistryClassi'         => (sizeof($arrEveStudentsClassified)==2)?0:1

        ); 


        $response = new JsonResponse();
        $response->setStatusCode(200);
        $response->setData($arrResponse);
        return $response;  
    }






}
 