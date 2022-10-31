<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Institucioneducativa;
use Sie\AppWebBundle\Form\InstitucioneducativaType;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Institucioneducativa controller.
 *
 */
class InstitucioneducativaController extends Controller {

    /**
     * Lists all Institucioneducativa entities.
     *
     */
    public function indexAction(Request $request) {
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        // data es un array con claves 'name', 'email', y 'message'

        /* return $this->render('SieAppWebBundle:Institucioneducativa:searchieducativa.html.twig', array(
          'form' => $this->createSearchForm()->createView(),
          )); */
        return $this->render('SieAppWebBundle:Institucioneducativa:search.html.twig', array(
                    'form' => $this->createSearchFormSie()->createView(),
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
        $institucioneducativa = new Institucioneducativa();
        $form = $this->createFormBuilder($institucioneducativa)
                ->setAction($this->generateUrl('institucioneducativa_result'))
                ->setMethod('GET')
                ->add('institucioneducativa', 'text', array('required' => true, 'invalid_message' => 'Campo obligatorio'))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    private function createSearchFormSie() {
        $institucioneducativa = new Institucioneducativa();
        $form = $this->createFormBuilder($institucioneducativa)
                ->setAction($this->generateUrl('institucioneducativa_view'))
                ->setMethod('POST')
                ->add('institucioneducativa', 'text', array('required' => true))
                ->add('gestion', 'choice', array("mapped" => false, 'choices' => array('2014' => '2014', '2015' => '2015'), 'required' => true))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * Find the institucion educativa.
     *
     */
    public function findieducativaAction(Request $request) {

        $sesion = $request->getSession();

        $form = $request->get('form');
        $ieducativa = strtoupper(($form['institucioneducativa']));

        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Institucioneducativa');
        $query = $repository->createQueryBuilder('i')
                ->where('i.institucioneducativa like :ieducativa')
                ->setParameter('ieducativa', '%' . $ieducativa . '%')
                ->orderBy('i.institucioneducativa', 'ASC')
                ->getQuery();
        $entities = $query->getResult();

        $msg = ($entities) ? 'Busqueda con resultados...' : 'No se encontro la información...';
        $this->get('session')->getFlashBag()->add('result', $msg);

        if (!$entities) {
            return $this->render('SieAppWebBundle:Institucioneducativa:searchieducativa.html.twig', array(
                        'form' => $this->createSearchForm()->createView(),
            ));
        }
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $entities, $this->get('request')->query->get('page', 1), 10
        );

        return $this->render('SieAppWebBundle:Institucioneducativa:resultinseducativa.html.twig', array(
                    'pagination' => $pagination,
        ));
    }

    /**
     * Creates a new Institucioneducativa entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new Institucioneducativa();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('institucioneducativa_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:Institucioneducativa:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Institucioneducativa entity.
     *
     * @param Institucioneducativa $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Institucioneducativa $entity) {
        $form = $this->createForm(new InstitucioneducativaType(), $entity, array(
            'action' => $this->generateUrl('institucioneducativa_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Institucioneducativa entity.
     *
     */
    public function newAction() {
        $entity = new Institucioneducativa();
        $form = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:Institucioneducativa:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Institucioneducativa entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institucioneducativa entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:Institucioneducativa:show.html.twig', array(
                    'entity' => $entity,
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Institucioneducativa entity.
     *
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institucioneducativa entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return $this->render('SieAppWebBundle:Institucioneducativa:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a Institucioneducativa entity.
     *
     * @param Institucioneducativa $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(Institucioneducativa $entity) {
        $form = $this->createForm(new InstitucioneducativaType(), $entity, array(
            'action' => $this->generateUrl('institucioneducativa_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Institucioneducativa entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Institucioneducativa entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('institucioneducativa_edit', array('id' => $id)));
        }

        return $this->render('SieAppWebBundle:Institucioneducativa:edit.html.twig', array(
                    'entity' => $entity,
                    'edit_form' => $editForm->createView(),
                    'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Institucioneducativa entity.
     *
     */
    public function deleteAction(Request $request, $id) {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Institucioneducativa entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('institucioneducativa'));
    }

    /**
     * Creates a form to delete a Institucioneducativa entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id) {
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('institucioneducativa_delete', array('id' => $id)))
                        ->setMethod('DELETE')
                        ->add('submit', 'submit', array('label' => 'Delete'))
                        ->getForm()
        ;
    }

    public function viewAction(Request $request) {
        //$sesion = $this->getRequest()->getSession();

        $sesion = new Session();
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        if ($request->getMethod() == 'POST') {
            $sesion->set('institucioneducativa', $form['institucioneducativa']);
        }
        $form['institucioneducativa'] = ($sesion->get('institucioneducativa')) ? $sesion->get('institucioneducativa') : $form['institucioneducativa'];
        $insEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
        if ($insEducativa) {

            $entities = $em->getRepository('SieAppWebBundle:UsuarioRol')->findBy(array('usuario' => $sesion->get('userId2')));
            //get the lugar tipo para el usuario loggeado
            $alugarTipo = array();
            foreach ($entities as $entity) {
                if ($entity->getLugarTipo())
                    $alugarTipo[] = $entity->getLugarTipo()->getId();
            }
            //verificamos si tiene tuicion para buscar sus usuarios
            $entities = $em->getRepository('SieAppWebBundle:LugarTipo');
            $query = $entities->createQueryBuilder('lt')
                    ->select('lt')
                    ->LeftJoin('SieAppWebBundle:JurisdiccionGeografica', 'jg', 'WITH', 'lt.id=jg.lugarTipoLocalidad')
                    ->LeftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'jg.id=ie.leJuridicciongeografica')
                    ->where('ie.id = :sie')
                    ->setParameter('sie', $form['institucioneducativa'])
                    ->getQuery();
            $lugares = $query->getResult();

            //paso inicial para hacer la busqueda de la tuiion, obtenemos el id de lugar
            foreach ($lugares as $lugar) {
                $lugarTipoId = $lugar->getLugarTipo()->getId();
            }
            // creamos una bandera para la busqueda de la tuicion
            $sw = 0;
            if (in_array($lugarTipoId, $alugarTipo))
                $sw = 1;
            //realizamos la busqueda del id de lugar para determinar la tuicion
            while (!$sw && $lugarTipoId) {
                $newEntity = $em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($lugarTipoId);
                if (in_array($newEntity->getId(), $alugarTipo)) {
                    $sw = 1;
                } else {
                    $lugarTipoId = (is_object($newEntity->getLugarTipo())) ? $newEntity->getLugarTipo()->getId() : 0;
                }
            }
            // no tiene tuicion mostramos la opcion de busqueda
            if ($sw) {
                $sesion->getFlashBag()->add('notice', 'El usuario no tiene tuicion para completar la busqueda...');
                return $this->render('SieAppWebBundle:Institucioneducativa:search.html.twig', array(
                            'form' => $this->createSearchFormSie()->createView(),
                ));
            }
            //si tenemos tuicion, realizamos la busqueda de los usuarios dependiendo del rol **para el caso distrital no problem
            //necesitamos ADD el rol para obtener los directores y secretarias -- esto hasta que tengamos datos
            $qusers = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
            $query = $qusers->createQueryBuilder('mi')
                    ->select('mi.id as maestroInsId,p.id as personId, p.paterno, p.materno, p.nombre', 'IDENTITY( mi.rolTipo) as rolId', '( ct.cargo) as cargo')
                    ->LeftJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona=p.id')
                    ->leftjoin('SieAppWebBundle:CargoTipo','ct', 'WITH','mi.cargoTipo = ct.id')
                    ->where('mi.institucioneducativa = :sie')
                    ->andwhere('mi.gestionTipo = :gestion')
                    ->setParameter('sie', $form['institucioneducativa'])
                    ->setParameter('gestion', $sesion->get('currentyear'))
                    ->getQuery();
            $personal = $query->getResult();
//            echo "<pre>";
//            print_r($personal);
//            echo "</pre>";
//            die;
            return $this->render('SieAppWebBundle:Institucioneducativa:view.html.twig', array('institucion' => $insEducativa, 'personal' => $personal));
        } else {
            $sesion->getFlashBag()->add('msgSearch', 'No existe información para el código introducido...');
            return $this->render('SieAppWebBundle:Institucioneducativa:search.html.twig', array(
                        'form' => $this->createSearchFormSie()->createView(),
            ));
        }



//        print_r($lugarTipoId);
        //tiene tuicion obtenemos sus usuarios dependiendo del user loggeado



        if ($request->getMethod() == 'POST' or ! ($sesion->get('institucioneducativa'))) {
            if ($request->getMethod() == 'POST') {
                $form = $request->get('form');
                $form['institucioneducativa'];
                $institucion = $form['institucioneducativa'];
                $sesion->set('institucioneducativa', $institucion);
            } else {
                $institucion = $sesion->get('institucioneducativa');
            }
            $em = $this->getDoctrine()->getManager();
            $result = $em->getRepository('SieAppWebBundle:UsuarioInstitucioneducativa')->findOneBy(array('usuario' => 8050342, 'institucioneducativa' => $institucion)); // funcion para ver la tuicion del usuario sobre la unidad educativa 
            if (!$result) { // si tiene tuicion
                $this->get('session')->getFlashBag()->add('msgSearch', 'No tiene permisos');
                return $this->redirect($this->generateUrl('institucioneducativa'));
            }
            $insEducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

            $query = $em->createQuery(
                            'SELECT mains, per, carT FROM SieAppWebBundle:MaestroInscripcion mains
                    JOIN mains.persona per 
                    JOIN mains.cargoTipo carT
                    WHERE mains.institucioneducativa = :insEdu'
                    )->setParameter('insEdu', $institucion);

            $personal = $query->getResult();

            //$personal = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findOneBy(array('institucioneducativa'=>$form['institucioneducativa'],'gestionTipo'=>2015));
            if (!$personal) {
                echo "no hay";
                die;
                $personal = array();
            }
            return $this->render('SieAppWebBundle:Institucioneducativa:view.html.twig', array('institucion' => $insEducativa, 'personal' => $personal));
        }
        return $this->redirect($this->generateUrl('institucioneducativa'));
    }

}
