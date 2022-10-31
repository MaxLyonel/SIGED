<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Institucioneducativanoacreditado;
use Sie\AppWebBundle\Form\InstitucioneducativanoacreditadoType;
use Sie\AppWebBundle\Entity\Estudiantenoacredidato;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Institucioneducativanoacreditado controller.
 *
 */
class InstitucioneducativanoacreditadoController extends Controller {

    private $session;

    public function __construct() {
        $this->session = new Session();
    }

    /**
     * Lists all Institucioneducativanoacreditado entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('SieAppWebBundle:Institucioneducativanoacreditado')->findAll();

        $entities = $em->getRepository('SieAppWebBundle:Institucioneducativanoacreditado');
        $query = $entities->createQueryBuilder('a')
                //->select('a', 'dt', 'oc')
                //->leftjoin('SieAppWebBundle:DependenciaTipo', 'dt', 'WITH', 'a.dependenciaTipo = dt.id')
                //->leftjoin('SieAppWebBundle:OrgcurricularTipo', 'oc', 'WITH', 'a.orgcurricularTipo=oc.id')
                ->getQuery();
        $entities = $query->getResult();


        return $this->render('SieAppWebBundle:Institucioneducativanoacreditado:index.html.twig', array(
                    'entities' => $entities,
        ));
    }

    /**
     * Creates a new Institucioneducativanoacreditado entity.
     *
     */
    public function createAction(Request $request) {

        $entity = new Institucioneducativanoacreditado();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        $formData = $request->get('sie_appwebbundle_institucioneducativanoacreditado');
//        print_r($formData);
//        die;
        if ($form->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->find($formData['dependenciaTipo']));
            $entity->setOrgcurricularTipo(($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find($formData['orgcurricularTipo'])));
            $entity->setInstitucioneducativa($formData['institucioneducativa']);
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('sie_ienoacreditadas_edit', array('id' => $entity->getId())));

            //return $this->redirect($this->generateUrl('sie_ienoacreditadas'));
            //return $this->redirect($this->generateUrl('sie_ienoacreditadas_show', array('id' => $entity->getId())));
            //return $this->redirect($this->generateUrl('sie_ienoacreditadas_newstudents', array('id' => $entity->getId())));

