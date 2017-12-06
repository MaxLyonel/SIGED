<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteEspecialIndirecta;
use Sie\AppWebBundle\Form\EstudianteEspecialIndirectaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\Estudiante;

/**
 * EstudianteEspecialIndirecta controller.
 *
 */
class EstudianteEspecialIndirectaController extends Controller {

    /**
     * Lists all EstudianteEspecialIndirecta entities.
     *
     */
    public function indexAction() {
        /* $em = $this->getDoctrine()->getManager();

          $entities = $em->getRepository('SieAppWebBundle:EstudianteEspecialIndirecta')->findAll();

          return $this->render('SieAppWebBundle:EstudianteEspecialIndirecta:index.html.twig', array(
          'entities' => $entities,
          )); */
        $em = $this->getDoctrine()->getManager();
        $estudiantesdiscapacidad = $em->getRepository('SieAppWebBundle:EstudianteEspecialIndirecta')->findAll();
        return $this->render('SieAppWebBundle:EstudianteEspecialIndirecta:index.html.twig', array('estdiscapacidad' => $estudiantesdiscapacidad));
    }

    private function createFormInscripcion() {
        $areas = array('Educacion Regular' => 'Educación Regular', 'Educación de personas Jóvenes y Adultas' => 'Educación de personas Jóvenes y Adultas',
            'Educacion Permanente' => 'Educacion Permanente', 'Alfabetizacion y Post Alfabetizacion (pnp)' => 'Alfabetizacion y Post Alfabetizacion (pnp)');
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteespecialindirecta_create'))
                        ->add('area', 'choice', array('choices' => $areas))
                        ->add('institucionCodigo', 'text')
                        ->add('institucionNombre', 'text')
                        ->add('estudiantePaterno', 'text', array('required' => false))
                        ->add('estudianteMaterno', 'text', array('required' => false))
                        ->add('estudianteNombre', 'text')
                        ->add('estudianteTelefono', 'text')
                        ->add('estudianteCarnet', 'text')
                        ->add('estudianteCodigo', 'text')
                        // discapacidades
                        ->add('disIntelectualGeneral', 'checkbox', array('required' => false))
                        ->add('disIntelectualDown', 'checkbox', array('required' => false))
                        ->add('disIntelectualAutismo', 'checkbox', array('required' => false))
                        ->add('disVisualTotal', 'checkbox', array('required' => false))
                        ->add('disVisualBaja', 'checkbox', array('required' => false))
                        ->add('disAuditiva', 'checkbox', array('required' => false))
                        ->add('disFisicoMotora', 'checkbox', array('required' => false))
                        ->add('disMultiple', 'checkbox', array('required' => false))
                        ->add('disOtros', 'checkbox', array('required' => false))
                        ->add('observacion', 'textarea', array('required' => false))
                        ->add('estudianteCarnetCodepedis', 'text', array('required' => false, 'disabled' => true))
                        ->add('estudianteCarnetIbc', 'text', array('required' => false, 'disabled' => true))
                        ->add('aceptar', 'submit', array('label' => 'Guardar'))
                        ->getForm();
    }

