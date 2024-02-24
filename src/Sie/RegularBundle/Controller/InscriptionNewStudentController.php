<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\PaisTipo;
use Sie\AppWebBundle\Form\EstudianteType;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteNotaCualitativa;
use Sie\AppWebBundle\Controller\DefaultController as DefaultCont;
/**
 * InscriptionIniPriTrue controller.
 *
 */
class InscriptionNewStudentController extends Controller {

    private $session;
    public $lugarNac;
    public $dpto;
    public $aCursos;
    public $aCursosOld;
    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->aCursos = $this->fillCursos();
        $this->aCursosOld = $this->fillCursosOld();
    }

    /**
     * Lists all Estudiante entities.
     *
     */
    public function indexAction() {
      //deshabilitado
      if (in_array($this->session->get('roluser'), array(9))){
           return $this->redirect($this->generateUrl('login'));
      }
      //return $this->redirect($this->generateUrl('login'));

     if (in_array($this->session->get('roluser'), array(8,10,7))){
     }else{
      return $this->redirect($this->generateUrl('login'));  
     }
      $arrWeenhayec = array(
        61710004,
        61710014,
        61710021,
        61710022,
        61710028,
        61710031,
        61710036,
        61710038,
        61710041,
        61710042,
        61710043,
        61710062,
        61710063,
        61710068,
        61710076,
        61710077,
        61710083,
        61710084,
        61710085,
        61710086,
        61710087,
        61710088,
        61710089,
        61710090,
        61710091,
        61710092,
        61710093,
        80730370,
        81981667,
        61710097,
        61710098
      );

      if( in_array($this->session->get('ie_id'), $arrWeenhayec) or in_array($this->session->get('roluser'), array(7,8,9,10))   ){
        //nothing todo
      }else{
        return $this->redirect($this->generateUrl('principal_web'));
      }


        //
        $em = $this->getDoctrine()->getManager();
        // return $this->redirectToRoute('principal_web');
        $id_usuario = $this->session->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
        ));
    }

    /**
     * Creates a form to search the users of student selected
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createSearchForm() {
        $estudiante = new Estudiante();
        
        $arrYears = ($this->session->get('roluser')==8)?array($this->session->get('currentyear')-1=>$this->session->get('currentyear')-1,$this->session->get('currentyear')=>$this->session->get('currentyear')):array($this->session->get('currentyear')=>$this->session->get('currentyear'));
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_new_student_result'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{10,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => $arrYears, 'attr' => array('class' => 'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * obtien el formulario para realizar la modificacion
     * @param Request $request
     */
    public function resultAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        //flag to know is a new estranjero student
        $sw = 0;
        $data = array();

        $form = $request->get('form');
      
        /**
         * add validation QA
         * @var [type]
         */
        $objObservation = $this->get('seguimiento')->getStudentObservationQA($form);
        // dump($objObservation);die;
        if($objObservation){
            $message = "Estudiante observado - rude " . $form['codigoRude'] . " :";
            $this->addFlash('notiext', $message);
            $observaionMessage='';
            // foreach ($objObservation as $key => $value) {
            //   $observaionMessage .=$value['obs']."-".$value['institucion_educativa_id']."*****";
            // }
            $observaionMessage = 'Estudiante presenta inconsistencia, se sugiere corregirlos por las opciones de calidad...';
            $this->addFlash('studentObservation', $observaionMessage);
            return $this->redirectToRoute('inscription_new_student_index');
        }


        if ($request->get('form')) {
            $form = $request->get('form');
        } else {
            // is turn on if the student exists
            $sw = 1;
            $data = array("id" => $request->get('id'), "codigoRude" => $request->get('codigoRude'), "pais" => $request->get('pais'), "ueprocedencia" => $request->get('ueprocedencia'));
            $form['codigoRude'] = $request->get('codigoRude');
        }

        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));

        //check if the student exists
        if ($student) {
            //check the tuicion
           //get Current Inscriptions By Gestoin

            $inscriptions = $this->getCurrentInscriptionsStudent($student->getCodigoRude());
            //dump($inscriptions); die;
            reset($inscriptions);
            $sw = true;
            $infoInscription=array();
            //look for the next level inscrption if it has
            while($sw &&  ($inscription = current($inscriptions))){
                /*get the last year inscription*/
                if($this->session->get('currentyear')-1 == $inscription['gestion'] and in_array($inscription['estadoMatriculaId'], array(11)) ){
                    $infoInscription = $inscription;
                    $sw = false;
                }else{
                    if($inscription['estadoMatriculaId']=='5' || $inscription['estadoMatriculaId']=='55' || $inscription['estadoMatriculaId']=='56' || $inscription['estadoMatriculaId']=='57' || $inscription['estadoMatriculaId']=='58' ){
                      $infoInscription = $inscription;
                      $sw=false;
                    }
                }
              next($inscriptions);
            }
            //dump($infoInscription); die;
            //get the last inscription
            if(!sizeof($infoInscription)>0){
              $cc=0;
              while ($cc <= 7 && $sw) {
                # code...
                reset($inscriptions);
                while($sw &&  ($inscription = current($inscriptions))){
                    if($inscription['gestion']==$form['gestion']-$cc){
                      $infoInscription = $inscription;
                      $sw=false;
                    }
                  next($inscriptions);
                }
                $cc++;
              }
            }

      // dump($infoInscription);die;
            $inscriptionsGestionSelected = $this->getCurrentInscriptionsByGestoin($student->getCodigoRude(), $form['gestion']);

            //check if the student was Approved on the gestion selected
            if ($inscriptionsGestionSelected) {
                $message = "El estudiante cuenta con inscripción en la gestion seleccionada";
                $this->addFlash('notiext', $message);
                return $this->redirectToRoute('inscription_new_student_index');
            }

            if($this->session->get('roluser')==9){ //si es director verificar que el operativo no este cerrado
              $objRegConsolidation =  $em->getRepository('SieAppWebBundle:RegistroConsolidacion')->findOneBy(array(
                    'unidadEducativa' => $this->session->get('ie_id'),  'gestion' => $this->session->get('currentyear')
                  ));
                if($objRegConsolidation){
                  $message = "No se puede realizar la inscripción debido a que la Unidad Educativa ya cerró su operativo de Inscripción";
                  $this->addFlash('notiext', $message);
                  return $this->redirectToRoute('inscription_new_student_index');
                }
            }

            $formInsc = $this->createFormInsc($student->getId(), $sw, $infoInscription, $form['gestion'], $form['codigoRude']);
            //everything is ok build the info
            return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:result.html.twig', array(
                        'datastudent' => $student,
                        'currentInscription' => $inscriptions,
                        'formInscription' => $formInsc->createView()
            ));
        } else {
            $message = "Estudiante con rude " . $form['codigoRude'] . " no se encuentra registrado";
            $this->addFlash('notiext', $message);
            return $this->redirectToRoute('inscription_new_student_index');
        }
    }
    /**
     * buil the Omitidos form
     * @param type $aInscrip
     * @return type form
     */
    private function createFormInsc($idStudent, $sw, $data, $gestionIns, $codigoRude) {

        $em = $this->getDoctrine()->getManager();

        return $formOmitido = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscription_new_student_save'))
                ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('nivel', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('grado', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                ->add('paralelo', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                ->add('turno', 'choice', array('attr' => array('class' => 'form-control', 'required' => false)))
                ->add('sw', 'hidden', array('data' => $sw))
                ->add('newdata', 'hidden', array('data' => serialize($data)))
                ->add('gestionIns', 'hidden', array('data' => $gestionIns))
                ->add('codigoRude', 'hidden', array('data'=>$codigoRude))
                ->add('observacionOmitido', 'hidden', array('label' => '', 'attr' => array('maxlength' => 250,'rows'=>"3" ,'class' => 'form-control','required' => true )))
                ->add('save', 'button', array('label' => 'Verificar y Registrar', 'attr'=> array('class' => 'btn btn-success' , 'onclick'=>'checkInscription()')))
                ->getForm();
    }

    private function getCurrentInscriptionsByGestoinValida($id, $gestion) {
    //$session = new Session();
        $swInscription = false;
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('ei.estadomatriculaTipo IN (:mat)')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                ->setParameter('mat', array( 3,4,5,6,10))
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();

            $objInfoInscription = $query->getResult();
            if(sizeof($objInfoInscription)>=1)
              return $objInfoInscription;
            else
              return false;

      }

    public function verificaInscriptionAction(Request $request){
      
      //create DB conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();

      //get values all data
      $form = $request->get('form');
      $swCorrectInscription = true;

      $setNotasInscription=false;
      if(unserialize($form['newdata'])){
        
      }else{ 
        $message = 'Algunos datos no son correctos, no se puede realizar la inscripción..';
        $this->addFlash('idNoInscription', $message);
        $swCorrectInscription = false;
        return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:menssageInscription.html.twig', array());

      }


      //validtation abuut if the ue close SEXTO
      if($form['nivel'] == 13 && $form['grado']==6 && $this->get('funciones')->verificarSextoSecundariaCerrado($form['institucionEducativa'],$form['gestionIns'])){
          $message = 'No se puede realizar la inscripción debido a que la Unidad Educativa seleccionada ya se cerro el operativo Sexto de Secundaria';
          $this->addFlash('idNoInscription', $message);
          return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:menssageInscription.html.twig', array('setNotasInscription'=> $setNotasInscription));
      }

      // validation if the ue is over 4 operativo
      $operativo = $this->get('funciones')->obtenerOperativo($form['institucionEducativa'],$form['gestionIns']);
      if($operativo >= 4){
        $message = 'No se puede realizar la inscripción debido a que para la Unidad Educativa seleccionada ya se consolidaron todos los operativos';
        $this->addFlash('idNoInscription', $message);
        return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:menssageInscription.html.twig', array('setNotasInscription'=> $setNotasInscription));
      }

      //validation inscription in the same U.E
      $objCurrentInscriptionStudent = $this->getCurrentInscriptionsByGestoinValida($form['codigoRude'],$form['gestionIns']);
//dump($objCurrentInscriptionStudent);die;
        if($objCurrentInscriptionStudent){
          if ($objCurrentInscriptionStudent[0]['sie']==$form['institucionEducativa']){
            $message = 'Estudiante ya cuenta con inscripción en la misma Unidad Educativa, realize el cambio de estado';
            $this->addFlash('idNoInscription', $message);
            return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:menssageInscription.html.twig', array(
               'setNotasInscription'=> $setNotasInscription,
            ));
          }
        }

      //dato actual de inscription
      $dataCurrentInscription = unserialize($form['newdata']);
     //  dump($dataCurrentInscription);
      //validate allow access
      $arrAllowAccessOption = array(7,8);
      if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){
        $defaultController = new DefaultCont();
        $defaultController->setContainer($this->container);

        $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$form['institucionEducativa'], 'gestion'=>$form['gestionIns']));
        //validate if the user download the sie file
        if($swAccess){
          $message = 'No se puede realizar la inscripción debido a que ya descargo el archivo SIE, favor realizar este proceso con su Tec. de Departamento';
          $this->addFlash('idNoInscription', $message);
          return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:menssageInscription.html.twig', array());
        }

      }

      ///validation
     
      $currentLevelStudent = $dataCurrentInscription['nivelId'].'-'.$dataCurrentInscription['cicloId'].'-'. $dataCurrentInscription['gradoId'];

      if (!($dataCurrentInscription['nivelId']>10)) {
        // getCourseOld
        $newInfInscription = $this->getCourseOld($dataCurrentInscription['nivelId'],$dataCurrentInscription['cicloId'],$dataCurrentInscription['gradoId'],$dataCurrentInscription['estadoMatriculaId']);
        $currentLevelStudent = $this->aCursos[$newInfInscription-1];
         //dump($this->aCursos[$newInfInscription-1]);
      }
      // dump($currentLevelStudent);die;
      $newLevelStudent = $form['nivel'].'-'.$this->getNewCicloStudent($form).'-'.$form['grado'];// dump($newLevelStudent);die;
   // dump($newLevelStudent); //12-1-1
    //dump(str_replace('-','',$currentLevelStudent) );die;
    //if doesnt have next curso info is new or extranjero do the inscription
     
    if( (str_replace('-','',$currentLevelStudent) )!=''){ 
      $arrIniPriLevel = array('11-1-0','11-1-1','11-1-2','12-1-1');
      if(in_array($currentLevelStudent, $arrIniPriLevel)){ 
        
          //|| ($currentLevelStudent == '12-1-1')
         //do the inscription
          $dataStudent = unserialize($form['newdata']);
          $arrYearStudent =$this->get('funciones')->getTheCurrentYear($dataStudent['fechaNacimiento']->format('d-m-Y'), '30-6-'.date('Y'));
          $yearStudent = $arrYearStudent['age'];  
        // //new student validation
        //  dump($yearStudent);
          if ($yearStudent<=6){
              switch ($yearStudent) {
                case 4:
                # check level
                $swCorrectInscription =(str_replace('-','',$newLevelStudent)=='1111')?true:false;
                break;
                case 5:
                # check level
                $swCorrectInscription =(str_replace('-','',$newLevelStudent)=='1112')?true:false;
                break;
                case 6:
                # check level
                $swCorrectInscription =(str_replace('-','',$newLevelStudent)=='1211')?true:false;
                break;
                default:
                  # code...
                  break;
              }

        }else{
          //do inscription
          $swCorrectInscription =(str_replace('-','',$newLevelStudent)=='1211')?true:false; //si es mayor a 6 años siempre debe ir a primaria
            
        }//end new student validation
        $keyNextLevelStudent = $this->getInfoInscriptionStudent($currentLevelStudent, $dataCurrentInscription['estadoMatriculaId']);
        $nextDataLevelStudent = str_replace('-','',$this->aCursos[$keyNextLevelStudent]);

        //dump($nextDataLevelStudent); 
        //dump($newLevelStudent);
        if($nextDataLevelStudent > str_replace('-','',$newLevelStudent) && $nextDataLevelStudent!='1112'){
          $swCorrectInscription = false;
        }
        if($nextDataLevelStudent==str_replace('-','',$newLevelStudent) &&  $swCorrectInscription==false ){
          $swCorrectInscription = true;
        }
        
        if($yearStudent >= 6 && str_replace('-','',$newLevelStudent)!='1211' && $nextDataLevelStudent<'1211'){
          $swCorrectInscription = false;
        }
       
        /////////////////////////////////
         // $keyNextLevelStudent = $this->getInfoInscriptionStudent($currentLevelStudent, $dataCurrentInscription['estadoMatriculaId']);

         // if($keyNextLevelStudent >= 0){
         //   if((str_replace('-','',$newLevelStudent)) < str_replace('-','',$currentLevelStudent) ){
         //     $message = 'Estudiante No Inscrito, no le puede bajar de curso';
         //     $this->addFlash('idNoInscription', $message);
         //     $swCorrectInscription = false;
         //   }else{
         //     //do the inscription
         //   }
         // }else{
         //   $message = 'Estudiante ya cuenta con inscripción';
         //   $this->addFlash('idNoInscription', $message);
         //   $swCorrectInscription = false;
         // }

       }else{ 
         //get the current level inscription to do the validation
         $keyNextLevelStudent = $this->getInfoInscriptionStudent($currentLevelStudent, $dataCurrentInscription['estadoMatriculaId']);
        //  dump($keyNextLevelStudent);die;
         //validate the next level
         if($keyNextLevelStudent>0){
           if($keyNextLevelStudent == sizeof($this->aCursos)){
             $message = 'Estudiante bachiller';
             $this->addFlash('idNoInscription', $message);
             $swCorrectInscription = false;
           }else{
             //validate the level selected
             //dump($newLevelStudent);
             //dump($this->aCursos[$keyNextLevelStudent]);die;
             if ($newLevelStudent == $this->aCursos[$keyNextLevelStudent]){
               //do the inscriptin
             }else{//dump($newInfInscription);die;
               $message = 'Estudiante No inscrito, el curso seleccionado no le corresponde.';////mensaje
               $this->addFlash('idNoInscription', $message);
               $swCorrectInscription = false;
             }
           }
           //dump(sizeof($this->aCursos));
           //dump('krlos');

         }else{
           $message = 'Estudiante ya cuenta con inscripción';
           $this->addFlash('idNoInscription', $message);
           $swCorrectInscription = false;
         }
       }
    }else{ 
      $message = 'Algunos datos no son correctos, no se puede realizar la inscripción ..';
      $this->addFlash('idNoInscription', $message);
      $swCorrectInscription = false;
      //do inscription inicial/ primaria or extranjero
      //get the year of student

         /* $dataStudent = unserialize($form['newdata']);
          $arrYearStudent =$this->get('funciones')->getTheCurrentYear($dataStudent['fechaNacimiento']->format('d-m-Y'), '30-6-'.date('Y'));
          $yearStudent = $arrYearStudent['age'];  
          // //new student validation
        if ($yearStudent<=6){
          switch ($yearStudent) {
            case 4:
            # check level
            $swCorrectInscription =(str_replace('-','',$newLevelStudent)=='1111')?true:false;
            break;
            case 5:
            # check level
            $swCorrectInscription =(str_replace('-','',$newLevelStudent)=='1112')?true:false;  
            break;
            case 6:
            # check level
            $swCorrectInscription =(str_replace('-','',$newLevelStudent)=='1211')?true:false;
            break;
            default:
              # code...
              break;
          }

        }else{
          //do inscription
          $swCorrectInscription =(str_replace('-','',$newLevelStudent)>='1212')?true:false;
        }//end new student validation
        $message='';
        if(!$swCorrectInscription){
          $message = 'Estudiante No inscrito, el curso seleccionado no le corresponde';
          $this->addFlash('idNoInscription', $message);
          $swCorrectInscription = false;
        }*/

    }///end validation
    if(!$swCorrectInscription){
      $message = 'Estudiante No inscrito, el curso seleccionado no le corresponde ..';
      $this->addFlash('idNoInscription', $message);
      $swCorrectInscription = false;
    }
      //dump($swCorrectInscription);
    //die;
    //check the inscription
       if($swCorrectInscription){
         //get the id of course
         $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
             'nivelTipo' => $form['nivel'],
             'gradoTipo' => $form['grado'],
             'paraleloTipo' => $form['paralelo'],
             'turnoTipo' => $form['turno'],
             'institucioneducativa' => $form['institucionEducativa'],
             'gestionTipo' => $form['gestionIns']
         ));
         //$student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=>$form['codigoRude']));
          //Recupero el CodUE de procedencia $form['codigoRude']
           $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude'] ));
           $query = $em->getConnection()->prepare('SELECT cod_ue_procedencia_id 
                                                   FROM estudiante_inscripcion  
                                                   WHERE estudiante_id ='. $estudiante->getId() .'
                                                   ORDER BY fecha_inscripcion desc limit 1');
           $query->execute();
           $ue_procedencia = $query->fetch();

           $id_usuario = $this->session->get('userId');

          // $obsId=($em->getRepository('SieAppWebBundle:ObservacionInscripcionTipo')->find(6))->getId();
         //inscriptions
         $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
         $query->execute();
         $studentInscription = new EstudianteInscripcion();
         $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
         $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
         $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
         $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
         $studentInscription->setObservacion('NUEVA INSCRIPCION');
         $studentInscription->setObservacionId(6);
         $studentInscription->setFechaInscripcion(new \DateTime('now'));
         $studentInscription->setFechaRegistro(new \DateTime('now'));
         $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
         $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(1));
         $studentInscription->setCodUeProcedenciaId($ue_procedencia['cod_ue_procedencia_id']);
         $studentInscription->setNumMatricula(0);
         $studentInscription->setUsuarioId($id_usuario);
         $em->persist($studentInscription);
         $em->flush();

         $query = $em->getConnection()->prepare('SELECT * from sp_crea_estudiante_asignatura_regular(:sie::VARCHAR, :estudiante_inscripcion_id::VARCHAR)');
         $query->bindValue(':estudiante_inscripcion_id', $studentInscription->getId());
         $query->bindValue(':sie', $form['institucionEducativa']);
         $query->execute();

         //add the areas to the student
         //$responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objCurso->getId(), $form['gestionIns']);

         // obtenemos las notas
         //$arrayNotas = $em->getRepository('SieAppWebBundle:EstudianteNota')->getArrayNotas($studentInscription->getId());
         //dump($arrayNotas);die;
         // Try and commit the transaction
         // Registro de materia curso oferta en el log
         $this->get('funciones')->setLogTransaccion(
             $studentInscription->getId(),
             'estudiante_inscripcion',
             'C',
             '',
             '',
             '',
             'SIGED',
             json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ ))
         );
         $em->getConnection()->commit();
         $message = 'Datos Registrados Correctamente';
         $this->addFlash('saveGoodInscription', $message);
         $setNotasInscription=true;
         return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:menssageInscription.html.twig', array(
           'setNotasInscription'=> $setNotasInscription,
           'setNotasForm'   => $this->setNotasForm($studentInscription->getId())->createView(),
           'idInscripcion'  => $studentInscription->getId()
         ));

         /*return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:notasIncriptions.html.twig', array(
             'asignaturas'=>$arrayNotas['notas'],'cualitativas'=>$arrayNotas['cualitativas'],'operativo'=>$arrayNotas['operativo'],
             'nivel'=>$form['nivel'],'idInscripcion'=>$studentInscription->getId()
         ));*/
         //notas
       }else{
         //no notas
         return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:menssageInscription.html.twig', array(
           'setNotasInscription'=> $setNotasInscription,
         ));

       }

     // die;
    }
    /*
    // this is the next step to do the setting up NOTAS to student
    */
    private function setNotasForm($data){
      return  $this->createFormBuilder()
                ->setAction($this->generateUrl('regularizarNotas_show'))
                // ->add('idInscripcion','text',array('data'=>$data))
                ->add('setNotas','submit',array('label'=>'Registrar Notas','attr'=>array('class'=>'btn btn-success')))
              ->getForm();
    }
    public function saveNotasInscriptionAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        // DAtos de las notas cuantitativas
        $idEstudianteNota = $request->get('idEstudianteNota');
        $idNotaTipo = $request->get('idNotaTipo');
        $idEstudianteAsignatura = $request->get('idEstudianteAsignatura');
        $notas = $request->get('nota');

        // Datos de las notas cualitativas
        $idEstudianteNotaCualitativa = $request->get('idEstudianteNotaCualitativa');
        $idNotaTipoCualitativa = $request->get('idNotaTipoCualitativa');
        $notaCualitativa = $request->get('notaCualitativa');


        $nivel = $request->get('nivel');
        $idInscripcion = $request->get('idInscripcion');
        // Registro y/o modificacion de notas
        for($i=0;$i<count($idEstudianteNota);$i++) {
            if($idEstudianteNota[$i] == 'nuevo'){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');")->execute();
                $newNota = new EstudianteNota();
                $newNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipo[$i]));
                $newNota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($idEstudianteAsignatura[$i]));
                if($nivel == 11){
                    $newNota->setNotaCuantitativa(0);
                    $newNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                }else{
                    $newNota->setNotaCuantitativa($notas[$i]);
                    $newNota->setNotaCualitativa('');
                }
                $newNota->setRecomendacion('');
                $newNota->setUsuarioId($this->session->get('userId'));
                $newNota->setFechaRegistro(new \DateTime('now'));
                $newNota->setFechaModificacion(new \DateTime('now'));
                $newNota->setObs('');
                $em->persist($newNota);
                $em->flush();
            }else{
                $updateNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->find($idEstudianteNota[$i]);
                if($updateNota){
                    if($nivel == 11){
                        $updateNota->setNotaCualitativa(mb_strtoupper($notas[$i],'utf-8'));
                    }else{
                        $updateNota->setNotaCuantitativa($notas[$i]);
                    }
                    $updateNota->setUsuarioId($this->session->get('userId'));
                    $updateNota->setFechaModificacion(new \DateTime('now'));
                    $em->flush();
                }
            }
        }

        // Registro de notas cualitativas de incial primaria yo secundaria
        for($j=0;$j<count($idEstudianteNotaCualitativa);$j++){
            if($idEstudianteNotaCualitativa[$j] == 'nuevo'){
                $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota_cualitativa');")->execute();
                $newCualitativa = new EstudianteNotaCualitativa();
                $newCualitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idNotaTipoCualitativa[$j]));
                $newCualitativa->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($idInscripcion));
                $newCualitativa->setNotaCuantitativa(0);
                $newCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                $newCualitativa->setRecomendacion('');
                $newCualitativa->setUsuarioId($this->session->get('userId'));
                $newCualitativa->setFechaRegistro(new \DateTime('now'));
                $newCualitativa->setFechaModificacion(new \DateTime('now'));
                $newCualitativa->setObs('');
                $em->persist($newCualitativa);
                $em->flush();
            }else{
                $updateCualitativa = $em->getRepository('SieAppWebBundle:EstudianteNotaCualitativa')->find($idEstudianteNotaCualitativa[$j]);
                if($updateCualitativa){
                    $updateCualitativa->setNotaCualitativa(mb_strtoupper($notaCualitativa[$j],'utf-8'));
                    $updateCualitativa->setUsuarioId($this->session->get('userId'));
                    $updateCualitativa->setFechaModificacion(new \DateTime('now'));
                    $em->flush();
                }
            }
        }
        $message = 'Datos Registrados Correctamente';
        $this->addFlash('saveGoodInscription', $message);
        return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:menssageInscription.html.twig', array());

    }


    /**
     *
     * @param type $nivel
     * @param type $grado
     */
    public function getInfoInscriptionStudent($currentLevelStudent, $matricula) {
      
        $sw = 1;
        $cursos = $this->aCursos;
        $keyLevel = ($matricula)?'':2;
        while (($acourses = current($cursos)) !== FALSE && $sw) {
            //$anivel = explode("-", current($cursos));
            if ($acourses == $currentLevelStudent) {
                $keyLevel = key($cursos);
                $sw = 0;
            }
            next($cursos);
        }
        switch ($matricula) {
          
          case 4:
            # efectivo
            $levelStudent = -1;
            break;
          case 5:
          case 55:
          case 56:
          case 57:
          case 58:
            # promovido
            $levelStudent = $keyLevel + 1;
            break;
          case 11:
            #repro
            $levelStudent = $keyLevel;
          default:
            # no next level
            $levelStudent = $keyLevel;
            break;
        }
        return ($levelStudent);
    }


    /**
     * get the new ciclo to the student
     * @param type $nivel
     * @param type $grado
     */
    public function getNewCicloStudent($data) {
      //dump($data);
      $sw = 1;
      $cursos = $this->aCursos;
      while (($acourses = current($cursos)) !== FALSE && $sw) {
          $arrLevel = explode("-", current($cursos));
          //dump($arrLevel);
          if ($arrLevel[0] == $data['nivel'] && $arrLevel[2] == $data['grado']) {
              $currentCiclo = $arrLevel[1];
              $sw = 0;
          }
          next($cursos);
      }
      return $currentCiclo;

    }

    /**
     * get the new ciclo to the student
     * @param type $nivel
     * @param type $grado
     */
    public function getNewCicloStudentold($data) {
      //dump($data);
      $sw = 1;
      $cursos = $this->aCursos;
      while (($acourses = current($cursos)) !== FALSE && $sw) {
          $arrLevel = explode("-", current($cursos));
          //dump($arrLevel);
          if ($arrLevel[0] == $data['nivel'] && $arrLevel[2] == $data['grado']) {
              $currentCiclo = $arrLevel[1];
              $sw = 0;
          }
          next($cursos);
      }
      return $currentCiclo;

    }


    ///get the year of student

    function tiempo_transcurrido($fecha_nacimiento, $fecha_control) {
        $fecha_actual = $fecha_control;

        if (!strlen($fecha_actual)) {
            $fecha_actual = date('d-m-Y');
        }

        // separamos en partes las fechas
        $array_nacimiento = explode("-", str_replace('/', '-', $fecha_nacimiento));
        $array_actual = explode("-", $fecha_actual);

        $anos = $array_actual[2] - $array_nacimiento[2]; // calculamos años
        $meses = $array_actual[1] - $array_nacimiento[1]; // calculamos meses
        $dias = $array_actual[0] - $array_nacimiento[0]; // calculamos días
        //ajuste de posible negativo en $días
        if ($dias < 0) {
            --$meses;

            //ahora hay que sumar a $dias los dias que tiene el mes anterior de la fecha actual
            switch ($array_actual[1]) {
                case 1:
                    $dias_mes_anterior = 31;
                    break;
                case 2:
                    $dias_mes_anterior = 31;
                    break;
                case 3:
                    if (bisiesto($array_actual[2])) {
                        $dias_mes_anterior = 29;
                        break;
                    } else {
                        $dias_mes_anterior = 28;
                        break;
                    }
                case 4:
                    $dias_mes_anterior = 31;
                    break;
                case 5:
                    $dias_mes_anterior = 30;
                    break;
                case 6:
                    $dias_mes_anterior = 31;
                    break;
                case 7:
                    $dias_mes_anterior = 30;
                    break;
                case 8:
                    $dias_mes_anterior = 31;
                    break;
                case 9:
                    $dias_mes_anterior = 31;
                    break;
                case 10:
                    $dias_mes_anterior = 30;
                    break;
                case 11:
                    $dias_mes_anterior = 31;
                    break;
                case 12:
                    $dias_mes_anterior = 30;
                    break;
            }

            $dias = $dias + $dias_mes_anterior;

            if ($dias < 0) {
                --$meses;
                if ($dias == -1) {
                    $dias = 30;
                }
                if ($dias == -2) {
                    $dias = 29;
                }
            }
        }

        //ajuste de posible negativo en $meses
        if ($meses < 0) {
            --$anos;
            $meses = $meses + 12;
        }

        $tiempo[0] = $anos;
        $tiempo[1] = $meses;
        $tiempo[2] = $dias;

        return $tiempo;
    }


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////check the code dowon



    /**
     * save inscription extranjero first time
     * @param Request $request
     * @return type
     */
    public function saveextAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        //encontramos las coincidencias
        $sameStudents = $this->getCoincidenciasStudent($form);

        return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:resultnorude.html.twig', array(
                    'newstudent' => $form,
                    'samestudents' => $sameStudents,
                    'formninguno' => $this->nobodyform($form)->createView()
        ));
    }

    public function savenewAction(Request $request) {
        $form = $request->get('form');

        $sw = 0;
        $data = array();
        $formExtranjeros = $this->createFormNewExtranjeros(0, $sw, $form);
        return $this->render($this->session->get('pathSystem') . ':InscriptionNewStudent:newextranjero.html.twig', array(
                    'datastudent' => $form,
                    'formExtranjeros' => $formExtranjeros->createView()
        ));
    }

    /**
     * todo the registration of extranjero
     * @param Request $request
     *
     */
    public function savenewextranjeroAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');

        try {
            //get info old about inscription
            //$dataNivel = $this->getInfoInscriptionStudent($form['nivel'], $form['grado']);

            $currentInscrip = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('estudiante' => $form['idStudent']));
            //create rude and get the student info
            $digits = 3;
            $mat = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);
            $rude = $form['institucionEducativa'] . $this->session->get('currentyear') . $mat . $this->gererarRude($form['institucionEducativa'] . $this->session->get('currentyear') . $mat);
            $newStudent = unserialize($form['newdata']);
            //put the id seq with the current data
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante');");
            $query->execute();
            //echo $rude;die('krlos');
            $student = new Estudiante();
            $student->setPaterno($newStudent['paterno']);
            $student->setMaterno($newStudent['materno']);
            $student->setNombre($newStudent['nombre']);
            $student->setCarnetIdentidad($newStudent['ci']);
            $student->setComplemento($newStudent['complemento']);
            $student->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->find($newStudent['genero']));
            $student->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->find($newStudent['pais']));
            $student->setFechaNacimiento(new \DateTime($newStudent['fnac']));
            $student->setCodigoRude($rude);
            //falta from UE && pais
            $em->persist($student);
            $em->flush();
            //print_r($currentInscrip);
            //get the id of course
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'nivelTipo' => $form['nivel'],
                'gradoTipo' => $form['grado'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'institucioneducativa' => $form['institucionEducativa'],
                'gestionTipo' => $this->session->get('currentyear')
            ));
            //put the id seq with the current data
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();

            $id_usuario = $this->session->get('userId');
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('currentyear')));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($student->getId()));
            $studentInscription->setObservacion(1);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(19));
            $studentInscription->setCodUeProcedenciaId(0);
            $studentInscription->setUsuarioId($id_usuario);
            $em->persist($studentInscription);
            $em->flush();

            $query = $em->getConnection()->prepare('SELECT * from sp_crea_estudiante_asignatura_regular(:sie::VARCHAR, :estudiante_inscripcion_id::VARCHAR)');
            $query->bindValue(':estudiante_inscripcion_id', $studentInscription->getId());
						$query->bindValue(':sie', $form['institucionEducativa']);
            $query->execute();
            // Try and commit the transaction
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodext', 'Inscripción Realizada...');
            return $this->redirect($this->generateUrl('inscription_new_student_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
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

    private function getCourseOld($nivel, $ciclo, $grado, $matricula) {
      //get the array of courses
        $cursos = $this->aCursosOld;
        //this is a switch to find the courses
        $sw = 1;
        $ind=0;
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
     * todo the registration of traslado
     * @param Request $request
     *
     */
    public function saveAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the variblees
        $form = $request->get('form');
      //dump($form);die;  
        try {
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'nivelTipo' => $form['nivel'],
                'gradoTipo' => $form['grado'],
                'paraleloTipo' => $form['paralelo'],
                'turnoTipo' => $form['turno'],
                'institucioneducativa' => $form['institucionEducativa'],
                'gestionTipo' => $form['gestionIns']
            ));
            //put the id seq with the current data
            $id_usuario = $this->session->get('userId');

            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
            $query->execute();
            //insert a new record with the new selected variables and put matriculaFinID like 5
            $studentInscription = new EstudianteInscripcion();
            $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
            $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestionIns']));
            $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
            $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
            $studentInscription->setObservacion(11);
            $studentInscription->setFechaInscripcion(new \DateTime('now'));
            $studentInscription->setFechaRegistro(new \DateTime('now'));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(1));
            $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
            $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(1));
            $studentInscription->setUsuarioId($id_usuario);
            $em->persist($studentInscription);
            $em->flush();
            
            //add the areas to the student
            //$query = $em->getConnection()->prepare('SELECT * from sp_genera_estudiante_asignatura(:estudiante_inscripcion_id::VARCHAR)');
            //$query->bindValue(':estudiante_inscripcion_id', $studentInscription->getId());
            //$query->execute();

            $query = $em->getConnection()->prepare('SELECT * from sp_crea_estudiante_asignatura_regular(:sie::VARCHAR, :estudiante_inscripcion_id::VARCHAR)');
            $query->bindValue(':estudiante_inscripcion_id', $studentInscription->getId());
						$query->bindValue(':sie', $form['institucionEducativa']);
            $query->execute();


            $responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objCurso->getId(), $form['gestionIns']);
            $em->getConnection()->commit();
            $this->session->getFlashBag()->add('goodext', 'Inscripción realizada sin problemas');
            return $this->redirect($this->generateUrl('inscription_new_student_index'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }

    /**
     * to add the areas to the student
     * @param type $studentInscrId
     * @param type $newCursoId
     * @return boolean
     */
    private function addAreasToStudent($studentInscrId, $newCursoId, $gestionIns) {
        $em = $this->getDoctrine()->getManager();
        //put the id seq with the current data
        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
        $query->execute();
        $areasEstudiante = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
            'estudianteInscripcion' => $studentInscrId,
            'gestionTipo' => $this->session->get('currentyear')
        ));
        //if doesnt have areas we'll fill these
        if (!$areasEstudiante) {
            $objAreas = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array(
                'insitucioneducativaCurso' => $newCursoId
            ));

            foreach ($objAreas as $areas) {
                //print_r($areas->getAsignaturaTipo()->getId());
                $studentAsignatura = new EstudianteAsignatura();
                $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestionIns));
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

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getCurrentInscriptionsStudent($id) {
//$session = new Session();
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId',
                 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId',
                 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa, IDENTITY(iec.cicloTipo) as cicloId, e.fechaNacimiento as fechaNacimiento')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaTipo', 'it', 'WITH', 'i.institucioneducativaTipo = it.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->andWhere('it = :idTipo')
                ->setParameter('id', $id)
                ->setParameter('idTipo',1)
                ->orderBy('iec.gestionTipo', 'DESC')
                ->addorderBy('ei.fechaInscripcion', 'DESC')
                ->getQuery();
        try {
            return $query->getResult();
        } catch (Exception $ex) {
            return $ex;
        }
    }

    /**
     * get the stutdents inscription - the record
     * @param type $id
     * @return \Sie\AppWebBundle\Controller\Exception
     */
    private function getCurrentInscriptionsByGestoin($id, $gestion) {
//$session = new Session();
        $swInscription = false;
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Estudiante');
        $query = $entity->createQueryBuilder('e')
                ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId', 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa')
                ->leftjoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                ->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->leftjoin('SieAppWebBundle:Institucioneducativa', 'i', 'WITH', 'iec.institucioneducativa = i.id')
                ->leftjoin('SieAppWebBundle:NivelTipo', 'n', 'WITH', 'iec.nivelTipo = n.id')
                ->leftjoin('SieAppWebBundle:GradoTipo', 'g', 'WITH', 'iec.gradoTipo = g.id')
                ->leftjoin('SieAppWebBundle:ParaleloTipo', 'p', 'WITH', 'iec.paraleloTipo = p.id')
                ->leftjoin('SieAppWebBundle:TurnoTipo', 't', 'WITH', 'iec.turnoTipo = t.id')
                ->leftJoin('SieAppWebBundle:EstadoMatriculaTipo', 'em', 'WITH', 'ei.estadomatriculaTipo = em.id')
                ->where('e.codigoRude = :id')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('i.institucioneducativaTipo = :insteducativaTipo')
                ->andwhere('ei.estadomatriculaTipo IN (:mat)')
                ->setParameter('id', $id)
                ->setParameter('gestion', $gestion)
                ->setParameter('insteducativaTipo', 1)
                ->setParameter('mat', array( 4,5,26,37,55,56,58))
                ->orderBy('iec.gestionTipo', 'DESC')
                ->getQuery();
        try {
            $objInfoInscription = $query->getResult();
            //dump($objInfoInscription);
            if(sizeof($objInfoInscription)>0){
              foreach ($objInfoInscription as $key => $value) {
                $objLastInscription = $value;
              }

              if(in_array($objLastInscription['nivelId'],array(1,2,3,11,12,13))){
                $swInscription=true;
              }else{
                $query = $em->getConnection()->prepare("select * from get_estudiante_historial_json('" . $id . "');");
                $query->execute();
                $dataInscriptionJson = $query->fetchAll();
                //dump($dataInscription);

                foreach ($dataInscriptionJson as $key => $inscription) {
                  # code...
                  $dataInscription  = json_decode($inscription['get_estudiante_historial_json'],true);
                  //check if the level if igual 15

                  if($dataInscription['nivel_tipo_id']==15)
                    $swInscription = true;

                }
              }
            }
            return $swInscription;;
        } catch (Exception $ex) {
            return $ex;
        }
    }


    /**
     * get the paralelto, turno and sie name
     * @param type $id like sie
     * @param type $n like nivel
     * @param type $g like grado
     * @return type
     */
    public function findIEAction($id, $gestionselected) {
        $em = $this->getDoctrine()->getManager();
        //get the tuicion
        //select * from get_ue_tuicion(137746,82480002)
        /*
          $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT)');
          $query->bindValue(':user_id', $this->session->get('userId'));
          $query->bindValue(':sie', $id);
          $query->execute();
          $aTuicion = $query->fetchAll();
         */
        $aniveles = array();
        // if ($aTuicion[0]['get_ue_tuicion']) {
        //get the IE
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
        $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "";
        $em = $this->getDoctrine()->getManager();
        //get the Niveles

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.nivelTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andwhere('iec.gestionTipo = :gestion')
                ->andwhere('iec.nivelTipo != :nivel')
                ->setParameter('sie', $id)
                ->setParameter('gestion', $gestionselected)
                ->setParameter('nivel', '13')
                ->distinct()
                ->getQuery();
        $aNiveles = $query->getResult();
        foreach ($aNiveles as $nivel) {
            $aniveles[$nivel[1]] = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel();
        }

        /*     } else {
          $nombreIE = 'No tiene Tuición';
          } */

     
        $response = new JsonResponse();

        return $response->setData(array('nombre' => $nombreIE, 'aniveles' => $aniveles));
    }

    /**
     * get grado
     * @param type $idnivel
     * @param type $sie
     * return list of grados
     */
    public function findgradoAction($idnivel, $sie, $gestionselected) {
        $em = $this->getDoctrine()->getManager();
        //get grado
        $agrados = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.gradoTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :idnivel')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('idnivel', $idnivel)
                ->setParameter('gestion', $gestionselected)
                ->distinct()
                ->orderBy('iec.gradoTipo', 'ASC')
                ->getQuery();
        $aGrados = $query->getResult();
        foreach ($aGrados as $grado) {
            $agrados[$grado[1]] = $em->getRepository('SieAppWebBundle:GradoTipo')->find($grado[1])->getGrado();
        }

        $response = new JsonResponse();
        return $response->setData(array('agrados' => $agrados));
    }

    /**
     * get the paralelos
     * @param type $idnivel
     * @param type $sie
     * @return type
     */
    public function findparaleloAction($grado, $sie, $nivel, $gestionselected) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $aparalelos = array();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.paraleloTipo)')
                //->leftjoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'ei.institucioneducativaCurso = iec.id')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('gestion', $gestionselected)
                ->distinct()
                ->orderBy('iec.paraleloTipo', 'ASC')
                ->getQuery();
        $aParalelos = $query->getResult();
        foreach ($aParalelos as $paralelo) {
            $aparalelos[$paralelo[1]] = $em->getRepository('SieAppWebBundle:ParaleloTipo')->find($paralelo[1])->getParalelo();
        }

        $response = new JsonResponse();
        return $response->setData(array('aparalelos' => $aparalelos));
    }

    /**
     * get the turnos
     * @param type $turno
     * @param type $sie
     * @param type $nivel
     * @param type $grado
     * @return type
     */
    public function findturnoAction($paralelo, $sie, $nivel, $grado, $gestionselected) {
        $em = $this->getDoctrine()->getManager();
//get grado
        $aturnos = array();

        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $entity->createQueryBuilder('iec')
                ->select('(iec.turnoTipo)')
                ->where('iec.institucioneducativa = :sie')
                ->andWhere('iec.nivelTipo = :nivel')
                ->andwhere('iec.gradoTipo = :grado')
                ->andwhere('iec.paraleloTipo = :paralelo')
                ->andWhere('iec.gestionTipo = :gestion')
                ->setParameter('sie', $sie)
                ->setParameter('nivel', $nivel)
                ->setParameter('grado', $grado)
                ->setParameter('paralelo', $paralelo)
                ->setParameter('gestion', $gestionselected)
                ->distinct()
                ->orderBy('iec.turnoTipo', 'ASC')
                ->getQuery();
        $aTurnos = $query->getResult();
        foreach ($aTurnos as $turno) {
            $aturnos[$turno[1]] = $em->getRepository('SieAppWebBundle:TurnoTipo')->find($turno[1])->getTurno();
        }

        $response = new JsonResponse();
        return $response->setData(array('aturnos' => $aturnos));
    }

}
