<?php

namespace Sie\PermanenteBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sie\AppWebBundle\Entity\EstudianteInscripcion;
use Sie\AppWebBundle\Form\EstudianteInscripcionType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\Estudiante;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
/**
 * EstudianteInscripcion controller.
 *
 */
class EstudianteInscripcionTecnicaController extends Controller {

    public $session;
    public $idInstitucion;

    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request, $op) {
        try {
            // generar los titulos para los diferentes sistemas
            $this->session = new Session();
            ////////////////////////////////////////////////////
            $em = $this->getDoctrine()->getManager();
            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                /*
                 * verificamos si existe la unidad educativa
                 */
                $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
                if (!$institucioneducativa) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                    return $this->render('SiePermanenteBundle:EstudianteInscripcionTecnica:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                
                /*
                * verificamos si tiene tuicion
                */
//                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
//                $query->bindValue(':user_id', $this->session->get('userId'));
//                $query->bindValue(':sie', $form['institucioneducativa']);
//                $query->bindValue(':rolId', $this->session->get('roluser'));
//                $query->execute();
//                $aTuicion = $query->fetchAll();
//
//                if ($aTuicion[0]['get_ue_tuicion']){
                    $institucion = $form['institucioneducativa'];
                    $gestion = $form['gestion'];
//                }else{
//                    $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
//                    return $this->render('SiePermanenteBundle:EstudianteInscripcion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
//                }
                
            } else {
                $nivelUsuario = $request->getSession()->get('roluser');
                if ($nivelUsuario != 9 && $nivelUsuario != 5) { // si no es institucion educativa 5 o administrativo 9
                    // formulario de busqueda de institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                        if ($op == 'search') {
                            return $this->render('SiePermanenteBundle:EstudianteInscripcionTecnica:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                        }
                    } else {

                        return $this->render('SiePermanenteBundle:EstudianteInscripcionTecnica:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                } else { // si es institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                    } else {
                        $funcion = new FuncionesController();
                        $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'), $request->getSession()->get('currentyear')); //5484231);
                        $gestion = $request->getSession()->get('currentyear');
                    }
                }
            }

            //$institucion = '40730421';
            //$gestion = '2014';

            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);

            // Vista lista personal administrativo
            $query = $em->createQuery(
                        'SELECT iec, cet FROM SieAppWebBundle:InstitucioneducativaCurso iec
                        JOIN iec.carreraespecialidadTipo cet
                        WHERE iec.institucioneducativa = :idInstitucion
                        AND iec.gestionTipo = :gestion')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $gestion);
            $carreras = $query->getResult();

            /*
             * obtenemos datos de la unidad educativa
             */
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

            return $this->render('SiePermanenteBundle:EstudianteInscripcionTecnica:index.html.twig', array(
                        'carreras' => $carreras, 'institucion' => $institucion, 'gestion' => $gestion
            ));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SiePermanenteBundle:EstudianteInscripcionTecnica:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }

    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('estudianteinscripcionTecnica'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' =>'8','class'=>'form-control jnumbers')))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }
    
    public function showAction(Request $request){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                    'SELECT ei FROM SieAppWebBundle:EstudianteInscripcion ei
                    INNER JOIN ei.estudiante e
                    WHERE ei.institucioneducativaCurso = :idCurso
                    ORDER BY e.paterno, e.materno')
                    ->setParameter('idCurso', $request->get('idCurso'));
        
        $inscritos = $query->getResult();   
        //$inscritos =array();                 
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($this->session->get('idInstitucion'));
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idCurso'));
        $this->session->set('idCurso', $request->get('idCurso'));
        
        return $this->render('SiePermanenteBundle:EstudianteInscripcionTecnica:show.html.twig', array(
                        'inscritos' => $inscritos,'ca'=>$curso, 'institucion' => $institucion, 'gestion' => $this->session->get('idGestion')
            ));
    }
    
    public function show_sessionAction(Request $request){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                    'SELECT ei,e FROM SieAppWebBundle:EstudianteInscripcion ei
                    JOIN ei.estudiante e
                    WHERE ei.institucioneducativaCurso = :idCurso
                    ORDER BY e.paterno, e.materno')
                    ->setParameter('idCurso', $this->session->get('idCurso'));
        
        $inscritos = $query->getResult();                    
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($this->session->get('idInstitucion'));
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($this->session->get('idCurso'));
        
        return $this->render('SiePermanenteBundle:EstudianteInscripcionTecnica:show.html.twig', array(
                        'inscritos' => $inscritos,'ca'=>$curso ,'institucion' => $institucion, 'gestion' => $this->session->get('idGestion')
            ));
    }
    
    /**
     * Displays a form to create a new EstudianteInscripcion entity.
     *
     */
    public function newAction(Request $request) {        
        $em = $this->getDoctrine()->getManager();
        // Datos de la institucion educativa
        $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($request->getSession()->get('idInstitucion'));
        //Sucurales
        $idIns = $institucion->getId();
        $idGestion = $request->getSession()->get('idGestion');
        $query = $em->createQuery(
                        'SELECT suc, st FROM SieAppWebBundle:InstitucioneducativaSucursal suc
                    JOIN suc.sucursalTipo st
                    WHERE suc.institucioneducativa = :idInstitucion
                    AND suc.gestionTipo = :gestion')
                ->setParameter('idInstitucion', $idIns)
                ->setParameter('gestion', $idGestion);

        $sucursales = $query->getResult();
        $sucursal = array();
        foreach ($sucursales as $s) {
            $sucursal[$s->getSucursalTipo()->getId()] = $s->getSucursalTipo()->getId();
        }
        // Sie el array esta vacio o no tiene sucursales llenamos uno por defecto
        if (empty($sucursal)) {
            $sucursal[0] = 0;
        }
        // Creacion de formularios para la inscripcion con rude y sin rude        
        $formConRude = $this->newFormConRude($institucion, $sucursal, $idGestion);
        $formSinRude = $this->newFormSinRude($institucion, $sucursal, $idGestion);
        $formSinRudeCursoCorto = $this->newFormSinRudeCursoCorto($institucion, $sucursal, $idGestion);

        return $this->render('SiePermanenteBundle:EstudianteInscripcionTecnica:new.html.twig', array('formConRude' => $formConRude->createView(), 'formSinRude' => $formSinRude->createView(),'formSinRudeCursoCorto' =>$formSinRudeCursoCorto->createView()));
    }

    private function newFormConRude($institucion, $sucursal, $gestion) {
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteinscripcionTecnica_create'))
                        ->add('tipo_inscripcion', 'hidden', array('data' => 'conRude'))
                        ->add('c_idInstitucion', 'hidden', array('data' => $institucion->getId()))
                        ->add('c_gestion', 'hidden', array('data' => $gestion))
                        ->add('c_idCurso','hidden',array('data'=>$this->session->get('idCurso')))
                        ->add('c_codigoIns', 'text', array('label' => 'Código', 'disabled' => true, 'data' => $institucion->getId(), 'attr' => array('class' => 'form-control')))
                        ->add('c_nombreIns', 'text', array('label' => 'Nombre', 'disabled' => true, 'data' => $institucion->getInstitucioneducativa(), 'attr' => array('class' => 'form-control')))
                        ->add('c_sucursalIns', 'choice', array('label' => 'Sucursal', 'choices' => $sucursal, 'attr' => array('class' => 'form-control')))
                        ->add('c_rude', 'text', array('label' => 'Código RUDE', 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off','pattern'=>'[0-9A-Z]{13,50}')))
                        ->add('c_carnet', 'text', array('label' => 'Carnet de Identidad', 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off','pattern'=>'[0-9A-Z]{3,15}')))
                        ->add('c_paterno', 'text', array('label' => 'Paterno', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_materno', 'text', array('label' => 'Materno', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_nombre', 'text', array('label' => 'Nombre(s)', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_fechaInscripcion', 'text', array('label' => 'Fecha de Inscripción', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_guardar', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-primary')))
                        ->getForm();
    }

    private function newFormSinRude($institucion, $sucursal, $gestion) {
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteinscripcionTecnica_create'))
                        ->add('tipo_inscripcion', 'hidden', array('data' => 'sinRude'))
                        ->add('idInstitucion', 'hidden', array('data' => $institucion->getId()))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('idCurso','hidden',array('data'=>$this->session->get('idCurso')))
                        ->add('codigoIns', 'text', array('label' => 'Código', 'disabled' => true, 'data' => $institucion->getId(), 'attr' => array('class' => 'form-control')))
                        ->add('nombreIns', 'text', array('label' => 'Nombre', 'disabled' => true, 'data' => $institucion->getInstitucioneducativa(), 'attr' => array('class' => 'form-control')))
                        ->add('sucursalIns', 'choice', array('label' => 'Sucursal', 'choices' => $sucursal, 'attr' => array('class' => 'form-control')))
                        ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{4,10}', 'autocomplete' => 'off')))
                        ->add('paterno', 'text', array('label' => 'Paterno', 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zA-Z\sÑñáéíóúÁÉÍÚÓ]{2,30}', 'autocomplete' => 'off')))
                        ->add('materno', 'text', array('label' => 'Materno', 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zA-Z\sÑñáéíóúÁÉÍÚÓ]{2,30}', 'autocomplete' => 'off')))
                        ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zA-Z\sÑñáéíóúÁÉÍÚÓ]{2,30}', 'autocomplete' => 'off')))
                        ->add('oficialia', 'text', array('label' => 'Oficialia', 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('libro', 'text', array('label' => 'Libro', 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('partida', 'text', array('label' => 'Partida', 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('folio', 'text', array('label' => 'Folio', 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('sangreTipoId', 'entity', array('label' => 'Tipo de Sangre', 'class' => 'SieAppWebBundle:SangreTipo', 'property' => 'grupoSanguineo', 'attr' => array('class' => 'form-control')))
                        ->add('idiomaMaternoId', 'entity', array('label' => 'Idioma Materno', 'class' => 'SieAppWebBundle:IdiomaMaterno', 'property' => 'idiomaMaterno', 'attr' => array('class' => 'form-control')))
                        ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off','maxlength' => 2,'pattern' => '[0-9]{1}[A-Z]{1}')))
                        ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('placeholder' => 'dd-mm-YYYY', 'class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('correo', 'text', array('label' => 'Correo', 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off','pattern' =>'^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*[.]([a-z]{2,3})$')))
                        ->add('pais', 'entity', array('label' => 'Pais', 'required' => true, 'class' => 'SieAppWebBundle:PaisTipo', 'property' => 'pais', 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control')))
                        ->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control')))
                        ->add('provincia', 'choice', array('label' => 'Provincia', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control')))
                        ->add('localidad', 'text', array('label' => 'Localidad', 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('celular', 'text', array('label' => 'Celular', 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{8}', 'autocomplete' => 'off')))
                        ->add('carnetCodepedis', 'text', array('label' => 'Carnet Codepedis', 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('carnetIbc', 'text', array('label' => 'Carnet I.B.C.', 'required' => false, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('generoTipo', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'property' => 'genero', 'attr' => array('class' => 'form-control')))
                        ->add('estadoCivil', 'entity', array('label' => 'Estado Civil', 'class' => 'SieAppWebBundle:EstadoCivilTipo', 'property' => 'estadoCivil', 'attr' => array('class' => 'form-control')))
                        ->add('fechaInscripcion', 'text', array('label' => 'Fecha de Inscripción', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('guardar', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-primary')))
                        ->getForm();
    }

    private function newFormSinRudeCursoCorto($institucion, $sucursal, $gestion) {
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteinscripcionTecnica_create'))
                        ->add('tipo_inscripcion', 'hidden', array('data' => 'cursoCorto'))
                        ->add('idInstitucion', 'hidden', array('data' => $institucion->getId()))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('idCurso','hidden',array('data'=>$this->session->get('idCurso')))
                        ->add('codigoIns', 'text', array('label' => 'Código', 'disabled' => true, 'data' => $institucion->getId(), 'attr' => array('class' => 'form-control')))
                        ->add('nombreIns', 'text', array('label' => 'Nombre', 'disabled' => true, 'data' => $institucion->getInstitucioneducativa(), 'attr' => array('class' => 'form-control')))
                        ->add('sucursalIns', 'choice', array('label' => 'Sucursal', 'choices' => $sucursal, 'attr' => array('class' => 'form-control')))
                        ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'required' => false, 'attr' => array('class' => 'form-control', 'pattern' => '[0-9]{4,10}', 'autocomplete' => 'off')))
                        ->add('paterno', 'text', array('label' => 'Paterno', 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zA-Z\sÑñáéíóúÁÉÍÚÓ]{2,30}', 'autocomplete' => 'off')))
                        ->add('materno', 'text', array('label' => 'Materno', 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zA-Z\sÑñáéíóúÁÉÍÚÓ]{2,30}', 'autocomplete' => 'off')))
                        ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('class' => 'form-control', 'pattern' => '[a-zA-Z\sÑñáéíóúÁÉÍÚÓ]{2,30}', 'autocomplete' => 'off')))
                        ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('placeholder' => 'dd-mm-YYYY', 'class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('generoTipo', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'property' => 'genero', 'attr' => array('class' => 'form-control')))
                        ->add('fechaInscripcion', 'text', array('label' => 'Fecha de Inscripción', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('guardar', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-primary')))
                        ->getForm();
    }
    /**
     * Creates a new EstudianteInscripcion entity.
     *
     */
    public function createAction(Request $request) {
        try {
            $form = $request->get('form');
            $em = $this->getDoctrine()->getManager();
            if ($form['tipo_inscripcion'] == 'conRude') {
                // Inscripcion de estudiante con rude
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneByCodigoRude($form['c_rude']);
                if ($estudiante) {
                    $inscripcion = new EstudianteInscripcion();
                    $inscripcion->setNumMatricula(0);
                    $inscripcion->setObservacionId(0);
                    $inscripcion->setObservacion(0);
                    $inscripcion->setFechaInscripcion(new \DateTime($form['c_fechaInscripcion']));
                    $inscripcion->setApreciacionFinal('');
                    $inscripcion->setOperativoId(1);
                    $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById(4));
                    //$inscripcion->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->findOneById(0));
                    //$inscripcion->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->findOneById(1));
                    $inscripcion->setEstudiante($estudiante);
                    //$inscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['c_gestion']));
                    //$inscripcion->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->findOneById(10));
                    //$inscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['c_idInstitucion']));
                    //$inscripcion->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById(18));
                    //$inscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(1));
                    //$inscripcion->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->findOneById($form['c_sucursalIns']));
                    //$inscripcion->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById(0));
                    $inscripcion->setFechaRegistro(new \DateTime('now'));
                    $inscripcion->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['c_idCurso']));

                    $em->persist($inscripcion);
                    $em->flush();
                    
                    $this->get('session')->getFlashBag()->add('newOk', 'El Estudiante fue inscrito correctamente');
                    return $this->redirect($this->generateUrl('estudianteinscripcionTecnica_show_session'));
                } else {
                    $this->get('session')->getFlashBag()->add('newError', 'El codigo RUDE ingresado no es válido, intentelo de nuevo.');
                    //$this->get('session')->addFlash('errorEstudiante', 'El codigo RUDE ingresado no es válido, intentelo de nuevo.');
                    return $this->redirect($this->generateUrl('estudianteinscripcionTecnica_show_session'));
                }
            } else {
                if($form['tipo_inscripcion'] == 'conRude'){
                    // inscripcion de estudiante sin Rude
                    // 
                    // Verificamos si los datos del nuevo estudiante coinciden con alguno de la base
                    $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad' => $form['carnetIdentidad'], 'fechaNacimiento' => new \DateTime($form['fechaNacimiento'])));
                    if (!$estudiante) {

                        // actualizando el rude del estudiante

                        $query = $em->getConnection()->prepare('SELECT fuc_codigoestudiante(:sie::VARCHAR,:gestion::VARCHAR)');
                        $query->bindValue(':sie', $form['idInstitucion']);
                        $query->bindValue(':gestion', $form['gestion']);
                        $query->execute();
                        $codigorude = $query->fetchAll();

                        $codigo = $codigorude[0];
                        $codigorude = $codigo['fuc_codigoestudiante'];

                        // Sie el estudiante no esta registrado
                        $newEstudiante = new Estudiante();
                        $newEstudiante->setCarnetCodepedis($form['carnetCodepedis']);
                        $newEstudiante->setCarnetIbc($form['carnetIbc']);
                        $newEstudiante->setCarnetIdentidad($form['carnetIdentidad']);
                        $newEstudiante->setCelular($form['celular']);
                        $newEstudiante->setCodigoRude($codigorude); /////
                        $newEstudiante->setComplemento($form['complemento']);
                        $newEstudiante->setCorreo($form['correo']);
                        $newEstudiante->setEstadoCivil($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById($form['estadoCivil']));
                        $newEstudiante->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                        $newEstudiante->setFolio($form['folio']);
                        $newEstudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
                        $newEstudiante->setIdiomaMaternoId($form['idiomaMaternoId'] );
                        $newEstudiante->setLibro($form['libro']);
                        $newEstudiante->setLocalidadNac($form['localidad']);
                        $newEstudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['departamento']));
                        $newEstudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['provincia']));
                        $newEstudiante->setMaterno($form['materno']);
                        $newEstudiante->setNombre($form['nombre']);
                        $newEstudiante->setOficialia($form['oficialia']);
                        $newEstudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->findOneById($form['pais']));
                        $newEstudiante->setPartida($form['partida']);
                        $newEstudiante->setPaterno($form['paterno']);
                        $newEstudiante->setSangreTipoId($form['sangreTipoId']);
                        $em->persist($newEstudiante);
                        $em->flush();

                        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($newEstudiante->getId());
                    }
                    $inscripcion = new EstudianteInscripcion();
                    $inscripcion->setNumMatricula(0);
                    $inscripcion->setObservacionId(0);
                    $inscripcion->setObservacion(0);
                    $inscripcion->setFechaInscripcion(new \DateTime($form['fechaInscripcion']));
                    $inscripcion->setApreciacionFinal('');
                    $inscripcion->setOperativoId(1);
                    $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById(4));
                    //$inscripcion->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->findOneById(0));
                    //$inscripcion->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->findOneById(1));
                    $inscripcion->setEstudiante($estudiante);
                    //$inscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestion']));
                    //$inscripcion->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->findOneById(10));
                    //$inscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idInstitucion']));
                    //$inscripcion->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById(18));
                    //$inscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(1));
                    //$inscripcion->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->findOneById($form['sucursalIns']));
                    //$inscripcion->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById(0));
                    $inscripcion->setFechaRegistro(new \DateTime('now'));
                    $inscripcion->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['idCurso']));

                    $em->persist($inscripcion);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('newOk', 'El Estudiante fue inscrito correctamente');
                    return $this->redirect($this->generateUrl('estudianteinscripcionTecnica_show_session'));
                }else{
                    // inscripcion de estudiante sin Rude curso corto
                    // 
                    // Verificamos si los datos del nuevo estudiante coinciden con alguno de la base
                    $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad' => $form['carnetIdentidad'], 'fechaNacimiento' => new \DateTime($form['fechaNacimiento'])));
                    if (!$estudiante) {

                        // actualizando el rude del estudiante

                        $query = $em->getConnection()->prepare('SELECT fuc_codigoestudiante(:sie::VARCHAR,:gestion::VARCHAR)');
                        $query->bindValue(':sie', $form['idInstitucion']);
                        $query->bindValue(':gestion', $form['gestion']);
                        $query->execute();
                        $codigorude = $query->fetchAll();

                        $codigo = $codigorude[0];
                        $codigorude = $codigo['fuc_codigoestudiante'];

                        // Sie el estudiante no esta registrado
                        $newEstudiante = new Estudiante();
                        $newEstudiante->setCarnetIdentidad($form['carnetIdentidad']);
                        $newEstudiante->setCodigoRude($codigorude); /////
                        $newEstudiante->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                        $newEstudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
                        $newEstudiante->setMaterno($form['materno']);
                        $newEstudiante->setNombre($form['nombre']);
                        $newEstudiante->setPaterno($form['paterno']);
                        $em->persist($newEstudiante);
                        $em->flush();

                        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($newEstudiante->getId());
                    }
                    $inscripcion = new EstudianteInscripcion();
                    $inscripcion->setNumMatricula(0);
                    $inscripcion->setObservacionId(0);
                    $inscripcion->setObservacion(0);
                    $inscripcion->setFechaInscripcion(new \DateTime($form['fechaInscripcion']));
                    $inscripcion->setApreciacionFinal('');
                    $inscripcion->setOperativoId(1);
                    $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById(4));
                    //$inscripcion->setParaleloTipo($em->getRepository('SieAppWebBundle:ParaleloTipo')->findOneById(0));
                    //$inscripcion->setCicloTipo($em->getRepository('SieAppWebBundle:CicloTipo')->findOneById(1));
                    $inscripcion->setEstudiante($estudiante);
                   // $inscripcion->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($form['gestion']));
                    //$inscripcion->setGradoTipo($em->getRepository('SieAppWebBundle:GradoTipo')->findOneById(10));
                    //$inscripcion->setInstitucioneducativa($em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['idInstitucion']));
                    //$inscripcion->setNivelTipo($em->getRepository('SieAppWebBundle:NivelTipo')->findOneById(18));
                    //$inscripcion->setPeriodoTipo($em->getRepository('SieAppWebBundle:PeriodoTipo')->findOneById(1));
                    //$inscripcion->setSucursalTipo($em->getRepository('SieAppWebBundle:SucursalTipo')->findOneById($form['sucursalIns']));
                    //$inscripcion->setTurnoTipo($em->getRepository('SieAppWebBundle:TurnoTipo')->findOneById(0));
                    $inscripcion->setFechaRegistro(new \DateTime('now'));
                    $inscripcion->setInstitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($form['idCurso']));

                    $em->persist($inscripcion);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('newOk', 'El Estudiante fue inscrito correctamente');
                    return $this->redirect($this->generateUrl('estudianteinscripcionTecnica_show_session'));
                }
            }
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('newError', 'No se pudo realizar la inscripción');
            return $this->redirect($this->generateUrl('estudianteinscripcionTecnica_show_session'));
        }
    }

    public function editAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($request->get('idInscripcion'));
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($inscripcion->getInstitucioneducativa());
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($inscripcion->getEstudiante());
            
            $formulario = $this->editForm($inscripcion,$institucion,$estudiante);
            return $this->render('SiePermanenteBundle:EstudianteInscripcionTecnica:edit.html.twig',array('formConRude'=>$formulario->createView()));
            
        } catch (Exception $ex) {

        }
    }
    
    private function editForm($inscripcion,$institucion,$estudiante){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                    'SELECT emt FROM SieAppWebBundle:EstadomatriculaTipo emt
                    WHERE emt.id IN (:id)')
                ->setParameter('id', array(4,5,6,11));

        $matricula = $query->getResult();
        $matriculaArray = array();
        foreach ($matricula as $m){
            $matriculaArray[$m->getId()] = $m->getEstadoMatricula();
        }
        
        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteinscripcionTecnica_update'))
                        ->add('idInscripcion', 'hidden', array('data' => $inscripcion->getId()))
                        ->add('codigoIns', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'data' => $institucion->getId(), 'attr' => array('class' => 'form-control')))
                        ->add('nombreIns', 'text', array('label' => 'Nombre Institución Educativa', 'disabled' => true, 'data' => $institucion->getInstitucioneducativa(), 'attr' => array('class' => 'form-control')))
                        ->add('sucursalIns', 'text', array('label' => 'Sucursal', 'data' => 0,'disabled'=>true, 'attr' => array('class' => 'form-control')))
                        ->add('rude', 'text', array('label' => 'Código RUDE','data'=>$estudiante->getCodigoRude(), 'required' => true,'disabled'=>true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('paterno', 'text', array('label' => 'Paterno','data'=>$estudiante->getPaterno(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('materno', 'text', array('label' => 'Materno','data'=>$estudiante->getMaterno(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nombre', 'text', array('label' => 'Nombre(s)','data'=>$estudiante->getNombre(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento','data'=>$estudiante->getFechaNacimiento()->format('d-m-Y'), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        /*
                        ->add('nivel', 'entity', array('label' => 'Nivel', 'class' => 'SieAppWebBundle:NivelTipo', 'property' => 'nivel', 'data'=>$em->getReference('SieAppWebBundle:NivelTipo',$inscripcion->getNivelTipo()->getId()),'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control'))) 
                        ->add('grado', 'choice', array('label' => 'Grado', 'choices' => $list, 'data'=>$inscripcion->getGradoTipo()->getId(),'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('paralelo', 'entity', array('label' => 'Paralelo', 'class' => 'SieAppWebBundle:ParaleloTipo', 'property' => 'paralelo', 'data'=>$em->getReference('SieAppWebBundle:ParaleloTipo',$inscripcion->getParaleloTipo()->getId()),'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('turno', 'entity', array('label' => 'Turno', 'class' => 'SieAppWebBundle:TurnoTipo', 'property' => 'turno', 'data'=>$em->getReference('SieAppWebBundle:TurnoTipo',$inscripcion->getTurnoTipo()->getId()),'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('periodo', 'entity', array('label' => 'Periodo', 'class' => 'SieAppWebBundle:PeriodoTipo', 'property' => 'periodo', 'data'=>$em->getReference('SieAppWebBundle:PeriodoTipo',$inscripcion->getPeriodoTipo()->getId()),'empty_value' => 'Seleccionar...', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        */
                        ->add('matricula','choice',array('label'=>'Matricula','choices'=>$matriculaArray,'data'=>$inscripcion->getEstadomatriculaTipo()->getId(),'attr'=>array('class'=>'form-control')))
                        ->add('fechaInscripcion', 'text', array('label' => 'Fecha de Inscripción','data'=>$inscripcion->getFechaInscripcion()->format('d-m-Y'), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary')))
                        ->getForm();
    }
    
    public function updateAction(Request $request){
        try{
            $form = $request->get('form');
            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($form['idInscripcion']);
            $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById($form['matricula']));
            $em->flush();
            
            
            $this->get('session')->getFlashBag()->add('updateOk', 'El registro fue modificado correctamente');
            return $this->redirect($this->generateUrl('estudianteinscripcionTecnica_show_session'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('updateError', 'Error al modificar el registro');
            return $this->redirect($this->generateUrl('estudianteinscripcionTecnica_show_session'));
        }
    }
    
    public function deleteAction(Request $request){
        try{
            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($request->get('idInscripcion'));
            $em->remove($inscripcion);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado exitosamente');
            return $this->redirect($this->generateUrl('estudianteinscripcionTecnica_show_session'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('deleteError', 'Error al eliminar el registro');
            return $this->redirect($this->generateUrl('estudianteinscripcionTecnica_show_session'));
        }
    }
    
    public function listargradosAction($nivel) {
        $em = $this->getDoctrine()->getManager();
        //$dep = $em->getRepository('SieAppWebBundle:GradoTipo')->findAll();
        if ($nivel == 11) {
            $query = $em->createQuery(
                            'SELECT gt
                            FROM SieAppWebBundle:GradoTipo gt
                            WHERE gt.id IN (:id)
                            ORDER BY gt.id ASC'
                    )->setParameter('id', array(1, 2));
        } else {
            $query = $em->createQuery(
                            'SELECT gt
                            FROM SieAppWebBundle:GradoTipo gt
                            WHERE gt.id IN (:id)
                            ORDER BY gt.id ASC'
                    )->setParameter('id', array(1, 2, 3, 4, 5, 6));
        }
        $gra = $query->getResult();
        $lista = array();
        foreach ($gra as $gr) {
            $lista[$gr->getId()] = $gr->getGrado();
        }
        $list = $lista;
        $response = new JsonResponse();
        return $response->setData(array('listagrados' => $list));
    }

    public function buscarestudiantesinstitucionAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                        'SELECT ei, e
                    FROM SieAppWebBundle:EstudianteInscripcion ei
                    JOIN ei.estudiante e')
                ->setMaxResults(100);
        $estudiantes = $query->getResult();

        $lista = array();
        foreach ($estudiantes as $est) {
            $lista[] = $est->getEstudiante()->getCodigoRude();
        }
        $list = $lista;
        //print_r($list);die;
        $response = new JsonResponse();
        return $response->setData($list);
    }
    
    public function listar_asignaturasAction($especialidad){
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                    'SELECT ieco
                    FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                    JOIN ieco.asignaturaEspecialidadTipo aet
                    WHERE ieco.insitucioneducativaCurso =:especialidad')
                ->setParameter('especialidad',$especialidad);
                
        $asignaturas = $query->getResult();

        $lista = array();
        foreach ($asignaturas as $as) {
            $lista[$as->getId()] = $as->getAsignaturaEspecialidadTipo()->getAsignaturaEspecialidad();
        }
        $list = $lista;
        $response = new JsonResponse();
        return $response->setData(array('asignaturas' => $list));
    }
    
    /*
     * notas
     */
    
    public function notasAction(Request $request){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion'));
        $estudiante  =$em->getRepository('SieAppWebBundle:Estudiante')->find($inscripcion->getEstudiante());
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso());
        $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso'=>$inscripcion->getInstitucioneducativaCurso()));
        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('p_estudiantenotas_create'))
                ->add('idInscripcion', 'hidden', array('data' => $inscripcion->getId()))
                ->add('idGestion','hidden',array('data'=>$this->session->get('idGestion')))
                ->add('guardar', 'submit', array('label' => 'Guardar', 'attr' => array('class' => 'btn btn-primary')))
                ->getForm();
        
        return $this->render('SiePermanenteBundle:EstudianteInscripcionTecnica:notas_index.html.twig',array('form'=>$form->createView(),'estudiante'=>$estudiante,'ca'=>$curso,'modulos'=>$cursoOferta));
    }
    
    public function notas_createAction(Request $request){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $asignatura = $request->get('asignatura');
        $notas = $request->get('nota');
        
        for($i=0;$i<count($asignatura);$i++){
            /*
             * Registro en tabla estudiante asignatura
             */
            $estudiante_asignatura =  new \Sie\AppWebBundle\Entity\EstudianteAsignatura();
            $estudiante_asignatura->setAsignaturaEspecialidadTipo($em->getRepository('SieAppWebBundle:AsignaturaEspecialidadTipo')->find($asignatura[$i]));
            $estudiante_asignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['idInscripcion']));
            $estudiante_asignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($form['idGestion']));
            $em->persist($estudiante_asignatura);
            $em->flush();
            
            /*
             * Registro de nota
             */
            $estudiante_nota = new \Sie\AppWebBundle\Entity\EstudianteNota();
            $estudiante_nota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($estudiante_asignatura->getId()));
            $estudiante_nota->setFechaRegistro(new \DateTime('now'));
            $estudiante_nota->setNotaCuantitativa($notas[$i]);
            $estudiante_nota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(1));
            $estudiante_nota->setUsuarioId($this->session->get('userId'));
            $em->persist($estudiante_nota);
            $em->flush();
        }
        
        $this->get('session')->getFlashBag()->add('notasOk', 'El registro de notas se realizó correctamente');
        return $this->redirect($this->generateUrl('estudianteinscripcionTecnica_show_session'));
        die;
    }
    
    public function notas_showAction(Request $request){
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($request->get('idInscripcion'));
        $estudiante  =$em->getRepository('SieAppWebBundle:Estudiante')->find($inscripcion->getEstudiante());
        $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso());
        
        $estudiante_asignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('estudianteInscripcion'=>$request->get('idInscripcion')));
        $notas = array();
        foreach($estudiante_asignatura as $ea){
            $estudiante_nota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findOneBy(array('estudianteAsignatura'=>$ea->getId()));
            if($estudiante_nota){
                $notas[] = array('asignatura'=>$ea->getAsignaturaEspecialidadTipo()->getAsignaturaEspecialidad(),'nota'=>$estudiante_nota->getNotaCuantitativa());
            }else{
                $notas[] = array('asignatura'=>$ea->getAsignaturaEspecialidadTipo()->getAsignaturaEspecialidad(),'nota'=>'');
            }
        }
        
        return $this->render('SiePermanenteBundle:EstudianteInscripcionTecnica:notas_show.html.twig',array('estudiante'=>$estudiante,'ca'=>$curso,'modulos'=>$notas));
    }
}