    /**
     * Creates a new EstudianteEspecialIndirecta entity.
     *
     */
    public function createAction(Request $request) {
        $form = $request->get('form');

        //print_r($form);die;
        $em = $this->getDoctrine()->getManager();
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($form['estudianteCodigo']);
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucionCodigo']);
        if (!$estudiante) {
            $this->get('session')->getFlashBag()->add('registroEspecialError', 'Error en el registro, ingrese nuevamente los datos');
            return $this->redirect($this->generateUrl('estudianteespecialindirecta'));
        }
        if (!$institucion) {
            $this->get('session')->getFlashBag()->add('registroEspecialError', 'Error en el registro, ingrese nuevamente los datos');
            return $this->redirect($this->generateUrl('estudianteespecialindirecta'));
        }
        
        $estudiante_especial_indirecta = $em->getRepository('SieAppWebBundle:EstudianteEspecialIndirecta')->findOneByEstudiante($estudiante->getId());
        if($estudiante_especial_indirecta){
            $this->get('session')->getFlashBag()->add('registroEspecialError', 'Error no puede registrar al mismo estudiante mas de una vez.');
            return $this->redirect($this->generateUrl('estudianteespecialindirecta'));
        }
        
        try {
            $estudiante_especial = new EstudianteEspecialIndirecta();
            $estudiante_especial->setInstitucioneducativaId($form['institucionCodigo']);
            $estudiante_especial->setInstitucioneducativaTipoId($form['area']);
            $estudiante_especial->setEstudiante($estudiante);
            $estudiante_especial->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById(2015));

            $estudiante_especial->setDisIntelectualGenerlal((isset($form['disIntelectualGeneral'])) ? 1 : 0);
            $estudiante_especial->setDisIntelectualDown((isset($form['disIntelectualDown'])) ? 1 : 0);
            $estudiante_especial->setDisIntelectualAutismo((isset($form['disIntelectualAutismo'])) ? 1 : 0);

            $estudiante_especial->setDisVisualTotal((isset($form['disVisualTotal'])) ? 1 : 0);
            $estudiante_especial->setDisVisualBaja((isset($form['disVisualBaja'])) ? 1 : 0);

            $estudiante_especial->setDisAuditiva((isset($form['disAuditiva'])) ? 1 : 0);

            $estudiante_especial->setDisFisicomotora((isset($form['disFisicoMotora'])) ? 1 : 0);

            $estudiante_especial->setDisMultiple((isset($form['disMultiple'])) ? 1 : 0);

            $estudiante_especial->setDisOtros((isset($form['disOtros'])) ? 1 : 0);

            $estudiante_especial->setObs($form['observacion']);

            $em->persist($estudiante_especial);
            $em->flush();



            // actualizar tabla estudiante
            $estudiante->setCarnetIdentidad((isset($form['estudianteCarnet'])) ? $form['estudianteCarnet'] : $estudiante->getCarnetIdentidad());
            $estudiante->setCelular($form['estudianteTelefono']);
            $estudiante->setCarnetCodepedis((isset($form['estudianteCarnetCodepedis'])) ? $form['estudianteCarnetCodepedis'] : "");
            $estudiante->setCarnetIbc((isset($form['estudianteCarnetIbc'])) ? $form['estudianteCarnetIbc'] : "");

            $em->persist($estudiante);
            $em->flush();

            $this->get('session')->getFlashBag()->add('registroEspecial', 'Registrado correctamente');

            return $this->redirect($this->generateUrl('estudianteespecialindirecta'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('registroEspecialError', 'Error en el registro, ingrese nuevamente los datos');

            return $this->redirect($this->generateUrl('estudianteespecialindirecta'));
        }
    }

    /**
     * Creates a form to create a EstudianteEspecialIndirecta entity.
     *
     * @param EstudianteEspecialIndirecta $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(EstudianteEspecialIndirecta $entity) {
        $form = $this->createForm(new EstudianteEspecialIndirectaType(), $entity, array(
            'action' => $this->generateUrl('estudianteespecialindirecta_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new EstudianteEspecialIndirecta entity.
     *
     */
    public function newAction() {
        $formulario = $this->createFormInscripcion();
        return $this->render('SieAppWebBundle:EstudianteEspecialIndirecta:new.html.twig', array('form' => $formulario->createView()));
    }

    /**
     * Finds and displays a EstudianteEspecialIndirecta entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EstudianteEspecialIndirecta')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EstudianteEspecialIndirecta entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:EstudianteEspecialIndirecta:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing EstudianteEspecialIndirecta entity.
     *
     */
    public function editAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $estudianteEspecial = $em->getRepository('SieAppWebBundle:EstudianteEspecialIndirecta')->findOneById($request->get('id'));
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($estudianteEspecial->getEstudiante()->getId());
        $codigorude = $estudiante->getCodigoRude();
        $codigosie = $estudianteEspecial->getInstitucioneducativaId();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($estudianteEspecial->getInstitucioneducativaId());

        $formulario = $this->createFormEdit($estudianteEspecial, $institucion, $estudiante);
        return $this->render('SieAppWebBundle:EstudianteEspecialIndirecta:edit.html.twig', array('form' => $formulario->createView(), 'codigosie' => $codigosie, 'codigorude' => $codigorude));
    }

