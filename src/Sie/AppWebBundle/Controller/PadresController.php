<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Sie\AppWebBundle\Entity\Session;
use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Entity\UsuarioRol;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\Apoderado;

class PadresController extends Controller {

    /**
     * main action to show the interface of padres
     * @param Request $request
     * @return type
     */
    public function mainAction(Request $request) {
        //die('krlos');
        $sesion = $request->getSession();
        $id_usuario = $sesion->get('userId');

        return $this->render('SieAppWebBundle:Padres:admin.html.twig');
    }

    /**
     * listado de padres y/o apoderados del estudiante
     * @param Request $request
     * @return type
     */
    public function verPadresAction(Request $request) {
        $sesion = $this->getRequest()->getSession();
        $rude = $sesion->get('perEstRude');

        if (strlen($rude) > 0) {
            $existe = 1;
            //echo "existe";
        } else {
            $existe = 0;
            //echo "no existe";
        }

        if ($request->getMethod() == "POST" or $existe == 1) {
            if ($request->getMethod() == "POST") {
                $rude = $request->get("rude");
                $sesion->set('perEstRude', $rude);
            } else {
                $sesion1 = $this->getRequest()->getSession();
                $rude = $sesion1->get('perEstRude');
            }
            $em = $this->getDoctrine()->getManager();
            $datosEst = $em->getRepository('SieAppWebBundle:Persona')->findOneByCodigoRude($rude);
            //print_r($datosEst->getId());die;
            if ($datosEst) {
                $em = $this->getDoctrine()->getManager();
                $query = $em->createQuery(
                                'SELECT apod, per, apodt FROM SieAppWebBundle:Apoderado apod
                    JOIN apod.personaApoderado per
                    JOIN apod.apoderadoTipo apodt
                    WHERE apod.personaEstudiante = :perEst'
                        )->setParameter('perEst', $datosEst->getId());

                $apoderados = $query->getResult();
                // echo count($apoderados); die();
                if ($apoderados) {
                    foreach ($apoderados as $list) {
                        // obtenr el usuario del apoderado
                        $persona = $list->getpersonaApoderado()->getId();
                        $datosUsuario = $em->getRepository('SieAppWebBundle:Usuario')->findOneBypersona($persona);
                        //echo "asdfsda";
                    }
                    $this->get('session')->getFlashBag()->add('mensaje1', 'Usuario registrado');

                    return $this->render('SieAppWebBundle:Padres:gestion.html.twig', array('datosEst' => $datosEst, 'apoderados' => $apoderados, 'usuario' => $datosUsuario));
                } else {
                    echo "no hay apoderados";
                    return $this->render('SieAppWebBundle:Padres:gestion.html.twig', array('datosEst' => $datosEst));
                }
            } else {
                $sesion = $request->getSession();
                $id_usuario = $sesion->get('iduser');
                $em1 = $this->getDoctrine()->getManager();
                $query = $em1->createQuery(
                                'SELECT uie
                                FROM SieAppWebBundle:UsuarioInstitucioneducativa uie
                                WHERE uie.usuario = :idus '
                        )->setParameter('idus', '1');
                $insEducativa = $query->getResult();

                $this->get('session')->getFlashBag()->add(
                        'mensaje', 'El codigo RUDE es invalido!!!'
                );
                return $this->render('SieAppWebBundle:Padres:admin.html.twig', array('insEducativa' => $insEducativa));
            }
        } else {
            die("Error no hay rude");
        }
    }

    /**
     * Adicionar apoderado para el estudiante
     * @param type $idEstudiante
     * @return array informacion del apoderado
     */
    public function apoderadoAddAction($idEstudiante) {
        $em = $this->getDoctrine()->getManager();
        $paises = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll();
        $genero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
        //print_r($genero);die();
        $estCivil = $em->getRepository('SieAppWebBundle:EstadocivilTipo')->findAll();
        $grupoSanguineo = $em->getRepository('SieAppWebBundle:SangreTipo')->findAll();
        $idiomaMat = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findAll();
        $apodTipo = $em->getRepository('SieAppWebBundle:ApoderadoTipo')->findAll();
        return $this->render('SieAppWebBundle:Padres:add.html.twig', array("pais" => $paises, "genero" => $genero,
                    "estCivil" => $estCivil, "gSanguineo" => $grupoSanguineo, "idiomaMaterno" => $idiomaMat, 'idEstudiante' => $idEstudiante, "apodTipo" => $apodTipo));
    }

