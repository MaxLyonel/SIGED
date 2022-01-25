<?php

namespace Sie\HerramientaBundle\Controller;

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
use Sie\AppWebBundle\Entity\UsuarioGeneracionRude;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * StudentsInscriptionsWithoutRude controller.
 *
 */
class StudentsInscriptionsWithoutRudeController extends Controller {

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

        //get the send values
        $infoUe = $request->get('infoUe');

        return $this->render('SieHerramientaBundle:StudentsInscriptionsWithoutRude:lookforstudent.html.twig', array(
          'form'=>$this->findStudentForm($infoUe)->createView()
        ));
    }
    /**
    * create the form to find the student by rude
    **/
    private function findStudentForm($data){
      //set the day array
      $arrDay = array();
      for($ind =1;$ind< 32 ; $ind++){
        $arrDay[$ind]=$ind;
      }
      //set the month array
      $arrMonth = array('1'=>'Enero',
                   '2'=>'Febrero',
                   '3'=>'Marzo',
                   '4'=>'Abril',
                   '5'=>'Mayo',
                   '6'=>'Junio',
                   '7'=>'Julio',
                   '8'=>'Agosto',
                   '9'=>'Septiembre',
                   '10'=>'Octubre',
                   '11'=>'Noviembre',
                   '12'=>'Diciembre');
      //set the year array
      $arrYear = array();
      $year = date('Y');
      $cc = 0;
      while($cc < 40){
        $arrYear[$year]=$year;
        $year-=1;
        $cc++;
      }
      $form = $this->createFormBuilder()
              ->add('carnetIdentidad','text', array('label'=>'Carnet Identidad', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Carnet Identidad', 'pattern' => '[A-Za-z0-9\sñÑ]{3,10}', 'maxlength' => '10', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('paterno','text', array('label'=>'paterno', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Paterno', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('materno','text', array('label'=>'materno', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Materno', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('nombre','text', array('label'=>'nombre', 'attr'=> array('class'=>'form-control', 'placeholder' => 'Nombre', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              //->add('fechaNacimiento','text', array('label'=>'Fecha Nacimiento', 'attr'=> array('class'=>'form-control', 'data-inputmask'=>"'alias': 'date'" ,'placeholder' => 'Fecha Nacimiento', 'pattern' => '[A-Za-z0-9\sñÑ]{3,18}', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('day','choice', array('label'=>'Dia','choices'=>$arrDay, 'attr'=> array('class'=>'form-control', 'data-inputmask'=>"'alias': 'date'" ,'placeholder' => '', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('month','choice', array('label'=>'Dia','choices'=>$arrMonth, 'attr'=> array('class'=>'form-control', 'data-inputmask'=>"'alias': 'date'" ,'placeholder' => '', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
              ->add('year','choice', array('label'=>'Dia','choices'=>$arrYear, 'attr'=> array('class'=>'form-control', 'data-inputmask'=>"'alias': 'date'" ,'placeholder' => '', 'maxlength' => '50', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
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
      //dump($form);die;
      $dataUe = unserialize($form['data']);

      //get the student info by rudeal
      $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->getDataStudentByCiOrdataStudent(
                                                                                                      $form['carnetIdentidad'],
                                                                                                      mb_strtoupper($form['nombre'], 'UTF-8') ,
                                                                                                      mb_strtoupper($form['paterno'], 'UTF-8'),
                                                                                                      mb_strtoupper($form['materno'], 'UTF-8'),
                                                                                                      $form['year'].'-'.$form['month'].'-'.$form['day']
                                                                                                    );
      //check if the student exist

        return $this->render($this->session->get('pathSystem').':StudentsInscriptionsWithoutRude:inscriptions.html.twig', array(
            'objStudent'=>$objStudent,
            'form'=>$this->doInscriptionForm($form['data'])->createView(),
            'dataUe'=>$form['data'],
            'exist'=>true
          ));
    }//end function
    /**
    * form todo the inscription
    **/
    private function doInscriptionForm($data){
      $form = $this->createFormBuilder()
              ->add('data', 'hidden', array('data'=> $data))
              ->add('infoStudent', 'hidden', array())
              ->add('inscription', 'button', array('label'=> 'Inscribir Nuevo', 'attr'=>array('class'=>'btn btn-success btn-stroke','ata-placement'=>'top', 'onclick'=>'doInscription()')))
              ->getForm();
     return $form;
    }
    /**
    * methdo to save the new inscription and create the rude
    **/
    public function saveInscriptionAction(Request $request){
      //create the conexion DB
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the send values
      $form= $request->get('form');
      $aInfoUeducativa = unserialize($form['data']);
      //convert values send
      $newStudent = json_decode($form['infoStudent'], true);
      $arrInfoUe = unserialize($form['data']);

      try {
        //crete a rudeal to the student
        $digits = 4;
        $mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
        $rude = $this->session->get('ie_id') . $this->session->get('currentyear') . $mat . $this->generarRude($this->session->get('ie_id') . $this->session->get('ie_gestion') . $mat);
        

        //save the new student data

        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante');");
        $query->execute();

        $student = new Estudiante();
        $student->setPaterno(mb_strtoupper($newStudent['paterno'], 'UTF-8'));
        $student->setMaterno(mb_strtoupper($newStudent['materno'], 'UTF-8'));
        $student->setNombre(mb_strtoupper($newStudent['nombre'], 'UTF-8'));
        $student->setCarnetIdentidad($newStudent['carnetIdentidad']);
        //$student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($newStudent['generoTipo']));
        $student->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find(1));
        $student->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(2));
        $student->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->find(10));
        $student->setFechaNacimiento(new \DateTime($newStudent['fnac']));
        $student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find(rand(1,2)));
        $student->setCodigoRude($rude);
        $em->persist($student);
        $em->flush();

        $studentId = $student->getId();

        //to register the new rude and the user
        $UsuarioGeneracionRude = new UsuarioGeneracionRude();
        $UsuarioGeneracionRude->setUsuarioId($this->session->get('userId'));
        $UsuarioGeneracionRude->setFechaRegistro(new \DateTime('now'));
        $aDatosCreacion = array('sie' => $this->session->get('ie_id'), 'rude' => $rude);
        $UsuarioGeneracionRude->setDatosCreacion(serialize($aDatosCreacion));
        $em->persist($UsuarioGeneracionRude);
        $em->flush();

        //save the inscription
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
        $query->execute();
        $studentInscription = new EstudianteInscripcion();
        $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($arrInfoUe['requestUser']['sie']));
        $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($arrInfoUe['requestUser']['gestion']));
        $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
        $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($student->getId()));
        $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
        $studentInscription->setObservacion(1);
        $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
        $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
        $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($arrInfoUe['ueducativaInfoId']['iecId']));
        //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
        $studentInscription->setCodUeProcedenciaId(0);
        $em->persist($studentInscription);
        $em->flush();
        //to do the submit data into DB
        //do the commit in DB
        $em->getConnection()->commit();
        $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito');

        //reload the students list
        $exist = true;
        $objStudents = array();
        $dataUe=(unserialize($form['data']));
        $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($aInfoUeducativa['ueducativaInfoId']['iecId']);

        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:seeStudents.html.twig', array(
                        'objStudents' => $objStudents,
                        'form' => $this->createFormStudentInscription($form['data'])->createView(),
                        'exist' => $exist,
                        'infoUe' => $form['data'],
                        'aData' => $form['data'],
                        'dataUe'=> $dataUe['ueducativaInfo'],
                        'paraleloname' => $aInfoUeducativa['ueducativaInfo']['paralelo'],
                        'gradoname' => $aInfoUeducativa['ueducativaInfo']['grado']
        ));
      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }
    }

    /**
     * create form to do the inscription
     * @return type obj form
     */
    private function createFormStudentInscription($data) {
        return $this->createFormBuilder()
                        ->add('inscription', 'button', array('label' => 'Inscribir', 'attr' => array('class' => 'btn btn-primary', 'onclick' => 'doInscription()')))
                        ->add('infoUe', 'text', array('data' => $data))
                        ->getForm();
    }


    /**
     * generate the new rude to the new student
     * @param type $cadena
     * @return type
     */
    private function generarRude($cadena) {
        $codigoRude = "";
        $codigoVerificacion = "123456789A0";
        $peso = 2;
        $sum = 0;
        $int = 0;
        while ($int < strlen($cadena)) {
            if ($peso == 7)
                $peso = 2;
            $sum = $sum + ($peso * ord(substr($cadena, $int, 1)));
            $peso = $peso + 1;
            $int = $int + 1;
        }
        return substr($codigoVerificacion, 10 - ($sum % 11), 1);
    }


    /**
    * methdo to save the new inscription
    **/
    public function saveInscriptionStudentAction(Request $request){

      //create the conexion DB
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the send values
      //get the send values
      $idStudent = $request->get('idStudent');
      $arrdataUe = $request->get('dataUe');
      $dataUe = unserialize($arrdataUe);
      try {

        //check if the student has an inscription on this year sesion->get('ie_gestion');
        $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $inscription2->createQueryBuilder('ei')
                ->select('ei.id as id')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                //->andwhere('ei.estadomatriculaTipo = :mat')
                ->setParameter('id', $idStudent)
                ->setParameter('gestion', $dataUe['requestUser']['gestion'])
                //->setParameter('mat', '4')
                //->setParameter('mat2', '5')
                ->getQuery();

        $selectedInscriptionStudent = $query->getResult();
        //$selectedInscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionStudentByYear($idStudent, $this->session->get('ie_gestion'),$dataUe['ueducativaInfoId']['iecId']);
        //dump($selectedInscriptionStudent);die;
        if(!$selectedInscriptionStudent){
            //save the inscription
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($dataUe['requestUser']['sie']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($dataUe['requestUser']['gestion']));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($idStudent));
            $studentInscription->setCodUeProcedenciaId($this->session->get('ie_id'));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime(date('Y-m-d')));
            $studentInscription->setFechaRegistro(new \DateTime(date('Y-m-d')));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($dataUe['ueducativaInfoId']['iecId']));
            //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
            $studentInscription->setCodUeProcedenciaId(0);
            $em->persist($studentInscription);
            $em->flush();
            //to do the submit data into DB
            //do the commit in DB
            $em->getConnection()->commit();
            //message registered the inscription
            $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito');
        }else{
            //message not registerd
            $this->session->getFlashBag()->add('noinscription', 'Estudiante No inscrito');
        }

        //reload the students list
        $exist = true;
        $objStudents = array();

        $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($dataUe['ueducativaInfoId']['iecId']);

        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:seeStudents.html.twig', array(
                      'objStudents' => $objStudents,
                      'form' => $this->createFormStudentInscription($arrdataUe)->createView(),
                      'exist' => $exist,
                      'infoUe' => $arrdataUe,
                      'aData' => $arrdataUe,
                      'dataUe'=> $dataUe['ueducativaInfo'],
                      'paraleloname' => $dataUe['ueducativaInfo']['paralelo'],
                      'gradoname' => $dataUe['ueducativaInfo']['grado']
        ));

      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }
    }






}
