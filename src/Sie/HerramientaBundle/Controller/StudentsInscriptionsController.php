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
        $this->aCursos = $this->fillCursos();
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
            ('11-1-1'),
            ('11-1-2'),
            ('12-1-1'),
            ('12-1-2'),
            ('12-1-3'),
            ('12-2-4'),
            ('12-2-5'),
            ('12-2-6'),
            ('13-1-1'),
            ('13-1-2'),
            ('13-2-3'),
            ('13-2-4'),
            ('13-3-5'),
            ('13-3-6')
        );
        return($this->aCursos);
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
     * obtiene el nivel, ciclo y grado
     * @param type $nivel
     * @param type $ciclo
     * @param type $grado
     * @param type $matricula
     * @return type return nivel, ciclo grado del estudiante
     */
    private function getCourse($nivel, $ciclo, $grado, $matricula) {
        //get the array of courses
        $cursos = $this->aCursos;
        //this is a switch to find the courses
        $sw = 1;
        //loof for the courses of student
        while (( $acourses = current($cursos)) !== FALSE && $sw) {
            if (current($cursos) == $nivel . '-' . $ciclo . '-' . $grado) {
                $ind = key($cursos);
                $currentCurso = current($cursos);
                $sw = 0;
            }
            next($cursos);
        }
        if ($matricula == 5 || $matricula == 56 || $matricula == 57 || $matricula == 58) {
            $ind = $ind + 1;
        }

        return $ind;
    }    

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function oldInscription($id) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $entity->createQueryBuilder('ei')
                ->select('ei.id as inscriptionId, IDENTITY(iec.nivelTipo) as nivelTipo', 'IDENTITY(iec.cicloTipo) as cicloTipo, IDENTITY(iec.gradoTipo) as gradoTipo', 'IDENTITY(ei.estadomatriculaTipo) as estadomatriculaTipo, IDENTITY(iec.gestionTipo) as gestion, ei.fechaInscripcion as fechaInscripcion, ei.fechaRegistro as fechaRegistro')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('ei.estudiante = :id')
                ->setParameter('id', $id)
                //->orderBy('ei.fechaInscripcion ', 'DESC')
                ->orderBy('iec.gestionTipo', 'DESC')
                ->addorderBy('iec.nivelTipo', 'DESC')
                ->addorderBy('iec.gradoTipo', 'DESC')
                ->getQuery();
        $resutlQuery = $query->getResult();
        //look for the next inscription depend the historial
        if ($resutlQuery) {

            reset($resutlQuery);
            $sw = true;
            while ($sw && $val = current($resutlQuery)) {
                if ($val['estadomatriculaTipo'] == '5' || $val['estadomatriculaTipo'] == '37' || $val['estadomatriculaTipo'] == '56' || $val['estadomatriculaTipo'] == '57' || $val['estadomatriculaTipo'] == '58') {
                    $keyCorrect = key($resutlQuery);
                    $sw = false;
                }
                next($resutlQuery);
            }
            if ($sw) {
                if ($resutlQuery) {

                    reset($resutlQuery);
                    $sw1 = true;
                    while ($sw1 && $val = current($resutlQuery)) {
                        $keyCorrect = key($resutlQuery);
                        $sw1 = false;
                        next($resutlQuery);
                    }
                } else {
                    //there is no key
                    $keyCorrect = 'krlos';
                }
            }
        }

        $resutlQuery = ($resutlQuery) ? $resutlQuery[$keyCorrect] : '0';

        try {
            return $resutlQuery;
        } catch (Exception $ex) {
            return $ex;
        }
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

        // validate the current inscriptino to the next level
        $inscriptionOld = $this->oldInscription($objStudent->getId());
        $newCourse = $this->aCursos[$this->getCourse($inscriptionOld['nivelTipo'], $inscriptionOld['cicloTipo'], $inscriptionOld['gradoTipo'], $inscriptionOld['estadomatriculaTipo'])];
        $strNewCourse = str_replace('-', '', $newCourse);
        $currentSelectedCourse = $dataUe['ueducativaInfoId']['nivelId'].$dataUe['ueducativaInfoId']['cicloId'].$dataUe['ueducativaInfoId']['gradoId'];
        if($strNewCourse == $currentSelectedCourse){

            $inscription2 = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
            $query = $inscription2->createQueryBuilder('ei')
                    ->select('ei.id as id')
                    ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso=iec.id')
                    ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                    ->leftJoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                    ->where('ei.estudiante = :id')
                    ->andwhere('iec.gestionTipo = :gestion')
                    ->andwhere('it.id = :ietipo')
                    ->andwhere('ei.estadomatriculaTipo IN (:mat)')
                    ->setParameter('id', $objStudent->getId())
                    ->setParameter('gestion', $dataUe['requestUser']['gestion'])
                    ->setParameter('mat', array(4,5))
                    //->setParameter('mat2', '5')
                    ->setParameter('ietipo', 1)
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
            //the student has an inscription on the same level
            $this->session->getFlashBag()->add('noinscription', 'No corresponde nivel, ni grado ...');
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
      $infoUe = $form['data'];
      
      $aInfoUeducativa = unserialize($form['data']);
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
        //$studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find());
        $studentInscription->setCodUeProcedenciaId(0);
        $em->persist($studentInscription);
        $em->flush();
        //add the areas to the student
        $query = $em->getConnection()->prepare('SELECT * from sp_genera_estudiante_asignatura(:estudiante_inscripcion_id::VARCHAR)');
        $query->bindValue(':estudiante_inscripcion_id', $studentInscription->getId());
        $query->execute();        
        //to do the submit data into DB
        //do the commit in DB
        $em->getConnection()->commit();
        $this->session->getFlashBag()->add('goodinscription', 'Estudiante inscrito');

        $response = new JsonResponse();
        return $response->setData(array('status'=>'success','infoUe'=>$infoUe));

        //reload the students list
        /*$exist = true;
        $objStudents = array();

        $objStudents = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->getListStudentPerCourseAlter($aInfoUeducativa['ueducativaInfoId']['iecId']);
        $dataUe=(unserialize($form['data']));
        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:seeStudents.html.twig', array(
                    'objStudents' => $objStudents,
                    'form' => $this->createFormStudentInscription($form['data'])->createView(),
                    'exist' => $exist,
                    'infoUe' => $form['data'],
                    'aData' => $form['data'],
                    'dataUe'=> $dataUe['ueducativaInfo'],
                    'paraleloname' => $aInfoUeducativa['ueducativaInfo']['paralelo'],
                    'gradoname' => $aInfoUeducativa['ueducativaInfo']['grado']
        ));*/
      } catch (Exception $e) {
        $em->getConnection()->rollback();
        return $response->setData(array('status'=>'error', 'msg'=>'error'));
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