            /* return $this->render('SieAppWebBundle:Institucioneducativanoacreditado:uenoacreditadaall.html.twig', array(
              'formnoacreditada' => $this->createnoacreditaForm($entity)->createView(),
              'uenoacreditada' => $entity,
              'forminicial1' => $this->createIniciaForm1($entity->getId())->createView()
              )); */
        }

        return $this->render('SieAppWebBundle:Institucioneducativanoacreditado:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    private function createIniciaForm1($id) {
        return $this->createFormBuilder()
                        //->setAction($this->generateUrl(''))
                        ->add('inicial', 'submit', array('label' => '1ro', 'attr' => array('class' => 'btn btn-primary')))
                        ->getForm();
    }

    private function createnoacreditaForm($data) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('sie_ienoacreditadas_newstudents'))
                        ->add('infoue', 'text', array('data' => serialize($data)))
                        ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                        ->getForm();
    }

    public function newstudentsAction(Request $request) {

        //print_r($request);        die;
        return $this->render('SieAppWebBundle:Institucioneducativanoacreditado:newstudents.html.twig');
        die;
    }

    /**
     * Creates a form to create a Institucioneducativanoacreditado entity.
     *
     * @param Institucioneducativanoacreditado $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Institucioneducativanoacreditado $entity) {
        $form = $this->createForm(new InstitucioneducativanoacreditadoType(), $entity, array(
            'action' => $this->generateUrl('sie_ienoacreditadas_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Continuar', 'attr' => array('class' => 'btn btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new Institucioneducativanoacreditado entity.
     *
     */
    public function newAction() {
        $entity = new Institucioneducativanoacreditado();
        $form = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:Institucioneducativanoacreditado:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Institucioneducativanoacreditado entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativanoacreditado')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institucioneducativanoacreditado entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:Institucioneducativanoacreditado:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Institucioneducativanoacreditado entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativanoacreditado')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institucioneducativanoacreditado entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        $paralelos = $em->getRepository('SieAppWebBundle:ParaleloTipo');

        //get the prralelos
        $query = $paralelos->createQueryBuilder('p')
                ->select('p')
                ->setMaxResults(2)
                ->getQuery();

        $paralels = $query->getArrayResult();
        $paralelo_form = $this->createFormBuilder()
                ->setAction($this->generateUrl('sie_ienoacreditadas_savestudents'))
                //->add('idue', 'text')
                ->add('save', 'submit', array('label' => 'Guardar y Cerrar', 'attr' => array('class' => 'btn btn-default')))
                ->getForm();


        return $this->render('SieAppWebBundle:Institucioneducativanoacreditado:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
                    'paralels' => $paralels,
                    'paralelo_form' => $paralelo_form->createView()
        ));
    }

    /**
     * get of students
     * @param type $id
     * @return list of students
     */
    public function listAction($id) {
        $em = $this->getDoctrine()->getManager();
        //get the infor about students on UE unacreditd
        $students = $em->getRepository('SieAppWebBundle:Estudiantenoacredidato')->getInfoEstudianteNoAcreditado($id);
        if ($students) {
            return $this->render('SieAppWebBundle:Institucioneducativanoacreditado:list.html.twig', array(
                        'entities' => $students
                            //,'form' => $this->createEditStudentForm()->createView()
            ));
        } else {
            //no info about the estudents
            $message = 'No tiene Estudiantes registrados';
            $this->addFlash('warning', $message);
            return $this->redirectToRoute('sie_ienoacreditadas');
        }
    }

    private function createEditStudentForm($data, $idie) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('sie_ienoacreditadas_savestudent'))
                        ->add('ci', 'text', array('required' => false, 'label' => 'CI', 'data' => $data->getCarnetIdentidad(), 'attr' => array('class' => 'form-control')))
                        ->add('nombre', 'text', array('data' => $data->getNombre(), 'attr' => array('class' => 'form-control')))
                        ->add('paterno', 'text', array('data' => $data->getPaterno(), 'attr' => array('class' => 'form-control')))
                        ->add('materno', 'text', array('data' => $data->getMaterno(), 'attr' => array('class' => 'form-control')))
                        ->add('fnac', 'date', array('widget' => 'single_text', 'label' => 'Fecha de Nacimiento', 'data' => $data->getFechaNacimiento(), 'attr' => array('class' => '')))
                        ->add('idie', 'hidden', array('data' => $idie))
                        ->add('id', 'hidden', array('data' => $data->getId()))
                        ->add('save', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-default')))
                        ->getForm();
    }

    public function editstudentAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        //find the student
        $entity = $em->getRepository('SieAppWebBundle:Estudiantenoacredidato')->find($form['id']);
        if ($entity) {
            return $this->render('SieAppWebBundle:Institucioneducativanoacreditado:editstudent.html.twig', array(
                        'entity' => $entity,
                        'form' => $this->createEditStudentForm($entity, $form['idie'])->createView()
            ));
            //to do the edit
        } else {
            //the student doesnto exist
            $this->session->getFlashBag()->add('success', 'Estudiante Eliminado');
            return $this->redirect($this->generateUrl('sie_ienoacreditadas_list', array('id' => $form['idie'])));
        }
    }

    private function createEditStudentForFm() {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('sie_ienoacreditadas_editstudent'))
                        ->add('next', 'submit', array('label' => 'Editar'))
                        ->getForm();
    }

    /**
     * save the student info 
     * @param Request $request
     * @return type
     * @throws return list
     */
    public function savestudentAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $entity = $em->getRepository('SieAppWebBundle:Estudiantenoacredidato')->find($form['id']);
        //check if it exists
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institucioneducativanoacreditado entity.');
        }
        ($form['ci'] != '') ? $entity->setCarnetIdentidad($form['ci']) : '';
        $entity->setNombre($form['nombre']);
        $entity->setPaterno($form['paterno']);
        $entity->setMaterno($form['materno']);

        $em->flush();
        //$entity->setFechaNacimiento(new \DateTime($form['fnac']));

        $this->session->getFlashBag()->add('success', 'Datos modificados satisfactoriamente');
        return $this->redirect($this->generateUrl('sie_ienoacreditadas_list', array('id' => $form['idie'])));
    }

    /**
     * remove student of UE
     * @param Request $request
     * @param type $id
     * @return type
     * @throws remove a student 
     */
    public function deletestudentAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Estudiantenoacredidato')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institucioneducativanoacreditado entity.');
        }
        $em->remove($entity);
        $em->flush();
        //}
        $this->session->getFlashBag()->add('success', 'Estudiante Eliminado');
        return $this->redirect($this->generateUrl('sie_ienoacreditadas_list', array('id' => $entity->getInstitucioneducativanoacreditada()->getId())));
    }

    public function savestudentsAction(Request $request) {

        $form = $request->get('form');
        $id = $request->get('id');
        $copyform = $form;

        //echo sizeof($form);
        $elementsVal = array_pop($form);
        $elementsVal = array_pop($form);

        //get nivel
        $nivel = $request->get('nivel');
        //get nivel
        $grado = $request->get('grado');
        //limit to the consult
        $limit = sizeof($form) / 8;

        $em = $this->getDoctrine()->getManager();
        for ($i = 1; $i <= $limit; $i++) {
            $slicearray = array_slice($form, 8 * ($i - 1), 8, true);
            $newaData = explode('-', implode('-', $slicearray));
            list($carnetIdentidad, $paterno, $materno, $nombre, $genero, $fechaNacimiento, $paralelo, $turno) = $newaData;
            //echo $paterno . "---" . $materno . "---" . $nombre . "---" . $fechaNacimiento;
            $studentNoacreditado = new Estudiantenoacredidato();
            ($carnetIdentidad) ? $studentNoacreditado->setCarnetIdentidad($carnetIdentidad) : '';
            $studentNoacreditado->setPaterno($paterno);
            $studentNoacreditado->setMaterno($materno);
            $studentNoacreditado->setNombre($nombre);
            $studentNoacreditado->setGeneroTipoId($genero);
            $studentNoacreditado->setFechaNacimiento(new \DateTime($fechaNacimiento));
            $studentNoacreditado->setInstitucioneducativanoacreditada($em->getRepository('SieAppWebBundle:Institucioneducativanoacreditado')->find($id));
            $studentNoacreditado->setGestion("2015");
            $studentNoacreditado->setNivelId($nivel);
            $studentNoacreditado->setGradoId($grado);
            $studentNoacreditado->setParalelo($paralelo);
            $studentNoacreditado->setTurnoId($turno);
            $em->persist($studentNoacreditado);
            $em->flush();
            //print_r($slicearray);
            //echo"<br><br><br>";
        }

        return $this->redirect($this->generateUrl('sie_ienoacreditadas_edit', array('id' => $id)));

        die;
    }

    public function getgenerosAction() {
        $em = $this->getDoctrine()->getManager();
        //create the contenedores
        $ageneros = array();
        $aparalelos = array();
        $aturnos = array();
        //todo the query on genero
        $entity = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
        //build the array with generos
        $generoindex = array('Masculino' => 'M', 'Femenino' => 'F', 'Sin dato' => '-');
        foreach ($entity as $genero) {
            $ageneros[$genero->getId()] = $generoindex[$genero->getGenero()];
        }
        //the same to paralelos
        $oParalelos = $em->getRepository('SieAppWebBundle:ParaleloTipo')->findAll();
        foreach ($oParalelos as $paralelos) {
            $aparalelos[$paralelos->getId()] = $paralelos->getParalelo();
        }
//        //the same to turnos
        $oTurnos = $em->getRepository('SieAppWebBundle:TurnoTipo')->findAll();
        foreach ($oTurnos as $turnos) {
            $aturnos[$turnos->getId()] = $turnos->getTurno();
        }

        $response = new JsonResponse();
        return $response->setData(array('ageneros' => $ageneros, 'aparalelos' => $aparalelos, 'aturnos' => $aturnos));
    }

    /**
     * Creates a form to edit a Institucioneducativanoacreditado entity.
     *
     * @param Institucioneducativanoacreditado $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Institucioneducativanoacreditado $entity) {
        $form = $this->createForm(new InstitucioneducativanoacreditadoType(), $entity, array(
            'action' => $this->generateUrl('sie_ienoacreditadas_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Actualizar', 'attr' => array('class' => 'btn btn-success')));

        return $form;
    }

    /**
     * Edits an existing Institucioneducativanoacreditado entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativanoacreditado')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institucioneducativanoacreditado entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);


        $formData = $request->get('sie_appwebbundle_institucioneducativanoacreditado');

        if ($editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $entity->setDependenciaTipo($em->getRepository('SieAppWebBundle:DependenciaTipo')->find($formData['dependenciaTipo']));
            //$entity->setOrgcurricularTipo(($formData['orgcurricularTipo']));
            $entity->setOrgcurricularTipo(($em->getRepository('SieAppWebBundle:OrgcurricularTipo')->find($formData['orgcurricularTipo'])));
            $entity->setInstitucioneducativa($formData['institucioneducativa']);

            $em->flush();
//            return $this->redirect($this->generateUrl('sie_ienoacreditadas'));
            return $this->redirect($this->generateUrl('sie_ienoacreditadas_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:Institucioneducativanoacreditado:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Institucioneducativanoacreditado entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        //if ($form->isValid()) {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativanoacreditado')->find($id);
        $students = $em->getRepository('SieAppWebBundle:Estudiantenoacredidato')->findBy(array('institucioneducativanoacreditada' => $id));
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institucioneducativanoacreditado entity.');
        }
        //remove the estudents by krlos
        array_walk($students, array($this, 'deleteEntity'), $em);
        $em->remove($entity);
        $em->flush();
        //}

        return $this->redirect($this->generateUrl('sie_ienoacreditadas'));
    }

    protected function deleteEntity($entity, $key, $em) {
        $em->remove($entity);
    }

    /**
     * Creates a form to delete a Institucioneducativanoacreditado entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('sie_ienoacreditadas_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

}
