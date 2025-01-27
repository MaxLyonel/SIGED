<?php

namespace Sie\HerramientaAlternativaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Sie\AppWebBundle\Entity\EstudianteInscripcionAlternativaExcepcional;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * StudentsInscriptions controller.
 *
 */
class StudentsInscriptionsController extends Controller {

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

      $gestion = $this->session->get('ie_gestion');
      // dump($this->session->All());die;
      //if ($this->session->get('userId') != 94161725)   return $this->redirect($this->generateUrl('principal_web'));
      // if ($this->session->get('userId') != 94161725){
        
      //   // if ($this->session->get('ie_id') != 82230127){
      //     return $this->redirect($this->generateUrl('principal_web'));
      //   // }
      // }    
        

        //get the send values
        $infoUe = $request->get('infoUe');
        $etapaespecialidad = $request->get('etapaespecialidad');
        $paralelo = $request->get('paralelo');

        return $this->render('SieHerramientaAlternativaBundle:StudentsInscriptions:newlookforstudent.html.twig', array(
          // 'form'=>$this->findStudentForm($infoUe)->createView()
          'iecId' => $request->get('iecId'),
          'infoUe'=> $infoUe,
          'etapaespecialidad'=> $etapaespecialidad,
          'paralelo'=> $paralelo,
        ));
    }
    /**
    * create the form to find the student by rude
    **/
    private function findStudentForm($data){
      $form = $this->createFormBuilder()
              ->add('rudeal','text', array('label'=>'Rudeal', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Rudeal', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('data', 'hidden', array('data'=> $data))
              ->add('find', 'button', array('label'=> 'Buscar', 'attr'=>array('class'=>'btn btn-success', 'onclick'=>'findStudent()')))
              ->getForm();
      return $form;
    }
    /**
    *find student method
    **/
    public function findStudentAction(Request $request){
      //crete the connexion into the DB
      //get the info send
      $em = $this->getDoctrine()->getManager();
      $form =  $request->get('form');
      $dataUe = unserialize($form['data']);

      //get the student info by rudeal
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['rudeal']));
      //check if the student exist
      if($objStudent){
        //check if the student has an inscription on this year sesion->get('ie_gestion');
        $selectedInscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionStudentByYear($objStudent->getId(), $this->session->get('ie_gestion'),$dataUe['ueducativaInfoId']['iecId']);
        if(!$selectedInscriptionStudent){
          //check if the level and grado is correct to the student//the next step is do it
            $objStudentInscriptions = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionAlternativaStudent($objStudent->getId());

            // added validation to show the inscription menor option
            $showCaseSpecialOption = false;
            if($dataUe['ueducativaInfoId']['nivelId'] ==15 && $dataUe['ueducativaInfoId']['cicloId']==2){
              $showCaseSpecialOption = true;
            }

            return $this->render($this->session->get('pathSystem').':StudentsInscriptions:inscriptions.html.twig', array(
              'objStudent'=>$objStudent,
              'objStudentInscriptions'=>$objStudentInscriptions,
              'form'=>$this->doInscriptionForm($form['data'], $objStudent->getId())->createView(),
              'exist'=>true,
              'showCaseSpecialOption'=>$showCaseSpecialOption,

            ));

        }else{
            //the student has an inscription on the same level
            $this->session->getFlashBag()->add('noinscription', 'El estudiante ya cuenta con inscripción en el mismo nivel');
            return $this->render($this->session->get('pathSystem').':StudentsInscriptions:inscriptions.html.twig', array(
              'exist'=>false
            ));
        }
      }else{
        //the student doesn't exist
        $this->session->getFlashBag()->add('noinscription', 'Estudiante no registrado');
        return $this->render($this->session->get('pathSystem').':StudentsInscriptions:inscriptions.html.twig', array(
          'exist'=>false
        ));
      }

    }//end function
    /**
    * form todo the inscription
    **/
    private function doInscriptionForm($data, $studentId){
      $form = $this->createFormBuilder()
              ->add('caseespecial', 'checkbox', array('label'=>'Inscripción Menores:', 'attr'=>array('class'=>'form-control', 'checked'=>false, 'onclick'=>'showHideReq(this)' ) ))
              ->add('data', 'hidden', array('data'=> $data))
              ->add('studentId', 'hidden', array('data'=> $studentId))
              ->add('inscriptionExcepacional', 'entity', array('label'=>'Motivo:','class' => 'SieAppWebBundle:EstudianteInscripcionAlternativaExcepcionalTipo', 'property' => 'descripcion'))
              ->add('documento', 'text', array('label'=>'Info Complementaria:', 'attr'=>array('class'=>'form-control' ) ))
              ->add('inscription', 'button', array('label'=> 'Inscribir', 'attr'=>array('class'=>'btn btn-success btn-stroke','ata-placement'=>'top', 'onclick'=>'doInscription()')))
              ->getForm();
     return $form;
    }
    /**
    * methdo to save the new inscription
    **/
    public function saveInscriptionAction(Request $request){

      //create the conexion DB
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the send values
      $form= $request->get('form');

      $aInfoUeducativa = unserialize($form['data']);
      
    //set the validate year
    $validateYear = false;
    //if not checked, so validate the studens olds year
    if(!isset($form['caseespecial'])){
      //get the curso info
      $objInstitucioneducativaCursoStudent=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']);

      //get the students year old

      // $yearStudent = (date('Y') - date('Y',strtotime($form['dateStudent'])));
      // $arrYearStudent =$this->get('funciones')->tiempo_transcurrido($form['dateStudent'], '30-6-'.date('Y'));
      // $yearStudent = $arrYearStudent[0];
      $yearStudent =$this->get('funciones')->getTheCurrentYear($form['dateStudent'], '30-8-'.date('Y'));
      // dump($objInstitucioneducativaCursoStudent);
      // dump($yearStudent);
      // die;
      //validate the humanisticos
      // if($objInstitucioneducativaCursoStudent->getNivelTipo()->getId()==15){
      if($aInfoUeducativa['ueducativaInfoId']['nivelId']==15){
        //validate to nivel=15 - ciclo=1 - grado=1
        // if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==1 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==1){
        //   if(!($yearStudent>=15)){
        //     $validateYear=true;
        //   }
        // }
        // elementales
        if($aInfoUeducativa['ueducativaInfoId']['cicloId']==1 and $aInfoUeducativa['ueducativaInfoId']['gradoId']==1){
          if(!($yearStudent>=15)){
            $validateYear=true;
          }
        }
        // avanzados
        //validate to nivel=15 - ciclo=1 - grado=2
        if($aInfoUeducativa['ueducativaInfoId']['cicloId']==1 and $aInfoUeducativa['ueducativaInfoId']['gradoId']==2){
          if(!($yearStudent>=16)){
            $validateYear=true;
          }
        }
        // aplicados
        //validate to nivel=15 - ciclo=2 - grado=1
        if($aInfoUeducativa['ueducativaInfoId']['cicloId']==2 and $aInfoUeducativa['ueducativaInfoId']['gradoId']==1){
          if(!($yearStudent>=17)){
            $validateYear=true;
          }
        }
        // complementarios
        //validate to nivel=15 - ciclo=2 - grado=2
        if($aInfoUeducativa['ueducativaInfoId']['cicloId']==2 and $aInfoUeducativa['ueducativaInfoId']['gradoId']==2){
          if(!($yearStudent>=18)){
            $validateYear=true;
          }
        }
        // avanzados
        //validate to nivel=15 - ciclo=2 - grado=3
        if($aInfoUeducativa['ueducativaInfoId']['cicloId']==2 and $aInfoUeducativa['ueducativaInfoId']['gradoId']==3){
          if(!($yearStudent>=18)){
            $validateYear=true;
          }
        }

      } else{
          if(!($yearStudent>=16)){
            $validateYear=true;
          }
      }//end first if - validate the humanisticos

    }
    //if the year is wrong go to show the alert
    if($validateYear){
      //reload the students list
      $exist = true;
      $objStudents = array();
      $this->session->getFlashBag()->add('noinscription', 'Estudiante no inscrito, pues no cumple la edad.');
      $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($aInfoUeducativa['ueducativaInfoId']['iecId']);
      $dataUe=(unserialize($form['data']));

      return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:seeStudents.html.twig', array(
                  'objStudents' => $objStudents,
                  'form' => $this->createFormStudentInscription($form['data'])->createView(),
                  'exist' => $exist,
                  'infoUe' => $form['data'],
                  'etapaespecialidad' => '',
                  'dataUe'=> $dataUe['ueducativaInfo'],
                  'totalInscritos'=>count($objStudents),
                   'swSetNameModIntEmer' => 'false',
                  'primariaNuevo' => $this->get('funciones')->validatePrimariaCourse($dataUe['ueducativaInfoId']['iecId'])
      ));

    }

      try {
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
        $query->execute();
        $studentInscription = new EstudianteInscripcion();
        $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
        $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
        $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
        $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['studentId']));
        $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
        $studentInscription->setObservacion(1);
        $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
        $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
        $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']));
        //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
        $studentInscription->setCodUeProcedenciaId(0);
        $em->persist($studentInscription);
        $em->flush();

        //save the inscription data when the inscription is excepcional
        if(isset($form['caseespecial'])){
          $estudianteInscripcionAlternativaExcepcionalObjNew = new EstudianteInscripcionAlternativaExcepcional();
          $estudianteInscripcionAlternativaExcepcionalObjNew->setEstudianteInscripcionAlternativaExcepcionalTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionAlternativaExcepcionalTipo')->find($form['inscriptionExcepacional']));
          $estudianteInscripcionAlternativaExcepcionalObjNew->setFecha(new \DateTime('now'));
          $estudianteInscripcionAlternativaExcepcionalObjNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
          $estudianteInscripcionAlternativaExcepcionalObjNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
          $estudianteInscripcionAlternativaExcepcionalObjNew->setDocumento($form['documento']);
          $em->persist($estudianteInscripcionAlternativaExcepcionalObjNew);
          $em->flush();
        }
        // save log info
        $this->get('funciones')->setLogTransaccion(
             $studentInscription->getId(),
             'estudiante_inscripcion',
             'C',
             '',
             '',
             '',
             'ALTERNATIVA',
             json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
         );
        // check if the course is PRIMARIA
         //  if( $aInfoUeducativa['ueducativaInfoId']['sfatCodigo'] == 15 &&
         //    $aInfoUeducativa['ueducativaInfoId']['setId'] == 13 &&
         //    $aInfoUeducativa['ueducativaInfoId']['periodoId'] == 3
         //  ){
         //    //set the new curricula to the student
         //     $data = array('iecId'=>$aInfoUeducativa['ueducativaInfoId']['iecId'], 'eInsId'=>$studentInscription->getId());
         //    $objNewCurricula = $this->get('funciones')->setCurriculaStudent($data);
         // }
          $data = array('iecId'=>$aInfoUeducativa['ueducativaInfoId']['iecId'], 'eInsId'=>$studentInscription->getId(), 'gestion' => $this->session->get('ie_gestion'));

          $objNewCurricula = $this->get('funciones')->setCurriculaStudent($data);

        //to do the submit data into DB
        //do the commit in DB
        $em->getConnection()->commit();
        $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito correctamente.');
        //reload the students list
        $exist = true;
        $objStudents = array();
        
        if ($aInfoUeducativa['ueducativaInfoId']['nivelId'] != '15'){
            $etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['ciclo'].' - '.$etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['grado'];
        }    
        else{
            $etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['grado'];
        }

        $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($aInfoUeducativa['ueducativaInfoId']['iecId']);
        if (count($objStudents) > 0){
            $existins = true;
        }
        else {
            $existins = false;
        }
        $dataUe=(unserialize($form['data']));
        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'form' => $this->createFormStudentInscription($form['data'])->createView(),
                    'exist' => $exist,
                    'existins' => $existins,
                    'infoUe' => $form['data'],
                    'etapaespecialidad' => $etapaespecialidad,
                    'dataUe'=> $dataUe['ueducativaInfo'],
                    'totalInscritos'=>count($objStudents),
                     'swSetNameModIntEmer' => 'false',
                    'primariaNuevo' => $this->get('funciones')->validatePrimariaCourse($dataUe['ueducativaInfoId']['iecId'])
        ));
      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
      }

    /**
     * create form to do the massive inscription
     * @return type obj form
     */
    private function createFormStudentInscription($data) {
        return $this->createFormBuilder()
                        ->add('inscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'doInscription()')))
                        ->add('infoUe', 'text', array('data' => $data))
                        ->getForm();
    }

    public function inscriptionbyCiAction(Request $request){

      // IS FIRST

      // ini var to the function
      $em = $this->getDoctrine()->getManager();
      $response = new JsonResponse();
      // get the send values
      $ci = $request->get('ci');
      $complemento = strtoupper($request->get('complemento'));
      $iecId = $request->get('iecId');
      
      // here is to find the studnen by CI  on table estudiante/persona
      // first look for student on ESTUDIANTE table 
      // set array conditions
      $arrayCondition['carnetIdentidad'] = $ci;
      $arrayCondition['segipId'] = 1;
      if($complemento){
        $arrayCondition['complemento'] = $complemento;
      }else{
        $arrayCondition['complemento'] = "";
      }

      // find the student by arrayCondition
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy($arrayCondition);
      // set result in the process to find
      $dataInscription = array();
      
      $arrGenero = array();
      $arrExpedido = array();
      $arrPais = array();
      $arrStudent = array();
      $arrNewStudent = array();
      $arrStudent = array();
      $arrDepto = array();
      $arrProvincia = array();

      $swstudent        = false;
      $swperson         = false;
      $swnewperson      = false;
      // check if the student exist
      if($objStudent){
        $swstudent = true;
        //get the student data 
        $arrStudent = array(
          'paterno'     =>$objStudent->getPaterno(),
          'materno'     =>$objStudent->getMaterno(),
          'nombre'     =>$objStudent->getNombre(),
          'fecNac'      =>$objStudent->getFechaNacimiento()->format('d-m-Y'),
          'carnet'      =>$objStudent->getCarnetIdentidad(),
          'genero'      =>$objStudent->getGeneroTipo()->getGenero(),
          'generoId'    =>$objStudent->getGeneroTipo()->getId(),
          'complemento' =>$objStudent->getComplemento(),
          'rude'        =>$objStudent->getCodigoRude(),
          'expedido'    =>$objStudent->getExpedido()->getSigla(),
          'expedidoId'  =>$objStudent->getExpedido()->getId(),
          'expedidoId2'  =>$objStudent->getExpedido()->getId(),
          'studentId'    =>$objStudent->getId(),
          'casespecial'  =>false,
          'excepcional'=>null,
          'infocomplementaria'=>null,
        );
        
      }else{
        // look into the PERSON table
        // set arrayCondition2
        $arrayCondition2['carnet'] = $ci;
        $arrayCondition2['segipId'] = 1;
        if($complemento){
          $arrayCondition2['complemento'] = $complemento;
        }else{
          $arrayCondition2['complemento'] = "";
        }
        $objStudent = $em->getRepository('SieAppWebBundle:Persona')->findOneBy($arrayCondition2);
        // dump($objStudent);die;
        // check if the person exist on the person table
        if($objStudent){
          // the person exist
          $swperson = true;
          $arrStudent = array(
            'paterno'     =>$objStudent->getPaterno(),
            'materno'     =>$objStudent->getMaterno(),
            'nombre'     =>$objStudent->getNombre(),
            'fecNac'      =>$objStudent->getFechaNacimiento()->format('d-m-Y'),
            'carnet'      =>$objStudent->getCarnet(),
            'complemento' =>$objStudent->getComplemento(),
            'expedido'    =>$objStudent->getExpedido()->getSigla(),
            'expedidoId'  =>$objStudent->getExpedido()->getId(),
            'expedidoId2'  =>$objStudent->getExpedido()->getId(),
            'genero'      =>$objStudent->getGeneroTipo()->getGenero(),
            'generoId'    =>$objStudent->getGeneroTipo()->getId(),
            'casespecial'  =>false,
            'excepcional'=>null,
            'infocomplementaria'=>null,

          );
          // dump($arrStudent);die;
        }
      }
      // this is to the new person
      $objExpedido = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();
      foreach ($objExpedido as $value) {
        $arrExpedido[] = array('expedidoId' => $value->getId(),'expedido' => $value->getSigla());
      }
      // get genero data
      $objGenero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
      foreach ($objGenero as $value) {
          if($value->getId()<3){
              $arrGenero[] = array('generoId' => $value->getId(),'genero' => $value->getGenero());                
          }
      }
      //get pais data
      $objPais = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll();
      foreach ($objPais as $value) {
        $arrPais[]=array('paisId'=>$value->getId(), 'pais'=>$value->getPais());
      }

      if(!$swperson  && !$swstudent){
        //set struct data person
        $arrNewStudent = array(
          'paisId'=>null,
          'lugarNacTipoId'=>null,
          'lugarProvNacTipoId'=>null,
          'localidad'=>null,
          'paterno'=>null,
          'materno'=>null,
          'nombre'=>null,
          'casespecial'  =>false,
          'excepcional'=>null,
          'infocomplementaria'=>null,
        );
        $swnewperson = true;
      }
      //get info about the course
      $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPerIecid($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'), $iecId);

      $objUeducativa = $objUeducativa[0];

      $sinfoUeducativa = (array(
                'ueducativaInfo' => array('ciclo' => $objUeducativa['ciclo'], 'nivel' => $objUeducativa['nivel'], 'grado' => $objUeducativa['grado'], 'paralelo' => $objUeducativa['paralelo'], 'turno' => $objUeducativa['turno']),
                'ueducativaInfoId' => array('nivelId' => $objUeducativa['nivelId'], 'cicloId' => $objUeducativa['cicloId'], 'gradoId' => $objUeducativa['gradoId'], 'turnoId' => $objUeducativa['turnoId'], 'paraleloId' => $objUeducativa['paraleloId'], 'iecId' => $objUeducativa['iecId'], 'setCodigo'=>$objUeducativa['setCodigo'], 'satCodigo'=>$objUeducativa['satCodigo'],'sfatCodigo'=>$objUeducativa['sfatCodigo'],'setId'=>$objUeducativa['setId'],'periodoId'=>$objUeducativa['periodoId'],)
            ));
      // added validation to show the inscription menor option
      $showCaseSpecialOption = ($sinfoUeducativa['ueducativaInfoId']['nivelId'] ==15 && ($sinfoUeducativa['ueducativaInfoId']['cicloId']==2 || $sinfoUeducativa['ueducativaInfoId']['cicloId']==1))?true:false;
      // user allowed to show the special inscription option
      $userAllowedOnCasespecial = in_array($this->session->get('roluser'), array(7,8,10))?true:false;

      $arrWithoutsegip = array(000000);
      $swWithoutSegip = in_array($this->session->get('userName'), $arrWithoutsegip)?true:false;


      // get all data about EstudianteInscripcionAlternativaExcepcionalTipo
      // $objExcepcional = $em->getRepository('SieAppWebBundle:EstudianteInscripcionAlternativaExcepcionalTipo')->findAll();
      $arridsexcepcional = array();
      if( $sinfoUeducativa['ueducativaInfoId']['nivelId'] ==15 && $sinfoUeducativa['ueducativaInfoId']['cicloId']==1 ){
        $arridsexcepcional = array(6);
      }
      if( $sinfoUeducativa['ueducativaInfoId']['nivelId'] ==15 && $sinfoUeducativa['ueducativaInfoId']['cicloId']==2 ){
        $arridsexcepcional = array(1,2,3,4,5,7,8,9,10);
      }
      $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcionAlternativaExcepcionalTipo');
      $query = $entity->createQueryBuilder('eiaet')
              ->select('eiaet')
              ->where('eiaet.id  IN (:idsexcepcional) ')
              ->setParameter('idsexcepcional', $arridsexcepcional )
              ->getQuery();
      $objExcepcional = $query->getResult();

      $arrExcepcional = array();
      foreach ($objExcepcional as $value) {
        $arrExcepcional[] = array('id'=>$value->getId(), 'description' => $value->getDescripcion());
      }

      $arrResponse = array(
            'status'                   => 'run',
            'dataStudent'              => $arrStudent,
            'swstudent'                => $swstudent,
            'swperson'                 => $swperson,
            'showCaseSpecialOption'    => $showCaseSpecialOption,
            'userAllowedOnCasespecial' => $userAllowedOnCasespecial,
            'swnewperson'              => $swnewperson,
            'arrGenero'                => $arrGenero,
            'arrExpedido'              => $arrExpedido,
            'arrPais'                  => $arrPais,
            'arrNewStudent'            => $arrNewStudent,
            'arrExcepcional'           => $arrExcepcional,
            'swWithoutSegip'           => $swWithoutSegip,
      );
      // dump($arrResponse);die;
      $response->setStatusCode(200);
      $response->setData($arrResponse);
      
      return $response;    
      
    }    

    public function getDeptoAction(Request $request){
        $response = new JsonResponse();
        // get the send values
        $paisId = $request->get('paisId');
        // create db conexino
        $em = $this->getDoctrine()->getManager();
        // get departamento
        if ($paisId == 1) {
            $condition = array('lugarNivel' => 1, 'paisTipoId' => $paisId);
        } else {
            $condition = array('lugarNivel' => 8, 'id' => '79355');
        }
        $objDepto = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy($condition);
        $arrDepto = array();
        foreach ($objDepto as $depto) {
            $arrDepto[]=array('deptoId'=>$depto->getId(),'depto'=>$depto->getLugar());
        }

        $response->setStatusCode(200);
        $response->setData(array(
            'arrDepto' => $arrDepto,
        ));
       
        return $response;        


    }

    public function getProvinciaAction(Request $request){
    
      $em = $this->getDoctrine()->getManager();
      $response = new JsonResponse();
      $lugarNacTipoId = $request->get('lugarNacTipoId');

      // / get provincias
      $objProv = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $lugarNacTipoId));

      $arrProvincia = array();
      foreach ($objProv as $prov) {
          $arrProvincia[] = array('provinciaId'=>$prov->getid(), 'provincia'=>$prov->getlugar());
      }

        $response->setStatusCode(200);
      $response->setData(array(
          'arrProvincia' => $arrProvincia,
      ));
     
      return $response;

    }
    // to do the check and inscription about the student
    public function checkDataStudentAction(Request $request){

      //dump('here');die;
      
      // IS SECOND

      //ini json var
      $response = new JsonResponse();
      // create db conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      // get the send data
      $paisId = $request->get('paisId');
      $lugarNacTipoId = $request->get('lugarNacTipoId');
      $lugarProvNacTipoId = $request->get('lugarProvNacTipoId');
      $localidad = $request->get('localidad');
      $paterno = mb_strtoupper($request->get('paterno'), 'utf-8');
      $materno = mb_strtoupper($request->get('materno'), 'utf-8');
      $nombre = mb_strtoupper($request->get('nombre'), 'utf-8');

      $oficialia = mb_strtoupper($request->get('oficialia'), 'utf-8');
      $libro = mb_strtoupper($request->get('libro'), 'utf-8');
      $partida = mb_strtoupper($request->get('partida'), 'utf-8');
      $folio = mb_strtoupper($request->get('folio'), 'utf-8');

      $fecNac = $request->get('fecNac');
      $fecNac = \DateTime::createFromFormat('d-m-Y', $fecNac);
      $fecNac = $fecNac->format('d-m-Y');
      $generoId = $request->get('generoId');
      $carnet = $request->get('carnet');
      $complemento = mb_strtoupper($request->get('complementoval'), 'utf-8');
      $iecId = $request->get('iecId');
      $expedidoId = $request->get('expedidoId');
      $expedidoId = $request->get('expedidoId');
      $withoutsegip = ($request->get('withoutsegip')=='true')?true:false;

      $reconocimiento = ($request->get('reconocimiento')=='false')?false:true;


      $casespecial = ($request->get('casespecial')=='false')?false:true;
      $excepcional = $request->get('excepcional');
      $infocomplementaria = $request->get('infocomplementaria');
      $arrRudesStudent = false;
      // set data to validate with segip function
      $arrParametros = array(
        'complemento'=>$complemento,
        'primer_apellido'=>$paterno,
        'segundo_apellido'=>$materno,
        'nombre'=>$nombre,
        'fecha_nacimiento'=>$fecNac
      );
      if($request->get('extranjero') == 1){
        $arrParametros['extranjero'] = 'e';
      } 
      
      // dump($carnet);
      // dump($arrParametros);
      if(!$withoutsegip){
        // dump("no tiene carnet");
        // get info segip
        
        // TEMPORAL: no hay servicio segip
        //$answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros,'prod', 'academico');
        $answerSegip = true;
      }else{
        // dump("si tiene carnet");
        $answerSegip = true;
      }
      // check if the data person is true
      if($answerSegip){
        $arrdataStudent = array(
          'paterno'=>$paterno,
          'materno'=>$materno,
          'nombre'=>$nombre,
          'fechaNacimiento'=>$fecNac,
          'oficialia'=>$oficialia,
          'libro'=>$libro,
          'partida'=>$partida,
          'folio'=>$folio
        );
        // dump("hereee");die;
        $objRudesStudent = $this->get('funciones')->searchByExcaustiveDataStudent($arrdataStudent);

        if(!($objRudesStudent)){
          // if(true){
          // set parameter to validate inscription
          $arrParameterToValidate = array('fecNac' => $fecNac , 'casespecial'=>$casespecial , 'iecId' => $iecId) ;
          // get the studnets age
          $swyearStudent = $this->validateYearsStudent($arrParameterToValidate );
          // check if the students has the required
          if(!($swyearStudent)){

            // now get the Centro info 
            try {
             
              
                // create rude code to the student
                
                $query = $em->getConnection()->prepare('SELECT get_estudiante_nuevo_rude(:sie::VARCHAR,:gestion::VARCHAR)');
                $query->bindValue(':sie', $this->session->get('ie_id'));            
                $query->bindValue(':gestion', $this->session->get('currentyear'));
                $query->execute();
                $codigorude = $query->fetchAll();
                $codigoRude = $codigorude[0]["get_estudiante_nuevo_rude"];  
                
                // set the data person to the student table
                $estudiante = new Estudiante();
                // set the new student
                $estudiante->setCodigoRude($codigoRude);
                $estudiante->setCarnetIdentidad($carnet);
                $estudiante->setComplemento(mb_strtoupper($complemento, 'utf-8'));
                $estudiante->setPaterno(mb_strtoupper($paterno, 'utf-8'));
                $estudiante->setMaterno(mb_strtoupper($materno, 'utf-8'));
                $estudiante->setNombre(mb_strtoupper($nombre, 'utf-8'));                        
                $estudiante->setFechaNacimiento(new \DateTime($fecNac));            
                $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($generoId));
                $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($paisId));

                $estudiante->setOficialia($oficialia);
                $estudiante->setLibro($libro);
                $estudiante->setPartida($partida);
                $estudiante->setFolio($folio);
                // check if the country is Bolivia
                if ($paisId === '1'){                    
                    $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarNacTipoId));
                    $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarProvNacTipoId));
                    $estudiante->setLocalidadNac($localidad);
                }else{//no Bolivia
                    $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
                    $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
                    $estudiante->setLocalidadNac('');
                }
                if($withoutsegip){
                  $estudiante->setSegipId(0);
                }else{
                  $estudiante->setSegipId(1);
                }
                $estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedidoId));
                $em->persist($estudiante);

                // set the inscription to the new student
                $studentInscription = new EstudianteInscripcion();
                $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($estudiante->getId()));
                $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
                $studentInscription->setObservacion(1);
                $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
                $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
                $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId));
                //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
                $studentInscription->setCodUeProcedenciaId(0);
                $em->persist($studentInscription);

                /*------------------- -------------------
                reconocimiento de saberes segundo semestre 2024
                -------------------------------------------*/
              
                /*
                if($reconocimiento == true){
                  $query = $em->getConnection()->prepare('INSERT INTO public.estudiante_inscripcion_alternativa_saberes (estudiante_inscripcion_id) VALUES (:estudiante_inscripcion_id)');
                                 
                  $query->bindValue(':estudiante_inscripcion_id', $studentInscription->getId());                
                  $query->execute();                  
                }*/



                // set all the courses modules to the student
                $data = array('iecId'=>$iecId, 'eInsId'=>$studentInscription->getId(), 'gestion' => $this->session->get('ie_gestion'));
                $objNewCurricula = $this->get('funciones')->setCurriculaStudent($data);

                $this->get('funciones')->setLogTransaccion(
                  $studentInscription->getId(),
                  'estudiante_inscripcion',
                  'C',
                  '',
                  '',
                  '',
                  'ALTERNATIVA',
                  json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                );

                $em->flush();

                // Try and commit the transaction
                $em->getConnection()->commit();

                $status = 'success';
                $code = 200;
                $message = "Estudiante registrado existosamente!!!";
                $swcreatestudent = true;
            } catch (Exception $e) {
              
              $em->getConnection()->rollback();
              echo 'Excepción capturada: ', $ex->getMessage(), "\n";
              
            }
          }else{

            $status = 'error';
            $code = 400;
            $message = "Estudiante no cumple con la edad Requerida";
            $swcreatestudent = false;            
          }

        }else{

          $arrRudesStudent = array();
          
          foreach ($objRudesStudent as $value) {
            $arrRudesStudent[]=array(
              'idstudent'=>$value->getId(),
              'ci'=>$value->getCarnetIdentidad(),
              'complemento'=>$value->getComplemento(),
              'codigorude'=>$value->getCodigoRude(),
              'paterno'=>$value->getPaterno(),
              'materno'=>$value->getMaterno(),
              'nombre'=>$value->getNombre(),
              'fnac'=>$value->getFechaNacimiento() ? $value->getFechaNacimiento()->format('d-m-Y') : ""
            );
          }

          $status = 'error';
          $code = 400;
          $message = "Estudiante Observado, tiene mas de un Código RUDE";
          $swcreatestudent = false;  

        }



      }else{

        $status = 'error';
        $code = 400;
        $message = "Registro no encontrado para el número de carnet y/o complemento, paterno, materno y nombres(s) ingresados. Debe registrar a la persona adecuadamente (la información es validada por SEGIP).";
        $swcreatestudent = false;

      }

      //send the response info
       $arrResponse = array(
          'status'          => $status,
          'code'            => $code,
          'message'         => $message,
          'swcreatestudent' => $swcreatestudent,
          'arrRudesStudent' => $arrRudesStudent
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
       
    }

    public function showHistoryAction(Request $request){
      // ini vars
      $response = new JsonResponse();
      $em = $this->getDoctrine()->getManager();
      //get the send data
      $rude = $request->get('rude');
      // set values to response
      $dataInscriptionR = array();
      $dataInscriptionA = array();
      $dataInscriptionE = array();
      $dataInscriptionP = [];

      if($rude){
      // // get all cardex info
        $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $rude . "') order by gestion_tipo_id_raep desc, estudiante_inscripcion_id_raep desc;");
        $query->execute();
        $dataInscription = $query->fetchAll();
        
        foreach ($dataInscription as $key => $inscription) {
            switch ($inscription['institucioneducativa_tipo_id_raep']) {
                case '1':
                    $dataInscriptionR[$key] = $inscription;
                    break;
                case '2':
                    $dataInscriptionA[$key] = $inscription;
                    break;
                case '4':
                    $dataInscriptionE[$key] = $inscription;
                    break;
                case '5':
                $bloquep = array();
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
//         dump($dataInscriptionR);
//         dump($dataInscriptionA);
//         dump($dataInscriptionE);
//         dump($dataInscriptionP);
// die;
        $dataInscriptionR = (sizeof($dataInscriptionR)>0)?$dataInscriptionR:false;
        $dataInscriptionA = (sizeof($dataInscriptionA)>0)?$dataInscriptionA:false;
        $dataInscriptionE = (sizeof($dataInscriptionE)>0)?$dataInscriptionE:false;
        $dataInscriptionP = (sizeof($dataInscriptionP)>0)?$dataInscriptionP:false;

        $status = 'success';
        $code = 200;
        $message = "historial del estudiante!!!";
        $swhistory = true;   
      
      }else{
        $status = 'error';
        $code = 400;
        $message = "No existe estudiante";
        $swhistory = true;   
      }



      $arrResponse = array(
      'status'          => $status,
      'code'            => $code,
      'message'         => $message,
      'swhistory'       => $swhistory,
      'dataInscriptionR' => $dataInscriptionR,
      'dataInscriptionA' => $dataInscriptionA,
      'dataInscriptionE' => $dataInscriptionE,
      'dataInscriptionP' => $dataInscriptionP,
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;



    }

    public function studentsInscriptionAction(Request $request){
      //ini json var

     // dump('x'); 
      //cuando ya hay dato
      /*dump($request); 
      die;*/
      

      $response = new JsonResponse();
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      // get the send values 
      $iecId = $request->get('iecId');
      $studentId = $request->get('studentId');
      $periodo = $this->session->get('ie_per_cod');
      $gestion = $this->session->get('ie_gestion');

      $reconocimiento = false;
      //$reconocimiento = $request->get('reconocimiento');
      //$reconocimiento = ($request->get('reconocimiento')=='false')?false:true;

      $casespecial = ($request->get('casespecial')=='false')?false:true;
      $excepcional = $request->get('excepcional');
      $infocomplementaria = $request->get('infocomplementaria');
      $fecNac = $request->get('fecNac');
      $arrRudesStudent = false;
      // set parameter to validate inscription
      $arrParameterToValidate = array('fecNac' => $fecNac , 'casespecial'=>$casespecial, 'iecId' => $iecId, 'studentId'=>$studentId) ;
      
     // get the studnets age
      $swyearStudent = $this->validateYearsStudent($arrParameterToValidate );

      // create db conexion
      if(!($swyearStudent)){
        // set data to validate STUDENTS RUDE
        $arrdataStudent = array(
          'paterno'=>$request->get('paterno'),
          'materno'=>$request->get('materno'),
          'nombre'=>$request->get('nombre'),
        );


        // LUIS
        
        // $objRudesStudent = $this->get('funciones')->lookforRudesbyDataStudent($arrdataStudent);
        
        // if(sizeof($objRudesStudent)==1){
        if(true){
          try {
            // dump($studentId);die;
            // get info about the students inscriptions
            // dump($iecId);die;            
            // ANTES DE LAS VALIDACIONES VERIFICAR SI NO CUMPLE
            $aproved = true; 
            $message = '';

            $resultsRegular = $this->checkIfRegularStudentNowGestion( $studentId, $gestion );

            $epaIds = '35, 34';
            $courseEPA = $this->checkCeaStudyType( $iecId, $epaIds );

            $esaIds = '45, 49, 52';
            $courseESA = $this->checkCeaStudyType( $iecId, $esaIds );

            if( $resultsRegular && ( $courseEPA || $courseESA ) ){
              $responseArray = array(
                'status'  => 401,
                'message' => "El estudiante pertenece a regular, no puede inscribirse en Humanistica",
              );
              
              $response->setStatusCode(401);
              $response->setData($responseArray);
              return $response;
            }

            // SOLO SON PERMITIDAS ESTE TIPO DE COMBINACIONES PARA LA INSCRIPCION
            // 1 - DE REGULAR A ALTERNATIVA SOLO (ETA)

            $etaIds = '1,12,20';
            $resultsEta = $this->checkCeaStudyType( $iecId, $etaIds );
            if( $resultsRegular && $resultsEta ){
              $aproved = false;
              $message = 'El estudiante pertenece a regular, no puede inscribirse en mismos horarios';
              // $message = 'No puede inscribirse, porque se encuentra inscrito en Educacion Regular de la gestion actual';
              if( $resultsRegular[0]['turno_tipo_id'] != $resultsEta[0]['turno_tipo_id'] ){
                $aproved = true;
              } 
            }

            // 2 -  DE EPA A ETA, DIFERENTES HORARIOS SI ES DIFERENTE CEA
            $etaIds = '34, 35';
            $existStudentEPA = $this->checkCeaOfStudent( $studentId, $etaIds, $periodo, $gestion);

            if( $existStudentEPA && $resultsEta ){
              $aproved = false;
              $message = 'El estudiante pertenece a EPA, no puede incribirse en mismos horarios';
              if( $existStudentEPA[0]['institucioneducativa_id'] != $resultsEta[0]['institucioneducativa_id'] ){
                if( $existStudentEPA[0]['turno_tipo_id'] != $resultsEta[0]['turno_tipo_id'] ){
                  $aproved = true;
                }
              } else {
                $aproved = true;
              }

            }
            // 3 - DE ESA A ETA, DIFERENTES HORARIOS SI ES DIFERENTE CEA
            $etaIds = '45, 49, 52';
            $existStudentESA = $this->checkCeaOfStudent( $studentId, $etaIds, $periodo, $gestion);

            if( $existStudentESA && $resultsEta ){
              $aproved = false;
              $message = 'El estudiante pertenece a ESA, no puede incribirse en mismos horarios';
              if( $existStudentESA[0]['institucioneducativa_id'] != $resultsEta[0]['institucioneducativa_id'] ){
                if( $existStudentESA[0]['turno_tipo_id'] != $resultsEta[0]['turno_tipo_id'] ){
                  $aproved = true;
                }
              } else {
                $aproved = true;
              }

            }

            if( !$aproved ){

              $responseArray = array(
                'status'  => 401,
                'message' => $message,
              );
              
              $response->setStatusCode(401);
              $response->setData($responseArray);
              return $response;

            }

            // check if the student has an inscription on this course
            $objCurrentInscription = $this->validateInscriptionStudent($studentId, $iecId);
            //dump($objCurrentInscription); die;
            if(!$objCurrentInscription){
              // do inscription
              // set the inscription to the new student             
              $studentInscription = new EstudianteInscripcion();
              $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
              $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
              $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
              $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($studentId));
              $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
              $studentInscription->setObservacion(1);
              $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
              $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
              $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId));
              //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
              $studentInscription->setCodUeProcedenciaId(0);
              $em->persist($studentInscription);

              //dump($studentInscription); die;

              //save the inscription data when the inscription is excepcional
              if($casespecial){
                $estudianteInscripcionAlternativaExcepcionalObjNew = new EstudianteInscripcionAlternativaExcepcional();
                $estudianteInscripcionAlternativaExcepcionalObjNew->setEstudianteInscripcionAlternativaExcepcionalTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionAlternativaExcepcionalTipo')->find($excepcional));
                $estudianteInscripcionAlternativaExcepcionalObjNew->setFecha(new \DateTime('now'));
                $estudianteInscripcionAlternativaExcepcionalObjNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentId));
                $estudianteInscripcionAlternativaExcepcionalObjNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $estudianteInscripcionAlternativaExcepcionalObjNew->setDocumento($infocomplementaria);
                $em->persist($estudianteInscripcionAlternativaExcepcionalObjNew);
              }

               


              // set all the courses modules to the student
              $data = array('iecId'=>$iecId, 'eInsId'=>$studentInscription->getId(), 'gestion' => $this->session->get('ie_gestion'));
              $objNewCurricula = $this->get('funciones')->setCurriculaStudent($data);

              $this->get('funciones')->setLogTransaccion(
                $studentInscription->getId(),
                'estudiante_inscripcion',
                'C',
                '',
                '',
                '',
                'ALTERNATIVA',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
              );

              $em->flush();
              //do the commit in DB
              $em->getConnection()->commit();
              $status = 'success';
              $code = 200;
              $message = "Estudiante registrado existosamente!!!";
              $swcreatestudent = true;   

              /*------------------- -------------------
                reconocimiento de saberes segundo semestre 2024
                -------------------------------------------*/
              
                if($reconocimiento == true){
                  $query = $em->getConnection()->prepare('INSERT INTO public.estudiante_inscripcion_alternativa_saberes (estudiante_inscripcion_id, usuario_id) VALUES (:estudiante_inscripcion_id, :usuario_id)');
                                 
                  $query->bindValue(':estudiante_inscripcion_id', $studentInscription->getId());                
                  $query->bindValue(':usuario_id', $this->session->get('userId'));                
                  $query->execute();                  
                }


            }else{
              $arrIdCourse = array('TEC'=>' en el mismo nivel - Técnica',1511=>'Elementales',1512=>'Avanzados',1521=>'Aplicados',1522=>'Complementarios',1523=>'Especializados');

              $status = 'error';
              $code = 400;
              $message = "Estudiante ya cuenta con una inscripcion en el curso ".$objCurrentInscription;
              $swcreatestudent = false;   

            }
            
          } catch (Exception $e) {
            echo 'error in save the data inscription';
            $em->getConnection()->rollback();
            
          }          

        }else{
          $arrRudesStudent = array();
          foreach ($objRudesStudent as $value) {
            $arrRudesStudent[]=array(
                                  'idstudent'=>$value->getId(),
                                  'ci'=>$value->getCarnetIdentidad(),
                                  'complemento'=>$value->getComplemento(),
                                  'codigorude'=>$value->getCodigoRude(),
                                  'paterno'=>$value->getPaterno(),
                                  'materno'=>$value->getMaterno(),
                                  'nombre'=>$value->getNombre(),
                                  'fnac'=>$value->getFechaNacimiento()->format('d-m-Y'),
                                  );
          }

          $status = 'error';
          $code = 400;
          $message = "Estudiante Observado, NO INSCRITO, tiene más de un Código RUDE. Se recomienda solucionar el caso a través del Técnico Distrital";
          $swcreatestudent = false;  

        }

      }else{
          $status = 'error';
          $code = 400;
          $message = "Estudiante no cumple con la edad requerida";
          $swcreatestudent = false;  
      }

      $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swcreatestudent' => $swcreatestudent,    
        'arrRudesStudent' => $arrRudesStudent,    
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }

    public function checkCeaStudyType( $institucionEducativaCursoId, $etaIds ){

      $em = $this->getDoctrine()->getManager();
      $db = $em->getConnection();

      $queryETA = "select sae.superior_acreditacion_tipo_id, ic.id,ic.turno_tipo_id, ic.institucioneducativa_id 
                    from superior_facultad_area_tipo sfat 
                    inner join superior_especialidad_tipo set2 on sfat.id=set2.superior_facultad_area_tipo_id 
                    inner join superior_acreditacion_especialidad sae on set2.id=sae.superior_especialidad_tipo_id 
                    inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id=sat.id
                    inner join superior_institucioneducativa_acreditacion sia on sia.acreditacion_especialidad_id=sae.id
                    inner join superior_institucioneducativa_periodo sip on sip.superior_institucioneducativa_acreditacion_id=sia.id
                    inner join institucioneducativa_curso ic on ic.superior_institucioneducativa_periodo_id=sip.id 
                    where sae.superior_acreditacion_tipo_id in (".$etaIds.")
                    and ic.id=".$institucionEducativaCursoId."";
      $stmt = $db->prepare($queryETA);
      $params = array();
      $stmt->execute($params);
      $resultsETA = $stmt->fetchAll();

      return $resultsETA;

    }

    public function checkCeaOfStudent( $estudianteId, $acreditacionTipoIds, $periodo, $gestion ){

      $em = $this->getDoctrine()->getManager();
      $db = $em->getConnection();
      $query = "select sae.superior_acreditacion_tipo_id, ic.id,ic.turno_tipo_id, ic.institucioneducativa_id 
                from superior_facultad_area_tipo sfat 
                inner join superior_especialidad_tipo set2 on sfat.id=set2.superior_facultad_area_tipo_id 
                inner join superior_acreditacion_especialidad sae on set2.id=sae.superior_especialidad_tipo_id 
                inner join superior_acreditacion_tipo sat on sae.superior_acreditacion_tipo_id=sat.id
                inner join superior_institucioneducativa_acreditacion sia on sia.acreditacion_especialidad_id=sae.id
                inner join superior_institucioneducativa_periodo sip on sip.superior_institucioneducativa_acreditacion_id=sia.id
                inner join institucioneducativa_curso ic on ic.superior_institucioneducativa_periodo_id=sip.id 
                inner join estudiante_inscripcion ei on ic.id=ei.institucioneducativa_curso_id 
                inner join institucioneducativa_sucursal is2 on sia.institucioneducativa_sucursal_id = is2.id
                where sae.superior_acreditacion_tipo_id in (". $acreditacionTipoIds .")
                and ei.estudiante_id= ". $estudianteId ."
                and ic.gestion_tipo_id = ". $gestion ."
                and is2.periodo_tipo_id = ". $periodo ."
                order by ic.gestion_tipo_id desc";
      $stmt = $db->prepare($query);
      $params = array();
      $stmt->execute($params);
      $results = $stmt->fetchAll();
      return $results;

    }

    public function checkIfRegularStudentNowGestion( $studentId, $gestion ){

      $em = $this->getDoctrine()->getManager();
      $db = $em->getConnection();
      $query = "select ic.id, ei.estudiante_id, ic.gestion_tipo_id, ic.turno_tipo_id from estudiante_inscripcion ei 
                        inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id=ic.id 
                        inner join institucioneducativa i on ic.institucioneducativa_id=i.id 
                        where i.institucioneducativa_tipo_id=1
                        and ei.estudiante_id=".$studentId."
                        and ic.gestion_tipo_id=".$gestion."
                        and ei.estadomatricula_tipo_id in (4,5,55,11,10,28)
                        order by ic.gestion_tipo_id desc
                        limit 1
                        ";
      $stmt = $db->prepare($query);
      $params = array();
      $stmt->execute($params);
      $results = $stmt->fetchAll();
      return $results;

    }

    public function checkCeaTypeOfStudent( $estudianteId ){

      $em = $this->getDoctrine()->getManager();
      $db = $em->getConnection();

      $query = "select ei.estudiante_id, ic.gestion_tipo_id, i.institucioneducativa_tipo_id,i.id from estudiante_inscripcion ei 
                  inner join institucioneducativa_curso ic on ei.institucioneducativa_curso_id=ic.id 
                  inner join institucioneducativa i on ic.institucioneducativa_id=i.id 
                  where ei.estudiante_id=".$estudianteId."
                  order by ic.gestion_tipo_id desc
                  limit 1";
      $stmt = $db->prepare($query);
      $params = array();
      $stmt->execute($params);
      $results = $stmt->fetchAll();

      return $results;      

    }

    public function studentsInscriptionlistAction(Request $request){
// dump($request);die;
      //ini json var
      $response = new JsonResponse();
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      // get the send values 
      $iecId = $request->get('iecId');
      $studentId = $request->get('idstudent');
      $casespecial = ($request->get('casespecial')=='false')?false:true;
      $excepcional = $request->get('excepcional');
      $infocomplementaria = $request->get('infocomplementaria');
      $fecNac = $request->get('fecNac');
      $arrRudesStudent = false;
      // set parameter to validate inscription
      $arrParameterToValidate = array('fecNac' => $fecNac , 'casespecial'=>$casespecial, 'iecId' => $iecId, 'studentId'=>$studentId) ;
      
     // get the studnets age
      $swyearStudent = $this->validateYearsStudent($arrParameterToValidate );

      // create db conexion
      if(!($swyearStudent)){
        
        if(true){
          try {
            // get info about the students inscriptions
            $objCurrentInscription = $this->validateInscriptionStudent($studentId, $iecId); 
            // check if the student has an inscription on this course
              if(!$objCurrentInscription){
              // do inscription
              // set the inscription to the new student
                $studentInscription = new EstudianteInscripcion();
                $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($studentId));
                $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
                $studentInscription->setObservacion(1);
                $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
                $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
                $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId));
                //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
                $studentInscription->setCodUeProcedenciaId(0);
                $em->persist($studentInscription);

                //save the inscription data when the inscription is excepcional
                if($casespecial){
                  $estudianteInscripcionAlternativaExcepcionalObjNew = new EstudianteInscripcionAlternativaExcepcional();
                  $estudianteInscripcionAlternativaExcepcionalObjNew->setEstudianteInscripcionAlternativaExcepcionalTipo($em->getRepository('SieAppWebBundle:EstudianteInscripcionAlternativaExcepcionalTipo')->find($excepcional));
                  $estudianteInscripcionAlternativaExcepcionalObjNew->setFecha(new \DateTime('now'));
                  $estudianteInscripcionAlternativaExcepcionalObjNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentId));
                  $estudianteInscripcionAlternativaExcepcionalObjNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                  $estudianteInscripcionAlternativaExcepcionalObjNew->setDocumento($infocomplementaria);
                  $em->persist($estudianteInscripcionAlternativaExcepcionalObjNew);
                }


                // set all the courses modules to the student
                $data = array('iecId'=>$iecId, 'eInsId'=>$studentInscription->getId(), 'gestion' => $this->session->get('ie_gestion'));
                $objNewCurricula = $this->get('funciones')->setCurriculaStudent($data);

                $em->flush();
                //do the commit in DB
                $em->getConnection()->commit();
                $status = 'success';
                $code = 200;
                $message = "Estudiante registrado existosamente!!!";
                $swcreatestudent = true;   


              }else{
                $arrIdCourse = array('TEC'=>' en el mismo nivel - Técnica',1511=>'Elementales',1512=>'Avanzados',1521=>'Aplicados',1522=>'Complementarios',1523=>'Especializados');

                $status = 'error';
                $code = 400;
                $message = "Estudiante ya cuenta con una inscripcion en el curso ".$objCurrentInscription;
                $swcreatestudent = false;   

              }
            
          } catch (Exception $e) {
            echo 'error in save the data inscription';
            $em->getConnection()->rollback();
            
          }          

        }else{
          $arrRudesStudent = array();
          foreach ($objRudesStudent as $value) {
            $arrRudesStudent[]=array(
                                  'idstudent'=>$value->getId(),
                                  'ci'=>$value->getCarnetIdentidad(),
                                  'complemento'=>$value->getComplemento(),
                                  'codigorude'=>$value->getCodigoRude(),
                                  'paterno'=>$value->getPaterno(),
                                  'materno'=>$value->getMaterno(),
                                  'nombre'=>$value->getNombre(),
                                  'fnac'=>$value->getFechaNacimiento()->format('d-m-Y'),
                                  );
          }

          $status = 'error';
          $code = 400;
          $message = "Estudiante Observado, NO INSCRITO, tiene más de un Código RUDE. Se recomienda solucionar el caso a través del Técnico Distrital";
          $swcreatestudent = false;  

        }

      }else{
          $status = 'error';
          $code = 400;
          $message = "Estudiante no cumple con la edad requerida";
          $swcreatestudent = false;  
      }

      $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swcreatestudent' => $swcreatestudent,    
        'arrRudesStudent' => $arrRudesStudent,    
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;
    }

    public function studentsInscriptionNEWAction(Request $request){

      /*dump($request);
      CUANDO NO HAY DATOS
      die;*/
      //ini json var
      $response = new JsonResponse();
      // create db conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      // get the send data
      $paisId = $request->get('paisId');
      $lugarNacTipoId = $request->get('lugarNacTipoId');
      $lugarProvNacTipoId = $request->get('lugarProvNacTipoId');
      $localidad = $request->get('localidad');
      $paterno = mb_strtoupper($request->get('paterno'), 'utf-8');
      $materno = mb_strtoupper($request->get('materno'), 'utf-8');
      $nombre = mb_strtoupper($request->get('nombre'), 'utf-8');
      $fecNac = $request->get('fecNac');
      $generoId = $request->get('generoId');
      $carnet = $request->get('carnet');
      $complemento = mb_strtoupper($request->get('complementoval'), 'utf-8');
      $iecId = $request->get('iecId');
      $expedidoId = $request->get('expedidoId');

      $reconocimiento = ($request->get('reconocimiento')=='false')?false:true;

      $casespecial = ($request->get('casespecial')=='false')?false:true;;
      $excepcional = $request->get('excepcional');
      $infocomplementaria = $request->get('infocomplementaria');
      $arrRudesStudent = false;
      // set data to validate with segip function
      $arrParametros = array(
        'complemento'=>$complemento,
        'primer_apellido'=>$paterno,
        'segundo_apellido'=>$materno,
        'nombre'=>$nombre,
        'fecha_nacimiento'=>$fecNac
      );
      
      // check if the data person is true
      if(true){        
        if(true){
        // if(true){
          if(true){

            // now get the Centro info 
            try {
             
              
                // create rude code to the student
                
                $query = $em->getConnection()->prepare('SELECT get_estudiante_nuevo_rude(:sie::VARCHAR,:gestion::VARCHAR)');
                $query->bindValue(':sie', $this->session->get('ie_id'));            
                $query->bindValue(':gestion', $this->session->get('currentyear'));
                $query->execute();
                $codigorude = $query->fetchAll();
                $codigoRude = $codigorude[0]["get_estudiante_nuevo_rude"];  
                
                // set the data person to the student table
                $estudiante = new Estudiante();
                // set the new student
                $estudiante->setCodigoRude($codigoRude);
                $estudiante->setCarnetIdentidad($carnet);
                $estudiante->setComplemento(mb_strtoupper($complemento, 'utf-8'));
                $estudiante->setPaterno(mb_strtoupper($paterno, 'utf-8'));
                $estudiante->setMaterno(mb_strtoupper($materno, 'utf-8'));
                $estudiante->setNombre(mb_strtoupper($nombre, 'utf-8'));                        
                $estudiante->setFechaNacimiento(new \DateTime($fecNac));            
                $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($generoId));
                $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($paisId));
                // check if the country is Bolivia
                if ($paisId === '1'){                    
                    $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarNacTipoId));
                    $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarProvNacTipoId));
                    $estudiante->setLocalidadNac($localidad);
                }else{//no Bolivia
                    $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
                    $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
                    $estudiante->setLocalidadNac('');
                }
                $estudiante->setSegipId(1);
                $estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedidoId));
                $em->persist($estudiante);

                // set the inscription to the new student
                $studentInscription = new EstudianteInscripcion();
                $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->session->get('ie_id')));
                $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
                $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($estudiante->getId()));
                $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
                $studentInscription->setObservacion(1);
                $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
                $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
                $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId));
                //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
                $studentInscription->setCodUeProcedenciaId(0);
                $em->persist($studentInscription);


                // set all the courses modules to the student
                $data = array('iecId'=>$iecId, 'eInsId'=>$studentInscription->getId(), 'gestion' => $this->session->get('ie_gestion'));
                $objNewCurricula = $this->get('funciones')->setCurriculaStudent($data);

                $this->get('funciones')->setLogTransaccion(
                  $studentInscription->getId(),
                  'estudiante_inscripcion',
                  'C',
                  '',
                  '',
                  '',
                  'ALTERNATIVA',
                  json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
              );


                $em->flush();

                // Try and commit the transaction
                $em->getConnection()->commit();

                /*------------------- -------------------
                reconocimiento de saberes segundo semestre 2024
                -------------------------------------------*/
              
               /* if($reconocimiento == true){
                  $query = $em->getConnection()->prepare('INSERT INTO public.estudiante_inscripcion_alternativa_saberes (estudiante_inscripcion_id, usuario_id) VALUES (:estudiante_inscripcion_id, :usuario_id)');
                                 
                  $query->bindValue(':estudiante_inscripcion_id', $studentInscription->getId());                
                  $query->bindValue(':usuario_id', $this->session->get('userId'));                
                  $query->execute();                  
                }*/

                $status = 'success';
                $code = 200;
                $message = "Estudiante registrado existosamente!!!";
                $swcreatestudent = true;
            } catch (Exception $e) {
              
              $em->getConnection()->rollback();
              echo 'Excepción capturada: ', $ex->getMessage(), "\n";
              
            }
          }
        }



      }

      //send the response info
       $arrResponse = array(
            'status'          => $status,
            'code'            => $code,
            'message'         => $message,
            'swcreatestudent' => $swcreatestudent,
            'arrRudesStudent' => $arrRudesStudent,
            
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;


    }    

    private function validateInscriptionStudent($studentId, $iecId){
      // /create db conexion
      $em = $this->getDoctrine()->getManager();
      // ini var to do the transaction
      $sw=true;
      $codCourse=false;
      $arrIdCourse = array(1511,1512,1521,1522,1523);

      // get the nivel,ciclo, grado todo the validation
      $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPerIecid($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'), $iecId);
      // get info course
      $objUeducativa = $objUeducativa[0];
      // look for students inscription to validate on Primaria && secundaria && tec
      $arrstudentInscription = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getStudentCourseAlterPerStudentId($studentId, $this->session->get('ie_gestion'), $this->session->get('ie_per_cod'));
      // if the level is primaria or seccundaria to the validation

      dump( $objUeducativa); 
      dump( $arrstudentInscription);
      die;

     if($objUeducativa['nivelId']==15){
       reset($arrstudentInscription);
       $sw = true;
       while ( $sw && ($inscription = current($arrstudentInscription))) {
         # code to validate the inscription
         $idCourse = $inscription['nivelId'].$inscription['cicloId'].$inscription['gradoId'];
         
         if(in_array($idCourse, $arrIdCourse)){
          $sw=false;
          // $codCourse = $idCourse;
          $codCourse = $objUeducativa['ciclo'].'-'.$objUeducativa['grado'];
         }
        
        next($arrstudentInscription);
       }
     }else{

      reset($arrstudentInscription);
      $sw = true;
       while ( $sw && ($inscription = current($arrstudentInscription))) {
       
         # code to validate the inscription
          $idCourseStudent = $inscription['nivelId'].$inscription['cicloId'].$inscription['gradoId'];
          $idCurrentCourse = $objUeducativa['nivelId'].$objUeducativa['cicloId'].$objUeducativa['gradoId'];
          
          if($idCourseStudent == $idCurrentCourse){
            $sw=false;
            $codCourse = $objUeducativa['ciclo'].' '.$objUeducativa['grado'];
          }
        next($arrstudentInscription);
       }

     }
     // die;
    return $codCourse;

    }

    private function validateYearsStudent($arrStudent){
      
      // create db conexion
      $em = $this->getDoctrine()->getManager();
      $validateYear = false;
      
        if(!($arrStudent['casespecial'])){

           //get curso info
          $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPerIecid($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'), $arrStudent['iecId']);

          $objUeducativa = $objUeducativa[0];

          $aInfoUeducativa = (array(
              'ueducativaInfo' => array('ciclo' => $objUeducativa['ciclo'], 'nivel' => $objUeducativa['nivel'], 'grado' => $objUeducativa['grado'], 'paralelo' => $objUeducativa['paralelo'], 'turno' => $objUeducativa['turno']),
              'ueducativaInfoId' => array('nivelId' => $objUeducativa['nivelId'], 'cicloId' => $objUeducativa['cicloId'], 'gradoId' => $objUeducativa['gradoId'], 'turnoId' => $objUeducativa['turnoId'], 'paraleloId' => $objUeducativa['paraleloId'], 'iecId' => $objUeducativa['iecId'], 'setCodigo'=>$objUeducativa['setCodigo'], 'satCodigo'=>$objUeducativa['satCodigo'],'sfatCodigo'=>$objUeducativa['sfatCodigo'],'setId'=>$objUeducativa['setId'],'periodoId'=>$objUeducativa['periodoId'],)
          ));

        //get the curso info
        $objInstitucioneducativaCursoStudent=$em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($arrStudent['iecId']);

        //get the students age
        $arrYearStudent =$this->get('funciones')->getTheCurrentYear($arrStudent['fecNac'], '30-8-'.date('Y'));
        $yearStudent = $arrYearStudent['age'];
        //validate the humanisticos
        // if($objInstitucioneducativaCursoStudent->getNivelTipo()->getId()==15){
        if($aInfoUeducativa['ueducativaInfoId']['nivelId']==15){
          //validate to nivel=15 - ciclo=1 - grado=1
          // if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==1 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==1){
          //   if(!($yearStudent>=15)){
          //     $validateYear=true;
          //   }
          // }
          // elementales
          if($aInfoUeducativa['ueducativaInfoId']['cicloId']==1 and $aInfoUeducativa['ueducativaInfoId']['gradoId']==1){
            if(!($yearStudent>=15)){
              $validateYear=true;
            }
          }
          // avanzados
          //validate to nivel=15 - ciclo=1 - grado=2
          if($aInfoUeducativa['ueducativaInfoId']['cicloId']==1 and $aInfoUeducativa['ueducativaInfoId']['gradoId']==2){
            if(!($yearStudent>=16)){
              $validateYear=true;
            }
          }
          // aplicados
          //validate to nivel=15 - ciclo=2 - grado=1
          if($aInfoUeducativa['ueducativaInfoId']['cicloId']==2 and $aInfoUeducativa['ueducativaInfoId']['gradoId']==1){
            if(!($yearStudent>=17)){
              $validateYear=true;
            }
          }
          // complementarios
          //validate to nivel=15 - ciclo=2 - grado=2
          if($aInfoUeducativa['ueducativaInfoId']['cicloId']==2 and $aInfoUeducativa['ueducativaInfoId']['gradoId']==2){
            if(!($yearStudent>=18)){
              $validateYear=true;
            }
          }
          // avanzados
          //validate to nivel=15 - ciclo=2 - grado=3
          if($aInfoUeducativa['ueducativaInfoId']['cicloId']==2 and $aInfoUeducativa['ueducativaInfoId']['gradoId']==3){
            if(!($yearStudent>=18)){
              $validateYear=true;
            }
          }

        } else{
            if(!($yearStudent>=15)){
              $validateYear=true;
            }
        }//end first if - validate the humanisticos

      }

      return ($validateYear);
      
    }

    public function showListStudentAction(Request $request){
      // create db conexion
        $em = $this->getDoctrine()->getManager();

        $iecId = $request->get('iecId');

        $objUeducativa = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getAlterCursosBySieGestSubPerIecid($this->session->get('ie_id'), $this->session->get('ie_gestion'), $this->session->get('ie_subcea'), $this->session->get('ie_per_cod'), $iecId);

        $objUeducativa = $objUeducativa[0];

        $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('ciclo' => $objUeducativa['ciclo'], 'nivel' => $objUeducativa['nivel'], 'grado' => $objUeducativa['grado'], 'paralelo' => $objUeducativa['paralelo'], 'turno' => $objUeducativa['turno']),
                    'ueducativaInfoId' => array('nivelId' => $objUeducativa['nivelId'], 'cicloId' => $objUeducativa['cicloId'], 'gradoId' => $objUeducativa['gradoId'], 'turnoId' => $objUeducativa['turnoId'], 'paraleloId' => $objUeducativa['paraleloId'], 'iecId' => $objUeducativa['iecId'], 'setCodigo'=>$objUeducativa['setCodigo'], 'satCodigo'=>$objUeducativa['satCodigo'],'sfatCodigo'=>$objUeducativa['sfatCodigo'],'setId'=>$objUeducativa['setId'],'periodoId'=>$objUeducativa['periodoId'],)
                ));

// dump($sinfoUeducativa);
// dump($objUeducativa);
// die;

        $infoUe = $sinfoUeducativa;
        $aInfoUeducativa = unserialize($infoUe);
        // dump($aInfoUeducativa);die;
        $exist = true;
        $objStudents = array();
        $dataUe=(unserialize($infoUe));
        // dump($infoUe);
       // dump($dataUe); die;
        $swSetNameModIntEmer = false;
      
        $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($iecId);
          //        dump($aInfoUeducativa['ueducativaInfoId']['iecId']);
          //        die;
        
        if (count($objStudents) > 0){
            $existins = true;
        }
        else {
            $existins = false;
        }
        if ($aInfoUeducativa['ueducativaInfoId']['nivelId'] != '15'){
            $etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['ciclo'].' - '.$etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['grado'];
        }
        else{
            $etapaespecialidad = $aInfoUeducativa['ueducativaInfo']['grado'];
        }

        $primariaNuevo = $this->get('funciones')->verificarMateriasPrimariaAlternativa($iecId);
        //dump($objStudents); die;
        // $primariaNuevo = $this->get('funciones')->validatePrimariaCourse($dataUe['ueducativaInfoId']['iecId']);

        return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'form' => $this->createFormStudentInscription($infoUe)->createView(),
                    'exist' => $exist,
                    'existins' => $existins,
                    'infoUe' => $infoUe,
                    'etapaespecialidad' => $etapaespecialidad,
                    'dataUe'=> $dataUe['ueducativaInfo'],
                    'totalInscritos'=>count($objStudents),
                    'swSetNameModIntEmer' => $swSetNameModIntEmer,
                    'primariaNuevo' => $primariaNuevo,
                    'iecId'  => $request->get('iecId')
        ));
    }           

}