    /**
     * seteamos la informacion para hacer el update
     * @param Request $request
     * @return object persona
     */
    public function apoderadoAdd2Action(Request $request) {

        if ($request->getMethod() == "POST") {
            $persona = new Persona();
            $persona->setCodigoRude($request->get("rude"));
            $persona->setCarnet($request->get("carnet"));
            $persona->setRda(0);
            $persona->setLibretaMilitar($request->get("libMilitar"));
            $persona->setPasaporte($request->get("pasaporte"));
            $persona->setPaterno($request->get("paterno"));
            $persona->setMaterno($request->get("materno"));
            $persona->setNombre($request->get("nombre"));
            $persona->setFechaNacimiento(new \DateTime($request->get("fechaNac")));
            $persona->setPaisNacId($request->get("pais"));
            $persona->setDepartamentoNacId($request->get("departamento"));
            $persona->setProvinciaNacId($request->get("provincia"));
            $persona->setLocalidadNac($request->get("localidad"));
            $persona->setSegipId(0);
            $persona->setComplemento($request->get("complemento"));

            //print_r($persona);die();

            $em = $this->getDoctrine()->getManager();
            $genero1 = $em->getRepository('SieAppWebBundle:GeneroTipo')->findOneByid($request->get("genero"));
            $persona->setGeneroTipo($genero1);

            $persona->setActivo($request->get("t"));

            $estCivil = $em->getRepository('SieAppWebBundle:EstadocivilTipo')->findOneByid($request->get("estadoCivil"));
            $persona->setEstadoCivilTipo($estCivil);

            $gruSan = $em->getRepository('SieAppWebBundle:SangreTipo')->findOneByid($request->get("grupoSanguineo"));
            $persona->setSangreTipo($gruSan);

            $idiMat = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneByid($request->get("idiomaMaterno"));
            $persona->setIdiomaMaternoId($idiMat);
            /* print_r($persona);
              die;
              echo"krlos";
              die; */

            $em->persist($persona);
            $em->flush();
            die;
            //Registrar en tabla apoderado
            $em2 = $this->getDoctrine()->getManager();
            $apoderado = $em2->getRepository('SieAppWebBundle:Persona')->findOneByid($persona->getId());
            $estudiante = $em2->getRepository('SieAppWebBundle:Persona')->findOneByid($request->get('idEstudiante'));
            $tipoApoderado = $em2->getRepository('SieAppWebBundle:ApoderadoTipo')->findOneByid($request->get('apoderadoTipo'));

            $apode = new Apoderado();
            $apode->setApoderadoTipo($tipoApoderado);
            $apode->setPersonaApoderado($apoderado);
            $apode->setPersonaEstudiante($estudiante);
            $apode->setEmpleo($request->get("empleo"));
            $apode->setGestionId(date("Y"));
            $apode->setIdiomaId($request->get("idiomaMaterno"));
            $apode->setInstruccionId(1);
            $apode->setTelefono($request->get("telefono"));
            $em2->persist($apode);
            $em2->flush();

            print_r($request->get("pais"));
            print_r($request->get("departamento"));
            print_r($request->get("provincia"));
            //die();

            return $this->redirect($this->generateUrl('padres_buscar'));
        }
    }

    /**
     * ver informacion apoderado
     * @param type $idApoderado
     * @return object persona y apoderado data
     */
    public function apoderadoShowAction($idApoderado) {
        $em = $this->getDoctrine()->getManager();
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneByid($idApoderado);
        $apoderado = $em->getRepository('SieAppWebBundle:Apoderado')->findOneBypersonaApoderado($idApoderado);

        return $this->render('SieAppWebBundle:Padres:show.html.twig', array('persona' => $persona, "apoderado" => $apoderado));
    }

