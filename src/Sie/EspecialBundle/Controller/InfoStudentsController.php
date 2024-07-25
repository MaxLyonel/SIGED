<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEspecial;
use Sie\AppWebBundle\Entity\EstadomatriculaTipo;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteDiscapacidadCertificado;
use Sie\AppWebBundle\Entity\Estudiante;


class InfoStudentsController extends Controller {

  public $session;
  public $idEstadoMatricula;
  // public $idInstitucion;

  /**
   * the class constructor
   */
  public function __construct() {
      //init the session values
      $this->session = new Session();

  }

  /**
   * list of request
   *
   */
  public function indexAction(Request $request) {
      //get the session's values
      $this->session = $request->getSession();
//        $id_usuario = $this->session->get('userId');
//        //validation if the user is logged
//        if (!isset($id_usuario)) {
//            return $this->redirect($this->generateUrl('login'));
//        }
//        return $this->render($this->session->get('pathSystem') . ':InfoStudents:index.html.twig', array());
      //get the value to send
     // return $this->redirect($this->generateUrl('login'));
      $form = $request->get('form');
      //dump($form); die;

      $em = $this->getDoctrine()->getManager();
      //find the levels from UE
      //levels gestion -1
      //$objLevelsOld = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getNivelBySieAndGestion($form['sie'], $form['gestion']);
      $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getInfoUeducativaEspecialBySieGestion($form['sie'], $form['gestion']);

      //dump($objUeducativa);die;

      $exist = true;
      $aInfoUnidadEductiva = array();
      if ($objUeducativa) {
          foreach ($objUeducativa as $uEducativa) {

              //get the literal data of unidad educativa
              $sinfoUeducativa = serialize(array(
                  'ueducativaInfo' => array('nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'paralelo' => $uEducativa['paralelo'], 'turno' => $uEducativa['turno'], 'programa' => $uEducativa['programa'], 'servicio' => $uEducativa['servicio'], 'areaEspecial' => $uEducativa['areaEspecial'], 'iecLugar'=>$uEducativa['iecLugar'], 'momento'=>$uEducativa['momento']),
                  'ueducativaInfoId' => array('paraleloId' => $uEducativa['paraleloId'], 'turnoId' => $uEducativa['turnoId'],'programaId'=>$uEducativa['especialProgramaTipo'],'servicioId'=>$uEducativa['especialServicioTipo'], 'nivelId' => $uEducativa['nivelId'], 'gradoId' => $uEducativa['gradoId'], 'cicloId' => $uEducativa['cicloTipoId'], 'iecId' => $uEducativa['iecId'], 'ieceId' => $uEducativa['ieceId'],'areaEspecialId' => $uEducativa['areaEspecialId'], 'modalidadId' => $uEducativa['modalidadId'],'momentoId' => $uEducativa['momentoId']),
                  'requestUser' => array('sie' => $form['sie'], 'gestion' => $form['gestion'])
                  //'iecId' => array('iecId' => $uEducativa['iecId'])
              ));

              //send the values to the next steps
              // $aInfoUnidadEductiva[$uEducativa['turno']]['('.$uEducativa['areaEspecial'].') '.$uEducativa['nivel']][$uEducativa['grado'].'/'.$uEducativa['programa']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa);
              /* if($uEducativa['iecLugar']){
                $aInfoUnidadEductiva[$uEducativa['turno']]['('.$uEducativa['areaEspecial'].') '.$uEducativa['nivel']][$uEducativa['grado'].'/'.$uEducativa['programa'].' ('. $uEducativa['iecLugar'] .')'][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa);
              }else{
                $aInfoUnidadEductiva[$uEducativa['turno']]['('.$uEducativa['areaEspecial'].') '.$uEducativa['nivel']][$uEducativa['grado'].'/'.$uEducativa['programa']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa);
              } */
              $momento = '';
              if($uEducativa['momentoId']!=99)
                $momento = ' ('.$uEducativa['momento'].')';

              $especialidad = '';
              $cont=1;
              if($uEducativa['especialidadId']!=99){
                $especialidad = ' ('.$uEducativa['iecId'].')';
                //$especialidad = ' ('.$cont.')';
                //$cont = $cont +1;
              }

              if($uEducativa['iecLugar']){ //dump("2");
                if ($uEducativa['nivelId'] == 411){
                  $aInfoUnidadEductiva[$uEducativa['turno']]['('.$uEducativa['areaEspecial'].') '.$uEducativa['nivel']][$uEducativa['programa'].' ('. $uEducativa['iecLugar'] .')'][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa);
                }elseif($uEducativa['nivelId'] == 410){ 
                  $aInfoUnidadEductiva[$uEducativa['turno']]['('.$uEducativa['areaEspecial'].') '.$uEducativa['nivel']][$uEducativa['servicio'].' ('. $uEducativa['iecLugar'] .')'][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa);
                }else{
                  $aInfoUnidadEductiva[$uEducativa['turno']]['('.$uEducativa['areaEspecial'].') '.$uEducativa['nivel']][$uEducativa['grado'].' ('. $uEducativa['iecLugar'] .')'][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa);
                }
                
              }else{
                if ($uEducativa['nivelId'] == 411){ 
                  
                  $aInfoUnidadEductiva[$uEducativa['turno']]['('.$uEducativa['areaEspecial'].') '.$uEducativa['nivel']][$uEducativa['programa'].$momento][$uEducativa['paralelo']]  = array('infoUe' => $sinfoUeducativa);
                  
                }elseif($uEducativa['nivelId'] == 410){ 
                  $aInfoUnidadEductiva[$uEducativa['turno']]['('.$uEducativa['areaEspecial'].') '.$uEducativa['nivel']][$uEducativa['servicio']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa);
                }elseif($uEducativa['nivelId'] == 405){ //tecnica
                  //LUEGO QUITAR .especial
                  $aInfoUnidadEductiva[$uEducativa['turno']]['('.$uEducativa['areaEspecial'].'-'.$uEducativa['grado'].') '.$uEducativa['nivel']][$uEducativa['especialidad']][$uEducativa['paralelo'].$especialidad]= array('infoUe' => $sinfoUeducativa);
                }
                else{
                  $aInfoUnidadEductiva[$uEducativa['turno']]['('.$uEducativa['areaEspecial'].') '.$uEducativa['nivel']][$uEducativa['grado']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa);
                }
              }              

          }
         // dump($aInfoUnidadEductiva);die;

      } else {
          $message = 'No existe información de la Unidad Educativa para la gestión seleccionada ó Código SIE no existe ';
          $this->addFlash('warninresult', $message);
          $exist = false;
      }
      $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);
      //$objInfoAutorizadaUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaNivelAutorizado')->getInfoAutorizadaUe($form['sie'], $form['gestion']);die('krlossdfdfdfs');
      $odataUedu = $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['sie']);

