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
        //get the send values
        $infoUe = $request->get('infoUe');

        return $this->render('SieHerramientaAlternativaBundle:StudentsInscriptions:lookforstudent.html.twig', array(
          'form'=>$this->findStudentForm($infoUe)->createView()
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

            return $this->render($this->session->get('pathSystem').':StudentsInscriptions:inscriptions.html.twig', array(
              'objStudent'=>$objStudent,
              'objStudentInscriptions'=>$objStudentInscriptions,
              'form'=>$this->doInscriptionForm($form['data'], $objStudent->getId())->createView(),
              'exist'=>true

            ));

        }else{
            //the student has an inscription on the same level
            $this->session->getFlashBag()->add('noinscription', 'Estudiante ya cuenta con inscripcion en el mismo nivel');
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

      $yearStudent = (date('Y') - date('Y',strtotime($form['dateStudent'])));

      //validate the humanisticos
      if($objInstitucioneducativaCursoStudent->getNivelTipo()->getId()==15){
        //validate to nivel=15 - ciclo=1 - grado=1
        if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==1 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==1){
          if(!($yearStudent>=15)){
            $validateYear=true;
          }
        }

        //validate to nivel=15 - ciclo=1 - grado=2
        if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==1 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==2){
          if(!($yearStudent>=16)){
            $validateYear=true;
          }
        }

        //validate to nivel=15 - ciclo=2 - grado=1
        if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==2 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==1){
          if(!($yearStudent>=17)){
            $validateYear=true;
          }
        }

        //validate to nivel=15 - ciclo=2 - grado=2
        if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==2 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==2){
          if(!($yearStudent>=18)){
            $validateYear=true;
          }
        }

        //validate to nivel=15 - ciclo=2 - grado=3
        if($objInstitucioneducativaCursoStudent->getCicloTipo()->getId()==2 and $objInstitucioneducativaCursoStudent->getGradoTipo()->getId()==3){
          if(!($yearStudent>=18)){
            $validateYear=true;
          }
        }
      }//end first if - validate the humanisticos

    }
    //if the year is wrong go to show the alert
    if($validateYear){
      //reload the students list
      $exist = true;
      $objStudents = array();
      $this->session->getFlashBag()->add('noinscription', 'Estudiante no inscrito no cumple la edad...');
      $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($aInfoUeducativa['ueducativaInfoId']['iecId']);
      $dataUe=(unserialize($form['data']));

      return $this->render($this->session->get('pathSystem') . ':InfoEstudianteRequest:seeStudents.html.twig', array(
                  'objStudents' => $objStudents,
                  'form' => $this->createFormStudentInscription($form['data'])->createView(),
                  'exist' => $exist,
                  'infoUe' => $form['data'],
                  'dataUe'=> $dataUe['ueducativaInfo']
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

        //to do the submit data into DB
        //do the commit in DB
        $em->getConnection()->commit();
        $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito');

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
                    'totalInscritos'=>count($objStudents)
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

}
