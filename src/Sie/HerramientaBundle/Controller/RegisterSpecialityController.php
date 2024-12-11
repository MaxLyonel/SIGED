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
use Sie\AppWebBundle\Entity\EstudianteInscripcionHumnisticoTecnico;
use Sie\AppWebBundle\Entity\InstitucioneducativaEspecialidadTecnicoHumanistico;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\User;

/**
 * RegisterSpeciality controller.
 *
 */
class RegisterSpecialityController extends Controller {

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
      //create DB conexion
      $em=$this->getDoctrine()->getManager();
      //get values send
        $infoUe = $request->get('infoUe');
        $infoStudent = $request->get('infoStudent');
        $arrInfoStudent  = json_decode($infoStudent,true);
        //dump(unserialize($infoUe));
        //get the specialty per student
        $objSpecialityStudent = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->getSpecialityPerStudent(
          $arrInfoStudent['eInsId']
        );

        //return the specialty template
        return $this->render($this->session->get('pathSystem') . ':RegisterSpeciality:registreSpeciality.html.twig', array(
                    'form' => $this->createRegistroForm($infoUe, $infoStudent)->createView(),
                    'student' => json_decode($infoStudent,true),
                    'objSpecialityStudent' => $objSpecialityStudent?$objSpecialityStudent[0]:false
        ));

        //  return $this->render('SieHerramientaBundle:RegisterSpeciality:notasTrimestre.html.twig',$data);

    }
    //create form to send the values todo the inscription
    private function createRegistroForm($infoUe, $infoStudent) {

      //create conexion DB
      $em = $this->getDoctrine()->getManager();

      $arrInfoUe = unserialize($infoUe);
      $arrInfoStudent = json_decode($infoStudent,true);

      $objSpeciality = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->getSpecialityByUnidadEducativa(
        $arrInfoUe['requestUser']['sie'],
        $arrInfoUe['requestUser']['gestion']
      );
      //built the speciailty array
      $arrSpeciality = array();
      foreach ($objSpeciality as $key => $speciality) {
        # code...
        $arrSpeciality[$speciality['ieethId'].'-'.$speciality['ethtId']] = $speciality['especialidad'];
      }
      //create fields form
        $form = $this->createFormBuilder()
                //->setAction($this->generateUrl('tecnico_humanistico_save'))
                //->add('especialidad', 'entity', array('label' => 'Especialidad', 'attr' => array('class' => 'form-control'), 'mapped' => false, 'class' => 'SieAppWebBundle:EspecialidadTipoHumnisticoTecnico'))
                ->add('especialidad', 'choice', array('label' => 'Especialidad','choices'=>$arrSpeciality, 'attr' => array('class' => 'form-control'), 'mapped' => false))
                ->add('horas', 'text', array('label' => 'Horas Cursadas', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{2,8}', 'maxlength' => '10', 'onkeypress' => 'onlyNumber(event)')))
                ->add('infoUe', 'hidden', array('data' => $infoUe))
                ->add('infoStudent', 'hidden', array('data' => $infoStudent))
                ->add('eInsId','hidden', array('data'=>$arrInfoStudent['eInsId']))
                //->add('registrar', 'button', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-default', 'onclick' => 'goSave(' . $idIns . ',' . $iddiv . ')')))
                ->add('registrar', 'button', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-default', 'onclick' => 'saveSpeciality()')))
                ->getForm();
        return $form;
    }

    public function saveAction(Request $request) {


        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        //get the values send
        $specialityId = $request->get('specialityId');
        $horas = $request->get('horas');
        $eInsId = $request->get('eInsId');
        //build the id speciality
        $arrSpecialities = explode('-', $specialityId);
//dump($eInsId);die;
        try {
          //        $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_inscripcion_humnistico_tecnico');");
          //        $query->execute();
                  $objInscriptionTecnicoHumanistico = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array(
                      'estudianteInscripcion' => $eInsId
                  ));
          //dump($objInscriptionTecnicoHumanistico);die;
                  //if the record exists
                  if ($objInscriptionTecnicoHumanistico) {
                      //todo the update
                      $objInscriptionTecnicoHumanistico->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($arrSpecialities[1]));
                      $objInscriptionTecnicoHumanistico->setInstitucioneducativaHumanisticoId($arrSpecialities[0]);
                      $objInscriptionTecnicoHumanistico->sethoras(($horas) ? $horas : 0);
                  } else {
                      //todo the insert
                      $objInscriptionTecnicoHumanistico = new EstudianteInscripcionHumnisticoTecnico();

                      $objInscriptionTecnicoHumanistico->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($arrSpecialities[1]));
                      $objInscriptionTecnicoHumanistico->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($eInsId));
                      $objInscriptionTecnicoHumanistico->setInstitucioneducativaHumanisticoId($arrSpecialities[0]);
                      $objInscriptionTecnicoHumanistico->sethoras(($horas) ? $horas : 0);
                      $objInscriptionTecnicoHumanistico->setObservacion('NUEVO.__.');
                  }
                  $em->persist($objInscriptionTecnicoHumanistico);
                  $em->flush();
                  // Try and commit the transaction
                  $em->getConnection()->commit();
                  die;
                  /*return $this->render($this->session->get('pathSystem') . ':TecnicoHumanistico:save.html.twig', array(
                              //'idIns' => $idIns,
                              //'iddiv' => $iddiv,
                              'rude' => 'krlos'
                  ));*/

        } catch (Exception $e) {
          $em->getConnection()->rollback();
          echo 'Excepción capturada: ', $ex->getMessage(), "\n";
        }
    }
}
