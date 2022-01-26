<?php

namespace Sie\RegularBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Sie\AppWebBundle\Entity\UsuarioSession;

/**
 * Usuario controller.
 *
 */
class UsuariosUEGestionController extends Controller {

    private $session;

    /**
     * Cargar lista de usuarios
     */
    public function indexAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $this->session = new Session();

            /*
             * Obtenemos el lugar al que pertenece el usuario 
             */
            $usuarioRol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $this->session->get('userId'), 'rolTipo' => $this->session->get('roluser')));

            $query = $em->getConnection()->prepare('SELECT get_usuario_tuicion_ie_listado_json(:usuario::INTEGER,:rol::INTEGER,:lugar::INTEGER)');
            $query->bindValue(':usuario', $this->session->get('userId'));
            $query->bindValue(':rol', $this->session->get('roluser'));
            $query->bindValue(':lugar', $usuarioRol->getLugarTipo()->getId());
            $query->execute();
            $usuarios = $query->fetchAll();
            
            $users = array();

            for ($i = 0; $i < count($usuarios); $i++) {
                $registro = json_decode($usuarios[$i]['get_usuario_tuicion_ie_listado_json']);
                $users[] = array(
                    'institucionEducativaId' => $registro->institucion_educativa_id,
                    'institucioneducativa' => $registro->institucioneducativa,
                    'juridiccionGeografica' => $registro->le_juridicciongeografica_id,
                    'lugarTipoIdDistrito' => $registro->lugar_tipo_id_distrito,
                    'lugarNivelId' => $registro->lugar_nivel_id,
                    'lugares' => $registro->lugares,
                    'usuarioRolId' => $registro->usuariorol_id,
                    'usuarioId' => $registro->usuario_id,
                    'rolTipoId' => $registro->rol_tipo_id,
                    'personaId' => $registro->persona_id,
                    'carnet' => $registro->carnet,
                    'paterno' => $registro->paterno,
                    'materno' => $registro->materno,
                    'nombre' => $registro->nombre,
                    'correo' => $registro->correo,
                    'celular' => $registro->celular,
                    'username' => $registro->username,
                    'genero' => $registro->genero,
                    'esactivo' => ($registro->esactivo) ? 'Si' : 'No');
            }

            $usuarios = $users;
