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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * StudentsInscriptionsRequest controller.
 *
 */
class StudentsInscriptionsRequestController extends Controller {

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

        return $this->render('SieHerramientaBundle:StudentsInscriptions:lookforstudent.html.twig', array(
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
                ->select('ei.id as id')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                ->where('ei.estudiante = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('ei.estadomatriculaTipo = :mat')
                ->andwhere('it.id = :ietipo')
                ->setParameter('id', $objStudent->getId())
                ->setParameter('gestion', $dataUe['requestUser']['gestion'])
                ->setParameter('mat', '4')
                ->setParameter('ietipo', 1)
                //->setParameter('mat2', '5')
                ->getQuery();

        $selectedInscriptionStudent = $query->getResult();

        //check if the student has an inscription on this year sesion->get('ie_gestion');
        //$selectedInscriptionStudent = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionStudentByYear($objStudent->getId(), $dataUe['requestUser']['gestion'],$dataUe['ueducativaInfoId']['iecId']);
        if(!$selectedInscriptionStudent){
          //check if the level and grado is correct to the student//the next step is do it
            $objStudentInscriptions = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->getInscriptionAlternativaStudent($objStudent->getId());
            //dump($objStudentInscriptions);die;

            return $this->render($this->session->get('pathSystem').':StudentsInscriptions:inscriptions.html.twig', array(
              'objStudent'=>$objStudent,
              'objStudentInscriptions'=>$objStudentInscriptions,
              'form'=>$this->doInscriptionForm($form['data'], $objStudent->getId())->createView(),
              'exist'=>true

            ));

        }else{
            //the student has an inscription on the same level
            $this->session->getFlashBag()->add('noinscription', 'Estudiante ya cuenta con inscripcion...');
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
              //->add('caseespecial', 'checkbox', array('label'=>'Validacion Especial', 'attr'=>array('class'=>'form-control', 'checked'=>false ) ))
              ->add('data', 'hidden', array('data'=> $data))
              ->add('studentId', 'hidden', array('data'=> $studentId))
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
      //get the values throght the infoUe
      $sie = $aInfoUeducativa['requestUser']['sie'];
      $iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
      $nivel = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
      $grado = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
      $turno = $aInfoUeducativa['ueducativaInfoId']['turnoId'];
      $ciclo = $aInfoUeducativa['ueducativaInfoId']['cicloId'];
      $gestion = $aInfoUeducativa['requestUser']['gestion'];
      $paralelo = $aInfoUeducativa['ueducativaInfoId']['paraleloId'];
      $gradoname = $aInfoUeducativa['ueducativaInfo']['grado'];
      $paraleloname = $aInfoUeducativa['ueducativaInfo']['paralelo'];
      //dump($aInfoUeducativa['ueducativaInfoId']['nivelId']);die;
      //dump($aInfoUeducativa);die;
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
        if($this->session->get('ue_modular') && $nivel >= 13){
          $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(67));
        } else {
          $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(7));
        }

        $studentInscription->setCodUeProcedenciaId(0);
        $em->persist($studentInscription);
        $em->flush();
        //add the areas to the student
        $responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $aInfoUeducativa['ueducativaInfoId']['iecId']);
        //to do the submit data into DB
        //do the commit in DB
        $em->getConnection()->commit();
        $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito');

        //reload the students list
        //$exist = true;
        //$objStudents = array();
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
            $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);
            $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecId' => $iecId, 'ciclo' => $ciclo, 'iecNextLevl' => $objNextCurso->getId()));
        } else {
            $message = 'No existen estudiantes inscritos...';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }

        //$objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($aInfoUeducativa['ueducativaInfoId']['iecId']);
        $dataUe=(unserialize($form['data']));
        /*return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'form' => $this->createFormStudentInscription($form['data'])->createView(),
                    'exist' => $exist,
                    'infoUe' => $form['data'],
                    'aData' => $form['data'],
                    'dataUe'=> $dataUe['ueducativaInfo'],
                    'paraleloname' => $aInfoUeducativa['ueducativaInfo']['paralelo'],
                    'gradoname' => $aInfoUeducativa['ueducativaInfo']['grado'],
                    'nivel'=>$aInfoUeducativa['ueducativaInfoId']['nivelId']
        ));*/

        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'sie' => $sie,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'gradoname' => $gradoname,
                    'paraleloname' => $paraleloname,
                    // 'nivelname' => $nivelname,
                    'form' => $this->createFormStudentInscription($form['data'])->createView(),
                    'infoUe' => $form['data'],
                    'exist' => $exist
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

    /**
     * to add the areas to the student
     * @param type $studentInscrId
     * @param type $newCursoId
     * @return boolean
     */
    private function addAreasToStudent($studentInscrId, $newCursoId) {
        $em = $this->getDoctrine()->getManager();
        $areasEstudiante = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
            'estudianteInscripcion' => $studentInscrId,
            'gestionTipo' => $this->session->get('currentyear')
        ));
        //if doesnt have areas we'll fill these
        if (!$areasEstudiante) {
            $objAreas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array(
                'insitucioneducativaCurso' => $newCursoId
            ));
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
            $query->execute();
            foreach ($objAreas as $areas) {
                //print_r($areas->getAsignaturaTipo()->getId());
                $studentAsignatura = new EstudianteAsignatura();
                $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
                $studentAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($studentInscrId));
                $studentAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($areas->getId()));
                $studentAsignatura->setFechaRegistro(new \DateTime('now'));
                $em->persist($studentAsignatura);
                $em->flush();
                //echo "<hr>";
            }
        }
        return true;
    }

}
