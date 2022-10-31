<?php

namespace Sie\AppWebBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use \Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Sie\AppWebBundle\Entity\Usuario;
use Sie\AppWebBundle\Entity\UsuarioRol;
use Sie\AppWebBundle\Entity\Persona;
use Sie\AppWebBundle\Entity\Apoderado;
use Sie\AppWebBundle\Entity\ApoderadoInscripcion;
use Sie\AppWebBundle\Entity\ApoderadoInscripcionDatos;
use Sie\AppWebBundle\Entity\Estudiante;

class ApoderadoController extends Controller {

    public $session;

    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request, $op) {

        $this->session = new Session();
        $tipoSistema = $request->getSession()->get('sysname');
        switch ($tipoSistema) {
            case 'REGULAR': $this->session->set('tituloTipo', 'Apoderados');
                $this->session->set('institucion', 'Unidad Educativa');
                $this->session->set('estudiante', 'Estudiante');
                $this->session->set('layout', 'layoutRegular.html.twig');
                break;

            case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Personal Docente');
                $this->session->set('estudiante', 'Estudiante');
                $this->session->set('layout', 'alternativaRegular.html.twig');
                break;

            case 'PERMANENTE':
                $this->session->set('tituloTipo', 'Facilitador');
                $this->session->set('institucion', 'Centro');
                $this->session->set('estudiante', 'Participante');
                $this->session->set('layout', 'layoutPermanente.html.twig');
                break;
            default: $this->session->set('tituloTipo', 'Maestro(s)');
                break;
        }
        ////////////////////////////////////////////////////
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') {
            $form = $request->get('form');
            // Verificamos si existe el estudiante
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $form['codigoRude']));
            if (!$estudiante) {
                $this->get('session')->getFlashBag()->add('noSearch', 'La/El estudiante no fue encontrada/o');
                return $this->render('SieAppWebBundle:Apoderado:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            $this->session->set('apEstudiante', $estudiante->getId());
            $this->session->set('apGestion', $form['gestion']);
            $idEstudiante = $estudiante->getId();
            $gestion = $form['gestion'];
        } else {
            if ($op == 'search') {
                return $this->render('SieAppWebBundle:Apoderado:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            $idEstudiante = $this->session->get('apEstudiante');
            if (!$idEstudiante) {
                return $this->render('SieAppWebBundle:Apoderado:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
            }
            $gestion = $this->session->get('apGestion');
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($idEstudiante);
        }

        $query = $em->createQuery(
                        'SELECT ei FROM SieAppWebBundle:EstudianteInscripcion ei
                            INNER JOIN ei.institucioneducativaCurso iec
                            WHERE ei.estudiante = :estudiante 
                            AND iec.gestionTipo = :gestion
                            AND ei.estadomatriculaTipo not in (:estado)')
                ->setParameter('estudiante', $idEstudiante)
                ->setParameter('gestion', $gestion)
                ->setParameter('estado', array(6));
        $inscripcion = $query->getResult();

        if (!$inscripcion) {
            $this->get('session')->getFlashBag()->add('noSearch', 'La/El estudiante no cuenta con inscripción en la gestión ' . $gestion);
            return $this->render('SieAppWebBundle:Apoderado:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }

        $repository = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion');

        $query = $repository->createQueryBuilder('ai')
                ->select('p.id perId, ai.id aiId, aid.id aidId, p.carnet, p.paterno, p.materno, p.nombre, at.apoderado apoTipo, aid.empleo')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'ai.persona = p.id')
                ->leftJoin('SieAppWebBundle:ApoderadoInscripcionDatos', 'aid', 'WITH', 'aid.apoderadoInscripcion = ai.id')
                ->innerJoin('SieAppWebBundle:ApoderadoTipo', 'at', 'WITH', 'ai.apoderadoTipo = at.id')
                ->where('ai.estudianteInscripcion = :inscripcion')
                ->setParameter('inscripcion', $inscripcion[0])
                //->distinct()
                ->getQuery();

        $apoderados = $query->getResult();
        //$apoderados = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->findBy(array('estudianteInscripcion' => $inscripcion));
        //dump($apoderados);die;
        return $this->render('SieAppWebBundle:Apoderado:index.html.twig', array(
                    'estudiante' => $estudiante,
                    'apoderados' => $apoderados,
                    'inscripcion' => $inscripcion
        ));
    }

    /*
     * formularios de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('apoderado'))
                ->add('codigoRude', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 20)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    public function findAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');

        return $this->render('SieAppWebBundle:Apoderado:search_persona.html.twig', array(
                    'form' => $this->searchForm($form['insId'])->createView(),
        ));
    }

    public function resultAction(Request $request) {

        // Verificamos si no ha caducado la session
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $form = $request->get('form');

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('SieAppWebBundle:Persona');

        $query = $repository->createQueryBuilder('per')
                ->select('per')
                //->innerJoin('SieAppWebBundle:ApoderadoInscripcion', 'apoi', 'WITH', 'apoi.persona = per.id')
                ->where('per.carnet = :carnet')
                ->andWhere('per.esvigente = :vigente OR per.esvigenteApoderado > :apoderado')
                ->setParameter('carnet', $form['carnetIdentidad'])
                ->setParameter('vigente', 't')
                ->setParameter('apoderado', 0)
                ->getQuery();

        $personas = $query->getResult();

        return $this->render('SieAppWebBundle:Apoderado:result.html.twig', array(
                    'personas' => $personas,
                    'insId' => $form['insId']
        ));
    }

    /**
     * Crea un formulario para buscar una persona por C.I.
     *
     */
    private function searchForm($insId) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('apoderado_result'))
                ->add('insId', 'hidden', array('data' => $insId))
                ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('autocomplete' => 'off', 'class' => 'form-control jnumbers', 'pattern' => '[0-9]{5,10}', 'maxlength' => '11')))
                ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jonlynumbersletters jupper', 'maxlength' => '2', 'autocomplete' => 'off')))
                ->add('buscar', 'submit', array('label' => 'Buscar coincidencias por C.I.', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        return $form;
    }

    /*
     * formularios de busqueda de institucion educativa
     */

//    private function formSearch($gestionactual) {
//        $gestiones = array($gestionactual => $gestionactual);
//        $form = $this->createFormBuilder()
//                ->setAction($this->generateUrl('apoderado_list'))
//                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
//                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
//                ->add('buscar', 'submit', array('label' => 'Buscar'))
//                ->getForm();
//        return $form;
//    }


    /* formulario nuevo apoderado */

    public function newAction(Request $request) {
        // Creamos el formulario de nuevo apoderado

        $form_aux = $request->get('form');

        $em = $this->getDoctrine()->getManager();
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('apoderado_create'))
                ->add('insId', 'hidden', array('data' => $form_aux['insId']))
                ->add('perId', 'hidden', array('data' => $form_aux['perId']))
                ->add('empleo', 'text', array('label' => 'Empleo', 'required' => true, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'maxlength' => '100')))
                ->add('apoderadoTipo', 'entity', array('class' => 'SieAppWebBundle:ApoderadoTipo', 'empty_value' => 'Seleccionar...', 'label' => 'Parentesco con el estudiante', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('instruccionTipo', 'entity', array('class' => 'SieAppWebBundle:InstruccionTipo', 'empty_value' => 'Seleccionar...', 'label' => 'Nivel de instrucción alcanzado', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        // Obtenemos los datos del estudiante
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($this->session->get('apEstudiante'));
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form_aux['insId']);
        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($form_aux['perId']);

        return $this->render('SieAppWebBundle:Apoderado:new.html.twig', array(
                    'form' => $form->createView(),
                    'estudiante' => $estudiante,
                    'inscripcion' => $inscripcion,
                    'persona' => $persona,
        ));
    }

    public function createAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $nuevoApoderado = new ApoderadoInscripcion();
        $nuevoApoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
        $nuevoApoderado->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($form['perId']));
        $nuevoApoderado->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['insId']));
        $em->persist($nuevoApoderado);
        $em->flush();

        $nuevoApoderadoDatos = new ApoderadoInscripcionDatos();
        $nuevoApoderadoDatos->setEmpleo(mb_strtoupper($form['empleo'], 'utf-8'));
        $nuevoApoderadoDatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
        $nuevoApoderadoDatos->setApoderadoInscripcion($nuevoApoderado);
        $em->persist($nuevoApoderadoDatos);
        $em->flush();

        $this->get('session')->getFlashBag()->add('newOk', 'El apoderado fue registrado correctamente');
        return $this->redirect($this->generateUrl('apoderado', array('op' => 'result')));
    }

    /* formulario para modificar datos apoderado */

    public function editAction(Request $request) {
        // Creamos el formulario de modificacion apoderado
        $em = $this->getDoctrine()->getManager();
        $form_aux = $request->get('form');

        $apoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($form_aux['aiId']);
        $apoderadodatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array('apoderadoInscripcion' => $apoderado));

        $persona = $em->getRepository('SieAppWebBundle:Persona')->find($apoderado->getPersona());

        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('apoderado_update'))
                ->add('aiId', 'hidden', array('data' => $form_aux['aiId']))
                ->add('empleo', 'text', array('label' => 'Empleo', 'data' => $apoderadodatos ? $apoderadodatos->getEmpleo() : '', 'required' => true, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'maxlength' => '100')))
                ->add('apoderadoTipo', 'entity', array('class' => 'SieAppWebBundle:ApoderadoTipo', 'data' => $em->getReference('SieAppWebBundle:ApoderadoTipo', $apoderado->getApoderadoTipo()->getId()), 'empty_value' => 'Seleccionar...', 'label' => 'Parentesco con el estudiante', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('instruccionTipo', 'entity', array('class' => 'SieAppWebBundle:InstruccionTipo', 'data' => $em->getReference('SieAppWebBundle:InstruccionTipo', $apoderadodatos ? $apoderadodatos->getInstruccionTipo()->getId() : 0), 'empty_value' => 'Seleccionar...', 'label' => 'Nivel de instruccion alcanzado', 'required' => true, 'attr' => array('class' => 'form-control')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();

        // Obtenemos los datos del estudiante
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->find($this->session->get('apEstudiante'));
        $query = $em->createQuery(
                        'SELECT ei FROM SieAppWebBundle:EstudianteInscripcion ei
                            JOIN ei.institucioneducativaCurso iec
                            WHERE ei.estudiante = :estudiante 
                            AND iec.gestionTipo = :gestion')
                ->setParameter('estudiante', $this->session->get('apEstudiante'))
                ->setParameter('gestion', $this->session->get('apGestion'));
        $inscripcion = $query->getResult();

        return $this->render('SieAppWebBundle:Apoderado:edit.html.twig', array(
                    'form' => $form->createView(),
                    'estudiante' => $estudiante,
                    'inscripcion' => $inscripcion,
                    'persona' => $persona
        ));
    }

    /*
     * Modificamoos los datos del apoderado 
     */

    public function updateAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $apoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($form['aiId']);
        $apoderadodatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array('apoderadoInscripcion' => $apoderado));

        /* /Actualizamos los datos del apoderado */
        $apoderado->setApoderadoTipo($em->getRepository('SieAppWebBundle:ApoderadoTipo')->find($form['apoderadoTipo']));
        $em->persist($apoderado);
        $em->flush();

        if ($apoderadodatos) {
            $apoderadodatos->setEmpleo(mb_strtoupper($form['empleo'], 'utf-8'));
            $apoderadodatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
        } else {
            $apoderadodatos = new ApoderadoInscripcionDatos();
            $apoderadodatos->setEmpleo(mb_strtoupper($form['empleo'], 'utf-8'));
            $apoderadodatos->setInstruccionTipo($em->getRepository('SieAppWebBundle:InstruccionTipo')->find($form['instruccionTipo']));
            $apoderadodatos->setApoderadoInscripcion($apoderado);
        }
        $em->persist($apoderadodatos);
        $em->flush();

        $this->get('session')->getFlashBag()->add('updateOk', 'Los datos fueron modificados correctamente');
        return $this->redirect($this->generateUrl('apoderado', array('op' => 'result')));

        $this->get('session')->getFlashBag()->add('updateError', 'Error al realizar la modificacion, intentelo nuevamente');
        return $this->redirect($this->generateUrl('apoderado', array('op' => 'result')));
    }

    /*
     * Eliminar el apoderado 
     */

    public function deleteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        $apoderado = $em->getRepository('SieAppWebBundle:ApoderadoInscripcion')->find($form['aiId']);
        if (!$apoderado) {
            $this->get('session')->getFlashBag()->add('deleteError', 'No se pudo eliminar el registro del apoderado');
            return $this->redirect($this->generateUrl('apoderado', array('op' => 'result')));
        }
        $apoderadodatos = $em->getRepository('SieAppWebBundle:ApoderadoInscripcionDatos')->findOneBy(array('apoderadoInscripcion' => $apoderado));

        $em->remove($apoderadodatos);
        $em->remove($apoderado);
        $em->flush();
        $this->get('session')->getFlashBag()->add('deleteOk', 'Se elimino el registro correctamente');
        return $this->redirect($this->generateUrl('apoderado', array('op' => 'result')));
    }

    /**
     * Obtenemos departamento apoderado
     *
     * @param type $pais
     *
     * @return array departamento
     */
    public function departamentosAction($pais) {
        $em = $this->getDoctrine()->getManager();
        $dep = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 1, 'paisTipoId' => $pais));
        $departamento = array();
        foreach ($dep as $d) {
            $departamento[$d->getId()] = $d->getLugar();
        }

        $dto = $departamento;
        $response = new JsonResponse();
        return $response->setData(array('departamento' => $dto));
    }

    /**
     * Obtenemos provincias apoderado
     *
     * @param type $departamento
     *
     * @return array provincias
     */
    public function provinciasAction($departamento) {
        $em = $this->getDoctrine()->getManager();
        $prov = $em->getRepository('SieAppWebBundle:LugarTipo')->findBy(array('lugarNivel' => 2, 'lugarTipo' => $departamento));
        $provincia = array();
        foreach ($prov as $p) {
            $provincia[$p->getid()] = $p->getlugar();
        }
        $response = new JsonResponse();
        return $response->setData(array('provincia' => $provincia));
    }

    /**
     * obtenemos localidades per apoderado
     *
     * @param type $provincia
     *
     * @return array localidades
     */
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

    /*
     *  funcion para verificar si la persona ya existe con el carnet, metodo ajax 
     */

    public function verificar_existe_persona_carnet_complementoAction($carnet, $complemento) {
        if ($complemento == 'ninguno') {
            $complemento = null;
        }
        $em = $this->getDoctrine()->getManager();
        $personas = $em->getRepository('SieAppWebBundle:Persona')->findBy(array('carnet' => $carnet));

        if ($personas) {

            /* Creamos el array para los generos */
            $generos = $em->getRepository('SieAppWebBundle:GeneroTipo')->findAll();
            $generosArray = array();
            foreach ($generos as $g) {
                $generosArray[$g->getId()] = $g->getGenero();
            }
            /* Creamos el array para los idiomas maternos */
            $idiomas = $em->getRepository('SieAppWebBundle:IdiomaMaterno')->findAll();
            $idiomasArray = array();
            foreach ($idiomas as $i) {
                $idiomasArray[$i->getId()] = $i->getIdiomaMaterno();
            }
            /* Creamos el array para estado civil */
            $estadoCiviles = $em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findAll();
            $estadoCivilArray = array();
            foreach ($estadoCiviles as $ec) {
                $estadoCivilArray[$ec->getId()] = $ec->getEstadoCivil();
            }
            /* Creamos el array para tipo de sangre */
            $tipoSangres = $em->getRepository('SieAppWebBundle:SangreTipo')->findAll();
            $tipoSangreArray = array();
            foreach ($tipoSangres as $ts) {
                $tipoSangreArray[$ts->getId()] = $ts->getGrupoSanguineo();
            }

//            $tablaP = '<table class="table table-bordered table-striped cf dataTable" id="tableApo"><thead class="cf"><tr><th>Persona(s)</th></tr></thead><tbody>';
//
//            foreach ($personas as $p) {
//                $tablaP .= '<tr><td>' . $p->getPaterno() . ' ' . $p->getMaterno() . ' ' . $p->getNombre() . '</tr></td>';
//            }
//            $tablaP .= '</tbody></table>';
            $personasArray = array();

            foreach ($personas as $p) {
                $personasArray[$p->getId()] = $p->getPaterno() . ' ' . $p->getMaterno() . ' ' . $p->getNombre();
            }

            /* Se envia los parametros en formato json */
            $response = new JsonResponse();
            return $response->setData(array('encontrado' => 'si',
                        'personasArray' => $personasArray,
                        //'tablaP' => $tablaP,
                        'generosArray' => $generosArray,
                        'idiomasArray' => $idiomasArray,
                        'estadoCivilArray' => $estadoCivilArray,
                        'tipoSangreArray' => $tipoSangreArray
            ));
        } else {
            $response = new JsonResponse();
            return $response->setData(array('encontrado' => 'no'));
        }
    }

    /*
     *  funcion para obtener la persona con el carnet, metodo ajax
     */

    public function obtener_persona_idAction($persona_id) {

        $em = $this->getDoctrine()->getManager();
        $persona = $em->getRepository('SieAppWebBundle:Persona')->findBy(array('id' => $persona_id));

        $paterno = $persona->getPaterno();
        $materno = $persona->getMaterno();
        $nombre = $persona->getNombre();
        $fechaNacimiento = $persona->getFechaNacimiento()->format('d-m-Y');
        $genero = $persona->getGeneroTipo()->getId();
        $direccion = $persona->getDireccion();
        $celular = $persona->getCelular();
        $correo = $persona->getCorreo();
        $idiomaMaterno = $persona->getIdiomaMaterno()->getId();
        $estadoCivil = $persona->getEstadocivilTipo()->getId();
        $tipoSangre = $persona->getSangreTipo()->getId();

        /* Se envia los parametros en formato json */
        $response = new JsonResponse();
        return $response->setData(array('encontrado' => 'si',
                    'paterno' => $paterno,
                    'materno' => $materno,
                    'nombre' => $nombre,
                    'fechaNacimiento' => $fechaNacimiento,
                    'genero' => $genero,
                    'direccion' => $direccion,
                    'celular' => $celular,
                    'correo' => $correo,
                    'idiomaMaterno' => $idiomaMaterno,
                    'estadoCivil' => $estadoCivil,
                    'tipoSangre' => $tipoSangre,
        ));
    }

}
