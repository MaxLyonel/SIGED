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
 * ChangeMatricula controller.
 *
 */
class ChangeMatriculaController extends Controller {

    public $session;
    public $idInstitucion;
    public $operativo;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
        $this->aCursosOld = $this->fillCursosOld();
    }

    public function indexAction(Request $request){
      //create db conexion
      $em = $this->getDoctrine()->getManager();
        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');
        $arrInfoStudent = json_decode($infoStudent,true);
        $objStudent = $em->getRepository('SieAppWebBundle:Estudiante')->find($arrInfoStudent['id']);
        return $this->render('SieHerramientaBundle:ChangeMatricula:index.html.twig', array(
            'infoStudent'         => json_decode($infoStudent,true),
            'infoUnidadEducativa' => unserialize($infoUe),
            'form'                => $this->matriculaForm($infoStudent, $infoUe)->createView(),
            'student' => $objStudent

        ));
    }

    private function matriculaForm($infoStudent, $infoUe){

      $arrInfoUe = unserialize($infoUe);
      $arrInfoData = $arrInfoUe['requestUser'];
      //get the operativo
      $this->operativo = $this->get('funciones')->obtenerOperativo($arrInfoData['sie'],$arrInfoData['gestion']);

      return $this->createFormBuilder()

            ->add('estadoMatricula', 'entity', array('class' => 'SieAppWebBundle:EstadomatriculaTipo','label'=>'Estado Mátricula ',
            'query_builder' => function (EntityRepository $e) {
                return $e->createQueryBuilder('emt')
                        ->where('emt.id IN (:id)')
                        ->setParameter('id', ($this->operativo>1)?array('4','10'):array('4','6'))
                        ->orderBy('emt.id', 'ASC')
                ;
            }, 'property' => 'estadomatricula'))
            ->add('infoStudent', 'hidden', array('data'=>$infoStudent))
            ->add('infoUe', 'hidden', array('data'=>$infoUe))
            ->add('Guardar','button', array('label'=>'Guardar', 'attr'=>array('class'=>'btn btn-primary btn-xs', 'onclick'=>'saveChangeMatricula()')))
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

// dump($aInfoUeducativa['requestUser']);die;
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
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $objTypeOfUE = $em->getRepository('SieAppWebBundle:InstitucioneducativaHumanisticoTecnico')->getTypeOfUE(array('sie'=>$sie,'gestion'=>$aInfoUeducativa['requestUser']['gestion']));
        $arrAllowInscription=array(1,2,3,4,5);
        $objTypeOfUEId = (sizeof($objTypeOfUE))>0?$objTypeOfUE[0]->getInstitucioneducativaHumanisticoTecnicoTipo()->getId():100;
        if(in_array((sizeof($objTypeOfUE))>0?$objTypeOfUE[0]->getInstitucioneducativaHumanisticoTecnicoTipo()->getId():100,$arrAllowInscription)){
          $this->session->set('allowInscription',true);
        }else{
          $this->session->set('allowInscription',false);
        }
        //get turnos
        //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($iecId, '5');
        //get th position of next level

        // $positionCurso = $this->getCourse($nivel, $ciclo, $grado, '5');
        $posicionCurso = ($gestion > 2010) ? $this->getCourse($nivel, $ciclo, $grado, '5') : $this->getCourseOld($nivel, $ciclo, $grado, '5');
        //$dataNextLevel = explode('-', $this->aCursos[$positionCurso]);

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
        // if ($objNextCurso) {
        //     //$objStudents = $em->getRepository('SieAppWebBundle:Estudiante')->getStudentsToInscription($objNextCurso->getId(), '5');
        //     //get students list
        //     $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerCourse($sie, $gestion, $nivel, $grado, $paralelo, $turno);
        //     $aData = serialize(array('sie' => $sie, 'nivel' => $nivel, 'grado' => $grado, 'paralelo' => $paralelo, 'turno' => $turno, 'gestion' => $gestion, 'iecId' => $iecId, 'ciclo' => $ciclo, 'iecNextLevl' => $objNextCurso->getId()));
        // } else {
        //     $message = 'No existen estudiantes inscritos...';
        //     $this->addFlash('warninsueall', $message);
        //     $exist = false;
        // }

        // Para el centralizador
        $itemsUe = $aInfoUeducativa['ueducativaInfo']['nivel'].",".$aInfoUeducativa['ueducativaInfo']['grado'].",".$aInfoUeducativa['ueducativaInfo']['paralelo'];

        $operativo = $em->getRepository('SieAppWebBundle:Estudiante')->getOperativoToCollege($sie,$gestion);



        //to add the especialidad
        $UePlenasAddSpeciality=false;
        $arrUePlenasAddSpeciality = array(
          '81410160',
          '81410080',
          '40730250',
          '81410037',
          '81410134',
          '82220009',
          '80480060',
          '81981445',
          '80660080',
          '81340038',
          '81340065',
          '80730395',
          '80730391',
          '71170009',
          '60730046',
          '71170010',
          '81410157',

          '60900064',
          '81981463',
          '81480060',
          '80630028',
          '81470005',
          '81470069',
          '80980556',
          '80920034',
          '80980514',
          '71480114',
          '40730531',
          '82220001',
          '81170016',
          '80480163'
        );
        $UePlenasAddSpeciality = (in_array($sie, $arrUePlenasAddSpeciality))?true:false;

        // Impresion de libretas
        $tipoUE = $this->get('funciones')->getTipoUE($sie,$gestion);
        $operativo = $this->get('funciones')->obtenerOperativo($sie,$gestion);

        $imprimirLibreta = false;
        $estadosPermitidosImprimir = array(4,5,11,55);

        if($tipoUE){
            /*
             *  GESTION ACTUAL
             */
            if($gestion == $this->session->get('currentyear')){
                // Unidades educativas plenas, modulares y humanisticas
                if(in_array($tipoUE['id'], array(1,3,5)) and $operativo >= 2){
                    $imprimirLibreta = true;
                }
                // Unidades educativas tecnicas tecnologicas
                if(in_array($tipoUE['id'], array(2)) and $operativo >= 4){
                    $imprimirLibreta = true;
                }
            }


            /*
             * GESTIONES PASADAS
             */
            if($gestion < $this->session->get('currentyear')){
                // Para unidades educativas en gestiones pasadas
                if(in_array($tipoUE['id'], array(1,2,3,5)) and $gestion >= 2014 and $gestion < $this->session->get('currentyear') and $operativo >= 4){
                    $imprimirLibreta = true;
                }

                // PAra ues tecnicas tecnologicas
                if(in_array($tipoUE['id'], array(2)) and $gestion >= 2011){
                    $imprimirLibreta = true;
                }

                // Caso especial de la unidad educativa AMERINST
                if($sie == '80730460' and $gestion <= 2015){
                    $imprimirLibreta = false;
                    if($gestion == 2014 and $nivel == 13 and $grado >= 4 and $paralelo >= 6){
                        $imprimirLibreta = true;
                    }
                    if($gestion == 2015 and $nivel == 13 and $grado >= 5 and $paralelo >= 6){
                        $imprimirLibreta = true;
                    }
                }
            }
        }

      $aRemovesUeAllowed = array(
      '61710014',
      '61710089',
      '61710076',
      '61710068',
      '61710090',
      '61710042',
      '61710087',
      '61710084',
      '61710083',
      '61710085',
      '61710088',
      '61710063',
      '61710028',
      '61710086',
      '61710041',
      '61710043',
      '61710062',
      '61710031',
      '61710077',
      '61710021',
      '61710022',
      '61710036',
      '61710038',
      '61710091',
      '61710092',
      '61710093',
      '61710004'
      );
  $this->session->set('removeInscriptionAllowed', false);
  if(in_array($this->session->get('ie_id'),$aRemovesUeAllowed))
    $this->session->set('removeInscriptionAllowed',true);
    $swChangeStatus = true;
    try {
      //ge notas to do the changeMatricula
      $notas = $this->get('notas')->regular($infoStudent['eInsId'],1);
      $notasStudent = $notas['cuantitativas'];
      $notasRegistradas = array();
      foreach ($notasStudent as $key => $value) {
          if(isset($value['notas'][0]['nota']) && $value['notas'][0]['nota']>0)
            $notasRegistradas[] = $value['notas'][0]['nota'];
      }
    
        if($form['estadoMatricula']==6){
          if(sizeof($notasRegistradas)>1){
            $message = 'Cambio no realizado, debido a que la/el estudiante cuenta con calificaciones';  
            $this->addFlash('noinscription',$message);
            $swChangeStatus=false;
          }
        }
        
        if($swChangeStatus){
          //find to update
          $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($infoStudent['eInsId']);
          $oldInscriptionStudent = clone $currentInscrip;
          $currentInscrip->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find($form['estadoMatricula']));
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

          // Try and commit the transaction
          $em->getConnection()->commit();

        }

    } catch (Exception $e) {
      $em->getConnection()->rollback();
      echo 'Excepción capturada: ', $ex->getMessage(), "\n";
    }
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
                    'exist' => $exist,
                    'itemsUe'=>$itemsUe,
                    'ciclo'=>$ciclo,
                    'operativo'=>$operativo,
                    'UePlenasAddSpeciality' => $UePlenasAddSpeciality,
                    'imprimirLibreta'=>$imprimirLibreta,
                    'estadosPermitidosImprimir'=>$estadosPermitidosImprimir
        ));
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