/*
            echo "<pre>";
            print_r($usuarios);
            echo "<pre>";
            die;*/

            return $this->render($this->session->get('pathSystem').':UsuariosUEGestion:index.html.twig', array('usuarios' => $usuarios));
        } catch (Exception $ex) {
            
        }
    }

    public function editAction(Request $request) {
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneById($request->get('idUsuario'));
        $usuarioRol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneById($request->get('idUsuarioRol'));
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($request->get('idPersona'));
        $estadoActivo = array('1' => 'Si', '0' => 'No');
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('usuariosuegestion_update'))
                ->add('ruta', 'hidden', array('data' => 'listaUsuarios')) // sirve para redireccionar una vez echo el update
                ->add('idUsuario', 'hidden', array('data' => $usuario->getId()))
                ->add('idUsuarioRol', 'hidden', array('data' => $usuarioRol->getId()))
                ->add('idPersona', 'hidden', array('data' => $persona->getId()))
                ->add('ci', 'text', array('label' => 'Carnet de Identidad', 'data' => $persona->getCarnet(), 'required' => true, 'attr' => array('class' => 'form-control', 'maxlength' => 10, 'pattern' => '[0-9]{4,10}', 'autocomplete' => 'off')))
                ->add('complemento', 'text', array('label' => 'Complemento', 'data' => $persona->getComplemento(), 'required' => false, 'attr' => array('class' => 'form-control', 'maxlength' => 2, 'pattern' => '[0-9]{1}[A-Z]{1}', 'autocomplete' => 'off')))
                ->add('paterno', 'text', array('label' => 'Paterno', 'data' => $persona->getPaterno(), 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => "[a-zA-Z\s'áéíóúÁÉÍÓÚ]{2,40}", 'autocomplete' => 'off', 'style' => 'text-transform:uppercase')))
                ->add('materno', 'text', array('label' => 'Materno', 'data' => $persona->getMaterno(), 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => "[a-zA-Z\s'áéíóúÁÉÍÓÚ]{2,40}", 'autocomplete' => 'off')))
                ->add('nombre', 'text', array('label' => 'Nombre(s)', 'data' => $persona->getNombre(), 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => "[a-zA-Z\s'áéíóúÁÉÍÓÚ]{2,40}", 'autocomplete' => 'off')))
                ->add('genero', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'property' => 'genero', 'data' => $em->getReference('SieAppWebBundle:GeneroTipo', $persona->getGeneroTipo()->getId()), 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'data' => $persona->getFechaNacimiento()->format('d-m-Y'), 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                ->add('estadoCivil', 'entity', array('label' => 'Estado Civil', 'class' => 'SieAppWebBundle:EstadoCivilTipo', 'property' => 'estadoCivil', 'data' => $em->getReference('SieAppWebBundle:EstadoCivilTipo', $persona->getEstadocivilTipo()->getId()), 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('celular', 'text', array('label' => 'Celular', 'data' => $persona->getCelular(), 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{8}', 'autocomplete' => 'off', 'maxlength' => 8)))
                ->add('correo', 'text', array('label' => 'Correo', 'data' => $persona->getCorreo(), 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '^[_a-zA-Z0-9-]+(.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(.[a-zA-Z0-9-]+)*[.]([a-zA-Z]{2,3})$', 'autocomplete' => 'off')))
                ->add('usuario', 'text', array('label' => 'Usuario', 'data' => $usuario->getUsername(), 'required' => true, 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('password', 'text', array('label' => 'Password', 'data' => '********', 'required' => true, 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('newPassword', 'text', array('label' => 'Nuevo Password', 'mapped' => false, 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off', 'pattern' => '[0-9a-zA-Z]{1,20}')))
                ->add('rol', 'text', array('label' => 'Rol', 'data' => $usuarioRol->getRolTipo()->getRol(), 'required' => true, 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('lugar', 'text', array('label' => 'Lugar', 'data' => ($usuarioRol->getLugarTipo()) ? $usuarioRol->getLugarTipo()->getLugar() : '', 'required' => true, 'disabled' => true, 'attr' => array('class' => 'form-control')))
                ->add('activo', 'choice', array('label' => 'Activo', 'choices' => $estadoActivo, 'data' => $usuarioRol->getEsactivo(), 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        return $this->render($this->session->get('pathSystem') . ':UsuariosUEGestion:edit.html.twig', array('form' => $form->createView()));
    }

    public function updateAction(Request $request) {
        /*$this->session = new Session();
        $form = $request->get('form');
        // Actualizacion de datos personales / persona
        $em = $this->getDoctrine()->getManager();
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['idPersona']);
        $persona->setCarnet($form['ci']);
        $persona->setComplemento($form['complemento']);
        $persona->setPaterno($form['paterno']);
        $persona->setMaterno($form['materno']);
        $persona->setNombre($form['nombre']);
        $persona->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['genero']));
        $persona->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
        $persona->setEstadocivilTipo($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById($form['estadoCivil']));
        $persona->setCelular($form['celular']);
        $persona->setCorreo($form['correo']);
        $em->persist($persona);
        $em->flush();
        // Actualizacion de password si es que se tiene el campo         newPassword lleno
         
        if ($form['newPassword'] != "") {
            $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneById($form['idUsuario']);
            $usuario->setPassword(md5($form['newPassword']));
            $em->persist($usuario);
            $em->flush();
        }
        // Actualizar activo e inactivo / usuario_rol         
        if (isset($form['activo'])) {
            $usuarioRol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneById($form['idUsuarioRol']);
            $usuarioRol->setEsactivo($form['activo']);
            $em->persist($usuarioRol);
            $em->flush();
        }

        if ($form['ruta'] == 'paginaPrincipal') {
            // Registramos la sesion del usuario
            $sesionUsuario = new UsuarioSession();
            $sesionUsuario->setFecharegistro((new \DateTime('now'))->format('Y-m-d'));
            $nombreUsuario = $this->session->get('lastname') . " " . $this->session->get('lastname2') . " " . $this->session->get('name');
            $sesionUsuario->setNombre($nombreUsuario);
            $sesionUsuario->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->findOneById($this->session->get('userId')));
            $em->persist($sesionUsuario);
            $em->flush();
            // Direccionamos a la pagina principal
            return $this->redirect($this->generateUrl('sie_login_homepage'));
        } else {
            // Direccionamos a la lista de usuarios
            return $this->redirect($this->generateUrl('usuariogestion'));
        }*/
    }

    public function activarDesactivar_usuarioAction(Request $request) {
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $usuarioRol = $em->getRepository('SieAppWebBundle:UsuarioRol')->find($request->get('idUsuarioRol'));
        if ($usuarioRol->getEsactivo()) {
            $newState = 'false';
            $nuevoEstado = 'No';
        } else {
            $newState = 'true';
            $nuevoEstado = 'Si';
        }
        $usuarioRol->setEsactivo($newState);
        $em->flush();

        return $this->render($this->session->get('pathSystem') . ':UsuariosUEGestion:botonActualizar.html.twig', array('estado' => $nuevoEstado, 'idUsuarioRol' => $usuarioRol->getId(), 'div' => $request->get('div')));
    }

}