    /**
     * editamos la informacion del apoderado
     * @param type $idApoderado
     * @return object form
     */
    public function apoderadoEditAction($idApoderado) {
        $em = $this->getDoctrine()->getManager();

        $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneByid($idApoderado);
        $apoderado = $em->getRepository('SieAppWebBundle:Apoderado')->findOneBypersonaApoderado($idApoderado);
        $paises = $em->getRepository('SieAppWebBundle:PaisTipo')->findAll();
        $departamentos = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipo' => $persona->getpaisNacId()));
        $provincias = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $persona->getdepartamentoNacId()));
        $localidades = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $persona->getprovinciaNacId()));

        $genero = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
        $estCivil = $em->getRepository('SieAppWebBundle:EstadocivilTipo')->findAll();
        $grupoSanguineo = $em->getRepository('SieAppWebBundle:SangreTipo')->findAll();
        $idiomaMat = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findAll();
        $apodTipo = $em->getRepository('SieAppWebBundle:ApoderadoTipo')->findAll();

        return $this->render('SieAppWebBundle:Padres:edit.html.twig', array(
                    "persona" => $persona,
                    "pais" => $paises,
                    "departamentos" => $departamentos,
                    "provincias" => $provincias,
                    "localidades" => $localidades,
                    "genero" => $genero,
                    "estCivil" => $estCivil,
                    "gSanguineo" => $grupoSanguineo,
                    "idiomaMaterno" => $idiomaMat,
                    'apoderado' => $apoderado,
                    "apodTipo" => $apodTipo))
        ;
        // return $this->render('SieAppWebBundle:Padres:edit.html.twig',array('persona'=>$apoderado));
    }

    /**
     * actualizamos informacion del apoderado
     * @param Request $request
     * @return object apoderado data upate
     */
    public function apoderadoUpdateAction(Request $request) {
        if ($request->getMethod() == "POST") {
            $em = $this->getDoctrine()->getManager();
            $persona = $em->getRepository('SieAppWebBundle:Persona')->findOneBy($request->get('idPersona'));
            $persona->setCodigoRude($request->get("rude"));
            $persona->setCarnet($request->get("carnet"));
            $persona->setRda(0);
            $persona->setLibretaMilitar($request->get("libMilitar"));
            $persona->setPasaporte($request->get("pasaporte"));
            $persona->setPaterno($request->get("paterno"));
            $persona->setMaterno($request->get("materno"));
            $persona->setNombre($request->get("nombre"));
            $persona->setFechaNacimiento(new \DateTime($request->get("fechaNac")));
            $persona->setPaisNacId($request->get("pais"));
            $persona->setDepartamentoNacId($request->get("departamento"));
            $persona->setProvinciaNacId($request->get("provincia"));
            $persona->setLocalidadNac($request->get("localidad"));
            $persona->setSegipId(0);
            $persona->setComplemento($request->get("complemento"));

            $genero1 = $em->getRepository('SieAppWebBundle:GeneroTipo')->findOneByid($request->get("genero"));
            $persona->setGeneroTipo($genero1);

            $persona->setActivo($request->get("t"));

            $estCivil = $em->getRepository('SieAppWebBundle:EstadocivilTipo')->findOneByid($request->get("estadoCivil"));
            $persona->setEstadoCivilTipo($estCivil);

            $gruSan = $em->getRepository('SieAppWebBundle:SangreTipo')->findOneByid($request->get("grupoSanguineo"));
            $persona->setSangreTipo($gruSan);

            $idiMat = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findOneByid($request->get("idiomaMaterno"));
            $persona->setIdiomaMaternoId($idiMat);

            $em->persist($persona);
            //$em->flush();
            //print_r($persona);die;

            $em2 = $this->getDoctrine()->getManager();
            $apode = $em2->getRepository('SieAppWebBundle:Apoderado')->findOneByid($request->get('idApoderado'));

            $tipoApoderado = $em2->getRepository('SieAppWebBundle:ApoderadoTipo')->findOneByid($request->get('apoderadoTipo'));

            $apode->setApoderadoTipo($tipoApoderado);
            //$apode->setPersonaApoderado($apoderado);
            //$apode->setPersonaEstudiante($estudiante);
            $apode->setEmpleo($request->get("empleo"));
            //$apode->setGestionId(date("Y"));
            $apode->setIdiomaId($request->get("idiomaMaterno"));
            //$apode->setInstruccionId(1);
            $apode->setTelefono($request->get("telefono"));
            $em2->persist($apode);
            $em2->flush();

            return $this->redirect($this->generateUrl('padres_buscar'));
        } else {

        }
    }

    /**
     * get infor sobre el apoderado
     * @param type $idap
     * @return array datos apoderado
     */
    public function usuarioFAction($idap) {

        $sesion = $this->getRequest()->getSession();
        $rude = $sesion->get('perEstRude');
        //print($rude);

        $em = $this->getDoctrine()->getManager();

        $datosApo = $em->getRepository('SieAppWebBundle:Persona')->findOneByid($idap);
        if ($datosApo) {
            //print_r($datosApo);
        }
        //print_r($datosApo);
        //die();
        return $this->render('SieAppWebBundle:Padres:usform.html.twig', array('datosApoderado' => $datosApo));
    }

    public function usuarioAgregarAction(Request $request) {
        /* if ($request->getMethod() == "POST") {
          $usu = $request->get("usuario");
          $pass1 = $request->get("password");
          $pass2 = $request->get("password2");
          $persona = $request->get("idapoderado");

          $em = $this->getDoctrine()->getManager();
          $datosApo = $em->getRepository('SieAppWebBundle:Persona')->findOneByid($persona);

          if ($pass1 != $pass2) {
          $this->get('session')->getFlashBag()->add('mensaje2', 'Los Passwords no coinciden intente de nuevo!!!');
          return $this->forward('SieAppWebBundle:Padres:usuarioF', array('idap' => $datosApo));
          } else {
          if (strlen($pass1) > 0 and strlen($usu) > 0) {
          // Agregar usuario
          $us = new Usuario();
          $us->setUsername($request->get("usuario"));
          $us->setPassword(md5($request->get("password")));
          $us->setFechaRegistro(new \DateTime('now'));
          $us->setPersona($datosApo);

          $em = $this->getDoctrine()->getManager();
          $em->persist($us);
          $em->flush();
          //Agregar usuario Rol
          $usuarioRol = new UsuarioRol();
          $rol = $em->getRepository('SieAppWebBundle:Rol')->findOneByid(1);
          $usuarioRol->setRolTipo($rol);
          $usuarioRol->setEsactivo("t");
          $usuario1 = $em->getRepository('SieAppWebBundle:Usuario')->findOneByid($us->getId());
          $usuarioRol->setUsuario($usuario1);
          $em1 = $this->getDoctrine()->getManager();
          $em1->persist($usuarioRol);
          $em1->flush();

          return $this->redirect($this->generateUrl('apoderado_show'));
          } else {
          $this->get('session')->getFlashBag()->add('mensaje2', 'El usuario y password no puede ser un valor nulo');
          return $this->forward('SieAppWebBundle:Padres:usuarioF', array('idap' => $datosApo));
          }
          }
          } */
        return $this->redirect($this->generateUrl('apoderado_show'));
    }

    public function departamentosAction($pais) {
        $em = $this->getDoctrine()->getManager();
        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipo' => $pais));
        $departamento = array();
        foreach ($dep as $d) {
            $departamento[$d->getid()] = $d->getlugar();
        }
        $dto = $departamento;
        $response = new JsonResponse();
        return $response->setData(array('departamento' => $dto));
    }

    public function provinciasAction($departamento) {
        $em = $this->getDoctrine()->getManager();
        $prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $departamento));
        $provincia = array();
        foreach ($prov as $p) {
            $provincia[$p->getid()] = $p->getlugar();
        }
        //print_r($provincia);die();
        $response = new JsonResponse();
        return $response->setData(array('provincia' => $provincia));
    }

    public function localidadesAction($provincia) {
        $em = $this->getDoctrine()->getManager();
        $loc = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 3, 'lugarTipo' => $provincia));
        $localidad = array();
        foreach ($loc as $l) {
            $localidad[$l->getid()] = $l->getlugar();
        }
        //print_r($provincia);die();
        $response = new JsonResponse();
        return $response->setData(array('localidad' => $localidad));
    }

}
