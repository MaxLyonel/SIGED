<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Form\UsuarioType;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Form\PersonaType;
use Sie\AppWebBundle\Entity\Estudiante;
use \Symfony\Component\HttpFoundation\Response;

/**
 * Usuario controller.
 *
 */
class UsuarioController extends Controller {

    /**
     * form to find the stutdent's users
     *
     */
    public function indexAction(Request $request) {
        // data es un array con claves 'name', 'email', y 'message'

        return $this->render('SieAppWebBundle:Usuario:searchuser.html.twig', array(
                    'form' => $this->createSearchForm()->createView(),
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
        $estudiante = new Estudiante();
        $agestion = array('2015' => '2015', '2016' => '2016');
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('usuario_main_find'))
                ->add('codigoRude', 'text', array('required' => true, 'invalid_message' => 'Campo 1 obligatorio'))
                ->add('fechaNacimiento', 'choice', array('choices' => $agestion,
                    'required' => true))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * Lists all Usuario entities.
     *
     */
    public function resultAction(Request $request) {

        $form = $request->get('form');
        $codigoRude = $form['codigoRude'];

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($codigoRude);
        //echo $entities->getId();    die;
        if ($entities) {
            $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Apoderado');
            /* $query = $repository->createQueryBuilder('a')
              ->join('a.', 'c')
              ->where('a.personaEstudiante = :id')
              ->setParameter('id', $entities->getId())
              ->getQuery(); */
            //          echo $entities->getId();
            //die;
            /*
              $query = $em->createQueryBuilder()
              ->from('Embed', 'e')
              ->select("e")
              ->leftJoin("User", "u", "WITH", "e.uid=u.id")
              ->leftJoin("Product", "p", "WITH", "e.pid=p.id")
              ->where("u.image > 0 OR p.image > 0")
              ->addOrderBy("COALESCE( u.timeCreated, p.timeCreated )", "DESC")
              ->setMaxResults(28)
              ->getQuery(); */

            $query = $em->createQuery(
                            'SELECT apod, per, apodt FROM SieAppWebBundle:Apoderado apod
                    JOIN apod.personaApoderado per 
                    JOIN apod.apoderadoTipo apodt
                    WHERE apod.personaEstudiante = :perEst'
                    )->setParameter('perEst', $entities->getId());
            $parents = $query->getResult();

            $datauser = array();

            if ($parents) {
                foreach ($parents as $parent) {
                    $person = $parent->getpersonaApoderado()->getId();
                    $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario')->findOneByPersona($person);
                    $active = ($repository) ? 1 : 0;
                    $datauser[$repository->getUsername()] = array('active' => $active, 'idapoderado' => $person, 'name' => $parent->getPersonaApoderado(), 'apoderado' => $parent->getapoderadoTipo(), 'fono' => $parent->gettelefono());
                }
            }
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $datauser, $this->get('request')->query->get('page', 1), 5
        );

        return $this->render('SieAppWebBundle:Usuario:resultuser.html.twig', array(
                    'pagination' => $pagination,
        ));
    }

    /**
     * Creates a new Usuario entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new Usuario();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('usuario_main_show', array('id' => $entity->getId())));
        }

        return $this->render('SieAppWebBundle:Usuario:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

     /**
     * Creates an account to user - create a Usuario entity.
     *
     * @param Usuario $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function createAccountAction($id) {

        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneByPersona($id);
        
        if ($usuario) {
            $form = $this->createForm(new UsuarioType() , $usuario);
            return $this->render('SieAppWebBundle:Usuario:createAccount.html.twig', array('form' => $form->createView()));
            /* $entitiesActivo = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneByUsuario($id);
              //verificamos si el rol esta activo, en el caso de no serlo tenemos q habilitarlo
              if (!$entitiesActivo->getEsactivo()) {

              echo 'rol' . $entitiesActivo->getEsactivo();
              } */
        } else {
            //usuario no tiene credenciales tenemos q crear las credenciales, obteniendo datos personales
        }
    }

    /**
     * Creates a form to create a Usuario entity.
     *
     * @param Usuario $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Usuario $entity) {
        $form = $this->createForm(new UsuarioType(), $entity, array(
            'action' => $this->generateUrl('usuario_main_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Usuario entity.
     *
     */
    public function newAction() {
        $entity = new Usuario();
        $form = $this->createCreateForm($entity);

        return $this->render('SieAppWebBundle:Usuario:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Return a ajax response
     */
    public function greetingAction() {

        /*
          $request = $this->container->get('request');
          $data = $request->request->get('data');
          var_dump($data);
          die; */
        $request = $this->get('request');
        //echo $request;
        $name = $request->request->get('formName');

        $formulario = '<form role="form">
                            <div class="form-group">
                                <label for="recipient-name" class="control-label">Usuario</label>
                                <input type="text" id="username" class="form-control" id="recipient-name">
                            </div>
                            <div class="form-group">
                                <label for="message-text" class="control-label">Password</label>
                                <input type="text"  id="password" class="form-control" id="recipient-name">
                            </div>
                            <div class="form-group">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary">Activar</button>
                    </div>
                        </form>';


        $return = array("responseCode" => 200, "formulario" => $formulario);

        $return = json_encode($return); //jscon encode the array

        return new Response($return, 200, array('Content-Type' => 'application/json')); //make sure it has the correct content type
    }

}
