<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Estudiante;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;

class InscriptionSpecialNewController extends Controller
{

    private $session;
    public function __construct(){
      $this->session = new Session();
    }
    /**
     * [indexAction description]
     * @return [type] [description]
     */
    public function indexAction()
    {
        return $this->render('SieRegularBundle:InscriptionSpecialNew:index.html.twig', array(
                // ...
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

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('inscriptionSpecialNew_find'))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'Rude', 'required' => true, 'invalid_message' => 'campo obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{10,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => array($this->session->get('currentyear')=>$this->session->get('currentyear')), 'attr' => array('class' => 'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
     }
    public function findAction(Request $request ){
      //get values to send by post
      $form = $request->get('form');

      $sw=0;
      //create db conexion
      $em = $this->getDoctrine()->getManager();
      //get one less inscription of student
      $inscriptionsGestionSelected = $this->getCurrentInscriptionsByGestoin($form['codigoRude'], $form['gestion']-1,4);
      $swDooInscription = $this->reviewInscription($inscriptionsGestionSelected);

      //look for the current inscription
      $inscriptionsGestionSelected = $this->getCurrentInscriptionsByGestoin($form['codigoRude'], $form['gestion'],1);
      $swDooInscriptionCurrent = $this->reviewInscription($inscriptionsGestionSelected);

      if( $swDooInscription ){
        if($swDooInscriptionCurrent){
          $message = 'Estudiante ya cuenta con registro de Inscripcion';
          $this->addFlash('notiext', $message);
          return $this->render('SieRegularBundle:InscriptionSpecialNew:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
              ));
        }else{

        }
      }else{
        $message = 'Estudiante ya cuenta con registro de Inscripcion';
        $this->addFlash('notiext', $message);
        return $this->render('SieRegularBundle:InscriptionSpecialNew:index.html.twig', array(
                  'form' => $this->createSearchForm()->createView(),
            ));
      }

      // die;
      //get the students data
      $datastudent = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude'=> strtoupper($form['codigoRude']) )) ;
      $form['idStudent'] = $datastudent->getId();
      $formExtranjeros = $this->createFormNewExtranjeros($datastudent->getId(), $sw, $form);
      // dump($form);die;
        return $this->render('SieRegularBundle:InscriptionSpecialNew:find.html.twig', array(
          'datastudent' => $datastudent,
          'formExtranjeros' => $formExtranjeros->createView(),
          'infoStudent' => $form
            ));
      }

      private function  reviewInscription($idTypeUE){
        $sw = true;
        switch ($idTypeUE) {

          case 0:
            # code...
            $sw=false;
            break;
          case 1:
            # code...
            $sw=true;
            break;
          case 2:
          case 3:
            # code...
            $sw=false;
            break;

          case 4:
            # code...
            $sw=true;
            break;
          case 5:
          case 6:
          case 7:
          case 8:
            # code...
            $sw=false;
            break;

          default:
            # code...
            break;
        }

        return $sw;
      }

      /**
       * buil the Omitidos form
       * @param type $aInscrip
       * @return type form
       */
      private function createFormNewExtranjeros($idStudent=0, $sw=0, $data=array()) {

          $em = $this->getDoctrine()->getManager();

          return $formOmitido = $this->createFormBuilder()
                  ->setAction($this->generateUrl('inscriptionSpecialNew_do_inscription'))
                  ->add('institucionEducativa', 'text', array('label' => 'SIE', 'attr' => array('maxlength' => 8, 'class' => 'form-control')))
                  ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                  ->add('nivel', 'choice', array('attr' => array('class' => 'form-control')))
                  ->add('grado', 'choice', array('attr' => array('class' => 'form-control')))
                  ->add('idStudent', 'hidden', array('mapped' => false, 'data' => $idStudent))
                  ->add('paralelo', 'choice', array('attr' => array('class' => 'form-control', 'required' => true)))
                  ->add('turno', 'choice', array('attr' => array('class' => 'form-control', 'required' => false)))
                  ->add('sw', 'hidden', array('data' => $sw))
                  ->add('gestion', 'hidden', array('data' => $data['gestion']))
                  ->add('newdata', 'hidden', array('data' => serialize($data)))
                  // ->add('optionInscription','hidden', array('data'=>$data['optionInscription']))
                  ->add('save', 'submit', array('label' => 'Verificar y Registrar', 'attr'=>array()))
                  ->getForm();
      }
      /**
       * [getCurrentInscriptionsByGestoinValida description]
       * @param  [type] $id      [description]
       * @param  [type] $gestion [description]
       * @return [type]          [description]
       */
       private function getCurrentInscriptionsByGestoin($id, $gestion,$instTipo) {
     //$session = new Session();
           $swInscription = false;
           $em = $this->getDoctrine()->getManager();

           $entity = $em->getRepository('SieAppWebBundle:Estudiante');
           $query = $entity->createQueryBuilder('e')
                   ->select('n.nivel as nivel', 'g.grado as grado', 'p.paralelo as paralelo', 't.turno as turno', 'em.estadomatricula as estadoMatricula', 'IDENTITY(iec.nivelTipo) as nivelId', 'IDENTITY(iec.gestionTipo) as gestion', 'IDENTITY(iec.gradoTipo) as gradoId', 'IDENTITY(iec.turnoTipo) as turnoId'
                   , 'IDENTITY(ei.estadomatriculaTipo) as estadoMatriculaId', 'IDENTITY(iec.paraleloTipo) as paraleloId', 'ei.fechaInscripcion', 'i.id as sie', 'i.institucioneducativa', 'IDENTITY(i.institucioneducativaTipo) as institucioneducativaTipo ')
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
                   ->andwhere('i.institucioneducativaTipo = :instTipo')
                   ->setParameter('id', $id)
                   ->setParameter('gestion', $gestion)
                   ->setParameter('instTipo', $instTipo)
                   ->setParameter('mat', array( 4,5,26,37,55,56,57,58 ))
                   ->orderBy('iec.gestionTipo', 'DESC')
                   ->getQuery();
           try {
               $objInfoInscription = $query->getResult();
               //dump($objInfoInscription);
               if(sizeof($objInfoInscription)>0){
                 foreach ($objInfoInscription as $key => $value) {
                   $objLastInscription = $value;
                 }
                 return $objLastInscription['institucioneducativaTipo'];
                //  if(in_array($objLastInscription['institucioneducativaTipo'],array(4))){
                //    $swInscription=true;
                //  }else{
                //    $swInscription=false;
                //   }

               }
               return $swInscription;

           } catch (Exception $ex) {
               return $ex;
           }
       }