    private function createFormEdit($estudianteEspecial, $institucion, $estudiante) {
        $areas = array('Educacion Regular' => 'Educación Regular', 'Educación de personas Jóvenes y Adultas' => 'Educación de personas Jóvenes y Adultas',
            'Educacion Permanente' => 'Educacion Permanente', 'Alfabetizacion y Post Alfabetizacion (pnp)' => 'Alfabetizacion y Post Alfabetizacion (pnp)');
        if ($estudiante->getCarnetIdentidad() == "") {
            $estado = false;
        } else {
            $estado = true;
        }
        // carnet codepedis
        $estatoCodepedis = ($estudiante->getCarnetCodepedis() == "") ? true : false;
        $estatoIbc = ($estudiante->getCarnetIbc() == "") ? true : false;


        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteespecialindirecta_update'))
                        ->add('idEspecial', 'hidden', array('data' => $estudianteEspecial->getId()))
                        ->add('idEstudiante', 'hidden', array('data' => $estudiante->getId()))
                        ->add('area', 'choice', array('choices' => $areas, 'data' => $estudianteEspecial->getInstitucioneducativaTipoId()))
                        ->add('institucionCodigo', 'text', array('data' => $institucion->getId(), 'disabled' => true))
                        ->add('institucionNombre', 'text', array('data' => $institucion->getInstitucioneducativa(), 'disabled' => true))
                        ->add('estudiantePaterno', 'text', array('required' => false, 'data' => $estudiante->getPaterno()))
                        ->add('estudianteMaterno', 'text', array('required' => false, 'data' => $estudiante->getMaterno()))
                        ->add('estudianteNombre', 'text', array('data' => $estudiante->getNombre()))
                        ->add('estudianteTelefono', 'text', array('data' => $estudiante->getCelular()))
                        ->add('estudianteCarnet', 'text', array('data' => $estudiante->getCarnetIdentidad(), 'disabled' => $estado))
                        ->add('estudianteCodigo', 'text', array('data' => $estudiante->getCodigoRude(), 'disabled' => true))
                        // discapacidades
                        ->add('disIntelectualGeneral', 'checkbox', array('required' => false, 'attr' => array('checked' => $estudianteEspecial->getDisIntelectualGenerlal())))
                        ->add('disIntelectualDown', 'checkbox', array('required' => false, 'attr' => array('checked' => $estudianteEspecial->getDisIntelectualDown())))
                        ->add('disIntelectualAutismo', 'checkbox', array('required' => false, 'attr' => array('checked' => $estudianteEspecial->getDisIntelectualAutismo())))
                        ->add('disVisualTotal', 'checkbox', array('required' => false, 'attr' => array('checked' => $estudianteEspecial->getDisVisualTotal())))
                        ->add('disVisualBaja', 'checkbox', array('required' => false, 'attr' => array('checked' => $estudianteEspecial->getDisVisualBaja())))
                        ->add('disAuditiva', 'checkbox', array('required' => false, 'attr' => array('checked' => $estudianteEspecial->getDisAuditiva())))
                        ->add('disFisicoMotora', 'checkbox', array('required' => false, 'attr' => array('checked' => $estudianteEspecial->getDisFisicomotora())))
                        ->add('disMultiple', 'checkbox', array('required' => false, 'attr' => array('checked' => $estudianteEspecial->getDisMultiple())))
                        ->add('disOtros', 'checkbox', array('required' => false, 'attr' => array('checked' => $estudianteEspecial->getDisOtros())))
                        ->add('observacion', 'textarea', array('required' => false, 'data' => $estudianteEspecial->getObs()))
                        ->add('checkCodepedis', 'checkbox', array('required' => false, 'attr' => array('checked' => !$estatoCodepedis)))
                        ->add('checkIbc', 'checkbox', array('required' => false, 'attr' => array('checked' => !$estatoIbc)))
                        ->add('estudianteCarnetCodepedis', 'text', array('required' => false, 'data' => $estudiante->getCarnetCodepedis(), 'disabled' => $estatoCodepedis))
                        ->add('estudianteCarnetIbc', 'text', array('required' => false, 'data' => $estudiante->getCarnetIbc(), 'disabled' => $estatoIbc))
                        ->add('aceptar', 'submit', array('label' => 'Guardar'))
                        ->getForm();
    }

    /**
     * Edits an existing EstudianteEspecialIndirecta entity.
     *
     */
    public function updateAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        // Actualizar estudiante
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($form['idEstudiante']);
        $estudiante->setCarnetIdentidad((isset($form['estudianteCarnet']))? $form['estudianteCarnet'] : $estudiante->getCarnetIdentidad());
        $estudiante->setCelular($form['estudianteTelefono']);
        $estudiante->setCarnetCodepedis((isset($form['estudianteCarnetCodepedis'])) ? $form['estudianteCarnetCodepedis'] : $estudiante->getCarnetCodepedis());
        $estudiante->setCarnetIbc((isset($form['estudianteCarnetIbc']))? $form['estudianteCarnetIbc'] : $estudiante->getCarnetIbc());
        $em->persist($estudiante);
        $em->flush();

