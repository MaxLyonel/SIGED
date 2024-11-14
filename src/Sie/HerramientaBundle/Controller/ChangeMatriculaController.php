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
use Sie\AppWebBundle\Entity\EstudianteInscripcionCambioestado;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;
use Sie\AppWebBundle\Entity\ValidacionProceso;


/**
 * ChangeMatricula controller.
 *
 */
class ChangeMatriculaController extends Controller {

    public $session;
    public $idInstitucion;
    public $operativo;
    public $arrQuestion;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
        $this->aCursosOld = $this->fillCursosOld();
        $this->arrQuestion = array(
        0 => "...",
        1 => "Nunca asistio a clases", //retiro abandono
        2 => "Asistio algunos dias a clases", //En tolerancia
        3 => "Asistio",
    );
    }

    public function indexAction(Request $request){
      //create db conexion
      $em = $this->getDoctrine()->getManager();
        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');
        $arrInfoStudent = json_decode($infoStudent,true);
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($arrInfoStudent['id']);
        $query  = $em->getConnection()->prepare("
                  select count(*) conteonota
                  from estudiante_asignatura ea 
                  inner join estudiante_nota en on en.estudiante_asignatura_id = ea.id
                  where ea.estudiante_inscripcion_id =".$arrInfoStudent['eInsId']." ;");
        $query->execute();
        $estNotas = $query->fetchAll();
        if ($estNotas[0]['conteonota'] > 0){
          unset($this->arrQuestion[1]);
        }
        return $this->render('SieHerramientaBundle:ChangeMatricula:indexquestion.html.twig', array(
            'infoStudent'         => json_decode($infoStudent,true),
            'infoUnidadEducativa' => unserialize($infoUe),
            'form'                => $this->matriculaForm($infoStudent, $infoUe)->createView(),
            'student' => $objStudent,
            'enTolerancia' => $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneBy(array('id'=>101))
        ));
    }

    private function matriculaForm($infoStudent, $infoUe){

      $arrInfoUe = unserialize($infoUe);
      $arrInfoData = $arrInfoUe['requestUser'];
      //get the operativo
      $this->operativo = $this->get('funciones')->obtenerOperativo($arrInfoData['sie'],$arrInfoData['gestion']);
      
      $arrDias = [];
      for ($i=1; $i <= 50; $i++) { 
          $arrDias[] = $i;
      }

      return $this->createFormBuilder()

            /*->add('estadoMatricula', 'entity', array('class' => 'SieAppWebBundle:EstadomatriculaTipo','label'=>'Estado Mátricula ',
            'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('emt')
                        ->where('emt.id IN (:id)')
                        ->setParameter('id', ($this->operativo>1)?array('4','10'):array('4','6'))
                        ->orderBy('emt.id', 'ASC')
                ;
            }, 'property' => 'estadomatricula'))*/

            ->add('questionStatus', 'choice', array('choices'=>$this->arrQuestion, 'attr'=>array('class'=>'form-control','onchange'=>'myFunctionSH(this)')))
            ->add('observation', 'textarea', array('attr'=>array('class'=>'form-control')))
             ->add('classdays', 'choice', array('choices'=>$arrDias, 'attr'=>array('class'=>'form-control')))

            ->add('infoStudent', 'hidden', array('data'=>$infoStudent))
            ->add('infoUe', 'hidden', array('data'=>$infoUe))
            ->add('Registrar','submit', array('label'=>'Guardar', 'attr'=>array('class'=>'btn btn-primary')))
            ->getForm();
    }
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
    private function fillCursosOld() {
        $this->aCursosOld = array(
            ('1-2-1'),
            ('1-3-2'),
            ('2-1-1'),
            ('2-1-2'),
            ('2-1-3'),
            ('2-2-4'),
            ('2-2-5'),
            ('2-2-6'),
            ('2-3-7'),
            ('2-3-8'),
            ('3-1-1'),
            ('3-1-2'),
            ('3-3-3'),
            ('3-3-4')
        );
        return($this->aCursosOld);
    }
    private function getCourse($nivel, $ciclo, $grado, $matricula) {
        //get the array of courses
        $cursos = $this->aCursos;
        //this is a switch to find the courses
        $sw = 1;
        //loof for the courses of student
        while (($acourses = current($cursos)) !== FALSE && $sw) {
            if (current($cursos) == $nivel . '-' . $ciclo . '-' . $grado) {
                $ind = key($cursos);
                $currentCurso = current($cursos);
                $sw = 0;
            }
            next($cursos);
        }
        if ($matricula == 5) {
            $ind = $ind + 1;
        }
        return $ind;
    }
    private function getCourseOld($nivel, $ciclo, $grado, $matricula) {
      //get the array of courses
        $cursos = $this->aCursosOld;
        //this is a switch to find the courses
        $sw = 1;
        // $ind=0;
        //loof for the courses of student
        while (( $acourses = current($cursos)) !== FALSE && $sw) {
            if (current($cursos) == $nivel . '-' . $ciclo . '-' . $grado) {
                $ind = key($cursos);
                $currentCurso = current($cursos);
                $sw = 0;
            }
            next($cursos);
        }
        if ($matricula == 5) {
            $ind = $ind + 1;
        }
        return $ind;
    }
    public function updateMatriculaAction(Request $request) {
        //get the info ue
        $form = $request->get('form');

        $infoUe = $form['infoUe'];
        $infoStudent = json_decode($form['infoStudent'],true) ;
        $aInfoUeducativa = unserialize($infoUe);
        
        $operativo = $this->get('funciones')->obtenerOperativo($aInfoUeducativa['requestUser']['sie'],$aInfoUeducativa['requestUser']['gestion']);
        
        if ($operativo > 3 ){
          $response = new JsonResponse();
          return $response->setData(array('status'=>'error', 'msg'=>'No puede modificar el estado, concluyo la gestión'));
        }
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $estudianteins = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($infoStudent['eInsId']);
        if (in_array($estudianteins->getestadomatriculaTipo()->getId(), [5, 55, 11]) ){
          $response = new JsonResponse();
          return $response->setData(array('status'=>'error', 'msg'=>'No puede modificar el estado de promovido o reprobado con calificaciones reportadas'));
        }
        $swChangeStatus = true;
        try {
        
          switch ($form['questionStatus'])
          {
            case 1:
              $updateMatricula = 6;
            break;
            case 2:
              $updateMatricula = 10;//retiro abandono
            break;
            case 3:
              $updateMatricula = 4;//efectivo
            break;
          }

          /*if($form['questionStatus']==1)
          {
            $updateMatricula = 6;
          }else{
            $updateMatricula = 6;
            // $updateMatricula = 9;
          }*/

          if($swChangeStatus){

            $objEstudianteInscripcionCambioestado = new EstudianteInscripcionCambioestado();
            $objEstudianteInscripcionCambioestado->setJustificacion($form['observation']);
            $objEstudianteInscripcionCambioestado->setJson(json_encode($form));
            $objEstudianteInscripcionCambioestado->setFechaRegistro(new \DateTime('now'));
            $objEstudianteInscripcionCambioestado->setUsuarioId($this->session->get('userId'));
            $objEstudianteInscripcionCambioestado->setEstudianteInscripcion( $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($infoStudent['eInsId']) );
             $em->persist($objEstudianteInscripcionCambioestado);
            
            //find to update
            $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($infoStudent['eInsId']);         
            $oldInscriptionStudent = clone $currentInscrip;
            
            $currentInscrip->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($updateMatricula));
            $em->persist($currentInscrip);
            $em->flush();
            $message = 'Cambio de estado realizado';  
            $this->addFlash('goodinscription',$message);
            // added set log info data
            $this->get('funciones')->setLogTransaccion(
                                  $infoStudent['eInsId'],
                                  'estudiante_inscripcion',
                                  'U',
                                  '',
                                  $currentInscrip,
                                  $oldInscriptionStudent,
                                  'SIGED',
                                  json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
            );     
            // retiramos la observacion regla 63 estado_matricula inconsistente
            $observacion = $em->getRepository('SieAppWebBundle:ValidacionProceso')->findOneBy(array('llave'=>$infoStudent['codigoRude'],'validacionReglaTipo'=>63));
            if($observacion)
            {
              $observacion->setEsActivo('t');
              $em->persist($observacion);
              $em->flush();
            }

            // Try and commit the transaction
            $em->getConnection()->commit();
          }

        } catch (Exception $e) {
          $em->getConnection()->rollback();
          return $response->setData(array('status'=>'error', 'msg'=>'No se pudo cambiar el estado de matrícula del estudiante'));
        }

        $response = new JsonResponse();
        return $response->setData(array('status'=>'success','infoUe'=>$infoUe));

        // return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:seeStudents.html.twig', array(
        //             'objStudents' => $objStudents,
        //             'sie' => $sie,
        //             'turno' => $turno,
        //             'nivel' => $nivel,
        //             'grado' => $grado,
        //             'paralelo' => $paralelo,
        //             'gestion' => $gestion,
        //             'aData' => $aData,
        //             'gradoname' => $gradoname,
        //             'paraleloname' => $paraleloname,
        //             // 'nivelname' => $nivelname,
        //             'form' => $this->createFormStudentInscription($infoUe)->createView(),
        //             'infoUe' => $infoUe,
        //             'exist' => $exist,
        //             'itemsUe'=>$itemsUe,
        //             'ciclo'=>$ciclo,
        //             'operativo'=>$operativo,
        //             'UePlenasAddSpeciality' => $UePlenasAddSpeciality,
        //             'imprimirLibreta'=>$imprimirLibreta,
        //             'estadosPermitidosImprimir'=>$estadosPermitidosImprimir
        // ));
    }

    public function updateMatricula1Action(Request $request){

      $form = $request->get('form');

      $infoUe = $form['infoUe'];
      $infoStudent = json_decode($form['infoStudent'],true) ;

      $aInfoUeducativa = unserialize($infoUe);

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

      //dump($infoStudent);die;
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      try {

        //find to update
        $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($infoStudent['eInsId']);
        $currentInscrip->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($form['estadoMatricula']));
        $em->persist($currentInscrip);
        $em->flush();
        // Try and commit the transaction
        $em->getConnection()->commit();

        $objNextCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
            'institucioneducativa' => $sie,
            'nivelTipo' => $nivel,
            'cicloTipo' => $ciclo,
            'gradoTipo' => $grado,
            'paraleloTipo' => $paralelo,
            'turnoTipo' => $turno,
            'gestionTipo' => $gestion
        ));
        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);
        $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecId' => $iecId, 'ciclo' => $ciclo, 'iecNextLevl' => $objNextCurso->getId()));
        // Para el centralizador
        $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];
    

        return $this->render($this->session->get('pathSystem') . ':InfoEstudiante:seeStudents.html.twig', array(
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
                    'itemsUe'=>$itemsUe,
                    'ciclo'=>$ciclo,
                    'exist' => true
        ));



      } catch (Exception $e) {
        $em->getConnection()->rollback();
        echo 'Excepción capturada: ', $ex->getMessage(), "\n";
      }

      //dump($form);die;
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




}