    public function doInscriptionAction(Request $request){


              $em = $this->getDoctrine()->getManager();
              $em->getConnection()->beginTransaction();
              //get the variblees
              $form = $request->get('form');
              // dump($form);die;
              $aDataStudent = unserialize($form['newdata']);
              // $aDataOption = json_decode($aDataStudent['dataOption'],true);

              try {

                //validation inscription in the same U.E
                // $objCurrentInscriptionStudent = $this->getCurrentInscriptionsByGestoinValida($aDataStudent['codigoRude'],$aDataStudent['gestion']);
                //
                //   if($objCurrentInscriptionStudent){
                //     if ($objCurrentInscriptionStudent[0]['sie']==$form['institucionEducativa']){
                //       $this->session->getFlashBag()->add('notiext', 'Estudiante ya cuenta con inscripción en la misma Unidad Educativa, realize el cambio de estado');
                //       return $this->redirect($this->generateUrl('inscription_extranjeros_index'));
                //     }
                //   }
                //check if the user can do the inscription
                //validate allow access
                  $arrAllowAccessOption = array(7,8);
                  if(!in_array($this->session->get('roluser'),$arrAllowAccessOption)){
                    $defaultController = new DefaultCont();
                    $defaultController->setContainer($this->container);

                    $swAccess = $defaultController->getAccessMenuCtrl(array('sie'=>$form['institucionEducativa'], 'gestion'=>$form['gestion']));
                    //validate if the user download the sie file
                    if($swAccess){
                      $message = 'No se puede realizar la inscripción debido a que ya descargo el archivo SIE';
                      $this->addFlash('notiext', $message);
                      return $this->redirect($this->generateUrl('inscriptionSpecialNew_index'));
                    }
                  }


                  //insert a new record with the new selected variables and put matriculaFinID like 5
                  $objCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                      'nivelTipo' => $form['nivel'],
                      'gradoTipo' => $form['grado'],
                      'paraleloTipo' => $form['paralelo'],
                      'turnoTipo' => $form['turno'],
                      'institucioneducativa' => $form['institucionEducativa'],
                      'gestionTipo' => $form['gestion']
                  ));
                  $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion');");
                  $query->execute();
                  //insert a new record with the new selected variables and put matriculaFinID like 5
                  $studentInscription = new EstudianteInscripcion();
                  $studentInscription->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($form['institucionEducativa']));
                  $studentInscription->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['gestion']));
                  $studentInscription->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
                  $studentInscription->setEstudiante($em->getRepository('SieAppWebBundle:Estudiante')->find($form['idStudent']));
                  $studentInscription->setObservacion(1);
                  $studentInscription->setFechaInscripcion(new \DateTime('now'));
                  $studentInscription->setFechaRegistro(new \DateTime('now'));
                  $studentInscription->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($objCurso->getId()));
                  $studentInscription->setEstadomatriculaInicioTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(74));
                  $em->persist($studentInscription);
                  $em->flush();
                  //add the areas to the student
                  $responseAddAreas = $this->addAreasToStudent($studentInscription->getId(), $objCurso->getId(),$form['gestion']);

                  // Try and commit the transaction
                  $em->getConnection()->commit();

                  $message = 'Datos Registrados Correctamente';
                  $this->addFlash('goodext', $message);
                  //return $this->redirect($this->generateUrl('inscription_extranjeros_index'));

                  return $this->forward('SieRegularBundle:RegularizarNotas:show',array(
                    'idInscripcion'=>$studentInscription->getId()
                    ));

              } catch (Exception $ex) {
                  $em->getConnection()->rollback();
                  echo 'Excepción capturada: ', $ex->getMessage(), "\n";
              }
        // return $this->render('SieRegularBundle:InscriptionSpecialNew:doInscription.html.twig', array(
        //         // ...
        //     ));
    }

    /**
     * to add the areas to the student
     * @param type $studentInscrId
     * @param type $newCursoId
     * @return boolean
     */
    private function addAreasToStudent($studentInscrId, $newCursoId, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $areasEstudiante = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array(
            'estudianteInscripcion' => $studentInscrId,
            'gestionTipo' => $gestion
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
                $studentAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($gestion));
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


    public function resultAction()
    {
        return $this->render('SieRegularBundle:InscriptionSpecialNew:result.html.twig', array(
                // ...
            ));    }

}
