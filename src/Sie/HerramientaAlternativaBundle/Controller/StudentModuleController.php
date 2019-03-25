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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * StudentModule controller.
 *
 */
class StudentModuleController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }
    /**
    * function index send request
    * the main function to add modueles
    **/
    public function indexAction(Request $request){
      //create the DB conexion
      $em= $this->getDoctrine()->getManager();
      //get the send values
      $infoUe = $request->get('infoUe');
      $infoStudent = $request->get('infoStudent');
      $arrInfoUe = unserialize($infoUe);
      $arrInfoStudent = json_decode($infoStudent, true);


     
      $arrCourseToSelected=array();

      //check data to set the new funcionalty and new way to load the curricula
      // if( $this->get('funciones')->validatePrimaria($this->session->get('ie_id'),$this->session->get('currentyear'),$infoUe)
      //     ){

      //   $data = array('iecId'=>$arrInfoUe['ueducativaInfoId']['iecId'], 'eInsId'=>$arrInfoStudent['eInsId']);

      //   $objNewCurricula = $this->get('funciones')->setCurriculaStudent($data);
      //   // $arrCourseToSelected = $this->get('funciones')->getCurriculaStudent($data);
      //   $form=array();
      //   $useTemplate = 'newcourseToSelected';
      //  }else{
      //     $form = $this->addModuloStudentForm($infoUe, $infoStudent, json_encode($arrCourseToSelected) )->createView();
      //     $useTemplate = 'courseToSelected';
      //  }
      
         // all funcionality 
                  //get the modules per course
          $objModulesPerCourse = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findModulesByCourse($arrInfoUe);

          foreach ($objModulesPerCourse as $key => $course) {
            # look for the student asignatura
            $objStudentAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
              'gestionTipo'                     => $this->session->get('ie_gestion'),
              'estudianteInscripcion'           => $arrInfoStudent['eInsId'],
              'institucioneducativaCursoOferta' => $course['iecoId']
            ));
            //check if exists the course if no exists add in array
            if($objStudentAsignatura){
              $course['takeModulo']='checked="checked"';

            }else{
              $course['takeModulo']='';
            }
            $arrCourseToSelected[]=$course;

          }
      $form = $this->addModuloStudentForm($infoUe, $infoStudent, json_encode($arrCourseToSelected) )->createView();
      $useTemplate = 'courseToSelected';
        //get required info about the studens 
       //get the inscription info and set the student info
      $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrInfoStudent['eInsId']);
      $arrDataStudent = array(
                          'codigoRude'=>$arrInfoStudent['codigoRude'],
                          'estudiante'=>$arrInfoStudent['paterno'].' '.$arrInfoStudent['materno'].' '.$arrInfoStudent['nombre'],
                          'estadoMatricula'=>$inscripcion->getEstadomatriculaTipo()->getEstadomatricula()
                        );

      $objStudentInfo = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($arrInfoStudent['id']);

          
      
      return $this->render('SieHerramientaAlternativaBundle:StudentModule:'.$useTemplate.'.html.twig', array(
        'arrCourseToSelected' => $arrCourseToSelected,
        'infoUe'              => $infoUe,
        'infoStudent'         => $infoStudent,
        'dataStudent'         => $arrDataStudent,
        'especialidad'        => $arrInfoUe['ueducativaInfo']['ciclo'],
        'area'                => $arrInfoUe['ueducativaInfo']['nivel'],
        'acreditacion'        => $arrInfoUe['ueducativaInfo']['grado'],
        'paralelo'            => $arrInfoUe['ueducativaInfo']['paralelo'],
        'turno'               => $arrInfoUe['ueducativaInfo']['turno'],
        'form'                => $form,
        'objStudentInfo'      => $objStudentInfo

       ));



    }

    private function addModuloStudentForm($infoUe, $infoStudent, $courses){
      $form = $this->createFormBuilder()
          ->add('infoUe', 'hidden', array('data'=>$infoUe))
          ->add('infoStudent', 'hidden', array('data'=>$infoStudent))
          ->add('courses', 'hidden', array('data'=>$courses))
          ->add('save', 'button', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-theme','onclick'=>'saveModulesToStudent()' )))
          ->getForm();
      return $form;
    }

    /**
    * function to save the modules to the student
    * send modules, infoUe, infoStudent
    **/
    public function saveModulesToStudentAction(Request $request){
      //create DB conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get the send values
      $form        = $request->get('form');
      $infoUe      = $form['infoUe'];
      $infoStudent = $form['infoStudent'];
      $modules     = (isset($form['modules']))?$form['modules']:false;

      $arrModules = json_decode($form['courses'],true);
      $arrRemoveModules = array();
      $arrAddModules    = array();
      foreach ($arrModules as $key => $value) {
        # code...

        if(in_array($value['iecoId'],$modules)){
          $arrAddModules[$value['iecoId']]=$value['iecoId'];
        }else{
          $arrRemoveModules[$value['iecoId']]=$value['iecoId'];
        }
      }

      //conver send values
      $arrInfoUe = unserialize($infoUe);
      $arrInfoStudent = json_decode($infoStudent, true);

      try {
        //check if we have selected modules to add, if have it to do the save
        if(sizeof($arrAddModules)>0){
          reset($arrAddModules);
          //todo the save module to the student
          while ($val = current($arrAddModules)){
            //find the module on EstudianteAsignatura
            $objStudentAsignaturaCheck = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
              'gestionTipo'                     => $this->session->get('ie_gestion'),
              'estudianteInscripcion'           => $arrInfoStudent['eInsId'],
              'institucioneducativaCursoOferta' => $val
            ));

            if(!$objStudentAsignaturaCheck){

              $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
              $query->execute();
              //save on estudiante_asignatura table
              $studentAsignaturaNew = new EstudianteAsignatura();
              $studentAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('ie_gestion')));
              $studentAsignaturaNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($arrInfoStudent['eInsId']));
              $studentAsignaturaNew->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($val));
              $studentAsignaturaNew->setFechaRegistro(new \DateTime('now'));
              $studentAsignaturaNew->setEstudianteasignaturaEstado($em->getRepository('SieAppWebBundle:EstudianteasignaturaEstado')->find(4));
              $em->persist($studentAsignaturaNew);
              $em->flush();
            }
            next($arrAddModules);
          }
        }

        //check if we have selected modules to remove, if have it to do the save
        if(sizeof($arrRemoveModules)>0){
          reset($arrRemoveModules);
          //todo the save module to the student
          while ($val = current($arrRemoveModules)){
            //find the module on EstudianteAsignatura
            $objStudentAsignaturaRemove = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array(
              'gestionTipo'                     => $this->session->get('ie_gestion'),
              'estudianteInscripcion'           => $arrInfoStudent['eInsId'],
              'institucioneducativaCursoOferta' => $val
            ));

            if($objStudentAsignaturaRemove){
              //before remove on estudiante_nota
              $objStudentNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array(
                'estudianteAsignatura'=>$objStudentAsignaturaRemove->getId()
              ));
              foreach ($objStudentNota as $key => $valueNota) {
                # remove the note
                $em->remove($valueNota);
              }
              $em->remove($objStudentAsignaturaRemove);
              $em->flush();
            }
            next($arrRemoveModules);
          }

        }
        // Try and commit the transaction
        $em->getConnection()->commit();

      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }

      die;
      return 1;
    }
    //the older from here
}
