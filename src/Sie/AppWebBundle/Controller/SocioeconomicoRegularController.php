<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\SocioeconomicoRegular;
use Sie\AppWebBundle\Form\SocioeconomicoRegularType;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Estudiante;

/**
 * SocioeconomicoRegular controller.
 *
 */
class SocioeconomicoRegularController extends Controller {

    private $session;

    /**
     * the class constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lists all SocioeconomicoRegular entities.
     *
     */
    public function indexAction() {

        $id_usuario = $this->session->get('userId');

        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        return $this->render('SieAppWebBundle:SocioeconomicoRegular:index.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
        ));
//        
//
//        $entities = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->findAll();
//
//        dump($entities);die;
//        
//        return $this->render('SieAppWebBundle:SocioeconomicoRegular:index.html.twig', array(
//            'entities' => $entities,
//        ));
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
                ->setAction($this->generateUrl('socioeconomicoregular_result'))
                ->add('codigoSie', 'text', array('mapped' => false, 'label' => 'SIE', 'required' => true, 'invalid_message' => 'Campo Obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{8,8}', 'maxlength' => '8', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('codigoRude', 'text', array('mapped' => false, 'label' => 'RUDE', 'required' => true, 'invalid_message' => 'Campo Obligatorio', 'attr' => array('class' => 'form-control', 'pattern' => '[0-9a-zA-Z\sñÑ]{14,18}', 'maxlength' => '18', 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('gestion', 'choice', array('mapped' => false, 'label' => 'Gestion', 'choices' => array('2016' => '2016', '2015' => '2015', '2014' => '2014'), 'attr' => array('class' => 'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * Obtiene el formulario para realizar la modificación
     * @param Request $request
     */
    public function resultAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');

        $sie = $form['codigoSie'];
        $rude = $form['codigoRude'];
        $gestion = $form['gestion'];


        //Información de la institución educativa
        $repository = $em->getRepository('SieAppWebBundle:Institucioneducativa');

        $query = $repository->createQueryBuilder('i')
                ->select('i.id ieducativaId, i.institucioneducativa ieducativa, d.id distritoId, d.distrito distrito, dp.id departamentoId, dp.departamento departamento, de.dependencia dependencia, jg.cordx cordx, jg.cordy cordy')
                ->innerJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'i.leJuridicciongeografica = jg.id')
                ->innerJoin('SieAppWebBundle:DistritoTipo', 'd', 'WITH', 'jg.distritoTipo = d.id')
                ->innerJoin('SieAppWebBundle:DepartamentoTipo', 'dp', 'WITH', 'd.departamentoTipo = dp.id')
                ->innerJoin('SieAppWebBundle:DependenciaTipo', 'de', 'WITH', 'i.dependenciaTipo = de.id')
                ->where('i.id = :ieducativa')
                ->setParameter('ieducativa', $sie)
                ->getQuery();

        $institucion = $query->getOneOrNullResult();

        //Información de la/el estudiante
        $student = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $rude));

        //Verifica si el estudiente existe
        if ($student) {

            //Verifica si la UE existe
            if ($institucion) {

                //Información de inscripción de la/el estudiante
                $repository = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');

                $query = $repository->createQueryBuilder('i')
                        ->select('i.id insId, nt.nivel nivel, gt.grado grado, pt.paralelo paralelo, tt.turno turno')
                        ->innerJoin('SieAppWebBundle:Estudiante', 'e', 'WITH', 'i.estudiante = e.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'i.institucioneducativaCurso = ic.id')
                        ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'ic.nivelTipo = nt.id')
                        ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'ic.gradoTipo = gt.id')
                        ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'ic.paraleloTipo = pt.id')
                        ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'ic.turnoTipo = tt.id')
                        ->where('ic.institucioneducativa = :ieducativa')
                        ->andWhere('e.codigoRude = :rude')
                        ->andWhere('ic.gestionTipo = :gestion')
                        ->setParameter('ieducativa', $sie)
                        ->setParameter('rude', $rude)
                        ->setParameter('gestion', $gestion)
                        ->getQuery();

                $inscription = $query->getOneOrNullResult();

                //Verifica si el estudiante cuenta con inscripción en la UE y n la gestión ingresada en el formulario de búsqueda
                if ($inscription) {

                    $socioeconomico = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->findOneBy(array('estudianteInscripcion' => $inscription['insId']));

                    if ($socioeconomico) {

                        //Si cuenta con datos socioeconómicos en la gestión se actualiza la información de los datos socioeconómicos
                        $idEstudiante = $student->getId();

                        $repository = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion');

                        $query = $repository->createQueryBuilder('apo')
                                ->where('apo.estudianteInscripcion = :idInscripcion')
                                ->andWhere('apo.esValidado > :validado')
                                ->setParameter('idInscripcion', $inscription['insId'])
                                ->setParameter('validado', 'f')
                                ->getQuery();

                        $apoderados = $query->getResult();

                        $id = $socioeconomico->getId();

                        $em = $this->getDoctrine()->getManager();

                        $entity = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->find($id);

                        if (!$entity) {
                            throw $this->createNotFoundException('Unable to find SocioeconomicoRegular entity.');
                        }

                        $editForm = $this->createEditForm($entity);

                        return $this->render('SieAppWebBundle:SocioeconomicoRegular:edit.html.twig', array(
                                    'entity' => $entity,
                                    'institucion' => $institucion,
                                    'student' => $student,
                                    'inscription' => $inscription,
                                    'edit_form' => $editForm->createView(),
                                    'sie' => $sie,
                                    'rude' => $rude,
                                    'gestion' => $gestion,
                                    'apoderados' => $apoderados,
                        ));
                    } else {

                        //Si no cuenta con datos socioeconómicos en la gestión se crea un nuevo registro en datos socioeconómicos
//                        $entity = new SocioeconomicoRegular();
//
//                        $form = $this->createCreateForm($entity);
//
//                        return $this->render('SieAppWebBundle:SocioeconomicoRegular:new.html.twig', array(
//                                    'entity' => $entity,
//                                    'institucion' => $institucion,
//                                    'student' => $student,
//                                    'inscription' => $inscription,
//                                    'form' => $form->createView(),
//                                    'sie' => $sie,
//                                    'rude' => $rude,
//                                    'gestion' => $gestion,
//                        ));

                        $message = "Estudiante con RUDE " . $rude . " no cuenta con datos socioeconómicos para la gestión " . $gestion;
                        $this->addFlash('notiext', $message);
                        return $this->redirectToRoute('socioeconomicoregular');
                    }
                } else {
                    $message = "Estudiante con RUDE " . $rude . " no cuenta con inscripción en la UE " . $sie . " para la gestión " . $gestion;
                    $this->addFlash('notiext', $message);
                    return $this->redirectToRoute('socioeconomicoregular');
                }
            } else {
                $message = "Institución Educativa " . $sie . " no se encuentra registrada";
                $this->addFlash('notiext', $message);
                return $this->redirectToRoute('socioeconomicoregular');
            }
        } else {
            $message = "Estudiante con RUDE " . $rude . " no se encuentra registrado";
            $this->addFlash('notiext', $message);
            return $this->redirectToRoute('socioeconomicoregular');
        }
    }

    /**
     * Creates a new SocioeconomicoRegular entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new SocioeconomicoRegular();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('socioeconomicoregular_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:SocioeconomicoRegular:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a SocioeconomicoRegular entity.
     *
     * @param SocioeconomicoRegular $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(SocioeconomicoRegular $entity) {
        $form = $this->createForm(new SocioeconomicoRegularType(), $entity, array(
            'action' => $this->generateUrl('socioeconomicoregular_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new SocioeconomicoRegular entity.
     *
     */
    public function newAction() {
        $entity = new SocioeconomicoRegular();
        $form = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:SocioeconomicoRegular:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a SocioeconomicoRegular entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SocioeconomicoRegular entity.');
        }

        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:SocioeconomicoRegular:show.html.twig', array(
                    'entity' => $entity,
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing SocioeconomicoRegular entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SocioeconomicoRegular entity.');
        }

        $editForm = $this->createEditForm($entity);
        //$deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:SocioeconomicoRegular:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a SocioeconomicoRegular entity.
     *
     * @param SocioeconomicoRegular $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(SocioeconomicoRegular $entity) {
        $form = $this->createForm(new SocioeconomicoRegularType(), $entity, array(
            'action' => $this->generateUrl('socioeconomicoregular_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar información'));

        return $form;
    }

    /**
     * Edits an existing SocioeconomicoRegular entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SocioeconomicoRegular entity.');
        }

        //$deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $message = "Se ha hactualizado correctamente la información ingresada en el formulario RUDE";
            $this->addFlash('goodext', $message);
            return $this->redirectToRoute('socioeconomicoregular');
            //return $this->redirect($this->generateUrl('socioeconomicoregular_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:SocioeconomicoRegular:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                        //'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a SocioeconomicoRegular entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:SocioeconomicoRegular')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find SocioeconomicoRegular entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('socioeconomicoregular'));
    }

    /**
     * Creates a form to delete a SocioeconomicoRegular entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('socioeconomicoregular_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

}
