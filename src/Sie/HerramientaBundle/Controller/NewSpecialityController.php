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
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * NewSpeciality controller.
 *
 */
class NewSpecialityController extends Controller {

    public $session;
    public $idInstitucion;
    public $arrUeModular;
    public $unidadEducativa;
    public $arrUeRegularizar;
    public $arrUeNocturnas;
    public $arrUeTecTeg;
    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
    }


    public function indexAction(Request $request){

      return $this->render($this->session->get('pathSystem') . ':NewSpeciality:index.html.twig', array(
          'form'=> $this->formNewSpeciality()->createView(),
      ));
    }
    private function formNewSpeciality(){
      //set new gestion to the select year
      $aGestion = array();
      $currentYear = date('Y');
      for ($i = 1; $i <= 2; $i++) {
          $aGestion[$currentYear] = $currentYear;
          $currentYear--;
      }
    return $this->createFormBuilder()
          ->add('sie', 'text', array('data'=>'', 'attr'=>array('class'=>'form-control', 'placeholder'=>'CÓDIGO SIE')))
          ->add('gestion', 'choice', array('label' => 'Gestión', 'choices' => $aGestion, 'attr' => array('class' => 'form-control')))
          ->add('find', 'button', array('label' =>'Buscar', 'attr' => array('class' => 'btn btn-info', 'onclick'=>'findSpeciality()')))
          ->getForm();
    }

    public function findAction(Request $request){
      //create the DB conexion
      $em = $this->getDoctrine()->getManager();
      //get the values send
      $form = $request->get('form');
      $jsonForm = json_encode($form);

      $objSpeciality = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->getSpecialityPerUE($form['sie'],$form['gestion']);

      return $this->render($this->session->get('pathSystem').':NewSpeciality:find.html.twig',array(
        'specialities' => $objSpeciality,
        'dataForm'     => $jsonForm
      ));

      //get
    }

    public function newAction(Request $request){
      //get send values
      $dataForm = $request->get('dataForm');

      return $this->render($this->session->get('pathSystem').':NewSpeciality:newspecialities.html.twig', array(
        'form'=>$this->newFormSpe($dataForm)->createView()
      ));
    }

    private function newFormSpe($dataForm){
      return $this->createFormBuilder()
            ->add('especialidad', 'entity', array('label' => 'Especialidad', 'attr' => array('class' => 'form-control'),
                'mapped' => false, 'class' => 'SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo',

            ))
            ->add('dataForm', 'hidden', array('data'=>$dataForm))
            ->add('save', 'button', array('label'=>'Guardar', 'attr'=>array('class'=>'btn btn-theme', 'onclick'=>'saveSpeciality()')))
            ->add('cancel', 'button', array('label'=>'Cancelar', 'attr'=>array('class'=>'btn btn-default', 'data-dismiss'=>'modal')))
            ->getForm();
    }

    public function saveAction(Request $request){
      //create the DB conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();
      //get sen values
      $sendValues = $request->get('form');
      $dataUnidadEducativa = json_decode($sendValues['dataForm'], true);

      //get the values send
      $form = json_decode($sendValues['dataForm'],true);
      $jsonForm = $sendValues['dataForm'];

      //look for especialidad per unidad Educativa and gestion
      $objSpecialityUe = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findOneBy(array(
        'institucioneducativa'=>$form['sie'],
        'gestionTipo'=>$form['gestion'],

      ));

      $InstitucioneducativaEspecialidadTecnicoHumanisticoExist = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findOneBy(array(
        'institucioneducativa'               => $dataUnidadEducativa['sie'],
        'gestionTipo'                        => $dataUnidadEducativa['gestion'],
        'especialidadTecnicoHumanisticoTipo' => $sendValues['especialidad']
      ));
      if($InstitucioneducativaEspecialidadTecnicoHumanisticoExist){
        //exist speciality do nothing
        $message = 'Especialidad no registrada, ya existe...';
        $typemessage='warning';
      }else{
        try {
          $InstitucioneducativaEspecialidadTecnicoHumanisticoObj = new InstitucioneducativaEspecialidadTecnicoHumanistico();
          $InstitucioneducativaEspecialidadTecnicoHumanisticoObj->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->find($dataUnidadEducativa['sie']));
          $InstitucioneducativaEspecialidadTecnicoHumanisticoObj->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($sendValues['especialidad']));
          $InstitucioneducativaEspecialidadTecnicoHumanisticoObj->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($dataUnidadEducativa['gestion']));
          $InstitucioneducativaEspecialidadTecnicoHumanisticoObj->setFechaRegistro(new \DateTime('now'));
          $em->persist($InstitucioneducativaEspecialidadTecnicoHumanisticoObj);
          $em->flush();
          $em->getConnection()->commit();
          $message = 'La especialidad fue registrada...';
          $typemessage='success';
        } catch (Exception $e) {
          $em->getConnection()->rollback();
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }

      }
      $this->session->set('typemessage',$typemessage);
      $this->addFlash('noticeSpeciality', $message);

      $objSpeciality = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->getSpecialityPerUE($form['sie'],$form['gestion']);

      return $this->render($this->session->get('pathSystem').':NewSpeciality:find.html.twig',array(
        'specialities' => $objSpeciality,
        'dataForm'     => $jsonForm
      ));

      //get
    }

    public function removeAction(Request $request){
      //create DB conexion
      $em = $this->getDoctrine()->getManager();
      $em->getConnection()->beginTransaction();

      //get the values Send
      $specialityId = $request->get('specialityId');
      $jsonForm = $request->get('dataForm');
      $form = json_decode($jsonForm, true);

/*
      select * from institucioneducativa_especialidad_tecnico_humanistico where institucioneducativa_id = 80930002 and gestion_tipo_id = 2016 limit 10
      select * from estudiante_inscripcion_humnistico_tecnico
      where institucioneducativa_humanistico_id in ( 2,  43   )
      */
      //valitdate if the especialiti
      //remove data
      $InstitucioneducativaEspecialidadTecnicoHumanisticoExist = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->findOneBy(array(
        'institucioneducativa'               => $form['sie'],
        'gestionTipo'                        => $form['gestion'],
        'especialidadTecnicoHumanisticoTipo' => $specialityId
      ));

      //search the studens to this speciality
      $studentsSSpeciality = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findBy(array(
        'institucioneducativaHumanisticoId' => $InstitucioneducativaEspecialidadTecnicoHumanisticoExist->getId()
      ));
      //if it has students dont remove
      if($studentsSSpeciality){
        $message = 'La especialidad no eliminada, por que cuenta con estudiantes...';
        $typemessage='warning';
      }else{
        if($InstitucioneducativaEspecialidadTecnicoHumanisticoExist){
          //exist speciality remove it
          try {
            //$InstitucioneducativaEspecialidadTecnicoHumanisticoExist
            $em->remove($InstitucioneducativaEspecialidadTecnicoHumanisticoExist);
            $em->flush();
            $em->getConnection()->commit();
            $message = 'La especialidad fue removida...';
            $typemessage='success';
          } catch (Exception $e) {
            $em->getConnection()->rollback();
            echo 'Excepción capturada: ', $ex->getMessage(), "\n";
          }
        }else{
          $message = 'Especialidad no eliminada...';
          $typemessage='warning';

        }
      }
      //get data about result
      $this->session->set('typemessage',$typemessage);
      $this->addFlash('noticeSpeciality', $message);

      //get data to review template
      $objSpeciality = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->getSpecialityPerUE($form['sie'],$form['gestion']);

      return $this->render($this->session->get('pathSystem').':NewSpeciality:find.html.twig',array(
        'specialities' => $objSpeciality,
        'dataForm'     => $jsonForm
      ));

    }


}
