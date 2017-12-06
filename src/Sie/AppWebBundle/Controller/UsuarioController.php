<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Form\UsuarioType;
use Sie\AppWebBundle\Entity\Estudiante;
use \Sie\AppWebBundle\Entity\UsuarioRol;
use Symfony\Component\HttpFoundation\Session\Session;

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


//        $dir = $this->get('kernel')->getRootDir() . '/../web/2015files/';
//        $files = scandir($dir, 1);
//        //echo sizeof($files) . '<br>';
//        $i = 0;
//        foreach ($files as $file) {
//
//            if (strcmp($file, '..') !== 0 and strcmp($file, '.') !== 0) {
//                echo $i . '-' . $file . '---> ';
//                $newdirs = scandir($dir . '/' . $file, 1);
//                foreach ($newdirs as $newdir) {
//                    if (strcmp($newdir, '..') !== 0 and strcmp($newdir, '.') !== 0) {
//                        if (strcmp($newdir, '5') === 0) {
//                            //echo '----' . $newdir . ' ';
//                            $empdir = scandir($dir . '/' . $file . '/' . $newdir);
//                            foreach ($empdir as $emp) {
//                                if (strcmp($emp, '..') !== 0 and strcmp($emp, '.') !== 0) {
//                                    echo "---<br>";
//                                    echo 'mv ' . $dir . $file . '/' . $newdir . '/' . $emp . ' ' . $this->get('kernel')->getRootDir() . '/../web/2015new/';
//                                    system('mv ' . $dir . $file . '/' . $newdir . '/' . $emp . ' ' . $this->get('kernel')->getRootDir() . '/../web/2015new/');
//                                    echo "---<br>";
//                                }
//                            }
//                        }
//                    }
//                }
//                echo '<br>';
//                $i++;
//            }
//        }
//
//        die('kkkk');
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
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
        $agestion = array('2015' => '2015');
        $form = $this->createFormBuilder($estudiante)
                ->setAction($this->generateUrl('usuario_main_find'))
                ->add('codigoRude', 'text', array('required' => true, 'invalid_message' => 'Campo 1 obligatorio'))
                ->add('gestion', 'choice', array("mapped" => false, 'choices' => $agestion, 'required' => true))
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
        $session = new Session();
        //$codigoRude = ($form) ? $form['codigoRude'] : $request->get('codigoRude');

        if ($form) {
            $codigoRude = $form['codigoRude'];
            $request->getSession()->set('codigoRude', $codigoRude);
        } else {
            $codigoRude = $request->getSession()->get('codigoRude');
        }

        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($codigoRude);
        //verificamos si existe el estudiante;
        if ($entities) {

            //obtenemos informaicon sobre el estudiante
            $query = $em->getConnection()->prepare('SELECT get_estudiante_datos (:rude::VARCHAR)');
            $query->bindValue(':rude', $entities->getCodigoRude());
            $query->execute();
            $dataStudent = $query->fetchAll();
            //verificamos si hay datos del estudiante
            if (!$dataStudent) {
                $session->getFlashBag()->add('notice', 'No existe información personal del Estudiante');
                return $this->render('SieAppWebBundle:Libreta:searchdatalibreta.html.twig', array('form' => $this->createSearchForm()->createView()));
            }
            $arraykeystudent = array('codigo_rude', 'carnet_identidad', 'paterno', 'materno', 'nombre', 'genero', 'lugar_nacimiento', 'oficialia', 'libro', 'partida', 'folio', 'grupo_sanguineo', 'fecha_nacimiento', 'estado_civil', 'pasaporte');
            //formateamos el resultado de la consulta datos estudiante
            $datastudent = $this->resetData($dataStudent, $arraykeystudent, 'get_estudiante_datos');


            //obtenemos informacion de inscripcion del estudiante            
            $query = $em->getConnection()->prepare('SELECT get_estudiante_inscripcion (:estudiante_id::INT)');
            $query->bindValue(':estudiante_id', $entities->getId());
            $query->execute();
            $dataInscription = $query->fetchAll();
            //verificamos si hay datos del estudiante
            if (!$dataInscription) {
                $session->getFlashBag()->add('notice', 'No existe información de Inscripción del Estudiante');
                return $this->render('SieAppWebBundle:Libreta:searchdatalibreta.html.twig', array('form' => $this->createSearchForm()->createView()));
            }
            $arraykeyinscription = array('estudiante_id', 'estudiante_inscripcion_id', 'grado_tipo_id', 'grado', 'paralelo_tipo_id', 'paralelo', 'gestion', 'ciclo_tipo_id', 'ciclo', 'dependencia_tipo_id', 'dependencia', 'orgcurricula', 'institucioneducativa_tipo_id', 'institucioneducativa_tipo', 'convenio_tipo_id', 'convenio', 'institucioneducativa_id', 'institucioneducativa', 'nivel_tipo_id', 'nivel', 'periodo_tipo_id', 'periodo', 'turno_tipo_id', 'turno', 'sucursal_tipo', 'sucursal', 'fecha_inscripcion', 'observacion');
            $query = $em->getConnection()->prepare('SELECT get_estudiante_libreta_nota_json(:rude::VARCHAR, :sie::INT, :gestion::INT, :nivel::INT, :grado::INT, :periodo::INT, :turno::INT, :sucursal::SMALLINT )');
            //formateamos el resultado de la consulta datos inscripcion
            $datainscription = $this->resetData($dataInscription, $arraykeyinscription, 'get_estudiante_inscripcion');

            //obtenemos apoderados para el estudiante
            $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Apoderado');
            $query = $em->createQuery(
                            'SELECT apod, per, apodt FROM SieAppWebBundle:Apoderado apod
                    JOIN apod.personaApoderado per 
                    JOIN apod.apoderadoTipo apodt
                    WHERE apod.personaEstudiante = :perEst'
                    )->setParameter('perEst', $entities->getId());
            $parents = $query->getResult();

            $datauser = array();
            //verificamos si tiene apoderados el apoderado
            if ($parents) {
                foreach ($parents as $parent) {

                    $person = $parent->getpersonaApoderado()->getId();
                    $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario')->findOneByPersona($person);
                    //validar si tiene usuario
                    if ($repository) {
                        $userinfo = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $repository->getId(), 'rolTipo' => 3));
                        //verficar si el usuario tiene rol de apoderado
                        if ($userinfo) {
                            $datauser[$repository->getUsername()] = array('active' => $userinfo->getEsactivo(), 'idapoderado' => $person, 'name' => $parent->getPersonaApoderado(), 'apoderado' => $parent->getapoderadoTipo(), 'fono' => $parent->gettelefono(), 'codigoRude' => $codigoRude, 'apoderadoActivo' => $parent->getEsactivo(), 'apoderadoId' => $parent->getId());
                        } else {
                            $datauser[$repository->getUsername()] = array('active' => 'si', 'idapoderado' => $person, 'name' => $parent->getPersonaApoderado(), 'apoderado' => $parent->getapoderadoTipo(), 'fono' => $parent->gettelefono(), 'codigoRude' => $codigoRude, 'apoderadoActivo' => $parent->getEsactivo(), 'apoderadoId' => $parent->getId());
                        }
                    } else {
                        $datauser[$person] = array('active' => 'no', 'idapoderado' => $person, 'name' => $parent->getPersonaApoderado(), 'apoderado' => $parent->getapoderadoTipo(), 'fono' => $parent->gettelefono(), 'codigoRude' => $codigoRude, 'apoderadoActivo' => $parent->getEsactivo(), 'apoderadoId' => $parent->getId());
                    }
                    //echo $userinfo->getEsactivo()."<br>";
                }
                //die;
            } else {//no user for this student
                $session->getFlashBag()->add('notice', 'No existe Apoderados para el Estudiante');
                return $this->render('SieAppWebBundle:Usuario:searchuser.html.twig', array('form' => $this->createSearchForm()->createView(),));
            }
        } else {
            $session->getFlashBag()->add('notice', 'El Rude es invalido o Estudiante no Existe');
            return $this->render('SieAppWebBundle:Usuario:searchuser.html.twig', array('form' => $this->createSearchForm()->createView()));
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
                $datauser, $this->get('request')->query->get('page', 1), 10
        );

        return $this->render('SieAppWebBundle:Usuario:resultuser.html.twig', array(
                    'pagination' => $pagination, 'datastudent' => $datastudent, 'datainscription' => $datainscription
        ));
    }

    /**
     * Lists all Usuario entities.
     *
     */
    public function index1Action(Request $request) {
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('SieAppWebBundle:Usuario')->findAll();
        $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario');
        $query = $repository->createQueryBuilder('u')
                ->where('u.username like :username')
                ->setParameter('username', 'FLAN%')
                ->getQuery();
        $entities = $query->getResult();

        $paginator = $this->get('knp_paginator');

        $pagination = $paginator->paginate(
                $entities, $request->query->get('page', 1)/* page number */, 10/* limit per page */
        );

        return $this->render('SieAppWebBundle:Usuario:index.html.twig', array('pagination' => $pagination));

        /* return $this->render('SieAppWebBundle:Usuario:index.html.twig', array(
          'entities' => $entities,
          )); */
    }

    /**
     * Creates a form to view  the data users of student selected
     *
     * @param mixed $id The entity id
     * @param mixed $rude The entity rude
     * @return \Symfony\Component\Form\Form The form
     */
    public function resetPasswdAction($id, $rude) {
        //echo md5("FmpG89skSA");die;
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneByPersona($id);
        //print_r($usuario);die;
        if (!$usuario) {
            //si no tiene usuario 
            $newUserForm = $this->createNewForm($id, $rude);
            return $this->render('SieAppWebBundle:Usuario:newuser.html.twig', array('form' => $newUserForm->createView()));
        } else {
            $rolApoderado = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('rolTipo' => '3', 'usuario' => $usuario->getId()));
            //verficacmos si tiene rol apoderados
            if ($rolApoderado) {
                //creamos el formulario para hacer el rest del passwd
                $formResetPasswd = $this->createResetPasswdForm($id, $rude);
                return $this->render('SieAppWebBundle:Usuario:resetpasswduser.html.twig', array('form' => $formResetPasswd->createView()));
            } else {
                //si no tiene rol de apoderado retornamos al listado de apoderado
                return $this->redirect($this->generateUrl('usuario_main_find'));
//                $formNewRol = $this->createNewRolForm($id, $rude);
//                return $this->render('SieAppWebBundle:Usuario:activaruser.html.twig', array('form' => $formResetPasswd->createView()));
            }
        }
    }

    private function createResetPasswdForm($id, $rude) {
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('persona' => $id));

        return $this->createFormBuilder($usuario)
                        ->setAction($this->generateUrl('usuario_reset_passwd_save'))
                        ->add('id', 'hidden', array('required' => 'required'))
                        ->add('username', 'text', array('required' => 'required', 'disabled' => true))
                        ->add('newpassword', 'text', array("mapped" => false, 'required' => true))
                        ->add('rude', 'hidden', array("mapped" => false, 'required' => 'required', 'data' => $rude))
                        ->add('save', 'submit', array('label' => 'Aceptar'))
                        ->getForm();
    }

    /**
     * save the new password fro the user selected
     *
     * @param mixed $request array
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function resetPasswdSaveAction(Request $request) {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        try {
            $user = $em->getRepository('SieAppWebBundle:Usuario')->findOneById($form['id']);
            $user->setpassword($form['newpassword']);
            $em->persist($user);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        $session->getFlashBag()->add('success', 'Password cambiado satisfactoriamente');
        return $this->redirect($this->generateUrl('usuario_main_find'));

        //$user = $em->getRepository('SieAppWebBundle')->findOneBy(array('usuario'=>$form))
    }

    /**
     * Creates a form to view  the users of student selected
     *
     * @param mixed $id The entity id
     * @param mixed $rude The entity rude
     * @return \Symfony\Component\Form\Form The form
     */
    public function createAccountAction(Request $request) {
        //echo md5("FmpG89skSA");die;
        $id = $request->get('id');
        $rude = $request->get('rude');
        $apoderadoId = $request->get('apoderadoId');
        $em = $this->getDoctrine()->getManager();
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneByPersona($id);
        if (!$usuario) {
            //creamos formulario para nuevo usuario
            $newUserForm = $this->createNewForm($id, $rude, $apoderadoId);
            return $this->render('SieAppWebBundle:Usuario:newuser.html.twig', array('form' => $newUserForm->createView()));
        } else {
            $rolApoderado = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('rolTipo' => '3', 'usuario' => $usuario->getId()));
            //verficacmos si tiene rol apoderados
            if ($rolApoderado) {
                $formActivar = $this->createActivarForm($id, $rude, $apoderadoId);
                return $this->render('SieAppWebBundle:Usuario:activaruser.html.twig', array('form' => $formActivar->createView()));
            } else {
                $formNewRol = $this->createNewRolForm($id, $rude, $apoderadoId);
                return $this->render('SieAppWebBundle:Usuario:activaruser.html.twig', array('form' => $formNewRol->createView()));
            }
            //print_R($rolApoderado);
            //die;
        }
    }

    private function createActivarForm($id, $rude, $apoderadoId) {
        $em = $this->getDoctrine()->getManager();
        $userRol = array('3' => 'Apoderado'); //$em->getRepository('SieAppWebBundle:RolTipo')->findById(3);
        $userActivo = array('t' => 'Si', 'f' => 'No');
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('persona' => $id));
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($id);
        $nombre = $persona->getNombre();
        $username = $nombre[0] . '' . ($persona->getPaterno()) ? $persona->getPaterno() : $persona->getMaterno() . '' . $persona->getCarnet() . '' . $this->generateRandomString(2);

        $usuario_rol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $usuario->getId(), 'rolTipo' => 3));

        return $this->createFormBuilder($usuario)
                        ->setAction($this->generateUrl('usuario_main_save_activar'))
                        ->add('username', 'text', array('required' => 'required', 'disabled' => true))
                        ->add('password', 'text', array('required' => true, 'disabled' => true))
                        ->add('userid', 'hidden', array("mapped" => false, 'required' => 'required', 'data' => $usuario_rol->getId(), 'disabled' => false))
                        ->add('apoderadoId', 'hidden', array('mapped' => false, 'data' => $apoderadoId))
                        ->add('rude', 'hidden', array("mapped" => false, 'required' => 'required', 'data' => $rude, 'disabled' => false))
                        ->add('rol', 'choice', array("mapped" => false, 'choices' => $userRol, 'disabled' => true))
                        ->add('activo', 'choice', array("mapped" => false, 'choices' => $userActivo, 'data' => ($em->getRepository('SieAppWebBundle:Apoderado')->findOneById($apoderadoId)->getEsactivo()) ? 't' : 'f'))
                        ->add('aceptar', 'submit', array('label' => 'Aceptar'))
                        ->getForm();
    }

    private function createNewRolForm($id, $rude, $apoderadoId) {
        $em = $this->getDoctrine()->getManager();
        $userRol = array('3' => 'Apoderado'); //$em->getRepository('SieAppWebBundle:RolTipo')->findById(3);
        $userActivo = array('t' => 'Si', 'f' => 'No');
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('persona' => $id));
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($id);
        $nombre = $persona->getNombre();
        //$username = $nombre[0] . '' . $persona->getPaterno() . '' . $persona->getCarnet() . '' . $this->generateRandomString(2);
        $username = $nombre[0] . '' . ($persona->getPaterno()) ? $persona->getPaterno() : $persona->getMaterno() . '' . $persona->getCarnet() . '' . $this->generateRandomString(2);
        return $this->createFormBuilder($usuario)
                        ->setAction($this->generateUrl('usuario_main_new_rol'))
                        ->add('username', 'text', array('required' => 'required', 'disabled' => true))
                        ->add('password', 'text', array('required' => true, 'disabled' => true))
                        ->add('userid', 'hidden', array("mapped" => false, 'required' => 'required', 'data' => $usuario->getId(), 'disabled' => false))
                        ->add('apoderadoId', 'hidden', array('mapped' => false, 'data' => $apoderadoId))
                        ->add('rude', 'hidden', array("mapped" => false, 'required' => 'required', 'data' => $rude, 'disabled' => false))
                        ->add('rol', 'choice', array("mapped" => false, 'choices' => $userRol))
                        ->add('activo', 'choice', array("mapped" => false, 'choices' => $userActivo))
                        ->add('aceptar', 'submit', array('label' => 'Registrar rol'))
                        ->getForm();
    }

    private function createNewForm($id, $rude, $apoderadoId) {
        $em = $this->getDoctrine()->getManager();
        $userRol = array('3' => 'Apoderado'); //$em->getRepository('SieAppWebBundle:RolTipo')->findById(3);
        $userActivo = array('t' => 'Si', 'f' => 'No');

        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($id);
        $nombre = $persona->getNombre();
        $username = $nombre[0] . '' . $persona->getPaterno() . '' . $persona->getCarnet() . '' . $this->generateRandomString(2);
        $password = $this->generateRandomString(10);
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('usuario_main_save_new'))
                        ->add('username', 'hidden', array('required' => 'required', 'data' => $username, 'disabled' => false))
                        ->add('usernameview', 'text', array('required' => 'required', 'data' => $username, 'disabled' => true))
                        ->add('password', 'hidden', array('required' => true, 'data' => $password, 'disabled' => false))
                        ->add('passwordview', 'text', array('required' => true, 'data' => $password, 'disabled' => true))
                        ->add('persona', 'hidden', array('required' => 'required', 'data' => $id, 'disabled' => false))
                        ->add('apoderadoId', 'hidden', array('mapped' => false, 'data' => $apoderadoId))
                        ->add('rol', 'choice', array("mapped" => false, 'choices' => $userRol))
                        ->add('activo', 'choice', array("mapped" => false, 'choices' => $userActivo))
                        ->add('rude', 'hidden', array("mapped" => false, 'required' => 'required', 'data' => $rude, 'disabled' => false))
                        ->add('aceptar', 'submit', array('label' => 'Aceptar'))
                        ->getForm();
    }

    function generateRandomString($length = 10) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    /**
     * Creates a form to create a  new Usuario entity by id.
     *
     * @param mixed $request array
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function saveActivoAction(Request $request) {
        //get the user id
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        //need to change the user inactive
        $this->changeStatusOfUser($form['rude']);
        //echo $form['userid'];

        try {
            $apoderado = $em->getRepository('SieAppWebBundle:Apoderado')->findOneById($form['apoderadoId']);
            $apoderado->setEsactivo($form['activo']);
            $em->persist($apoderado);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }


        // envio de correo electronico
        $mensaje = \Swift_Message::newInstance()
                ->setSubject('Hola')
                ->setFrom('sie@gmail.com')
                ->setTo('jhonnyq17@gmail.com')
                ->setBody($this->renderView('SieAppWebBundle:Usuario:emailUser.html.twig', array('usuario' => 'jhonny', 'password' => 'aaabbbccc123')), 'text/html');

        $this->get('mailer')->send($mensaje);


        return $this->redirect($this->generateUrl('usuario_main_find'));
        //$user = $em->getRepository('SieAppWebBundle')->findOneBy(array('usuario'=>$form))
    }

    /**
     * change the estatus of user
     *
     */
    public function changeStatusOfUser($rude) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($rude);
        //echo $entities->getId();    die;
        if ($entities) {
            //$repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Apoderado');

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
                    try {
                        $apoderado = $em->getRepository('SieAppWebBundle:Apoderado')->findOneById($parent->getId());
                        $apoderado->setEsactivo('f');
                        $em->persist($apoderado);
                        $em->flush();
                    } catch (Exception $ex) {
                        echo $ex->getTraceAsString();
                    }
                }
            }

            return 1;
        }
    }

    /**
     * Creates a form to create a  new Usuario entity by id.
     *
     * @param mixed $request array
     *
     * @return \Symfony\Component\Form\Form The form
     */
    public function saveNewAction(Request $request) {
        //print_r($request);
        $em = $this->getDoctrine()->getManager();

        $form = $request->get('form');
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneById($form['persona']);
        try {
            //guardamos datos de usuario
            $usuario = new Usuario();
            $usuario->setPersona($persona);
            $usuario->setUsername($form['username']);

            $pass = md5($form['password']);
            $usuario->setPassword($pass);
            $usuario->setFechaRegistro(new \DateTime('now'));
            $em->persist($usuario);
            $em->flush();
            //guardamos datos usuario rol
            $usuarioRol = new UsuarioRol();
            $usuarioRol->setRolTipo($em->getRepository('SieAppWebBundle:RolTipo')->find($form['rol']));

            //$usuarioRol->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($form['persona']));
            $usuarioRol->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($usuario->getId()));

            $usuarioRol->setEsactivo('t');
            $usuarioRol->setLugarTipo(null);
            $em->persist($usuarioRol);
            $em->flush();

            $apoderado = $em->getRepository('SieAppWebBundle:Apoderado')->findOneById($form['apoderadoId']);
            $apoderado->setEsactivo($form['activo']);
            $em->persist($apoderado);
            $em->flush();

            ///need to route the apoderados list
            /// Generar datos para el mensaje

            $ap_nombre = $persona->getNombre() . ' ' . $persona->getPaterno() . ' ' . $persona->getMaterno();
            $ap_usuario = $form['username'];
            $ap_password = $form['password'];

            $this->get('session')->getFlashBag()->add('newUser', array(
                'nombre' => $ap_nombre,
                'usuario' => $ap_usuario,
                'password' => $ap_password,
                'mensaje' => 'El Usuario fue registrado correctamente'
            ));
            return $this->redirect($this->generateUrl('usuario_main_find'));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    public function saveNewRolAction(Request $request) {

        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');


        try {
            //cambiar estado de los demas apoderados
            $this->changeStatusOfUser($form['rude']);

            $usuarioRol = new UsuarioRol();
            $usuarioRol->setRolTipo($em->getRepository('SieAppWebBundle:RolTipo')->find($form['rol']));

            //$usuarioRol->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($form['persona']));
            $usuarioRol->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($form['userid']));

            $usuarioRol->setEsactivo('t');
            $usuarioRol->setLugarTipo(null);

            $em->persist($usuarioRol);
            $em->flush();

            // actualizar estado del apoderado tabla apoderado

            $apoderado = $em->getRepository('SieAppWebBundle:Apoderado')->findOneById($form['apoderadoId']);
            $apoderado->setEsactivo($form['activo']);
            $em->persist($apoderado);
            $em->flush();

            return $this->redirect($this->generateUrl('usuario_main_find'));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }

    private function resetData($dataStudent, $akey, $key) {
        foreach ($dataStudent as $datastudent) {
            $data = str_replace(array('(', ')', '"'), '', $datastudent[$key]);
            $element = explode(',', $data);
            $result = array_combine($akey, $element);
        }
        return $result;
    }

    //get the notas values into an array NOTAS
    private function resetDataArray($datas, $key) {
        $result = array();
        foreach ($datas as $k => $val) {
            $result[] = json_decode($val[$key], true);
        }
        $nota = array();
        foreach ($result as $key => $value) {
            $nota[$value['asignatura']][$value['nota_orden']] = $value['nota_cuantitativa'];
        }
        return $nota;
    }

    ///////////////////////////////////////  otros usuarios  //////////////////////////////////////////////

    public function asignarUsuarioAction(Request $request) {
        $person = $request->get('idPersona');
        $em = $this->getDoctrine()->getManager();
        //$repository = $em->getRepository('SieAppWebBundle:Usuario')->findOneByPersona($person);
        //verificamos si el existe usuario con el rol de unidad educativa 
        $userRol = $em->getRepository('SieAppWebBundle:Usuario');
        $query = $userRol->createQueryBuilder('u')
                ->select('u')
                ->LeftJoin('SieAppWebBundle:UsuarioRol', 'ur', 'with', 'u.id=ur.usuario')
                ->where('u.persona = :persona')
                ->andwhere('ur.rolTipo = :roltipo')
                ->setParameter('persona', $person)
                ->setParameter('roltipo', '5')
                ->getQuery();
        $repository = $query->getResult();

        if ($repository) {
            //obtenemos el id de user
            foreach ($repository as $repo) {
                $iduser = $repo->getId();
            }
            $formActivar = $this->createActiveForm($iduser, $person);
            return $this->render('SieAppWebBundle:Usuario:activaruser.html.twig', array('form' => $formActivar->createView()));
        } else {
            $formNewUser = $this->createNewUserForm($person, $request->get('idInstitucion'));
            return $this->render('SieAppWebBundle:Usuario:gestionuser.html.twig', array('form' => $formNewUser->createView()));
        }
    }

    private function createNewUserForm($idPersona, $idInstitucion) {
        $em = $this->getDoctrine()->getManager();
        $userRol = $em->getRepository('SieAppWebBundle:RolTipo')->findAll();
        $userActivo = array('t' => 'Si', 'f' => 'No');
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($idPersona);
        $nombre = $persona->getNombre();
        $username = $idInstitucion;
        $userpasswd = $this->generateRandomString(10);
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('usuario_nuevo'))
                        ->add('username', 'hidden', array('required' => 'required', 'data' => $username, 'disabled' => false))
                        ->add('usernameview', 'text', array('required' => 'required', 'data' => $username, 'disabled' => false))
                        ->add('password', 'hidden', array('required' => true, 'data' => $userpasswd, 'disabled' => false))
                        ->add('passwordview', 'text', array('required' => true, 'data' => $userpasswd, 'disabled' => true))
                        ->add('persona', 'hidden', array('required' => 'required', 'data' => $idPersona, 'disabled' => false))
                        ->add('rol', 'entity', array('mapped' => false, 'class' => 'SieAppWebBundle:RolTipo', 'property' => 'rol'))
                        ->add('activo', 'choice', array("mapped" => false, 'choices' => $userActivo))
                        ->add('aceptar', 'submit', array('label' => 'Aceptar'))
                        ->getForm();
    }

    public function nuevoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $form = $request->get('form');

        //echo md5($form['password']);die;
        try {

            //$entity = $em->getRepository('SieAppWebBundle:Persona')->findOneBy($form['persona']);
            $persona = $em->getRepository('SieAppWebBundle:Persona')->find($form['persona']);
            //guardamos datos de usuario
            $usuario = new Usuario();
            $usuario->setUsername($form['username']);
            $usuario->setPassword(md5($form['password']));
            $usuario->setFechaRegistro(new \DateTime('now'));
            $usuario->setPersona($persona);
            $em->persist($usuario);
            $em->flush();
            //guardamos datos usuario rol
            $usuarioRol = new UsuarioRol();
            $usuarioRol->setRolTipo($em->getRepository('SieAppWebBundle:RolTipo')->find($form['rol']));
            //$usuarioRol->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find(array('persona' => $form['persona'])));
            $usuarioRol->setEsactivo($form['activo']);
            $usuarioRol->setUsuario($em->getRepository('SieAppWebBundle:Usuario')->find($usuario->getId()));

            $em->persist($usuarioRol);
            $em->flush();

            ///need to route the apoderados list
            return $this->redirect($this->generateUrl('institucioneducativa_view'));
        } catch (Exception $exc) {
            $em->getConnection()->rollback();
            echo $exc->getTraceAsString();
        }
    }

    private function createActiveForm($id, $personaId) {
        $em = $this->getDoctrine()->getManager();
        $userRol = array('5' => 'Unidad Educativa'); //$em->getRepository('SieAppWebBundle:RolTipo')->findById(3);
        $userActivo = array('t' => 'Si', 'f' => 'No');
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->find($id);
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($id);
        $nombre = $persona->getNombre();
        $username = $nombre[0] . '' . ($persona->getPaterno()) ? $persona->getPaterno() : $persona->getMaterno() . '' . $persona->getCarnet() . '' . $this->generateRandomString(2);

        $usuario_rol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $id, 'rolTipo' => '5'));

        return $this->createFormBuilder($usuario)
                        ->setAction($this->generateUrl('usuario_main_save_active'))
                        ->add('username', 'text', array('required' => 'required', 'disabled' => true))
                        ->add('password', 'text', array('required' => true, 'disabled' => true))
                        ->add('userid', 'hidden', array("mapped" => false, 'required' => 'required', 'data' => $usuario_rol->getId(), 'disabled' => false))
                        ->add('personaId', 'hidden', array("mapped" => false, 'required' => 'required', 'data' => $personaId, 'disabled' => false))
                        ->add('rol', 'choice', array("mapped" => false, 'choices' => $userRol, 'disabled' => true))
                        ->add('activo', 'choice', array("mapped" => false, 'choices' => $userActivo, 'data' => ($em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $id))->getEsactivo()) ? 't' : 'f'))
                        ->add('aceptar', 'submit', array('label' => 'Aceptar'))
                        ->getForm();
    }

    // guardamos el estado del usuario unidad educativa
    public function saveActiveAction(Request $request) {
        //get the user id
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        //need to change the user inactive
        //$this->changeStatusOfUserUe($form['personaId']);
        //die;
        //echo $form['userid'];
        $rolUser = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneById($form['userid']);
        try {
            $session->getFlashBag()->add('success', 'La cuenta fue modificada satisfactoriamente');
            $rolUser->setEsactivo($form['activo']);
            $em->persist($rolUser);
            $em->flush();
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
        return $this->redirect($this->generateUrl('institucioneducativa_view'));
    }

    /**
     * change the estatus of user
     *
     */
    public function changeStatusOfUserUe($rude) {
        //die('dfdfdfdf');
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($rude);
        //echo $entities->getId();    die;
        if ($entities) {
            //$repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Apoderado');

            /* $query = $em->createQuery(
              'SELECT apod, per, apodt FROM SieAppWebBundle:Apoderado apod
              JOIN apod.personaApoderado per
              JOIN apod.apoderadoTipo apodt
              WHERE apod.personaEstudiante = :perEst'
              )->setParameter('perEst', $entities->getId());
              $parents = $query->getResult(); */

            //necesitamos ADD el rol para obtener los directores y secretarias -- esto hasta que tengamos datos
            $qusers = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
            $query = $qusers->createQueryBuilder('mi')
                    ->select('mi.id as maestroInsId,p.id as personId, p.paterno, p.materno, p.nombre')
                    ->LeftJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona=p.id')
                    ->where('mi.institucioneducativa = :sie')
                    ->andwhere('mi.gestionTipo = :gestion')
                    ->setParameter('sie', $form['institucioneducativa'])
                    ->setParameter('gestion', 2015)
                    ->getQuery();
            $parents = $query->getArrayResult();
            echo 'krlos';
            print_r($parents);
            die;
            $datauser = array();

            if ($parents) {
                foreach ($parents as $parent) {

                    $person = $parent->getpersonaApoderado()->getId();
                    $user = $em->getRepository('SieAppWebBundle:Usuario')->findOneByPersona($person);
                    if ($user) {
                        $usuario_rol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $user->getId(), 'rolTipo' => 3));
                        //$usuario_rol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneByUsuario(13774654);
                        //echo $usuario_rol->getId();die;
                        if ($usuario_rol) {
                            $repository = $this->getDoctrine()->getRepository('SieAppWebBundle:Usuario')->findOneByPersona($person);
                            $userinfo = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneById($usuario_rol->getId());
                            if ($userinfo) {
                                //if ($userinfo->getEsactivo()) {
                                try {
                                    $userinfo->setEsactivo('f');
                                    $em->persist($userinfo);
                                    $em->flush();
                                } catch (Exception $exc) {
                                    echo $exc->getTraceAsString();
                                }
                                //}
                            }
                        }
                    }
                    //echo $usuario_rol->getId()."<br>";
                }
            }

            return 1;
        }
    }

    private function updateUserForm($idPersona) {
        $em = $this->getDoctrine()->getManager();
        $userRol = $em->getRepository('SieAppWebBundle:RolTipo')->findAll();
        $userActivo = array('t' => 'Si', 'f' => 'No');
        $usuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBy(array('persona' => $id));
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($id);
        $nombre = $persona->getNombre();
        $username = $nombre[0] . '' . ($persona->getPaterno()) ? $persona->getPaterno() : $persona->getMaterno() . '' . $persona->getCarnet() . '' . $this->generateRandomString(2);

        $usuario_rol = $em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $usuario->getId(), 'rolTipo' => 3));

        return $this->createFormBuilder($usuario)
                        ->setAction($this->generateUrl('usuario_main_save_activar'))
                        ->add('username', 'text', array('required' => 'required', 'disabled' => true))
                        ->add('password', 'text', array('required' => true, 'disabled' => true))
                        ->add('userid', 'hidden', array("mapped" => false, 'required' => 'required', 'data' => $usuario_rol->getId(), 'disabled' => false))
                        ->add('rude', 'hidden', array("mapped" => false, 'required' => 'required', 'data' => $rude, 'disabled' => false))
                        ->add('rol', 'choice', array("mapped" => false, 'choices' => $userRol, 'disabled' => true))
                        ->add('activo', 'choice', array("mapped" => false, 'choices' => $userActivo, 'data' => ($em->getRepository('SieAppWebBundle:UsuarioRol')->findOneBy(array('usuario' => $usuario->getId()))->getEsactivo()) ? 't' : 'f'))
                        ->add('aceptar', 'submit', array('label' => 'Aceptar'))
                        ->getForm();
    }

}
