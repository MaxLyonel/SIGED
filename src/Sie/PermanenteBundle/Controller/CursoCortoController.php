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
class CursoCortoController extends Controller {

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
            $cursosCortos = $em->createQuery(
                'SELECT cet FROM SieAppWebBundle:CarreraespecialidadTipo cet
                INNER JOIN  cet.carreraTipo ct
                WHERE ct.escursocorto = :op
                ORDER BY cet.codigo')
                ->setParameter('op',true)
                ->getResult();
            //$carreras = $em->getRepository('SieAppWebBundle:CarreraTipo')->findAll();

            return $this->render('SiePermanenteBundle:CursoCorto:index.html.twig', array('cursosCortos' => $cursosCortos));
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

            $activo = array('1'=>'Si','0'=>'No');
            $em = $this->getDoctrine()->getManager();
            $carreraCursoCorto = $em->getRepository('SieAppWebBundle:CarreraTipo')->findOneBy(array('escursocorto'=>true));
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('cursocorto_create'))
                    ->add('carreraCursoCorto','hidden',array('data'=>$carreraCursoCorto->getId()))
                    ->add('codigo', 'text', array('label' => 'Código', 'attr' => array('class' => 'form-control jnumbers','pattern'=>'[0-9]{1,5}','autocomplete'=>'off','maxlength'=>'5')))
                    ->add('curso', 'text', array('label' => 'Curso', 'attr' => array('class' => 'form-control jnumbersletters','pattern'=>'[0-9a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,100}','autocomplete'=>'off','maxlength'=>'100')))
                    ->add('descripcion', 'text', array('label' => 'Descripción', 'required' => false, 'attr' => array('class' => 'form-control','pattern'=>'[0-9a-zA-Z\sñÑ]{2,50}','autocomplete'=>'off')))
                    ->add('esactivo','choice',array('label'=>'Activo','choices'=>$activo,'attr'=>array('class'=>'form-control')))
                    ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                    ->getForm();
            return $this->render('SiePermanenteBundle:CursoCorto:new.html.twig', array('form' => $form->createView()));
        } catch (Exception $ex) {

        }
        
    }

    public function createAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        try {
            $form = $request->get('form');
            $em->getConnection()->beginTransaction();

            $newCursoCorto = new CarreraespecialidadTipo();
            $newCursoCorto->setEspecialidad($form['curso']);
            $newCursoCorto->setObs($form['descripcion']);
            $newCursoCorto->setEsactivo($form['esactivo']);
            $newCursoCorto->setCodigo($form['codigo']);
            $newCursoCorto->setCarreraTipo($em->getRepository('SieAppWebBundle:CarreraTipo')->find($form['carreraCursoCorto']));
            $em->persist($newCursoCorto);
            $em->flush();

            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('newOk', 'El registro se realizó correctamente');
            return $this->redirect($this->generateUrl('cursocorto'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('newError', 'Error en el registro');
            return $this->redirect($this->generateUrl('cursocorto'));
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

            $activo = array('1'=>'Si','0'=>'No');
            $em = $this->getDoctrine()->getManager();
            $cursoCorto = $em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->find($request->get('idCurso'));
            
            $form = $this->createFormBuilder()
                    ->setAction($this->generateUrl('cursocorto_update'))
                    ->add('idCursoCorto', 'hidden', array('data' => $cursoCorto->getId()))
                    ->add('codigo', 'text', array('label' => 'Código','data'=>$cursoCorto->getCodigo(), 'attr' => array('class' => 'form-control jnumbers','pattern'=>'[0-9]{1,5}','autocomplete'=>'off','maxlength'=>'5')))
                    ->add('curso', 'text', array('label' => 'Curso','data'=>$cursoCorto->getEspecialidad(), 'attr' => array('class' => 'form-control jnumbersletters','pattern'=>'[0-9a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,100}','autocomplete'=>'off','maxlength'=>'100')))
                    ->add('descripcion', 'text', array('label' => 'Descripción','data'=>$cursoCorto->getObs(), 'required' => false, 'attr' => array('class' => 'form-control','pattern'=>'[0-9a-zA-Z\sñÑ]{2,50}','autocomplete'=>'off')))
                    ->add('esactivo','choice',array('label'=>'Activo','choices'=>$activo,'data'=>$cursoCorto->getEsactivo(),'attr'=>array('class'=>'form-control')))
                    ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                    ->getForm();

            return $this->render('SiePermanenteBundle:CursoCorto:edit.html.twig', array('form' => $form->createView()));
        } catch (Exception $ex) {

        }        
    }

    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        try {
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');

            $updateCursoCorto = $em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->find($form['idCursoCorto']);
            $updateCursoCorto->setEspecialidad($form['curso']);
            $updateCursoCorto->setObs($form['descripcion']);
            $updateCursoCorto->setEsactivo($form['esactivo']);
            $updateCursoCorto->setCodigo($form['codigo']);
            $em->persist($updateCursoCorto);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('updateOk', 'Los datos se actualizaron correctamente');
            return $this->redirect($this->generateUrl('cursocorto'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('updateError', 'Error en la actualización de datos');
            return $this->redirect($this->generateUrl('cursocorto'));
        }
    }

    public function deleteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        try {
            $this->session = new Session();
            // Validamos que solo ingresen solo usuarios de distrito , departamento, nacional
            if(($this->session->get('roluser') != 7) and ($this->session->get('roluser') != 8) and ($this->session->get('roluser') != 10)){
                echo "No tiene permisos para ver el contenido de esta pagina...";
                die;
            }
            
            $em->getConnection()->beginTransaction();
            $cursoCorto = $em->getRepository('SieAppWebBundle:CarreraespecialidadTipo')->find($request->get('idCurso'));
            $em->remove($cursoCorto);
            $em->flush();
            $em->getConnection()->commit();
            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado correctamente');
            return $this->redirect($this->generateUrl('cursocorto'));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('deleteError', 'Error al elimiar el registro');
            return $this->redirect($this->generateUrl('cursocorto'));
        }
    }
}
