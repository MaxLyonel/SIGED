<?php

namespace Sie\DgesttlaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\EspecialAreaTipo;
use Sie\AppWebBundle\Entity\InstitucioneducativaAreaEspecialAutorizado;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\InstitucioneducativaNivelAutorizado;
use Sie\AppWebBundle\Entity\InstitucioneducativaSucursal;
use Sie\AppWebBundle\Entity\TtecEstudianteInscripcion;

class InscriptionDgController extends Controller {
  //def session var
  public $session;
  // implemtne the construct
   public function __construct(){
     $this->session = new Session();

   }
   //index action to input the ci to find the student
    public function indexAction(Request $request){
      //validate if the session is still alive
      $id_usuario = $this->session->get('userId');
      if (!isset($id_usuario)) {
          return $this->redirect($this->generateUrl('login'));
      }

      $formInstitucioneducativaId = $this->createSearch();
      return $this->render('SieDgesttlaBundle:InscriptionDg:index.html.twig', array(
        'formSearch' => $formInstitucioneducativaId->createView()
      ));
    }

    private function createSearch() {
      $form = $this->createFormBuilder()
      // ->setAction($this->generateUrl('dgesttla_inst_result'))
          // ->add('tipo_search', 'hidden', array('data' => 'institucioneducativa'))
                ->add('ciStudent', 'text', array('label'=>'Carnet de Indentidad','required' => true, 'invalid_message' => 'Campo obligatorio'))
                ->add('lookAsignatures', 'button', array('label' => 'buscar', 'attr' => array('class' => 'btn btn-success btn-block btn-xs', 'onclick' => 'lookDataAsignaturas()')))

                ->getForm();
        return $form;
    }

    public function lookAsignaturesAction(Request $request){
      //get the data send
      $form = $request->get('form');

      //create DB conexion
      $em = $this->getDoctrine()->getManager();
      //look student to check if the person exist
      $objPerson = $em->getRepository('SieAppWebBundle:Persona')->findOneBy(array(
        'carnet' => $form['ciStudent'],
      ));

      $objNextMaterias = array();

      //student to check if the person exist
      if($objPerson){

        //look the inscription on this year
        $objInscription = $em->getRepository('SieAppWebBundle:TtecEstudianteInscripcion')->findOneBy(array(
          'persona'=>$objPerson->getId()
        ));
        $form['personaId'] = $objPerson->getId();
        //check if it has history
        if(is_object($objInscription)){

          //get the history
            $objInscriptionHistory =   $this->get('dgfunctions')->getInscriptionHistory($objPerson->getId());
            //get last
            $objLastInscription =   $this->get('dgfunctions')->getLastInscription($objPerson->getId());

            // dump($objLastInscription);die;
            reset($objLastInscription);
            $arrayCurrentPeriodo = array();
            //get the next student-s periodo
            while($data = current($objLastInscription)){
            // dump($data['estadomatricula_tipo_fin_id'].'/'.$data['periodoid']);

              //check it the studenets status is not 4
              if($data['estadomatricula_tipo_fin_id']!=4){
                if($data['estadomatricula_tipo_fin_id']==5){
                  //students status promovido
                  $arrayCurrentPeriodo[$data['periodoid']+1] = array('personaId'=>$objPerson->getId(),'periodoid'=>$data['periodoid']+1,'denominacionId'=>$data['denominacionid'], 'gestionIns'=>$this->session->get('currentyear')  );
                }else {
                  //students status reprobado
                  $arrayCurrentPeriodo[$data['periodoid']] = array('personaId'=>$objPerson->getId(),'periodoid'=>$data['periodoid'],'denominacionId'=>$data['denominacionid'], 'gestionIns'=>$this->session->get('currentyear')  );
                }
              }else {
                $arrayCurrentPeriodo[$data['periodoid']] = array('personaId'=>$objPerson->getId(),'periodoid'=>$data['periodoid'],'denominacionId'=>$data['denominacionid'], 'gestionIns'=>$this->session->get('currentyear')  );
              }

              next($objLastInscription);
            }
            // dump($arrayCurrentPeriodo);
            // die;
            $arrayNextAsignaturas = array();
            foreach ($arrayCurrentPeriodo as $key => $value) {
                // $query = $em->getConnection()->prepare("select * from sp_validacion_superior_asignatura_corresponde_web('" . $value['personaId'] . "','" . $value['periodoid'] . "','" . $value['denominacinoId'] . "');");
                // $query->execute();
                // $datoNextAsig = $query->fetchAll();
                // dump($value);
                // dump($value);
                // die;
                 $objNextMaterias = $this->get('dgfunctions')->getNextMaterias($value,'old');
                 // dump($objNextMaterias);
                // $arrayNextAsignaturas[]= $datoNextAsig;
              // dump($value);
            }

            return $this->render('SieDgesttlaBundle:InscriptionDg:lookAsignatures.html.twig', array(
                    'objInscriptionHistory' => $objInscriptionHistory,
                    'objNextMaterias' => $objNextMaterias,
                    'data' => serialize($form),
                    'objPerson' => $objPerson,

                ));
        }else{
          //no history
          //new inscription

          return $this->render('SieDgesttlaBundle:InscriptionDg:newInscription.html.twig', array(
                  'objPersonId' => $objPerson->getId(),
                  'formNewInscription' => $this->formNewInscription($objPerson->getId())->createView(),
                  'objPerson' => $objPerson,
          ));
        }

      }else{
        //no exist person
          echo 'no existe persona';die;
      }
// dump($objInscriptionHistory);die;
      return $this->render('SieDgesttlaBundle:InscriptionDg:lookAsignatures.html.twig', array(
              'objInscriptionHistory' => $objInscriptionHistory,
              'objLastInscription' => $objLastInscription,
              'objPerson' => $objPerson,
          ));
    }

