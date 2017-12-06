<?php

namespace Sie\PermanenteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\AreatematicaTipo;

/**
 * EstudianteInscripcion controller.
 *
 */
class AreaTematicaController extends Controller {

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
            // Validamos que solo ingresen solo usuarios de distrito , departamento, nacional
            if(($this->session->get('roluser') != 7) and ($this->session->get('roluser') != 8) and ($this->session->get('roluser') != 10)){
                echo "No tiene permisos para ver el contenido de esta pagina...";
                die;
            }
            $em = $this->getDoctrine()->getManager();
            $areasTematicas = $em->createQuery(
                'SELECT at FROM SieAppWebBundle:AreatematicaTipo at
                ORDER BY at.id')
                ->getResult();

            return $this->render('SiePermanenteBundle:AreaTematica:index.html.twig', array('areas' => $areasTematicas));
        } catch (Exception $ex) {
            
        }
    }

    /*
     * Crear formulario para nueva carrera
     */

    public function newAction() {
        try{
            $this->session = new Session();
            // Validamos que solo ingresen solo usuarios de distrito , departamento, nacional
            if(($this->session->get('roluser') != 7) and ($this->session->get('roluser') != 8) and ($this->session->get('roluser') != 10)){
                echo "No tiene permisos para ver el contenido de esta pagina...";
                die;
            }

            $em = $this->getDoctrine()->getManager();
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('areatematica_create'))
                    ->add('areatematica', 'text', array('label' => 'Área Temática', 'attr' => array('class' => 'form-control jnumbersletters','pattern'=>'[0-9a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,100}','autocomplete'=>'off','maxlength'=>'100')))
                    ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                    ->getForm();
            return $this->render('SiePermanenteBundle:AreaTematica:new.html.twig', array('form' => $form->createView()));
        } catch (Exception $ex) {

        }
        
    }

    public function createAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        try {
            $form = $request->get('form');
            $em->getConnection()->beginTransaction();

            // Actualizacmos el id
            //$query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('areatematica_tipo');");
            //$query->execute();

            $newArea = new AreatematicaTipo();
            $newArea->setAreatematica($form['areatematica']);
            $em->persist($newArea);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'El registro se realizó correctamente');
            return $this->redirect($this->generateUrl('areatematica'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Error en el registro');
            return $this->redirect($this->generateUrl('areatematica'));
        }
    }

    public function editAction(Request $request) {
        try{
            $this->session = new Session();
            // Validamos que solo ingresen solo usuarios de distrito , departamento, nacional
            if(($this->session->get('roluser') != 7) and ($this->session->get('roluser') != 8) and ($this->session->get('roluser') != 10)){
                echo "No tiene permisos para ver el contenido de esta pagina...";
                die;
            }

            $em = $this->getDoctrine()->getManager();
            $areaTematica = $em->getRepository('SieAppWebBundle:AreatematicaTipo')->find($request->get('idArea'));
            
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('areatematica_update'))
                    ->add('idAreatematica', 'hidden', array('data' => $areaTematica->getId()))
                    ->add('areatematica', 'text', array('label' => 'Área Temática','data'=>$areaTematica->getAreatematica(), 'attr' => array('class' => 'form-control jnumbersletters','pattern'=>'[0-9a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,100}','autocomplete'=>'off','maxlength'=>'100')))
                    ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                    ->getForm();

            return $this->render('SiePermanenteBundle:AreaTematica:edit.html.twig', array('form' => $form->createView()));
        } catch (Exception $ex) {

        }        
    }

    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        try {
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');

            $updateAreatematica = $em->getRepository('SieAppWebBundle:AreatematicaTipo')->find($form['idAreatematica']);
            $updateAreatematica->setAreatematica($form['areatematica']);
            $em->persist($updateAreatematica);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Los datos se actualizaron correctamente');
            return $this->redirect($this->generateUrl('areatematica'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la actualización de datos');
            return $this->redirect($this->generateUrl('areatematica'));
        }
    }

    public function deleteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        try {
            $em->getConnection()->beginTransaction();
            $this->session = new Session();
            // Validamos que solo ingresen solo usuarios de distrito , departamento, nacional
            if(($this->session->get('roluser') != 7) and ($this->session->get('roluser') != 8) and ($this->session->get('roluser') != 10)){
                echo "No tiene permisos para ver el contenido de esta pagina...";
                die;
            }
            // VErificamos si el area tematica es refernciada desde los cursos cortos
            $cursosCortos = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoCorto')->findBy(array('areatematicaTipo'=>$request->get('idArea')));
            if(count($cursosCortos)>0){
                $em->getConnection()->commit();
                $this->get('session')->getFlashBag()->add('deleteError', 'No se puede eliminar el área temática porque esta siendo utilizado');
                return $this->redirect($this->generateUrl('areatematica'));
            }

            $areaTematica = $em->getRepository('SieAppWebBundle:AreatematicaTipo')->find($request->get('idArea'));
            $em->remove($areaTematica);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado correctamente');
            return $this->redirect($this->generateUrl('areatematica'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('deleteError', 'Error al elimiar el registro');
            return $this->redirect($this->generateUrl('areatematica'));
        }
    }
}