      return $this->render($this->session->get('pathSystem') . ':InfoStudents:index.html.twig', array(
                  'aInfoUnidadEductiva' => $aInfoUnidadEductiva,
                  'sie' => $form['sie'],
                  'gestion' => $form['gestion'],
                  'objUe' => $objUe,
                  //'form' => $this->removeForm()->createView(),
                  'exist' => $exist,
        //          'levelAutorizados' => $objInfoAutorizadaUe,
                  'odataUedu' => $odataUedu
      ));
  }


  public function seeStudentsAction(Request $request) {

      //get the info ue
      $infoUe = $request->get('infoUe');
      $aInfoUeducativa = unserialize($infoUe);
     //dump($aInfoUeducativa);die;
      //get the values throght the infoUe
      $sie = $aInfoUeducativa['requestUser']['sie'];
      $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
      $ieceId = $aInfoUeducativa['ueducativaInfoId']['ieceId'];
      $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
      $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
      $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
      $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
      $momentoId = $aInfoUeducativa['ueducativaInfoId']['momentoId'];
      $gestion = $aInfoUeducativa['requestUser']['gestion'];
      $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
      $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
      $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];
      $nivelname = $aInfoUeducativa['ueducativaInfo']['nivel'];
      $turnoname = $aInfoUeducativa['ueducativaInfo']['turno'];
      $momento = $aInfoUeducativa['ueducativaInfo']['momento'];
      $modalidad = $aInfoUeducativa['ueducativaInfoId']['modalidadId'];
      $programa = $aInfoUeducativa['ueducativaInfoId']['programaId'];
      $servicio = $aInfoUeducativa['ueducativaInfoId']['servicioId'];
     // dump($nivel);
     // dump($programa);
     // dump($servicio);die;
     // dump($momentoId);die;
      //get db connexion
      $em = $this->getDoctrine()->getManager();
      $objArea = $em->getRepository('SieAppWebBundle:EspecialAreaTipo')->find($aInfoUeducativa['ueducativaInfoId']['areaEspecialId']);

      //get next level data
      $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
          'institucioneducativa' => $sie,
          'nivelTipo' => $nivel,
          'cicloTipo' => $ciclo,
          'gradoTipo' => $grado,
          'paraleloTipo' => $paralelo,
          'turnoTipo' => $turno,
          'gestionTipo' => $gestion
      ));

      $exist = true;
      $objStudents = array();
      $aData = array();
      //check if the data exist
      if ($objNextCurso) {
          //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($objNextCurso->getId(), '5');
          //get students list
          $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourseSpecial($sie, $gestion, $ieceId);
          $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecId' => $iecId, 'ciclo' => $ciclo, 'iecNextLevl' => $objNextCurso->getId()));
      } else {
          $message = 'No existen estudiantes inscritos...';
          $this->addFlash('warninsueall', $message);
          $exist = false;
      }

      // Para el centralizador
      $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

      $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);
      $arrDataLibreta = array();
      $arrDataLibreta['areaEspecialId'] = ($aInfoUeducativa['ueducativaInfoId']['areaEspecialId'])?$aInfoUeducativa['ueducativaInfoId']['areaEspecialId']:'';
      $arrDataLibreta['nivelId'] = ($aInfoUeducativa['ueducativaInfoId']['nivelId'])?$aInfoUeducativa['ueducativaInfoId']['nivelId']:'';
      $nivelesLibreta = array(400,401,402,408,403,404,412);
      $programasLibreta = array(7,8,25,26); //
     //dump($aInfoUeducativa['ueducativaInfoId']['programaId']);die; //escuelas mentoras = 32, areas transversales = 17
     //dump($nivel);die;
      if($gestion >2019 and $nivel <> 405){
        $arrDataLibreta['calificaciones'] = true;
      }elseif(in_array($nivel,$nivelesLibreta ) or ($nivel == 411 and (in_array($aInfoUeducativa['ueducativaInfoId']['programaId'],$programasLibreta)))){
        $arrDataLibreta['calificaciones'] = true;
      }else{
        $arrDataLibreta['calificaciones'] = false;
      }
       $programasSinNotas = array(19, 26,27,29); //No esta definido la forma de registro de las notas por tanto calificaciones=0

      if(in_array($aInfoUeducativa['ueducativaInfoId']['programaId'], $programasSinNotas)  and $gestion>2020){
          $arrDataLibreta['calificaciones'] = true;
      }
    //para visual y programas
     //dump($nivel); dump($objArea->getId()); die;
      //para ahbilitar servicios
     
      //dump($nivel);die;
      //2023 llenado de calificaciones TRIMESTRAL
       $nivelesConNotas = array(401,412,403,404);
       $programasConNotas = array(25,8); 
       
      if($gestion >= 2023 and in_array($nivel,$nivelesConNotas) or ($nivel == 411 and (in_array($aInfoUeducativa['ueducativaInfoId']['programaId'],$programasLibreta)))){
       
        $arrDataLibreta['calificaciones'] = true;
      }
      else{ 
        $arrDataLibreta['calificaciones'] = false;
      }
      // dump($arrDataLibreta);die;
      //$arrDataLibreta['calificaciones'] = true; 

      if( (in_array($nivel,$nivelesLibreta ) or ($nivel == 411 and (in_array($aInfoUeducativa['ueducativaInfoId']['programaId'],$programasLibreta)))) and $gestion>2019){
        $arrDataLibreta['libreta'] = true;
      }else{
        $arrDataLibreta['libreta'] = false;
      }    
     //dump($arrDataLibreta);die;
      //para talento y dificultades en general  - SEMESTRAL
      if(($nivel==410 or $nivel==411) and $gestion>2021 and ($objArea->getId()==7 or $objArea->getId()==6)){
        $arrDataLibreta['calificaciones'] = true;
        $arrDataLibreta['libreta'] = false;
      }
      
      //auditiva- lengua de señas, modulo o servicio lbs y programas - SEMESTRAL
      if($gestion>2022 and $objArea->getId()==1 and (($nivel==411 and in_array($programa,[19,22,41,43,44,46,39])) or ($nivel==410 and $servicio==40))){
        $arrDataLibreta['calificaciones'] = true;
      }
      //visual- servicios complementarios - SEMESTRAL
      if($gestion>2022 and $objArea->getId()==2 and ($nivel==410 and in_array($servicio,[35,36,37,38]))){
        $arrDataLibreta['calificaciones'] = true;
      }
      //visual- programa multiple - SEMESTRAL
      if($gestion>2022 and $objArea->getId()==2 and ($nivel==411 and in_array($programa,[26, 47,48]))){
        $arrDataLibreta['calificaciones'] = true;
      }
      //intelectual- atención temprana - SEMESTRAL
      if($gestion>2022 and $objArea->getId()==3 and ($nivel==409 and in_array($programa,[28])) ){
        $arrDataLibreta['calificaciones'] = true;
      }
      //intelectual- programa multilple y/o itinerarios educativos - TRIMESTRAL
      if($gestion>2022 and $objArea->getId()==3 and ($nivel==411 and in_array($programa,[37,38])) ){
        $arrDataLibreta['calificaciones'] = true;
        $arrDataLibreta['libreta'] = true;
      }
      if($gestion>2023 and $objArea->getId()==4 and ($nivel==411 and in_array($programa,[28])) ){
        $arrDataLibreta['calificaciones'] = true;
        $arrDataLibreta['libreta'] = true;
      }
      //metal-psiquica - SEMESTRAL
      if($gestion>2022 and $objArea->getId()==10  ){
        $arrDataLibreta['calificaciones'] = true;
      }

      if($nivel==410 and $gestion>2023 and $servicio==20){
        $arrDataLibreta['calificaciones'] = true;
        }
      //para bono
      //dump($objArea->getId());die;
      //dump($modalidad);die;
      //dump($nivel);die;
      $arrDataLibreta['bono'] = false;
      $areasBono=array(1,2,3,4,10,12);  
      if( $gestion>2021 and in_array($objArea->getId(), $areasBono) and $modalidad==1 and $nivel!=410){
        $arrDataLibreta['bono'] = false; //true para activar
      }
      
      // $UePlenasAddSpeciality = (in_array($sie, $arrUePlenasAddSpeciality))?true:false;

      $objRegistroConsolidacion = $em->createQueryBuilder()
        ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
        ->from('SieAppWebBundle:RegistroConsolidacion', 'rc')
        ->where('rc.unidadEducativa = :ue')
        ->andWhere('rc.gestion = :gestion')
        ->setParameter('ue',$sie)
        ->setParameter('gestion',$gestion)
        ->getQuery()
        ->getResult();
      
      $operativo_fin = false;
      if ($gestion >= 2020){
        if($objRegistroConsolidacion){
          if($objRegistroConsolidacion[0]['bim1'] > 0 and $objRegistroConsolidacion[0]['bim2'] > 0 and $objRegistroConsolidacion[0]['bim3'] > 0){
            $operativo_fin = true;
          }
        }
      } else {
        if($objRegistroConsolidacion){
          if($objRegistroConsolidacion[0]['bim1'] > 0 and $objRegistroConsolidacion[0]['bim2'] > 0 and $objRegistroConsolidacion[0]['bim3'] > 0 and $objRegistroConsolidacion[0]['bim4'] > 0){
            $operativo_fin = true;
          }
        }
      }
      //dump($arrDataLibreta['calificaciones']);die;
    //  dump($arrDataLibreta);die;
      return $this->render($this->session->get('pathSystem') . ':InfoStudents:seeStudents.html.twig', array(
        'operativo_fin' => $operativo_fin,
        'objStudents' => $objStudents,
        'sie' => $sie,
        'turno' => $turno,
        'nivel' => $nivel,
        'grado' => $grado,
        'paralelo' => $paralelo,
        'gestion' => $gestion,
        'aData' => $aData,
        'gradoname' => $gradoname,
        'paraleloname' => $paraleloname,
        'nivelname' => $nivelname,
        'turnoname' => $turnoname,
        'form' => $this->createFormStudentInscription($infoUe)->createView(),
        'infoUe' => $infoUe,
        'exist' => $exist,
        'itemsUe'=>$itemsUe,
        'ciclo'=>$ciclo,
        'operativo'=>$operativo,
        'arrDataLibreta'=> $arrDataLibreta,
        'ueducativaInfo'=> $aInfoUeducativa['ueducativaInfo'],
        'ueducativaInfoId'=> $aInfoUeducativa['ueducativaInfoId'],        
        'areaEspecial' => $objArea->getAreaEspecial()
      ));
  }
  /**
   * create form to do the massive inscription
   * @return type obj form
   */
  private function createFormStudentInscription($data) {
      return $this->createFormBuilder()
                      ->add('inscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-success', 'onclick' => 'doInscription()')))
                      ->add('infoUe', 'hidden', array('data' => $data))
                      ->getForm();
  }
  /*OPEN The popup to do the inscription*/
  public function openInscriptionAction(Request $request){
      //get the send values
      $infoUe = $request->get('infoUe');

      return $this->render('SieEspecialBundle:InfoStudents:lookforstudent.html.twig', array(
        'form'=>$this->findStudentForm($infoUe)->createView()
      ));
  }
  /*OPEN The popup to do the inscription*/
  public function openNewInscriptionAction(Request $request){
      //get the send values
      $infoUe = $request->get('infoUe');
      $arrInfoUe = unserialize( $infoUe);

      $em = $this->getDoctrine()->getManager();
        //validation if the user is logged
      /*  if (!isset($this->userlogged)) {
            return $this->redirect($this->generateUrl('login'));
        }*/
      $enableoption = true; 
      $message = ''; 
    
        
        $arrExpedido = array();
         // this is to the new person
        $objExpedido = $em->getRepository('SieAppWebBundle:DepartamentoTipo')->findAll();

        foreach ($objExpedido as $value) {
          $arrExpedido[$value->getId()] = $value->getSigla();
        }
      $userAllowedOnwithoutCI = in_array($this->session->get('roluser'), array(7,8,10))?true:false;

      return $this->render('SieEspecialBundle:InfoStudents:newlookforstudent.html.twig', array(
          //'form'=>$this->findStudentForm($infoUe)->createView(),
          'arrExpedido'=>$objExpedido,
          'allowwithoutci' => $userAllowedOnwithoutCI,
          'enableoption' => $enableoption,
          'message' => $message,
          'dataInscription' => $arrInfoUe,
      ));
  }
  /**
  * create the form to find the student by rude
  **/
  private function findStudentForm($data){
    $form = $this->createFormBuilder()
            ->add('rudeal','text', array('label'=>'Rude', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Rude', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
            ->add('data', 'hidden', array('data'=> $data))
            ->add('find', 'button', array('label'=> 'Buscar', 'attr'=>array('class'=>'btn btn-facebook', 'onclick'=>'findStudent()')))
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
    //solo para casos de inscripciones por excepcion se valida que sea la departamental o distrital
    if($this->session->get('roluser')==9){ 
        $this->session->getFlashBag()->add('notalento', 'La Inscripción Excepcional solo esta habilitado al técnico de la Departamental o Distrital');
        return $this->render($this->session->get('pathSystem').':InfoStudents:inscriptions.html.twig', array('exist'=>false ));
      }

    //get the student info by rudeal
    $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['rudeal']));
    //check if the student exist
    if($objStudent){
      //////////////////////////ajuste temporal para casos incompletos /////////////////////////////////
      $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
      $query = $inscription2->createQueryBuilder('ei')
        ->select('ei.id as id, iec.id as iecStudentId')
        ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
        ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
        ->leftJoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
        ->where('ei.estudiante = :id')
        ->andwhere('iec.gestionTipo = :gestion')
        ->andwhere('it.id = :ietipo')
        ->andwhere('ei.estadomatriculaTipo IN (:mat)')
        ->setParameter('id', $objStudent->getId())
        ->setParameter('gestion', $dataUe['requestUser']['gestion'])
        ->setParameter('mat', array(4,5,7,68,79,78,28))
        ->setParameter('ietipo', 4)
        ->getQuery();
        $selectedInscriptionStudent = $query->getResult();
        //dump($selectedInscriptionStudent);die;
        if(count($selectedInscriptionStudent)>0){
        $objInscriptionSpecialNew = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->findOneBy(array(
        'estudianteInscripcion'=>$selectedInscriptionStudent[0]['id']));
        }
   
      if (empty($objInscriptionSpecialNew) && count($selectedInscriptionStudent)>0) {
        //dump($selectedInscriptionStudent[0]['iecStudentId']);
          $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array(
          'institucioneducativaCurso'=>$selectedInscriptionStudent[0]['iecStudentId']));

          //dump($curso);
         // dump($curso->getId());die;
          $studentInscriptionEspecial = new EstudianteInscripcionEspecial();
          $studentInscriptionEspecial->setInstitucioneducativaCursoEspecial($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->find($curso->getId()));
          $studentInscriptionEspecial->setEspecialAreaTipo($em->getRepository('SieAppWebBundle:EspecialAreaTipo')->find($curso->getEspecialAreaTipo()->getId()));
          $studentInscriptionEspecial->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($selectedInscriptionStudent[0]['id']));
          $em->persist($studentInscriptionEspecial);
          $em->flush();
      }
      /////////////////////////////////////////////////////////////////////////////////////////////////

      ///// descomentar estas dos lineas para que nadie se inscriba
      // $this->session->getFlashBag()->add('notalento', 'EL proceso de inscripción esta cerrado');
      // return $this->render($this->session->get('pathSystem').':InfoStudents:inscriptions.html.twig', array('exist'=>false ));
      ///////fin de validacion de inscripcion
      
      if($dataUe['ueducativaInfoId']['areaEspecialId']==7) {
        $estudianteTalento = $em->getRepository('SieAppWebBundle:EstudianteTalento')->findOneBy(array('estudiante' => $objStudent->getId()));
        if (empty($estudianteTalento)) {
            $this->session->getFlashBag()->add('notalento', 'El Estudiante no está registrado como Talento Extraordinario');
            return $this->render($this->session->get('pathSystem').':InfoStudents:inscriptions.html.twig', array(
                'exist'=>false
            ));
        }
      }
      /*
      else { //191123 esto comentar cuando sea etapa de inscripcuibm, solo se habilito para TALENTO por HR 54332/2023 
       $this->session->getFlashBag()->add('notalento', 'EL proceso de inscripción solo esta habilitado para Talento Extraordinario');
          return $this->render($this->session->get('pathSystem').':InfoStudents:inscriptions.html.twig', array(
              'exist'=>false
        ));
      }
     */
 
      $listaprogramas = array(7,8,9,10,11,14,15,16);
      if($dataUe['requestUser']['gestion'] >= 2022){
        $listaprogramas = array(7,8,25,29,26,12);
      }
     
      if($dataUe['ueducativaInfoId']['areaEspecialId'] == 2 and $dataUe['ueducativaInfoId']['programaId'] == 12) {
        $inscriptionvisual = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $inscriptionvisual->createQueryBuilder('ei')
          ->select('ei.id as id, iec.id as iecStudentId')
          ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
          ->leftjoin('SieAppWebBundle:InstitucioneducativaCursoEspecial', 'iece', 'WITH', 'iece.institucioneducativaCurso=iec.id')
          ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
          ->where('ei.estudiante = :id')
          ->andwhere('iec.gestionTipo = :gestion')
          ->andwhere('ei.estadomatriculaTipo IN (:mat)')
          ->andwhere('iece.especialProgramaTipo IN (:prog) or iec.nivelTipo = :nivel')
          ->setParameter('id', $objStudent->getId())
          ->setParameter('gestion', $dataUe['requestUser']['gestion'])
          ->setParameter('mat', array(4,79,68,7,80))
          ->setParameter('prog', $listaprogramas)
          ->setParameter('nivel', 405)
          ->getQuery();

        $inscripcion = $query->getResult();

        if (empty($inscripcion)) {
          $this->session->getFlashBag()->add('noinscription', 'Estudiante no inscrito, pues debe contar con una inscripcion en algún otro programa');
            return $this->render($this->session->get('pathSystem').':InfoStudents:inscriptions.html.twig', array(
                'exist'=>false
            ));

        }
      }
      $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
      $query = $inscription2->createQueryBuilder('ei')
        ->select('ei.id as id, iec.id as iecStudentId')
        ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
        ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
        ->leftJoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
        ->where('ei.estudiante = :id')
        ->andwhere('iec.gestionTipo = :gestion')
        // ->andwhere('it.id = :ietipo')
        ->andwhere('ei.estadomatriculaTipo IN (:mat)')
        ->setParameter('id', $objStudent->getId())
        ->setParameter('gestion', $dataUe['requestUser']['gestion'])
        ->setParameter('mat', array(4,5,68,79,78,28))
        //->setParameter('mat2', '5')
        // ->setParameter('ietipo', 1)
        ->getQuery();


      $selectedInscriptionStudent = $query->getResult();
      //dump($selectedInscriptionStudent,$dataUe);die;
      if($selectedInscriptionStudent){
        $objInscriptionSpecialNew = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->findOneBy(array(
            'estudianteInscripcion'=>$selectedInscriptionStudent[0]['id']
        ));
        //check if the student has an inscription on this year sesion->get('ie_gestion');
        //$selectedInscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionStudentByYear($objStudent->getId(), $dataUe['requestUser']['gestion'],$dataUe['ueducativaInfoId']['iecId']);
        if($selectedInscriptionStudent[0]['iecStudentId']!=$dataUe['ueducativaInfoId']['iecId']){
          //check if the level and grado is correct to the student//the next step is do it
            $objStudentInscriptions = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionAlternativaStudent($objStudent->getId());

            return $this->render($this->session->get('pathSystem').':InfoStudents:inscriptions.html.twig', array(
              'objStudent'=>$objStudent,
              'objStudentInscriptions'=>$objStudentInscriptions,
              'form'=>$this->doInscriptionForm($form['data'], $objStudent->getId())->createView(),
              'exist'=>true

            ));
        }else{
          //the student has an inscription on the same level
          $this->session->getFlashBag()->add('noinscription', 'Estudiante ya cuenta con inscripcion...');
          return $this->render($this->session->get('pathSystem').':InfoStudents:inscriptions.html.twig', array(
            'exist'=>false
          ));
        }


      }else{
        //check if the level and grado is correct to the student//the next step is do it
          $objStudentInscriptions = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionAlternativaStudent($objStudent->getId());
          return $this->render($this->session->get('pathSystem').':InfoStudents:inscriptions.html.twig', array(
            'objStudent'=>$objStudent,
            'objStudentInscriptions'=>$objStudentInscriptions,
            'form'=>$this->doInscriptionForm($form['data'], $objStudent->getId())->createView(),
            'exist'=>true

          ));
      }
    }else{
      //the student doesn't exist
      $this->session->getFlashBag()->add('noinscription', 'Estudiante no registrado');
      return $this->render($this->session->get('pathSystem').':InfoStudents:inscriptions.html.twig', array(
        'exist'=>false
      ));
    }

  }//end function
  /**
  * form todo the inscription
  **/
  private function doInscriptionForm($data, $studentId){
    $form = $this->createFormBuilder()
    //->add('caseespecial', 'checkbox', array('label'=>'Validacion Especial', 'attr'=>array('class'=>'form-control', 'checked'=>false ) ))
    ->add('data', 'hidden', array('data'=> $data))
    ->add('studentId', 'hidden', array('data'=> $studentId))
    ->add('inscription', 'button', array('label'=> 'Inscribir', 'attr'=>array('class'=>'btn btn-success','data-placement'=>'top', 'onclick'=>'doInscription()')))
    ->getForm();
    return $form;
  }

  /**
  * methdo to save the new inscription
  **/
  public function saveInscriptionAction(Request $request){ 
    $em = $this->getDoctrine()->getManager();
    $em->getConnection()->beginTransaction();
    //get the send values
    $form = $request->get('form');
    $aInfoUeducativa = unserialize($form['data']);

    $sie = $aInfoUeducativa['requestUser']['sie'];
    $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
    $ieceId = $aInfoUeducativa['ueducativaInfoId']['ieceId'];
    $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
    $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
    $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
    $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
    $gestion = $aInfoUeducativa['requestUser']['gestion'];
    $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
    $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
    $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];
    $nivelname = $aInfoUeducativa['ueducativaInfo']['nivel'];
    $turnoname = $aInfoUeducativa['ueducativaInfo']['turno'];
  //set the validate year
    $id_usuario = $this->session->get('userId');
 
 // die;
    //create the conexion DB
    try {
      //restart the id on estudiante_inscripcion table
      $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
      $query->execute();
      //do the inscription to the student
      $studentInscription = new EstudianteInscripcion();
      $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($aInfoUeducativa['requestUser']['sie']));
      $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($aInfoUeducativa['requestUser']['gestion']));
      //extemporaneos especial
      //$studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(7)); extemporaneo
      $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(68));
      $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['studentId']));
      $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
      $studentInscription->setObservacion(1);
      $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
      $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
      $studentInscription->setUsuarioId($id_usuario);
      $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($aInfoUeducativa['ueducativaInfoId']['iecId']));
      //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
      $studentInscription->setCodUeProcedenciaId(0);
      $em->persist($studentInscription);
      $em->flush();

      $studentInscriptionEspecial = new EstudianteInscripcionEspecial();
      $studentInscriptionEspecial->setInstitucioneducativaCursoEspecial($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->find($aInfoUeducativa['ueducativaInfoId']['ieceId']));
      $studentInscriptionEspecial->setEspecialAreaTipo($em->getRepository('SieAppWebBundle:EspecialAreaTipo')->find($aInfoUeducativa['ueducativaInfoId']['areaEspecialId']));
      $studentInscriptionEspecial->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
      $em->persist($studentInscriptionEspecial);
      $em->flush();
      //to do the submit data into DB
      //do the commit in DB
      //$em->getConnection()->commit();
      //verificamos asignaturas
      
      //if doesnt have areas we'll fill these
     
          $objAreas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array(
              'insitucioneducativaCurso' => $aInfoUeducativa['ueducativaInfoId']['iecId']
          ));
         
        if(count($objAreas)>0){// dump($studentInscription->getId());die;
          foreach ($objAreas as $areas) {
              //print_r($areas->getAsignaturaTipo()->getId());
              $estInscripcion = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion'=>$studentInscription->getId(),'institucioneducativaCursoOferta'=>$areas->getId()));
              if(!$estInscripcion){
                $studentAsignatura = new EstudianteAsignatura();
                $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
                $studentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
                $studentAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($areas->getId()));
                $studentAsignatura->setFechaRegistro(new \DateTime('now'));
                $em->persist($studentAsignatura);
                $em->flush();
                
              }
          }
        }
        $em->getConnection()->commit();
      
      
      $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito');
      // Para el centralizador
      $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

      $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);

      
      $arrDataLibreta = array();
      $arrDataLibreta['areaEspecialId'] = ($aInfoUeducativa['ueducativaInfoId']['areaEspecialId'])?$aInfoUeducativa['ueducativaInfoId']['areaEspecialId']:'';
      $arrDataLibreta['nivelId'] = ($aInfoUeducativa['ueducativaInfoId']['nivelId'])?$aInfoUeducativa['ueducativaInfoId']['nivelId']:'';
      $nivelesLibreta = array(400,401,402,403,404, 408);
      $programasLibreta = array(7,8,9,12,14,15,25);
      if($gestion >2019 and $nivel <> 405){
        $arrDataLibreta['calificaciones'] = true;
      }elseif(in_array($nivel,$nivelesLibreta ) or ($nivel == 411 and (in_array($aInfoUeducativa['ueducativaInfoId']['programaId'],$programasLibreta)))){
        $arrDataLibreta['calificaciones'] = true;
      }else{
        $arrDataLibreta['calificaciones'] = false;
      }
      
      if((in_array($nivel,$nivelesLibreta ) or ($nivel == 411 and (in_array($aInfoUeducativa['ueducativaInfoId']['programaId'],$programasLibreta)))) and $gestion>2019){
        $arrDataLibreta['libreta'] = true;
      }else{
        $arrDataLibreta['libreta'] = false;
      }
      //reload the students list
      $exist = true;
      $objStudents = array();

      $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourseSpecial($aInfoUeducativa['requestUser']['sie'], $aInfoUeducativa['requestUser']['gestion'],$aInfoUeducativa['ueducativaInfoId']['ieceId']);
      $dataUe=(unserialize($form['data']));
      $objArea = $em->getRepository('SieAppWebBundle:EspecialAreaTipo')->find($aInfoUeducativa['ueducativaInfoId']['areaEspecialId']);

      $objRegistroConsolidacion = $em->createQueryBuilder()
        ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
        ->from('SieAppWebBundle:RegistroConsolidacion', 'rc')
        ->where('rc.unidadEducativa = :ue')
        ->andWhere('rc.gestion = :gestion')
        ->setParameter('ue',$sie)
        ->setParameter('gestion',$gestion)
        ->getQuery()
        ->getResult();
      
      $operativo_fin = false;
      if ($gestion >= 2020){
        if($objRegistroConsolidacion){
          if($objRegistroConsolidacion[0]['bim1'] > 0 and $objRegistroConsolidacion[0]['bim2'] > 0 and $objRegistroConsolidacion[0]['bim3'] > 0){
            $operativo_fin = true;
          }
        }
      } else {
        if($objRegistroConsolidacion){
          if($objRegistroConsolidacion[0]['bim1'] > 0 and $objRegistroConsolidacion[0]['bim2'] > 0 and $objRegistroConsolidacion[0]['bim3'] > 0 and $objRegistroConsolidacion[0]['bim4'] > 0){
            $operativo_fin = true;
          }
        }
      }
 
      return $this->render($this->session->get('pathSystem') . ':InfoStudents:seeStudents.html.twig', array(
        'operativo_fin' => $operativo_fin,
        'objStudents' => $objStudents,
        'sie' => $sie,
        'turno' => $turno,
        'nivel' => $nivel,
        'grado' => $grado,
        'paralelo' => $paralelo,
        'gestion' => $gestion,
        'infoUe' => $form['data'],
        'aData' => $form['data'],
        'gradoname' => $gradoname,
        'paraleloname' => $paraleloname,
        'nivelname' => $nivelname,
        'turnoname' => $turnoname,
        'form' => $this->createFormStudentInscription($form['data'])->createView(),
        'exist' => $exist,
        'itemsUe'=>$itemsUe,
        'ciclo'=>$ciclo,
        'operativo'=>$operativo,
        'areaEspecial' => $objArea->getAreaEspecial(),
        'arrDataLibreta'=> $arrDataLibreta,
        'ueducativaInfo'=> $aInfoUeducativa['ueducativaInfo'],
        'ueducativaInfoId'=> $aInfoUeducativa['ueducativaInfoId']        
        // 'UePlenasAddSpeciality' => $UePlenasAddSpeciality
      ));

    } catch (Exception $e) {
      $em->getConnection()->rollback();
      echo 'Excepción capturada: ', $ex->getMessage(), "\n";
    }
  }

  public function removeInscriptionAction(Request $request) {

      //create new connection
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the info ue
      $infoUe = $request->get('infoUe');
      $aInfoUeducativa = unserialize($infoUe);
      $estInsEspId = $request->get('estInsEspId');
      $estInsId = $request->get('estInsId');

      try {
        //remove observaciones
        $objInscriptionObservacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionObservacion')->findby(array('estudianteInscripcion'=>$estInsId));
        if($objInscriptionObservacion){
          foreach($objInscriptionObservacion as $value) {
            $em->remove($value);
            $em->flush();
          }
        }

        $objEstAsig = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findby(array('estudianteInscripcion'=>$estInsId));
        foreach ($objEstAsig as $key => $value) {
          $objNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura' => $value));
          foreach ($objNota as $element) {
              $em->remove($element);
          }
          $em->flush();
          # remove asignaturas
          $em->remove($value);
        }
        $em->flush();

        /**
         * Eliminamos los registros de nota cualitativa aux
         */
        // $query = $em->getConnection()->prepare("
        //      DELETE FROM __estudiante_nota_cualitativa_aux WHERE estudiante_inscripcion_id = " . $estInsId . "
        //  ");
        //  $query->execute();


        $objNotaC = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->findBy(array('estudianteInscripcion' => $estInsId));
        foreach ($objNotaC as $element) {
            $em->remove($element);
        }
        $em->flush();

        //remove inscription
        $objInscriptionSpecial = $em->getRepository('SieAppWebBundle:EstudianteInscripcionEspecial')->find($estInsEspId);
        if($objInscriptionSpecial){
          $em->remove($objInscriptionSpecial);
          $em->flush();
        }

        //step 4 delete socio economico data
        // $objSocioEco = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->findBy(array('estudianteInscripcion' => $estInsEspId ));
        // foreach ($objSocioEco as $element) {
        //     $em->remove($element);
        // }
        // $em->flush();


        //step 5 delete apoderado_inscripcion data
        $objApoIns = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $estInsId ));

        foreach ($objApoIns as $element) {
            $objApoInsDat = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findBy(array('apoderadoInscripcion' => $element->getId()));
            foreach ($objApoInsDat as $element1){
                $em->remove($element1);
            }
            $em->remove($element);
        }
        $em->flush();

        //paso 6 borrando apoderados
        $apoderados = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $estInsId ));
        foreach ($apoderados as $element) {
            $em->remove($element);
        }
        $em->flush();

        //borrando rude
        $rudes = $em->getRepository('SieAppWebBundle:Rude')->findBy(array('estudianteInscripcion' => $estInsId ));
        foreach ($rudes as $element) {
            $em->remove($element);
        }
        $em->flush();
        //paso 7 borrando apoderados
        // $objEstudianteInscripcionSocioeconomicoRegular = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->findOneBy(array('estudianteInscripcion' => $estInsId ));
        // if($objEstudianteInscripcionSocioeconomicoRegular){
        //   $objEstudianteInscripcionSocioeconomicoRegNacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegNacion')->findBy(array(
        //     'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
        //   ));
        //   foreach ($objEstudianteInscripcionSocioeconomicoRegNacion as $element) {
        //       $em->remove($element);
        //   }
        //   $em->flush();

        //   $objEstudianteInscripcionSocioeconomicoRegInternet = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternet')->findBy(array(
        //     'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
        //   ));
        //   foreach ($objEstudianteInscripcionSocioeconomicoRegInternet as $element) {
        //       $em->remove($element);
        //   }
        //   $em->flush();

        //   $objEstudianteInscripcionSocioeconomicoRegHablaFrec = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegHablaFrec')->findBy(array(
        //     'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
        //   ));
        //   foreach ($objEstudianteInscripcionSocioeconomicoRegHablaFrec as $element) {
        //       $em->remove($element);
        //   }
        //   $em->flush();

        //   $em->remove($objEstudianteInscripcionSocioeconomicoRegular);
        //   $em->flush();
        // }

        //eliminados los datos de la tabla bjp_apoderado_inscripcion 
        $bjpApoderadoInscripcion = $em->getRepository('SieAppWebBundle:BjpApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $estInsId ));
        foreach ($bjpApoderadoInscripcion as $element)
        {
            $em->remove($element);
        }
        $em->flush();

        $objInscription = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estInsId);
        if($objInscription){
          $em->remove($objInscription);
          $em->flush();
        }

        $em->getConnection()->commit();

      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }


      //get the values throght the infoUe
      $sie = $aInfoUeducativa['requestUser']['sie'];
      $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
      $ieceId = $aInfoUeducativa['ueducativaInfoId']['ieceId'];
      $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
      $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
      $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
      $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
      $gestion = $aInfoUeducativa['requestUser']['gestion'];
      $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
      $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
      $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];
      $nivelname = $aInfoUeducativa['ueducativaInfo']['nivel'];
      $turnoname = $aInfoUeducativa['ueducativaInfo']['turno'];
      //get db connexion
      $em = $this->getDoctrine()->getManager();


      //get next level data
      $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
          'institucioneducativa' => $sie,
          'nivelTipo' => $nivel,
          'cicloTipo' => $ciclo,
          'gradoTipo' => $grado,
          'paraleloTipo' => $paralelo,
          'turnoTipo' => $turno,
          'gestionTipo' => $gestion
      ));

      $exist = true;
      $objStudents = array();
      $aData = array();
      //check if the data exist
      if ($objNextCurso) {
          //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($objNextCurso->getId(), '5');
          //get students list
          $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourseSpecial($sie, $gestion, $ieceId);
          $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecId' => $iecId, 'ciclo' => $ciclo, 'iecNextLevl' => $objNextCurso->getId()));
      } else {
          $message = 'No existen estudiantes inscritos...';
          $this->addFlash('warninsueall', $message);
          $exist = false;
      }

      $arrDataLibreta = array();
      $arrDataLibreta['areaEspecialId'] = ($aInfoUeducativa['ueducativaInfoId']['areaEspecialId'])?$aInfoUeducativa['ueducativaInfoId']['areaEspecialId']:'';
      $arrDataLibreta['nivelId'] = ($aInfoUeducativa['ueducativaInfoId']['nivelId'])?$aInfoUeducativa['ueducativaInfoId']['nivelId']:'';
      $nivelesLibreta = array(400,401,402,403,404,408);
      $programasLibreta = array(7,8,9,12,14,15,25);
      
      if($gestion >2019 and $nivel <> 405){
        $arrDataLibreta['calificaciones'] = true;
      }elseif(in_array($nivel,$nivelesLibreta ) or ($nivel == 411 and (in_array($aInfoUeducativa['ueducativaInfoId']['programaId'],$programasLibreta)))){
        $arrDataLibreta['calificaciones'] = true;
      }else{
        $arrDataLibreta['calificaciones'] = false;
      }
      
      if((in_array($nivel,$nivelesLibreta ) or ($nivel == 411 and (in_array($aInfoUeducativa['ueducativaInfoId']['programaId'],$programasLibreta)))) and $gestion>2019){
        $arrDataLibreta['libreta'] = true;
      }else{
        $arrDataLibreta['libreta'] = false;
      }      

      // Para el centralizador
      $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];
      $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);
      // $UePlenasAddSpeciality = (in_array($sie, $arrUePlenasAddSpeciality))?true:false;
      $objArea = $em->getRepository('SieAppWebBundle:EspecialAreaTipo')->find($aInfoUeducativa['ueducativaInfoId']['areaEspecialId']);

      $objRegistroConsolidacion = $em->createQueryBuilder()
        ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
        ->from('SieAppWebBundle:RegistroConsolidacion', 'rc')
        ->where('rc.unidadEducativa = :ue')
        ->andWhere('rc.gestion = :gestion')
        ->setParameter('ue',$sie)
        ->setParameter('gestion',$gestion)
        ->getQuery()
        ->getResult();
      
      $operativo_fin = false;
      if ($gestion >= 2020){
        if($objRegistroConsolidacion){
          if($objRegistroConsolidacion[0]['bim1'] > 0 and $objRegistroConsolidacion[0]['bim2'] > 0 and $objRegistroConsolidacion[0]['bim3'] > 0){
            $operativo_fin = true;
          }
        }
      } else {
        if($objRegistroConsolidacion){
          if($objRegistroConsolidacion[0]['bim1'] > 0 and $objRegistroConsolidacion[0]['bim2'] > 0 and $objRegistroConsolidacion[0]['bim3'] > 0 and $objRegistroConsolidacion[0]['bim4'] > 0){
            $operativo_fin = true;
          }
        }
      }

      return $this->render($this->session->get('pathSystem') . ':InfoStudents:seeStudents.html.twig', array(
        'operativo_fin' => $operativo_fin,
        'objStudents' => $objStudents,
        'sie' => $sie,
        'turno' => $turno,
        'nivel' => $nivel,
        'grado' => $grado,
        'paralelo' => $paralelo,
        'gestion' => $gestion,
        'aData' => $aData,
        'gradoname' => $gradoname,
        'paraleloname' => $paraleloname,
        'nivelname' => $nivelname,
        'turnoname' => $turnoname,
        'form' => $this->createFormStudentInscription($infoUe)->createView(),
        'infoUe' => $infoUe,
        'exist' => $exist,
        'itemsUe'=>$itemsUe,
        'ciclo'=>$ciclo,
        'operativo'=>$operativo,
        'areaEspecial' => $objArea->getAreaEspecial(),
        'arrDataLibreta'=> $arrDataLibreta,
        'ueducativaInfo'=> $aInfoUeducativa['ueducativaInfo'],
        'ueducativaInfoId'=> $aInfoUeducativa['ueducativaInfoId'],   
        // 'UePlenasAddSpeciality' => $UePlenasAddSpeciality
      ));
  }

  public function getCertificadoDiscapacidadAction(Request $request) {
    $em = $this->getDoctrine()->getManager();
    $idEstudiante = $request->get("idEstudiante");
    $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante);
    $estudianteDiscapacidadCertificado = $em->getRepository('SieAppWebBundle:EstudianteDiscapacidadCertificado')->findOneBy(array("estudiante" => $idEstudiante));
    $tieneCertificado = true;
    $mensaje = null;
    $certificados = null;

    if ($estudianteDiscapacidadCertificado) {
      $certificados = json_decode($estudianteDiscapacidadCertificado->getCertificados(), true);
    } else {
      $estadoServicioDiscapacidad = $this->get('sie_app_web.agetic')->estadoServicioDiscapacidad();
      if($estadoServicioDiscapacidad) {
        $certificadoDiscapacidad = $this->get('sie_app_web.agetic')->buscarCertificadoDiscapacidad($estudiante->getCarnetIdentidad());
        if($certificadoDiscapacidad["codigo"] === 1) {
          $datos = $certificadoDiscapacidad["datos"];
          $estudianteDiscapacidadCertificado = new EstudianteDiscapacidadCertificado();
          $estudianteDiscapacidadCertificado->setDepartamentoRegistro($datos["departamentoRegistro"]);
          $estudianteDiscapacidadCertificado->setCedulaIdentidad($datos["cedulaIdentidad"]);
          $estudianteDiscapacidadCertificado->setPaterno($datos["apellidoPaterno"]);
          $estudianteDiscapacidadCertificado->setMaterno($datos["apellidoMaterno"]);
          $estudianteDiscapacidadCertificado->setNombre($datos["nombres"]);
          $estudianteDiscapacidadCertificado->setFechaNacimiento($datos["fechaNacimiento"]);
          $estudianteDiscapacidadCertificado->setSexo($datos["sexo"]);
          $estudianteDiscapacidadCertificado->setCelular($datos["celular"]);
          $estudianteDiscapacidadCertificado->setDireccion($datos["direccion"]);
          $estudianteDiscapacidadCertificado->setCertificados(json_encode($datos["certificados"]));
          $estudianteDiscapacidadCertificado->setEsValidado(1);
          $estudianteDiscapacidadCertificado->setFechaRegistro(new \DateTime('now'));
          $estudianteDiscapacidadCertificado->setEstudiante($estudiante);
          $em->persist($estudianteDiscapacidadCertificado);
          $em->flush();
          $certificados = json_decode($estudianteDiscapacidadCertificado->getCertificados(), true);
        } else {
          $tieneCertificado = false;
        }
      } else {
        $mensaje = "La verificación de Certificados de Discapacidad se encuentra temporalmente fuera de servicio.";
      }
    }

    return $this->render('SieEspecialBundle:InfoStudents:certificado_discapacidad.html.twig', array(
      'idEstudiante' => $idEstudiante,
      'estudiante' => $estudiante,
      'estudianteDiscapacidadCertificado' => $estudianteDiscapacidadCertificado,
      'certificados' => $certificados,
      'tieneCertificado' => $tieneCertificado,
      'mensaje' => $mensaje
    ));
  }