    private function formNewInscription($personaId) {
      $form = $this->createFormBuilder()
      // ->setAction($this->generateUrl('dgesttla_inst_result'))
          // ->add('tipo_search', 'hidden', array('data' => 'institucioneducativa'))
                ->add('institucionEducativa', 'text', array('label'=>'RIE','required' => true, 'invalid_message' => 'Campo obligatorio', 'attr'=>array('class'=>'form-control')))
                ->add('institucionEducativaName', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('carreras', 'choice', array('attr' => array('class' => 'form-control')))
                ->add('denominacionId', 'choice', array('label'=>'Denominacion','attr' => array('class' => 'form-control')))
                ->add('personaId', 'hidden', array('data'=>$personaId))
                ->add('gestionIns', 'hidden', array('data' => $this->session->get('currentyear')))
                ->add('periodoid', 'choice', array('label'=>'Periodo','choices'=>array('10'=>'ANUAL','1'=>'SEMESTRE'),'attr' => array('class' => 'form-control')))
                ->add('Regitrar', 'button', array('label' => 'buscar', 'attr' => array('class' => 'btn btn-success btn-block btn-xs', 'onclick' => 'doRegistration()')))

                ->getForm();
        return $form;
    }



    public function getCarrerasAction(Request $request){

      //get the send values
      $id = $request->get('id');
      $gestionselected = $request->get('gestionselected');

      $em = $this->getDoctrine()->getManager();

      $aCarreras = array();
      // if ($aTuicion[0]['get_ue_tuicion']) {
      //get the IE
      $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);
      $nombreIE = ($institucion) ? $institucion->getInstitucioneducativa() : "";
      $em = $this->getDoctrine()->getManager();
      //get the Niveles

      $aResult =   $this->get('dgfunctions')->getCarrerasBySie($id);
      // dump($aResult);die;
      foreach ($aResult as $carrera) {

        $aCarreras[$carrera['ctid']] = $carrera['nombre'];
          // $aCarreras[$nivel[1]] = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel();
      }

      $response = new JsonResponse();

      return $response->setData(array('nombre' => $nombreIE, 'aCarreras' => $aCarreras));
    }

    public function getDenominationAction(Request $request){

      //get the send values
      $id = $request->get('id');
      $gestionselected = $request->get('gestionselected');
      $institucionId = $request->get('institucionId');

      $em = $this->getDoctrine()->getManager();

      $aDenomination = array();

      $aResult =   $this->get('dgfunctions')->getDenominations($id);

      // dump($aResult);die;
      foreach ($aResult as $denomination) {

        $aDenomination[$denomination['id']] = $denomination['denominacion'];
          // $aCarreras[$nivel[1]] = $em->getRepository('SieAppWebBundle:NivelTipo')->find($nivel[1])->getNivel();
      }

      $response = new JsonResponse();

      return $response->setData(array('aDenomination' => $aDenomination));
    }

    public function doRegistrationAction(Request $request){
      //create db conexion
      $em = $this->getDoctrine()->getManager();
      //get the send values
      $form = $request->get('form');
//dump($form);die;
      //look student to check if the person exist
      $objPerson = $em->getRepository('SieAppWebBundle:Persona')->find($form['personaId']);
// dump($objPerson);die;
      $objNextMaterias = $this->get('dgfunctions')->getNextMaterias($form,'new');
      // dump($objNextMaterias);die;
      return $this->render('SieDgesttlaBundle:InscriptionDg:nextMaterias.html.twig', array(
              'objNextMaterias' => $objNextMaterias,
              'data' => serialize($form),
              'objPerson' => $objPerson,

          ));

      // dump($form);
      // //create db conexion
      // $em = $this->getDoctrine()->getManager();
      // $query = $em->getConnection()->prepare("select * from sp_validacion_superior_asignatura_corresponde_web('" . $form['personaId'] . "','10','" . $form['denominacion'] . "');");
      // $query->execute();
      // $datoNextAsig = $query->fetchAll();
      // foreach ($datoNextAsig as $key => $value) {
      //   dump($value);
      // }
      //$arrayNextAsignaturas[]= $datoNextAsig;

    }

    public function doInscriptionDgAction(Request $request){
      // create DB conexion
      $em = $this->getDoctrine()->getManager();
      //get the send values
      $dataSend = $request->get('verificarReg');
      $arrInfo = unserialize($dataSend['data']);
      $selectedAsig = $dataSend['parmat'];
      // dump($dataSend);
      // dump($selectedAsig);

      $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('ttec_estudiante_inscripcion');");
      $query->execute();

      reset($selectedAsig);
      while($val = current($selectedAsig)){

        $objInscription = new TtecEstudianteInscripcion();
        $objInscription->setEstadomatriculaTipoInicio($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(1));
        $objInscription->setEstadomatriculaTipoFin($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->find(4));
        $objInscription->setFechaInscripcion(new \DateTime('now'));
        $objInscription->setFechaRegistro(new \DateTime('now'));
        $objInscription->setTtecParaleloMateria($em->getRepository('SieAppWebBundle:TtecParaleloMateria')->find(key($selectedAsig)));
        $objInscription->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($arrInfo['personaId']));
        $em->persist($objInscription);
        $em->flush();
        next($selectedAsig);
      }

      //inscriptinoDgOk
      $message = 'Estudiante registrado satisfactoriamente...';
      $this->addFlash('inscriptinoDgOk', $message);

      return $this->redirect($this->generateUrl('InscriptionDg_index'));
      dump('krlos');die;
      return $this->render('SieDgesttlaBundle:InscriptionDg:doInscriptionDg.html.twig', array(
          // ...
      ));
    }

}