        // Actualizar estudiante especial
        $estudiante_especial = $em->getRepository('SieAppWebBundle:EstudianteEspecialIndirecta')->findOneById($form['idEspecial']);
        $estudiante_especial->setInstitucionEducativaTipoId($form['area']);
        $estudiante_especial->setDisIntelectualGenerlal((isset($form['disIntelectualGeneral'])) ? 1 : 0);
        $estudiante_especial->setDisIntelectualDown((isset($form['disIntelectualDown'])) ? 1 : 0);
        $estudiante_especial->setDisIntelectualAutismo((isset($form['disIntelectualAutismo'])) ? 1 : 0);
        $estudiante_especial->setDisVisualTotal((isset($form['disVisualTotal'])) ? 1 : 0);
        $estudiante_especial->setDisVisualBaja((isset($form['disVisualBaja'])) ? 1 : 0);
        $estudiante_especial->setDisAuditiva((isset($form['disAuditiva'])) ? 1 : 0);
        $estudiante_especial->setDisFisicomotora((isset($form['disFisicoMotora'])) ? 1 : 0);
        $estudiante_especial->setDisMultiple((isset($form['disMultiple'])) ? 1 : 0);
        $estudiante_especial->setDisOtros((isset($form['disOtros'])) ? 1 : 0);
        $estudiante_especial->setObs($form['observacion']);
        
        $em->persist($estudiante_especial);
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('registroActualizado', 'El registro fue modificado con exito');

        return $this->redirect($this->generateUrl('estudianteespecialindirecta'));
    }

    /**
     * Deletes a EstudianteEspecialIndirecta entity.
     *
     */
    public function deleteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $estudianteDiscapacidad = $em->getRepository('SieAppWebBundle:EstudianteEspecialIndirecta')->findOneById($request->get('id'));
        $em->remove($estudianteDiscapacidad);
        $em->flush();

        $this->get('session')->getFlashBag()->add('registroEliminado', 'El registro fue eliminado');

        return $this->redirect($this->generateUrl('estudianteespecialindirecta'));
    }

    /**
     * Creates a form to delete a EstudianteEspecialIndirecta entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteespecialindirecta_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    public function buscarInstitucionEducativaAction($id) {
        //echo "dfsd";die;
        $em = $this->getDoctrine()->getManager();
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($id);
        $nombre = ($institucion) ? $institucion->getInstitucioneducativa() : "";
        $response = new JsonResponse();
        return $response->setData(array('nombre' => $nombre));
    }

    public function buscarEstudianteAction($id) {
        //echo "dfsd";die;
        $em = $this->getDoctrine()->getManager();
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($id);
        $paterno = ($estudiante) ? $estudiante->getPaterno() : "";
        $materno = ($estudiante) ? $estudiante->getMaterno() : "";
        $nombre = ($estudiante) ? $estudiante->getNombre() : "";
        $carnet = ($estudiante) ? $estudiante->getCarnetIdentidad() : "";
        $codigo = ($estudiante) ? $estudiante->getCodigoRude() : "";
        $celular = ($estudiante) ? $estudiante->getCelular() : "";
        $fechaNacimiento = ($estudiante) ? $estudiante->getFechaNacimiento()->format('d-m-Y') : "";
        
        
        
        $response = new JsonResponse();
        return $response->setData(array('paterno' => $paterno, 'materno' => $materno, 'nombre' => $nombre, 'carnet' => $carnet, 'codigo' => $codigo,'celular'=>$celular,'fechaNacimiento'=>$fechaNacimiento));
    }

    public function buscarEstudianteCarnetAction($carnet) {
        //echo "dfsd";die;
        $em = $this->getDoctrine()->getManager();
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCarnetIdentidad($carnet);
        $paterno = ($estudiante) ? $estudiante->getPaterno() : "";
        $materno = ($estudiante) ? $estudiante->getMaterno() : "";
        $nombre = ($estudiante) ? $estudiante->getNombre() : "";
        $carnets = ($estudiante) ? $estudiante->getCarnetIdentidad() : "";
        $codigo = ($estudiante) ? $estudiante->getCodigoRude() : "";
        $celular = ($estudiante) ? $estudiante->getCelular() : "";
        $fechaNacimiento = ($estudiante) ? $estudiante->getFechaNacimiento()->format('d-m-Y') : "";
        
        $existe = ($estudiante)?1:0;
        
        $response = new JsonResponse();
        return $response->setData(array('paterno' => $paterno, 'materno' => $materno, 'nombre' => $nombre, 'carnet' => $carnets, 'codigo' => $codigo,'celular'=>$celular,'fechaNacimiento'=>$fechaNacimiento,'existe'=>$existe));
    }

}
