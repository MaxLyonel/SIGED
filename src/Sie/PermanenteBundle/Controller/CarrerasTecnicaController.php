<?php

namespace Sie\PermanenteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\CarreraTipo;
use Sie\AppWebBundle\Entity\CarreraespecialidadTipo;

/**
 * EstudianteInscripcion controller.
 *
 */
class CarrerasTecnicaController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request) {
        try {
            // generar los titulos para los diferentes sistemas

            $this->session = new Session();
            $tipoSistema = $request->getSession()->get('sysname');
            switch ($tipoSistema) {
                case 'REGULAR': $this->session->set('tituloTipo', 'Carreras Técnicas');
                    break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Carreras Técnicas Alternativa');
                    break;
                case 'PERMANENTE': $this->session->set('tituloTipo', 'Programas y Cursos');
                    break;
                default: $this->session->set('tituloTipo', 'Carreras default');
                    break;
            }

            $em = $this->getDoctrine()->getManager();
            $carreras = $em->getRepository('SieAppWebBundle:CarreraTipo')->findAll();

            return $this->render('SiePermanenteBundle:CarrerasTecnica:index.html.twig', array('carreras' => $carreras));
        } catch (Exception $ex) {
            
        }
    }

    /*
     * Formulario de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {
        try{
            $gestiones = array($gestionactual => $gestionactual);
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('carrerastecnica'))
                    ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                    ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                    ->add('buscar', 'submit', array('label' => 'Buscar'))
                    ->getForm();
            return $form;
        } catch (Exception $ex) {

        }
        
    }

    /*
     * Crear formulario para nueva carrera
     */

    public function newAction() {
        try{
            $activo = array('1'=>'Si','0'=>'No');
            $em = $this->getDoctrine()->getManager();
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('carrerastecnica_create'))
                    ->add('codigo', 'text', array('label' => 'Código', 'attr' => array('class' => 'form-control','pattern'=>'[0-9a-zA-Z]{2,10}','autocomplete'=>'off')))
                    ->add('carrera', 'text', array('label' => 'Nombre', 'attr' => array('class' => 'form-control','pattern'=>'[0-9a-zA-Z\sñÑ]{2,50}','autocomplete'=>'off')))
                    ->add('descripcion', 'text', array('label' => 'Descripción', 'required' => false, 'attr' => array('class' => 'form-control','pattern'=>'[0-9a-zA-Z\sñÑ]{2,50}','autocomplete'=>'off')))
                    ->add('orgcurricular', 'entity', array('label' => 'Org. Curricular', 'class' => 'SieAppWebBundle:OrgcurricularTipo', 'attr' => array('class' => 'form-control')))
                    ->add('institucionTipo', 'entity', array('label' => 'Institución Tipo', 'class' => 'SieAppWebBundle:InstitucioneducativaTipo', 'attr' => array('class' => 'form-control')))
                    ->add('esactivo','choice',array('label'=>'Activo','choices'=>$activo,'attr'=>array('class'=>'form-control')))
                    ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                    ->getForm();
            return $this->render('SiePermanenteBundle:CarrerasTecnica:new.html.twig', array('form' => $form->createView()));
        } catch (Exception $ex) {

        }
        
    }

    public function createAction(Request $request) {
        try {
            $form = $request->get('form');
            $em = $this->getDoctrine()->getManager();
            $carrera = new CarreraTipo();
            $carrera->setCarrera($form['carrera']);
            $carrera->setDescripcion($form['descripcion']);
            $carrera->setOrgcurricularTipo($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find($form['orgcurricular']));
            $carrera->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find($form['institucionTipo']));
            $carrera->setEsactivo($form['esactivo']);
            $carrera->setCodigo($form['codigo']);
            $em->persist($carrera);
            $em->flush();

            $this->get('session')->getFlashBag()->add('newOk', 'El registro se realizó correctamente');
            return $this->redirect($this->generateUrl('carrerastecnica'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('newError', 'Error en el registro');
            return $this->redirect($this->generateUrl('carrerastecnica'));
        }
    }

    public function editAction(Request $request) {
        try{
            $activo = array('1'=>'Si','0'=>'No');
            $em = $this->getDoctrine()->getManager();
            $carrera = $em->getRepository('SieAppWebBundle:CarreraTipo')->find($request->get('idCarrera'));
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('carrerastecnica_update'))
                    ->add('idCarrera', 'hidden', array('data' => $carrera->getId()))
                    ->add('codigo', 'text', array('label' => 'Código','data'=>$carrera->getCodigo(), 'attr' => array('class' => 'form-control','pattern'=>'[0-9a-zA-Z]{2,10}','autocomplete'=>'off')))
                    ->add('carrera', 'text', array('label' => 'Nombre', 'data' => $carrera->getCarrera(), 'attr' => array('class' => 'form-control','pattern'=>'[0-9a-zA-Z\sñÑ]{2,50}','autocomplete'=>'off')))
                    ->add('descripcion', 'text', array('label' => 'Descripción', 'data' => $carrera->getDescripcion(), 'required' => false, 'attr' => array('class' => 'form-control','pattern'=>'[0-9a-zA-Z\sñÑ]{2,50}','autocomplete'=>'off')))
                    ->add('orgcurricular', 'entity', array('label' => 'Org. Curricular', 'class' => 'SieAppWebBundle:OrgcurricularTipo', 'data' => $em->getReference('SieAppWebBundle:OrgcurricularTipo', $carrera->getOrgcurricularTipo()->getId()), 'attr' => array('class' => 'form-control')))
                    ->add('institucionTipo', 'entity', array('label' => 'Institución Tipo', 'class' => 'SieAppWebBundle:InstitucioneducativaTipo','data'=>$em->getReference('SieAppWebBundle:InstitucioneducativaTipo',$carrera->getInstitucioneducativaTipo()->getId()), 'attr' => array('class' => 'form-control')))
                    ->add('esactivo','choice',array('label'=>'Activo','choices'=>$activo,'data'=>$carrera->getEsactivo(),'attr'=>array('class'=>'form-control')))
                    ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary')))
                    ->getForm();
            return $this->render('SiePermanenteBundle:CarrerasTecnica:edit.html.twig', array('form' => $form->createView()));
        } catch (Exception $ex) {

        }        
    }

    public function updateAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $form = $request->get('form');
            $carrera = $em->getRepository('SieAppWebBundle:CarreraTipo')->find($form['idCarrera']);
            $carrera->setCarrera($form['carrera']);
            $carrera->setCodigo($form['codigo']);
            $carrera->setDescripcion($form['descripcion']);
            $carrera->setEsactivo($form['esactivo']);
            $carrera->setOrgcurricularTipo($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find($form['orgcurricular']));
            $carrera->setInstitucioneducativaTipo($em->getRepository('SieAppWebBundle:InstitucioneducativaTipo')->find($form['institucionTipo']));
            $em->flush();

            $this->get('session')->getFlashBag()->add('updateOk', 'Los datos se actualizaron correctamente');
            return $this->redirect($this->generateUrl('carrerastecnica'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la actualización de datos');
            return $this->redirect($this->generateUrl('carrerastecnica'));
        }
    }

    public function deleteAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $carrera = $em->getRepository('SieAppWebBundle:CarreraTipo')->find($request->get('idCarrera'));
            $em->remove($carrera);
            $em->flush();
            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado correctamente');
            return $this->redirect($this->generateUrl('carrerastecnica'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('deleteError', 'Error al elimiar el registro');
            return $this->redirect($this->generateUrl('carrerastecnica'));
        }
    }
    
    /*
     * Especialidades de la carrera
     */
    public function especialidadesAction(Request $request){
        try{
            $this->session = new Session();
            $this->session->set('idCarrera',$request->get('idCarrera'));
            $em = $this->getDoctrine()->getManager();
            $carrera = $em->getRepository('SieAppWebBundle:CarreraTipo')->find($request->get('idCarrera'));
            $especialidades = $em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->findBy(array('carreraTipo'=>$request->get('idCarrera')));
            
            return $this->render('SiePermanenteBundle:CarrerasTecnica:especialidad_index.html.twig',array('carrera'=>$carrera,'especialidades'=>$especialidades));
            
        } catch (Exception $ex) {
            
        }
        
        
    }
    
    public function especialidadesSessionAction(){
        try{
            $this->session = new Session();
            $idCarrera = $this->session->get('idCarrera');
            
            $em = $this->getDoctrine()->getManager();
            $carrera = $em->getRepository('SieAppWebBundle:CarreraTipo')->find($idCarrera);
            $especialidades = $em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->findBy(array('carreraTipo'=>$idCarrera));
            
            return $this->render('SiePermanenteBundle:CarrerasTecnica:especialidad_index.html.twig',array('carrera'=>$carrera,'especialidades'=>$especialidades));
        } catch (Exception $ex) {
            
        }
        
        
    }
    
    public function especialidades_newAction(Request $request){
        try{
            $activo = array('1'=>'Si','0'=>'No');
            $em = $this->getDoctrine()->getManager();
            $carrera = $em->getRepository('SieAppWebBundle:CarreraTipo')->find($request->get('idCarrera'));
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('carrerastecnica_especialidades_create'))
                    ->add('idCarrera','hidden',array('data'=>$request->get('idCarrera')))
                    ->add('codigo','text',array('label'=>'Código','required'=>true,'attr'=>array('class'=>'form-control jnumbers jupper','pattern'=>'[0-9]{1,10}','autocomplete'=>'off','maxlength'=>'10')))
                    ->add('especialidad','text',array('label'=>'Curso','required'=>true,'attr'=>array('class'=>'form-control jnumbersletters','pattern'=>'[0-9a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,60}','autocomplete'=>'off','maxlength'=>'60')))
                    ->add('obs','text',array('label'=>'Observación','required'=>false,'attr'=>array('class'=>'form-control jnumbersletters','pattern'=>'[0-9a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,60}','autocomplete'=>'off','maxlength'=>'60')))
                    ->add('esactivo','choice',array('label'=>'Activo','choices'=>$activo,'attr'=>array('class'=>'form-control')))
                    ->add('guardar','submit',array('label'=>'Guardar Cambios','attr'=>array('class'=>'btn btn-primary')))
                    ->getForm();
            return $this->render('SiePermanenteBundle:CarrerasTecnica:especialidad_new.html.twig',array('form'=>$form->createView(),'carrera'=>$carrera));
        } catch (Exception $ex) {

        }
    }
    
    public function especialidades_createAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            $especialidad = new CarreraespecialidadTipo();
            $especialidad->setCarreraTipo($em->getRepository('SieAppWebBundle:CarreraTipo')->find($form['idCarrera']));
            $especialidad->setCodigo($form['codigo']);
            $especialidad->setEspecialidad($form['especialidad']);
            $especialidad->setEsactivo($form['esactivo']);
            $especialidad->setObs($form['obs']);
            $em->persist($especialidad);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'El registro se realizó correctamente');
            return $this->redirect($this->generateUrl('carrerastecnica_especialidades_session'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        } 
    }
    
    
    public function especialidades_editAction(Request $request){
        try{
            $activo = array('1'=>'Si','0'=>'No');
            $em = $this->getDoctrine()->getManager();
            $especialidad = $em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->find($request->get('idEspecialidad'));
            $carrera = $em->getRepository('SieAppWebBundle:CarreraTipo')->find($especialidad->getCarreraTipo()->getId());
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('carrerastecnica_especialidades_update'))
                    ->add('idEspecialidad','hidden',array('data'=>$request->get('idEspecialidad')))
                    ->add('codigo','text',array('label'=>'Código','data'=>$especialidad->getCodigo(),'required'=>true,'attr'=>array('class'=>'form-control jnumbers','pattern'=>'[0-9]{1,10}','autocomplete'=>'off','maxlength'=>'10')))
                    ->add('especialidad','text',array('label'=>'Curso','data'=>$especialidad->getEspecialidad(),'required'=>true,'attr'=>array('class'=>'form-control jnumbersletters','pattern'=>'[0-9a-zA-Z\sñÑ]{2,60}','autocomplete'=>'off','maxlength'=>'60')))
                    ->add('obs','text',array('label'=>'Observación','data'=>$especialidad->getObs(),'required'=>false,'attr'=>array('class'=>'form-control jnumbersletters','pattern'=>'[0-9a-zA-Z\sñÑ]{2,60}','autocomplete'=>'off','maxlength'=>'60')))
                    ->add('esactivo','choice',array('label'=>'Activo','choices'=>$activo,'data'=>$especialidad->getEsactivo(),'attr'=>array('class'=>'form-control')))
                    ->add('guardar','submit',array('label'=>'Guardar Cambios','attr'=>array('class'=>'btn btn-primary')))
                    ->getForm();
            return $this->render('SiePermanenteBundle:CarrerasTecnica:especialidad_edit.html.twig',array('form'=>$form->createView(),'carrera'=>$carrera));
        } catch (Exception $ex) {

        }
        
    }
    
    public function especialidades_updateAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            $especialidad = $em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->find($form['idEspecialidad']);
            $especialidad->setCodigo($form['codigo']);
            $especialidad->setEspecialidad($form['especialidad']);
            $especialidad->setEsactivo($form['esactivo']);
            $especialidad->setObs($form['obs']);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Los datos se actualizaron correctamente');
            return $this->redirect($this->generateUrl('carrerastecnica_especialidades_session'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }
    
    public function especialidades_deleteAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager(); 
            $em->getConnection()->beginTransaction();
            $especialidad = $em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->find($request->get('idEspecialidad'));
            // Verificacamos si la especialidad esta asignada a una unidad educativa
            $institucion_carreraespecialidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaCarreraespecialidad')->findBy(array('carreraespecialidadTipo'=>$especialidad->getId()));
            if($institucion_carreraespecialidad){
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('deleteError', 'El curso no se puede eliminar, porque esta asignada a uno o varios centros');
                return $this->redirect($this->generateUrl('carrerastecnica_especialidades_session'));
            }

            $em->remove($especialidad);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado correctamente');
            return $this->redirect($this->generateUrl('carrerastecnica_especialidades_session'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();

        } 
    }
}