public function checksegipstudentAction(Request $request){
  //dumP($request);die;
      //ini vars
      $response = new JsonResponse();
      $em = $this->getDoctrine()->getManager();
      //get send values
      $carnet = trim($request->get('cifind'));
      $complemento = trim($request->get('complementofind'));
      $fecNac = trim($request->get('fecnacfind'));
      $paterno = trim($request->get('paterno'));
      $materno = trim($request->get('materno'));
      $nombre = trim($request->get('nombre'));
      $withoutcifind = ($request->get('withoutcifind')=='false')?false:true;
      $expedidoIdfind = $request->get('expedidoIdfind');
      $arrGenero = array();
      $arrPais = array();
      $arrStudentExist = false;
      $studentId = false;
      $existStudent = '';
      // check if the inscription is by ci or not
      // list($day, $month, $year) = explode('-', $fecNac);
      $arrayCondition['paterno'] = mb_strtoupper($paterno,'utf-8');
      $arrayCondition['materno'] = mb_strtoupper($materno,'utf-8');
      $arrayCondition['nombre']  = mb_strtoupper($nombre,'utf-8');
      $arrayCondition['fechaNacimiento'] = new \DateTime(date("Y-m-d", strtotime($fecNac))) ;
      if($withoutcifind){ 
        // find the student by arrayCondition
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findBy($arrayCondition);
        $existStudent = false;
        if(sizeof($objStudent)>0){
          $existStudent=true;       
        }
        $answerSegip = true;
      }else{
        $arrayCondition['carnetIdentidad'] = $carnet;
        if($complemento){
          $arrayCondition['complemento'] = $complemento;
        }else{
          $arrayCondition['complemento'] = "";
        }
        // find the student by arrayCondition
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->findBy($arrayCondition);
        // dump($objStudent);die;
        $existStudent = false;
        if(sizeof($objStudent)>0){
          $existStudent=true;       
        }
        if(!$existStudent){
          // to do the segip validation
            $arrParametros = array(
                'complemento'=>$complemento,
                'primer_apellido'=>$paterno,
                'segundo_apellido'=>$materno,
                'nombre'=>$nombre,
                'fecha_nacimiento'=>$fecNac
              );
              
          $answerSegip = $this->get('sie_app_web.segip')->verificarPersonaPorCarnet( $carnet,$arrParametros,'prod', 'academico');
        }
      }
        // check if the student exists
        if(!$existStudent){
              // check if the data person is true
              if($answerSegip){
                // validate the year old on the student
                $arrYearStudent =$this->get('funciones')->getTheCurrentYear($fecNac, '30-6-'.date('Y'));
                $yearStudent = $arrYearStudent['age'];
                // check if the student is on 5 - 8 years old
                 $arrValidationYearOld = in_array($this->session->get('roluser'), array(7,8,10))?array(4,5,6,7,8,9,10,11,12,13,14,15,16,17,18):array(4,5,6);
                 $dataGenderAndCountry = $this->getGenderAndCountry();
                          $arrGenero = $dataGenderAndCountry['gender'];
                          $arrPais   = $dataGenderAndCountry['country'];
                          $status = 'success';
                          $code = 200;
                          $message = "Estudiante cumple con los requerimientos!!!";
                          $swcreatestudent = true; 
                 //if($yearStudent<=8 && $yearStudent>=4){
                 /*if(in_array( $yearStudent, $arrValidationYearOld )){
                          
                          $dataGenderAndCountry = $this->getGenderAndCountry();
                          $arrGenero = $dataGenderAndCountry['gender'];
                          $arrPais   = $dataGenderAndCountry['country'];
                          $status = 'success';
                          $code = 200;
                          $message = "Estudiante cumple con los requerimientos!!!";
                          $swcreatestudent = true; 


                      }else{
                        $status = 'error';
                  $code = 400;
                  $message = "Estudiante no cumple con la edad requerida";
                  $swcreatestudent = false; 
                      }*/
              }else{
            $status = 'error';
            $code = 400;
            $message = "Estudiante no cumple con la validacion segip";
            $swcreatestudent = false; 
              }

        }else{

          $arrStudentExist = array();
          if(sizeof($objStudent)>0){
            foreach ($objStudent as $value) {
              $arrStudentExist[] = array(
                'paterno'=>$value->getPaterno(),
                'materno'=>$value->getMaterno(),
                'nombre'=>$value->getNombre(),
                'carnet'=>$value->getCarnetIdentidad(),
                'complemento'=>$value->getComplemento(),
                'fecNac'=>$value->getFechaNacimiento()->format('d-m-Y') ,
                'rude'=>$value->getCodigoRude() ,
                'idStudent'=>$value->getId() ,
                'articuleten'=>0 ,
              );
            }
            
          }

          // $studentId = $objStudent->getId();
          $existStudent = true;

          $status = 'error';
          $code = 400;
          $message = "Estudiante ya cuenta con registro codigo RUDE";
          $swcreatestudent = false; 

        }
    
       $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swcreatestudent' => $swcreatestudent,    
        'arrGenero' => $arrGenero,    
        'arrPais' => $arrPais,    
        'arrStudentExist' => $arrStudentExist,    
        'existStudent' => $existStudent,    
        'swhomonimo' => $withoutcifind,

        
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;

      die;
    }

    public function getGenderAndCountry(){
        $em = $this->getDoctrine()->getManager();
             // get genero data
       $objGenero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
        foreach ($objGenero as $value) {
            if($value->getId()<3){
                $arrGenero[] = array('generoId' => $value->getId(),'genero' => $value->getGenero());
            }
        }
        $arrData['gender'] = $arrGenero;

              //get pais data
         $entity = $em->getRepository('SieAppWebBundle:PaisTipo');
         $query = $entity->createQueryBuilder('pt')
                  ->orderBy('pt.pais', 'ASC')
                  ->getQuery();
          $objPais = $query->getResult();
         

        // $objPais = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll(array('pais'=>'ASC'));
        foreach ($objPais as $value) {
          $arrPais[]=array('paisId'=>$value->getId(), 'pais'=>$value->getPais());
        }
        $arrData['country'] = ($arrPais) ;

        return $arrData;
    }

    public function getDeptoAction(Request $request){
      
        $response = new JsonResponse();
        $paisId = $request->get('paisId');
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

    /**
     * todo the special inscription
     * @param Request $request
     *
     */
    public function doInscriptionAction(Request $request) {
      //dump($request);die;
      $arrDatos = json_decode($request->get('datos'), true);
      //dump($arrDatos);die;
       // ini vars
        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();        
        // get the send values
        //get info ABOUT UE
       
        $gestion = $this->session->get('currentyear') ;

        $paterno = mb_strtoupper($request->get('paterno'), 'utf-8') ;
        $materno = mb_strtoupper($request->get('materno'), 'utf-8');
        $nombre = mb_strtoupper($request->get('nombre'), 'utf-8');
       
        $fecNac = $request->get('fecnacfind');
        $genero = $request->get('generoId');
        $swnewforeign = $request->get('swnewforeign');
        $sie = $request->get('sieInsc');
        $iecId = $request->get('iecId');
        $ieceId = $request->get('ieceId');
        $areaEspecialId = $request->get('areaEspecialId');

        $withoutcifind = ($request->get('withoutcifind')=='false')?false:true;

        $swinscription=true;
            $arrStudent=array();
            $arrInscription=array();
            // check the years old validation
            if($swinscription){

                  //insert a new record with the new selected variables and put matriculaFinID like 5
                  $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($iecId);

                   try {

            
                      if($swnewforeign){    
                        // get info about ubication
                        $paisId = $request->get('paisId');
                        $localidad = $request->get('localidad');
                        $lugarNacTipoId = ($request->get('lugarNacTipoId')=='false')?'':$request->get('lugarNacTipoId');
                       
                        $lugarProvNacTipoId = ($request->get('lugarProvNacTipoId')=='false')?'':$request->get('lugarProvNacTipoId');
                       
                        $carnet = ($request->get('cifind')=='false')?'':$request->get('cifind');
                        
                        $complemento = ($request->get('complementofind')=='false')?'':$request->get('complementofind');

                        $expedidoId = $request->get('expedidoIdfind');

                        // create rude code to the student
                    
                        $query = $em->getConnection()->prepare('SELECT get_estudiante_nuevo_rude(:sie::VARCHAR,:gestion::VARCHAR)');
                        $query->bindValue(':sie', $sie);            
                        $query->bindValue(':gestion', $gestion);
                        $query->execute();
                        $codigorude = $query->fetchAll();
                        $codigoRude = $codigorude[0]["get_estudiante_nuevo_rude"];  
                        
                        // set the data person to the student table
                        $estudiante = new Estudiante();
                        // set the new student
                        $estudiante->setCodigoRude($codigoRude);               
                        $estudiante->setPaterno(mb_strtoupper($paterno, 'utf-8'));
                        $estudiante->setMaterno(mb_strtoupper($materno, 'utf-8'));
                        $estudiante->setNombre(mb_strtoupper($nombre, 'utf-8'));                        
                        $estudiante->setFechaNacimiento(new \DateTime($fecNac));            
                        $estudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($genero));
                        $estudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($paisId));
                        // check if the country is Bolivia
                        if ($paisId == '1'){                    
                            $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarNacTipoId));
                            $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find($lugarProvNacTipoId));
                            $estudiante->setLocalidadNac(mb_strtoupper($localidad, 'utf-8'));
                        }else{//no Bolivia
                            $estudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
                            $estudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find('11'));
                            $estudiante->setLocalidadNac('');
                        }

                        if(!$withoutcifind){
                          $estudiante->setCarnetIdentidad($carnet);
                          $estudiante->setComplemento(mb_strtoupper($complemento, 'utf-8'));
                          $estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find($expedidoId));
                          $estudiante->setSegipId(1);
                        }else{
                          $estudiante->setComplemento('');
                          $estudiante->setExpedido($em->getRepository('SieAppWebBundle:DepartamentoTipo')->find(0));
                        }
                        
                        $em->persist($estudiante);
                       

                        $studentId = $estudiante->getId();
                        $oldstudentCodigoRude = $estudiante->getCodigoRude();
                      }else{
                        $studentId = $request->get('idStudent');
                        $oldstudentCodigoRude = $request->get('rude');
                      }
                  //$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
                  //$query->execute();
                  //insert a new record with the new selected variables and put matriculaFinID like 5

                  //do the inscription to the student
                  $studentInscription = new EstudianteInscripcion();
                  $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie));
                  $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
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
                  $em->flush();

                  $studentInscriptionEspecial = new EstudianteInscripcionEspecial();
                  $studentInscriptionEspecial->setInstitucioneducativaCursoEspecial($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->find($ieceId));
                  $studentInscriptionEspecial->setEspecialAreaTipo($em->getRepository('SieAppWebBundle:EspecialAreaTipo')->find($areaEspecialId));
                  $studentInscriptionEspecial->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
                  $em->persist($studentInscriptionEspecial);
                  $em->flush();                  

                  $arrStudent = array(
                      'rude'=>$oldstudentCodigoRude,
                      'nombre'=>$nombre,
                      'paterno'=>$paterno,
                      'materno'=>$materno,
                      'fecNac'=>$fecNac
                  );

                  $arrInscription = array(
                      'nivelTipo' => $objCurso->getNivelTipo()->getNivel(),
                      'gradoTipo' => $objCurso->getGradoTipo()->getGrado(),
                      'paraleloTipo' => $objCurso->getParaleloTipo()->getParalelo(),
                      'turnoTipo' => $objCurso->getTurnoTipo()->getTurno(),
                      'sie' => $sie,
                      'institucioneducativa' => $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($sie)->getInstitucioneducativa() ,
                  );

                  $em->persist($studentInscription);
                  $em->flush();          

                  // Registro de materia curso oferta en el log
                  $this->get('funciones')->setLogTransaccion(
                      $studentInscription->getId(),
                      'estudiante_inscripcion',
                      'C',
                      '',
                      '',
                      '',
                      'ESPECIAL',
                      json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
                  );
                  // Try and commit the transaction
                  $em->getConnection()->commit();

                  $status = 'success';
                  $code = 200;
                  $message = "Estudiante inscripto Correctamente";
                  $swinscription = true; 

                } catch (Exception $ex) {
                    $em->getConnection()->rollback();
                    echo 'Excepción capturada: ', $ex->getMessage(), "\n";
                }

               

            }else{
              $status = 'error';
              $code = 400;
              $message = "El estudiante no cumple con los requerimientos para la INSCRIPCIÓN";
              $swinscription = false; 
            }



    $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swinscription'   => $swinscription,       
        'arrStudent'      => $arrStudent,       
        'arrInscription'  => $arrInscription,       
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;

    }

    public function goOldInscriptionAction(Request $request){
      //dump($request);die;
      $response = new JsonResponse();
      $em = $this->getDoctrine()->getManager();
      //get send values
      $carnet = $request->get('cifind');
      $complemento = $request->get('complementofind');
      $fecNac = $request->get('fecnacfind');
      $paterno = $request->get('paterno');
      $materno = $request->get('materno');
      $nombre = $request->get('nombre');
      $withoutcifind = true;
      $expedidoIdfind = $request->get('expedidoIdfind');
    
    $swnewforeign = $request->get('swnewforeign');
    $swCurrentInscription = false;
    if($swnewforeign == 0){
      
      /*validate students inscripción in current year*/
      $rude = $request->get('rude');
      //$swCurrentInscription = $this->getCurrentInscriptionsByGestoinValida($rude,$this->currentyear);
      $swCurrentInscription  = false;
    

    }else{

    }
    


      $arrGenero = array();
      $arrPais = array();
    $arrStudentExist = false;
    $existStudent = true;
    $answerSegip = true;

    if(!$swCurrentInscription){
      
      $dataGenderAndCountry = $this->getGenderAndCountry();
      $arrGenero = $dataGenderAndCountry['gender'];
      $arrPais   = $dataGenderAndCountry['country'];
      $status = 'success';
      $code = 200;
      $message = "Estudiante cumple con los requerimientos!!!";
      $swcreatestudent = true; 

      $arrStudentExist = array(
          'paterno'=>$request->get('paterno'),
          'materno'=>$request->get('materno'),
          'nombre'=>$request->get('nombre'),
          'carnet'=>$request->get('cifind'),
          'complemento'=>$request->get('complementofind'),
          'fecNac'=>$request->get('fecnacfind'),
          'rude'=>'' ,
      );
    }else{

      $status = 'error';
      $code = 200;
      $message = "ESTUDIANTE YA CUENTA CON INSCRIPCIÓN EN LA GESTION ACTUAL";
      $swcreatestudent = false; 

    }

  
       $arrResponse = array(
        'status'          => $status,
        'code'            => $code,
        'message'         => $message,
        'swcreatestudent' => $swcreatestudent,    
        'arrGenero' => $arrGenero,    
        'arrPais' => $arrPais,    
        'arrStudentExist' => $arrStudentExist,    
        'existStudent' => $existStudent,    
        'swhomonimo' => !$swCurrentInscription,  
        
      );
      
      $response->setStatusCode(200);
      $response->setData($arrResponse);

      return $response;     
        
    }  
      /*Modificacion de estado de matricula*/
  public function cambiarEstadoMatriculaAction(Request $request){
    //dump($request);die;
    $em = $this->getDoctrine()->getManager();
    $estInsId = $request->get('estInsId');
    $eslibreta = $request->get('eslibreta');
    $infoUe = $request->get('infoUe');
    //dump($estInsId);die;
    $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($estInsId);
    $estadomatriculaId = $inscripcion->getEstadomatriculaTipo()->getId();
    if($eslibreta == true){
      $emPermitidos = array(10,6,$estadomatriculaId);
    }else{
      $emPermitidos = array(6,$estadomatriculaId);
    }
    $estadosMatricula = $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findBy(array('id'=>$emPermitidos));
    $emArray = array();
    foreach($estadosMatricula as $em){
      $emArray[$em->getId()] = $em->getestadomatricula();

    }
    
    return $this->render('SieEspecialBundle:InfoStudents:cambiarEstadoMatricula.html.twig', array(
      'form'=>$this->estadoMatriculaForm($estadomatriculaId,$estInsId,$emArray,$infoUe)->createView(),
      'inscripcion' => $inscripcion,
    ));
  }
  /**
  *  Formulario de estado matricula
  **/
  private function estadoMatriculaForm($estadomatriculaId,$estInsId,$emArray,$infoUe){
    
    $form = $this->createFormBuilder()
            ->add('ieId', 'hidden', array('data'=> $estInsId))
            ->add('data', 'hidden', array('data'=> $infoUe))
            ->add('estadomatriculaTipo','choice',array('label'=>'Cambiar estado:','required'=>true,'data'=>$estadomatriculaId,'choices'=>$emArray,'attr' => array('class' => 'form-control')))
            ->add('guardar', 'button', array('label'=> 'Guardar', 'attr'=>array('class'=>'btn btn-success', 'onclick'=>'guardarEstado()')))
            ->getForm();
    return $form;
  }

  /*guardar estado de matricula*/
  public function guardarEstadoMatriculaAction(Request $request){
    //dump($request);die;
    $em = $this->getDoctrine()->getManager();
    $form = $request->get('form');
    $aInfoUeducativa = unserialize($form['data']);

    $sie = $aInfoUeducativa['requestUser']['sie'];
    $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
    $ieceId = $aInfoUeducativa['ueducativaInfoId']['ieceId'];
    $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
    $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
    $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
    $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
    $gestion = $aInfoUeducativa['requestUser']['gestion'];
    $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
    $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
    $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];
    //dump($form);die;
    $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['ieId']);
    $notas = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->createQueryBuilder('ea')
                    ->select('en')
                    ->innerJoin('SieAppWebBundle:EstudianteNota', 'en', 'with', 'en.estudianteAsignatura = ea.id')
                    ->where('ea.estudianteInscripcion='.$form['ieId'])
                    ->getQuery()
                    ->getResult();
  
  $sw = true;
  if($form['estadomatriculaTipo'] == 6 and $notas){  //NO INCORPORADO
    $sw = false;
    $this->session->getFlashBag()->add('noinscription', 'No corresponde el cambio de estado a <strong>NO INCORPORADO</strong>, pues el estudiante cuenta con calificaciones.');
  }
  if($form['estadomatriculaTipo'] == 10 and !$notas) { //RETIRO ABANDONO
    $sw = false;
    $this->session->getFlashBag()->add('noinscription', 'No corresponde el cambio de estado a <strong>RETIRADO ABANDONO</strong>, pues el estudiante no cuenta con calificaciones.');
  }
  if($sw == true){
    try {
      $em->getConnection()->beginTransaction();
      
      $inscripcion->setEstadomatriculaInicioTipo($inscripcion->getEstadomatriculaTipo());
      $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($form['estadomatriculaTipo']));
      $em->flush();
      $em->getConnection()->commit();

      $this->session->getFlashBag()->add('goodinscription', 'Se cambió el estado de matricula del estudiante');
    }catch (Exception $e){
      $em->getConnection()->rollback();
      echo 'Excepción capturada: ', $ex->getMessage(), "\n";
    }

  }
    
    // Para el centralizador
    $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

    $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);
    
    $arrDataLibreta = array();
    $arrDataLibreta['areaEspecialId'] = ($aInfoUeducativa['ueducativaInfoId']['areaEspecialId'])?$aInfoUeducativa['ueducativaInfoId']['areaEspecialId']:'';
    $arrDataLibreta['nivelId'] = ($aInfoUeducativa['ueducativaInfoId']['nivelId'])?$aInfoUeducativa['ueducativaInfoId']['nivelId']:'';
    $nivelesLibreta = array(400,401,402,408,403,404);
    $programasLibreta = array(7,8,9,12,14,15,25);
    
    if($gestion >2019 and $nivel <> 405){
      $arrDataLibreta['calificaciones'] = true;
    }elseif(in_array($nivel,$nivelesLibreta ) or ($nivel == 411 and (in_array($aInfoUeducativa['ueducativaInfoId']['programaId'],$programasLibreta)))){
      $arrDataLibreta['calificaciones'] = true;
    }else{
      $arrDataLibreta['calificaciones'] = false;
    }
    
    if((in_array($nivel,$nivelesLibreta ) or ($nivel == 411 and (in_array($aInfoUeducativa['ueducativaInfoId']['programaId'],$programasLibreta)))) and $gestion>2019){
      $arrDataLibreta['libreta'] = true;
    }else{
      $arrDataLibreta['libreta'] = false;
    }
    //reload the students list
    $exist = true;
    $objStudents = array();

    $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourseSpecial($aInfoUeducativa['requestUser']['sie'], $aInfoUeducativa['requestUser']['gestion'],$aInfoUeducativa['ueducativaInfoId']['ieceId']);
    $dataUe=(unserialize($form['data']));

    return $this->render($this->session->get('pathSystem') . ':InfoStudents:seeStudents.html.twig', array(
                'objStudents' => $objStudents,
                'sie' => $sie,
                'turno' => $turno,
                'nivel' => $nivel,
                'grado' => $grado,
                'paralelo' => $paralelo,
                'gestion' => $gestion,
                'infoUe' => $form['data'],
                'aData' => $form['data'],
                'gradoname' => $gradoname,
                'paraleloname' => $paraleloname,
                // 'nivelname' => $nivelname,
                'form' => $this->createFormStudentInscription($form['data'])->createView(),
                'exist' => $exist,
                'itemsUe'=>$itemsUe,
                'ciclo'=>$ciclo,
                'operativo'=>$operativo,
                'arrDataLibreta'=> $arrDataLibreta,
                'ueducativaInfo'=> $aInfoUeducativa['ueducativaInfo'],
                'ueducativaInfoId'=> $aInfoUeducativa['ueducativaInfoId']
                // 'UePlenasAddSpeciality' => $UePlenasAddSpeciality
    ));

  }        


}
