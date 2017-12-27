<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\EstudianteInscripcionEspecial;

class InfoStudentsController extends Controller {

  public $session;
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
      $form = $request->get('form');

      $em = $this->getDoctrine()->getManager();
      //find the levels from UE
      //levels gestion -1
      //$objLevelsOld = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getNivelBySieAndGestion($form['sie'], $form['gestion']);
      $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getInfoUeducativaEspecialBySieGestion($form['sie'], $form['gestion']);

      $exist = true;
      $aInfoUnidadEductiva = array();
      if ($objUeducativa) {
          foreach ($objUeducativa as $uEducativa) {

              //get the literal data of unidad educativa
              $sinfoUeducativa = serialize(array(
                  'ueducativaInfo' => array('nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'paralelo' => $uEducativa['paralelo'], 'turno' => $uEducativa['turno']),
                  'ueducativaInfoId' => array('paraleloId' => $uEducativa['paraleloId'], 'turnoId' => $uEducativa['turnoId'], 'nivelId' => $uEducativa['nivelId'], 'gradoId' => $uEducativa['gradoId'], 'cicloId' => $uEducativa['cicloTipoId'], 'iecId' => $uEducativa['iecId'], 'ieceId' => $uEducativa['ieceId'],'areaEspecialId' => $uEducativa['areaEspecialId']),
                  'requestUser' => array('sie' => $form['sie'], 'gestion' => $form['gestion'])
              ));

              //send the values to the next steps
              $aInfoUnidadEductiva[$uEducativa['turno']]['('.$uEducativa['areaEspecial'].') '.$uEducativa['nivel']][$uEducativa['grado'].'/'.$uEducativa['programa']][$uEducativa['paralelo']] = array('infoUe' => $sinfoUeducativa);

          }

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

      // Para el centralizador
      $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

      $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);

      $arrDataLibreta['areaEspecialId'] = ($aInfoUeducativa['ueducativaInfoId']['areaEspecialId'])?$aInfoUeducativa['ueducativaInfoId']['areaEspecialId']:'';
      $arrDataLibreta['nivelId'] = ($aInfoUeducativa['ueducativaInfoId']['nivelId'])?$aInfoUeducativa['ueducativaInfoId']['nivelId']:'';

      // $UePlenasAddSpeciality = (in_array($sie, $arrUePlenasAddSpeciality))?true:false;

      return $this->render($this->session->get('pathSystem') . ':InfoStudents:seeStudents.html.twig', array(
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
                  // 'nivelname' => $nivelname,
                  'form' => $this->createFormStudentInscription($infoUe)->createView(),
                  'infoUe' => $infoUe,
                  'exist' => $exist,
                  'itemsUe'=>$itemsUe,
                  'ciclo'=>$ciclo,
                  'operativo'=>$operativo,
                  'arrDataLibreta'=> $arrDataLibreta
                  // 'UePlenasAddSpeciality' => $UePlenasAddSpeciality
      ));
  }
  /**
   * create form to do the massive inscription
   * @return type obj form
   */
  private function createFormStudentInscription($data) {
      return $this->createFormBuilder()
                      ->add('inscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'doInscription()')))
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
  /**
  * create the form to find the student by rude
  **/
  private function findStudentForm($data){
    $form = $this->createFormBuilder()
            ->add('rudeal','text', array('label'=>'Rude', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Rude', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
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
              ->setParameter('mat', array(4,5))
              //->setParameter('mat2', '5')
              // ->setParameter('ietipo', 1)
              ->getQuery();

      $selectedInscriptionStudent = $query->getResult();
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
            ->add('inscription', 'button', array('label'=> 'Inscribir', 'attr'=>array('class'=>'btn btn-danger btn-stroke','ata-placement'=>'top', 'onclick'=>'doInscription()')))
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
  //set the validate year

    try {
      //restart the id on estudiante_inscripcion table
      $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
      $query->execute();
      //do the inscription to the student
      $studentInscription = new EstudianteInscripcion();
      $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($aInfoUeducativa['requestUser']['sie']));
      $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($aInfoUeducativa['requestUser']['gestion']));
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

      $studentInscriptionEspecial = new EstudianteInscripcionEspecial();
      $studentInscriptionEspecial->setInstitucioneducativaCursoEspecial($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->find($aInfoUeducativa['ueducativaInfoId']['ieceId']));
      $studentInscriptionEspecial->setEspecialAreaTipo($em->getRepository('SieAppWebBundle:EspecialAreaTipo')->find($aInfoUeducativa['ueducativaInfoId']['areaEspecialId']));
      $studentInscriptionEspecial->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscription->getId()));
      $em->persist($studentInscriptionEspecial);
      $em->flush();
      //to do the submit data into DB
      //do the commit in DB
      $em->getConnection()->commit();
      $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito');
      // Para el centralizador
      $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

      $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);


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
        //dump($objInscriptionObservacion);die;
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
        $objSocioEco = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->findBy(array('estudianteInscripcion' => $estInsEspId ));
        //dump($objSocioEco);die;
        foreach ($objSocioEco as $element) {
            $em->remove($element);
        }
        $em->flush();


        //step 5 delete apoderado_inscripcion data
        $objApoIns = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $estInsId ));
        //dump($objApoIns);die;

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
        //paso 7 borrando apoderados
         $objEstudianteInscripcionSocioeconomicoRegular = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegular')->findOneBy(array('estudianteInscripcion' => $estInsId ));
         if($objEstudianteInscripcionSocioeconomicoRegular){
           $objEstudianteInscripcionSocioeconomicoRegNacion = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegNacion')->findBy(array(
             'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
           ));
           foreach ($objEstudianteInscripcionSocioeconomicoRegNacion as $element) {
               $em->remove($element);
           }
           $em->flush();

           $objEstudianteInscripcionSocioeconomicoRegInternet = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegInternet')->findBy(array(
             'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
           ));
           foreach ($objEstudianteInscripcionSocioeconomicoRegInternet as $element) {
               $em->remove($element);
           }
           $em->flush();

           $objEstudianteInscripcionSocioeconomicoRegHablaFrec = $em->getRepository('SieAppWebBundle:EstudianteInscripcionSocioeconomicoRegHablaFrec')->findBy(array(
             'estudianteInscripcionSocioeconomicoRegular' => $objEstudianteInscripcionSocioeconomicoRegular->getId()
           ));
           foreach ($objEstudianteInscripcionSocioeconomicoRegHablaFrec as $element) {
               $em->remove($element);
           }
           $em->flush();

           $em->remove($objEstudianteInscripcionSocioeconomicoRegular);
           $em->flush();
         }

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

      // Para el centralizador
      $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];
      $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);
      // $UePlenasAddSpeciality = (in_array($sie, $arrUePlenasAddSpeciality))?true:false;

      return $this->render($this->session->get('pathSystem') . ':InfoStudents:seeStudents.html.twig', array(
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
                  // 'nivelname' => $nivelname,
                  'form' => $this->createFormStudentInscription($infoUe)->createView(),
                  'infoUe' => $infoUe,
                  'exist' => $exist,
                  'itemsUe'=>$itemsUe,
                  'ciclo'=>$ciclo,
                  'operativo'=>$operativo,
                  // 'UePlenasAddSpeciality' => $UePlenasAddSpeciality
      ));
  }
}
