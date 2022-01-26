<?php

namespace Sie\AppWebBundle\Controller;

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
class EstudianteInscripcionController extends Controller {

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
            $tipoSistema = $request->getSession()->get('sysname');
            switch ($tipoSistema) {
                case 'REGULAR': $this->session->set('tituloTipo', 'Estudiantes Inscritos');
                    break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Personal Administrativo');
                    break;
                default: $this->session->set('tituloTipo', 'Estudiantes Inscritos');
                    break;
            }

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
                    return $this->render($this->session->get('pathSystem') . ':EstudianteInscripcion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
//                    return $this->render('SieAppWebBundle:EstudianteInscripcion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
//                }
            } else {

                $nivelUsuario = 3; //$request->getSession()->get('roluser');
                if ($nivelUsuario != 9 && $nivelUsuario != 5) { // si no es institucion educativa 5 o administrativo 9
                    if ($op == 'search') {
                        return $this->render($this->session->get('pathSystem') . ':EstudianteInscripcion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                    // formulario de busqueda de institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                    } else {

                        return $this->render('SieAppWebBundle:EstudianteInscripcion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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

            // Lista de cursos institucioneducativa
            $query = $em->createQuery(
                            'SELECT iec FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    WHERE iec.institucioneducativa = :idInstitucion
                    AND iec.gestionTipo = :gestion
                    AND iec.nivelTipo IN (:niveles)
                    ORDER BY iec.turnoTipo, iec.nivelTipo, iec.gradoTipo, iec.paraleloTipo')
                    ->setParameter('idInstitucion', $institucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('niveles', array(11, 12, 13));

            $cursos = $query->getResult();
            /*
             * Guardamos la consulta en un array ordenado
             */
            $cursosArray = array();
            foreach ($cursos as $cur) {
                $cursosArray[] = array(
                    'turno' => $cur->getTurnoTipo()->getTurno(),
                    'nivel' => $cur->getNivelTipo()->getNivel(),
                    'grado' => $cur->getGradoTipo()->getGrado(),
                    'paralelo' => $cur->getParaleloTipo()->getParalelo(),
                    'idInstitucion' => $cur->getInstitucioneducativa()->getId(),
                    'idGestion' => $cur->getGestionTipo()->getId(),
                    'idInstitucionCurso' => $cur->getId(),
                    'idNivel' => $cur->getNivelTipo()->getId(),
                    'idTurno' => $cur->getTurnoTipo()->getId()
                );
            }

            for ($i = 0; $i < count($cursosArray); $i++) {
                //$inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso'=>$cursosArray[$i]['idInstitucionCurso']));
                $query = $em->createQuery(
                                'SELECT ei FROM SieAppWebBundle:EstudianteInscripcion ei
                        JOIN ei.estudiante e
                        WHERE ei.institucioneducativaCurso = :institucion
                        ORDER BY e.paterno, e.materno, e.nombre')
                        ->setParameter('institucion', $cursosArray[$i]['idInstitucionCurso']);

                $inscritos = $query->getResult();
                if (count($inscritos) > 0) {
                    for ($j = 0; $j < count($inscritos); $j++) {
                        $cursosArray[$i]['inscritos'][] = array(
                            'rude' => $inscritos[$j]->getEstudiante()->getCodigoRude(),
                            'ci' => $inscritos[$j]->getEstudiante()->getCarnetIdentidad(),
                            'paterno' => $inscritos[$j]->getEstudiante()->getPaterno(),
                            'materno' => $inscritos[$j]->getEstudiante()->getMaterno(),
                            'nombre' => $inscritos[$j]->getEstudiante()->getNombre(),
                            'fecha' => $inscritos[$j]->getEstudiante()->getFechaNacimiento()->format('d-m-Y'),
                            'idInscripcion' => $inscritos[$j]->getId()
                        );
                    }
                } else {
                    $cursosArray[$i]['inscritos'] = null; //array('campo'=>'','codigo'=>'','asignatura'=>'adfdasf','maestro'=>'dafdsaf');
                }
            }

            $est = array();
            $est = $cursosArray;
            $niveles = array();
            if (count($est) > 0) {
                $n = $est[0]['nivel'];
                $c = 0;
                $niveles[0] = array($n, array('0' => $est[0]));

                for ($j = 1; $j < count($est); $j++) {
                    if ($est[$j]['nivel'] == $n) {
                        $niveles[$c][1][] = $est[$j];
                    } else {
                        $c++;
                        $n = $est[$j]['nivel'];
                        $niveles[$c] = array($n, array('0' => $est[$j]));
                    }
                }
            }

            $est = $niveles;

            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

            return $this->render($this->session->get('pathSystem') . ':EstudianteInscripcion:index.html.twig', array(
                        'inscritos' => $est, 'institucion' => $institucion, 'gestion' => $gestion
            ));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieAppWebBundle:EstudianteInscripcion:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }

    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual, $gestionactual - 1 => $gestionactual - 1);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('estudianteinscripcion'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'on', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
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


        return $this->render('SieAppWebBundle:EstudianteInscripcion:new.html.twig', array('formConRude' => $formConRude->createView(), 'formSinRude' => $formSinRude->createView()));
    }

    private function newFormConRude($institucion, $sucursal, $gestion) {

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                        'SELECT DISTINCT tt.id,tt.turno
                FROM SieAppWebBundle:InstitucioneducativaCurso iec
                JOIN iec.institucioneducativa ie
                JOIN iec.turnoTipo tt
                WHERE ie.id = :id
                AND iec.gestionTipo = :gestion
                ORDER BY tt.id')
                ->setParameter('id', $institucion->getId())
                ->setParameter('gestion', $gestion);
        $turnos = $query->getResult();
        $turnosArray = array();
        for ($i = 0; $i < count($turnos); $i++) {
            $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
        }

        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteinscripcion_create'))
                        ->add('tipo_inscripcion', 'hidden', array('data' => 'conRude'))
                        ->add('c_idInstitucion', 'hidden', array('data' => $institucion->getId()))
                        ->add('c_gestion', 'hidden', array('data' => $gestion))
                        ->add('c_codigoIns', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'data' => $institucion->getId(), 'attr' => array('class' => 'form-control')))
                        ->add('c_nombreIns', 'text', array('label' => 'Nombre Institución Educativa', 'disabled' => true, 'data' => $institucion->getInstitucioneducativa(), 'attr' => array('class' => 'form-control')))
                        ->add('c_sucursalIns', 'choice', array('label' => 'Sucursal', 'choices' => $sucursal, 'attr' => array('class' => 'form-control')))
                        ->add('c_rude', 'text', array('label' => 'Código RUDE', 'required' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('c_paterno', 'text', array('label' => 'Paterno', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_materno', 'text', array('label' => 'Materno', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_nombre', 'text', array('label' => 'Nombre(s)', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_turno', 'choice', array('label' => 'Turno', 'choices' => $turnosArray, 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('c_grado', 'choice', array('label' => 'Grado', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('c_paralelo', 'choice', array('label' => 'Paralelo', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('c_periodo', 'entity', array('label' => 'Periodo', 'class' => 'SieAppWebBundle:PeriodoTipo', 'data' => $em->getReference('SieAppWebBundle:PeriodoTipo', 1), 'property' => 'periodo', 'required' => true, 'disabled' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('c_fechaInscripcion', 'text', array('label' => 'Fecha de Inscripción', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('c_guardar', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-primary')))
                        ->getForm();
    }

    private function newFormSinRude($institucion, $sucursal, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
                        'SELECT DISTINCT tt.id,tt.turno
                FROM SieAppWebBundle:InstitucioneducativaCurso iec
                JOIN iec.institucioneducativa ie
                JOIN iec.turnoTipo tt
                WHERE ie.id = :id
                AND iec.gestionTipo = :gestion
                ORDER BY tt.id')
                ->setParameter('id', $institucion->getId())
                ->setParameter('gestion', $gestion);
        $turnos = $query->getResult();
        $turnosArray = array();
        for ($i = 0; $i < count($turnos); $i++) {
            $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
        }

        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteinscripcion_create'))
                        ->add('tipo_inscripcion', 'hidden', array('data' => 'sinRude'))
                        ->add('idInstitucion', 'hidden', array('data' => $institucion->getId()))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('codigoIns', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'data' => $institucion->getId(), 'attr' => array('class' => 'form-control')))
                        ->add('nombreIns', 'text', array('label' => 'Nombre Institución Educativa', 'disabled' => true, 'data' => $institucion->getInstitucioneducativa(), 'attr' => array('class' => 'form-control')))
                        ->add('sucursalIns', 'choice', array('label' => 'Sucursal', 'choices' => $sucursal, 'attr' => array('class' => 'form-control')))
                        ->add('carnetIdentidad', 'text', array('label' => 'Carnet de Identidad', 'required' => true, 'attr' => array('class' => 'form-control jnumbers', 'pattern' => '[0-9]{4,10}', 'autocomplete' => 'off', 'maxlength' => '10')))
                        ->add('paterno', 'text', array('label' => 'Paterno', 'required' => false, 'attr' => array('class' => 'form-control jletters jupper', 'pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}', 'autocomplete' => 'off', 'maxlength' => '45')))
                        ->add('materno', 'text', array('label' => 'Materno', 'required' => false, 'attr' => array('class' => 'form-control jletters jupper', 'pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}', 'autocomplete' => 'off', 'maxlength' => '45')))
                        ->add('nombre', 'text', array('label' => 'Nombre(s)', 'required' => true, 'attr' => array('class' => 'form-control jletters jupper', 'pattern' => '[a-zA-Z\sñÑáéíóúÁÉÍÓÚ]{2,45}', 'autocomplete' => 'off', 'maxlength' => '45')))
                        ->add('oficialia', 'text', array('label' => 'Oficialia', 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off', 'maxlength' => '25')))
                        ->add('libro', 'text', array('label' => 'Libro', 'required' => false, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '25')))
                        ->add('partida', 'text', array('label' => 'Partida', 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off', 'maxlength' => '25')))
                        ->add('folio', 'text', array('label' => 'Folio', 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off', 'maxlength' => '25')))
                        ->add('sangreTipoId', 'entity', array('label' => 'Tipo de Sangre', 'class' => 'SieAppWebBundle:SangreTipo', 'property' => 'grupoSanguineo', 'attr' => array('class' => 'form-control jupper')))
                        ->add('idiomaMaternoId', 'entity', array('label' => 'Idioma Materno', 'class' => 'SieAppWebBundle:IdiomaMaterno', 'property' => 'idiomaMaterno', 'attr' => array('class' => 'form-control jupper')))
                        ->add('complemento', 'text', array('label' => 'Complemento', 'required' => false, 'attr' => array('class' => 'form-control jupper', 'autocomplete' => 'off', 'pattern' => '[0-9]{1}[a-zA-Z]{1}', 'maxlength' => '2')))
                        ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'required' => true, 'attr' => array('placeholder' => 'dd-mm-YYYY', 'class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('correo', 'text', array('label' => 'Correo', 'required' => false, 'attr' => array('class' => 'form-control jemail', 'autocomplete' => 'off')))
                        ->add('pais', 'entity', array('label' => 'Pais', 'required' => true, 'class' => 'SieAppWebBundle:PaisTipo', 'property' => 'pais', 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                        ->add('departamento', 'choice', array('label' => 'Departamento', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                        ->add('provincia', 'choice', array('label' => 'Provincia', 'required' => true, 'empty_value' => 'Seleccionar..', 'attr' => array('class' => 'form-control jupper')))
                        ->add('localidad', 'text', array('label' => 'Localidad', 'required' => true, 'attr' => array('class' => 'form-control jnumbersletters jupper', 'autocomplete' => 'off', 'maxlength' => '100')))
                        ->add('celular', 'text', array('label' => 'Celular', 'required' => false, 'attr' => array('class' => 'form-control jcell', 'autocomplete' => 'off')))
                        ->add('carnetCodepedis', 'text', array('label' => 'Carnet Codepedis', 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off', 'maxlength' => '15')))
                        ->add('carnetIbc', 'text', array('label' => 'Carnet I.B.C.', 'required' => false, 'attr' => array('class' => 'form-control jnumbers', 'autocomplete' => 'off', 'maxlength' => '15')))
                        ->add('generoTipo', 'entity', array('label' => 'Género', 'class' => 'SieAppWebBundle:GeneroTipo', 'property' => 'genero', 'attr' => array('class' => 'form-control jupper')))
                        ->add('estadoCivil', 'entity', array('label' => 'Estado Civil', 'class' => 'SieAppWebBundle:EstadoCivilTipo', 'property' => 'estadoCivil', 'attr' => array('class' => 'form-control jupper')))
                        ->add('turno', 'choice', array('label' => 'Turno', 'choices' => $turnosArray, 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nivel', 'choice', array('label' => 'Nivel', 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('grado', 'choice', array('label' => 'Grado', 'required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
                        ->add('paralelo', 'choice', array('label' => 'Paralelo', 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('periodo', 'entity', array('label' => 'Periodo', 'disabled' => true, 'class' => 'SieAppWebBundle:PeriodoTipo', 'data' => $em->getReference('SieAppWebBundle:PeriodoTipo', 1), 'property' => 'periodo', 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control')))
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
                    $inscripcion->setEstudiante($estudiante);
                    $inscripcion->setFechaRegistro(new \DateTime('now'));
                    /* Obteniendo el id del curso */
                    $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                        'gestionTipo' => $form['c_gestion'],
                        'institucioneducativa' => $form['c_idInstitucion'],
                        'turnoTipo' => $form['c_turno'],
                        'nivelTipo' => $form['c_nivel'],
                        'gradoTipo' => $form['c_grado'],
                        'paraleloTipo' => $form['c_paralelo']
                    ));
                    /**
                     * Si no existe el curso seleccionado, redireccionamos y creamos el mensaje de error
                     */
                    if (!$institucionCurso) {
                        $this->get('session')->getFlashBag()->add('errorEstudiante', 'El codigo RUDE ingresado no es válido, intentelo de nuevo.');
                        return $this->redirect($this->generateUrl('estudianteinscripcion_new'));
                    }

                    $inscripcion->setInstitucioneducativaCurso($institucionCurso);
                    $em->persist($inscripcion);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add('registroConRudeOk', 'El Estudiante fue inscrito correctamente');
                    return $this->redirect($this->generateUrl('estudianteinscripcion', array('op' => 'result')));
                } else {
                    $this->get('session')->getFlashBag()->add('errorEstudiante', 'El codigo RUDE ingresado no es válido, intentelo de nuevo.');
                    return $this->redirect($this->generateUrl('estudianteinscripcion_new'));
                }
            } else {
                // inscripcion de estudiante sin Rude
                // 
                // Verificamos si los datos del nuevo estudiante coinciden con alguno de la base
                $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('carnetIdentidad' => $form['carnetIdentidad'], 'fechaNacimiento' => new \DateTime($form['fechaNacimiento'])));
                if (!$estudiante) {

                    // Generamos el rude del estudiante
                    /*
                      $query = $em->getConnection()->prepare('SELECT fuc_codigoestudiante(:sie::VARCHAR,:gestion::VARCHAR)');
                      $query->bindValue(':sie', $form['idInstitucion']);
                      $query->bindValue(':gestion', $form['gestion']);
                      $query->execute();
                      $codigorude = $query->fetchAll();

                      $codigo = $codigorude[0]; */
                    $codigorude = $form['idInstitucion'] . $form['gestion']; //$codigo['fuc_codigoestudiante'];
                    // Registramos al estudiante
                    $newEstudiante = new Estudiante();
                    $newEstudiante->setCarnetCodepedis($form['carnetCodepedis']);
                    $newEstudiante->setCarnetIbc($form['carnetIbc']);
                    $newEstudiante->setCarnetIdentidad($form['carnetIdentidad']);
                    $newEstudiante->setCelular($form['celular']);
                    $newEstudiante->setCodigoRude($codigorude);
                    $newEstudiante->setComplemento(mb_strtoupper($form['complemento'], 'utf-8'));
                    $newEstudiante->setCorreo(mb_strtolower($form['correo'], 'utf-8'));
                    $newEstudiante->setEstadoCivil($em->getRepository('SieAppWebBundle:EstadoCivilTipo')->findOneById($form['estadoCivil']));
                    $newEstudiante->setFechaNacimiento(new \DateTime($form['fechaNacimiento']));
                    $newEstudiante->setFolio($form['folio']);
                    $newEstudiante->setGeneroTipo($em->getRepository('SieAppWebBundle:GeneroTipo')->findOneById($form['generoTipo']));
                    $newEstudiante->setIdiomaMaternoId($form['idiomaMaternoId']);
                    $newEstudiante->setLibro($form['libro']);
                    $newEstudiante->setLocalidadNac($form['localidad']);
                    $newEstudiante->setLugarNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['departamento']));
                    $newEstudiante->setLugarProvNacTipo($em->getRepository('SieAppWebBundle:LugarTipo')->findOneById($form['provincia']));
                    $newEstudiante->setPaterno(mb_strtoupper($form['paterno'], 'utf-8'));
                    $newEstudiante->setMaterno(mb_strtoupper($form['materno'], 'utf-8'));
                    $newEstudiante->setNombre(mb_strtoupper($form['nombre'], 'utf-8'));
                    $newEstudiante->setOficialia($form['oficialia']);
                    $newEstudiante->setPaisTipo($em->getRepository('SieAppWebBundle:PaisTipo')->findOneById($form['pais']));
                    $newEstudiante->setPartida($form['partida']);
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
                $inscripcion->setEstudiante($estudiante);
                /* Obteniendo el id del curso */
                $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                    'gestionTipo' => $form['gestion'],
                    'institucioneducativa' => $form['idInstitucion'],
                    'turnoTipo' => $form['turno'],
                    'nivelTipo' => $form['nivel'],
                    'gradoTipo' => $form['grado'],
                    'paraleloTipo' => $form['paralelo']
                ));
                /**
                 * Si no existe el curso seleccionado, redireccionamos y creamos el mensaje de error
                 */
                if (!$institucionCurso) {
                    $this->get('session')->getFlashBag()->add('errorEstudiante', 'No se pudo realizar la inscripcion en el curso seleccionado, intentelo nuevamente.');
                    return $this->redirect($this->generateUrl('estudianteinscripcion_new'));
                }
                $inscripcion->setInstitucioneducativaCurso($institucionCurso);
                $inscripcion->setFechaRegistro(new \DateTime('now'));
                $em->persist($inscripcion);
                $em->flush();

                $this->get('session')->getFlashBag()->add('registroConRudeOk', 'El Estudiante fue inscrito correctamente');
                return $this->redirect($this->generateUrl('estudianteinscripcion', array('op' => 'result')));
            }
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('errorInscripcion', 'No se pudo realizar la inscripción');
            return $this->redirect($this->generateUrl('estudianteinscripcion_new'));
        }
    }

    public function editAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($request->get('idInscripcion'));
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId());
            $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($inscripcion->getEstudiante());
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($inscripcion->getInstitucioneducativaCurso()->getId());

            $formulario = $this->editForm($inscripcion, $institucion, $estudiante, $institucionCurso);
            return $this->render($this->session->get('pathSystem') . ':EstudianteInscripcion:edit.html.twig', array('formConRude' => $formulario->createView()));
        } catch (Exception $ex) {
            
        }
    }
    

    private function editForm($inscripcion, $institucion, $estudiante, $institucionCurso) {
        $this->session = new Session();
        $em = $this->getDoctrine()->getManager();
        /**
         * Listamos los turnos
         */
        $query = $em->createQuery(
                        'SELECT DISTINCT tt.id,tt.turno
                FROM SieAppWebBundle:InstitucioneducativaCurso iec
                JOIN iec.institucioneducativa ie
                JOIN iec.turnoTipo tt
                WHERE ie.id = :id
                AND iec.gestionTipo = :gestion
                ORDER BY tt.id')
                ->setParameter('id', $institucion->getId())
                ->setParameter('gestion', $this->session->get('idGestion'));
        $turnos = $query->getResult();
        $turnosArray = array();
        for ($i = 0; $i < count($turnos); $i++) {
            $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
        }
        /**
         * Listamos los niveles del turno seleccionado
         */
        $query = $em->createQuery(
                        'SELECT DISTINCT nt.id,nt.nivel
                FROM SieAppWebBundle:InstitucioneducativaCurso iec
                JOIN iec.institucioneducativa ie
                JOIN iec.nivelTipo nt
                WHERE ie.id = :id
                AND iec.gestionTipo = :gestion
                AND iec.turnoTipo = :turno
                ORDER BY nt.id')
                ->setParameter('id', $institucion->getId())
                ->setParameter('gestion', $this->session->get('idGestion'))
                ->setParameter('turno', $institucionCurso->getTurnoTipo()->getId());
        $niveles = $query->getResult();
        $nivelesArray = array();
        for ($i = 0; $i < count($niveles); $i++) {
            $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
        }
        /**
         * Listamos los grados segun el turno y nivel
         */
        $query = $em->createQuery(
                        'SELECT DISTINCT gt.id,gt.grado
                FROM SieAppWebBundle:InstitucioneducativaCurso iec
                JOIN iec.institucioneducativa ie
                JOIN iec.gradoTipo gt
                WHERE ie.id = :id
                AND iec.gestionTipo = :gestion
                AND iec.turnoTipo = :turno
                AND iec.nivelTipo = :nivel
                ORDER BY gt.id')
                ->setParameter('id', $institucion->getId())
                ->setParameter('gestion', $this->session->get('idGestion'))
                ->setParameter('turno', $institucionCurso->getTurnoTipo()->getId())
                ->setParameter('nivel', $institucionCurso->getNivelTipo()->getId());
        $grados = $query->getResult();
        $gradosArray = array();
        for ($i = 0; $i < count($grados); $i++) {
            $gradosArray[$grados[$i]['id']] = $grados[$i]['grado'];
        }
        /**
         * Listamos los paralelos segun el turno nivel y grado
         */
        $query = $em->createQuery(
                        'SELECT DISTINCT pt.id,pt.paralelo
                FROM SieAppWebBundle:InstitucioneducativaCurso iec
                JOIN iec.institucioneducativa ie
                JOIN iec.paraleloTipo pt
                WHERE ie.id = :id
                AND iec.gestionTipo = :gestion
                AND iec.turnoTipo = :turno
                AND iec.nivelTipo = :nivel
                AND iec.gradoTipo = :grado
                ORDER BY pt.id')
                ->setParameter('id', $institucion->getId())
                ->setParameter('gestion', $this->session->get('idGestion'))
                ->setParameter('turno', $institucionCurso->getTurnoTipo()->getId())
                ->setParameter('nivel', $institucionCurso->getNivelTipo()->getId())
                ->setParameter('grado', $institucionCurso->getGradoTipo()->getId());
        $paralelos = $query->getResult();
        $paralelosArray = array();
        for ($i = 0; $i < count($paralelos); $i++) {
            $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
        }

        return $this->createFormBuilder()
                        ->setAction($this->generateUrl('estudianteinscripcion_update'))
                        ->add('idInscripcion', 'hidden', array('data' => $inscripcion->getId()))
                        ->add('idInstitucion', 'hidden', array('data' => $institucion->getId()))
                        ->add('gestion', 'hidden', array('data' => $this->session->get('idGestion')))
                        ->add('codigoIns', 'text', array('label' => 'Institución Educativa', 'disabled' => true, 'data' => $institucion->getId(), 'attr' => array('class' => 'form-control')))
                        ->add('nombreIns', 'text', array('label' => 'Nombre Institución Educativa', 'disabled' => true, 'data' => $institucion->getInstitucioneducativa(), 'attr' => array('class' => 'form-control')))
                        ->add('sucursalIns', 'text', array('label' => 'Sucursal', 'data' => 0, 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('rude', 'text', array('label' => 'Código RUDE', 'data' => $estudiante->getCodigoRude(), 'required' => true, 'disabled' => true, 'attr' => array('class' => 'form-control', 'autocomplete' => 'off')))
                        ->add('paterno', 'text', array('label' => 'Paterno', 'data' => $estudiante->getPaterno(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('materno', 'text', array('label' => 'Materno', 'data' => $estudiante->getMaterno(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nombre', 'text', array('label' => 'Nombre(s)', 'data' => $estudiante->getNombre(), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('fechaNacimiento', 'text', array('label' => 'Fecha de Nacimiento', 'data' => $estudiante->getFechaNacimiento()->format('d-m-Y'), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('turno', 'choice', array('label' => 'Turno', 'choices' => $turnosArray, 'data' => $institucionCurso->getTurnoTipo()->getId(), 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('nivel', 'choice', array('label' => 'Nivel', 'choices' => $nivelesArray, 'data' => $institucionCurso->getNivelTipo()->getId(), 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('grado', 'choice', array('label' => 'Grado', 'choices' => $gradosArray, 'data' => $institucionCurso->getGradoTipo()->getId(), 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('paralelo', 'choice', array('label' => 'Paralelo', 'choices' => $paralelosArray, 'data' => $institucionCurso->getParaleloTipo()->getId(), 'empty_value' => 'Seleccionar...', 'required' => true, 'attr' => array('class' => 'form-control')))
                        ->add('periodo', 'entity', array('label' => 'Periodo', 'class' => 'SieAppWebBundle:PeriodoTipo', 'property' => 'periodo', 'data' => $em->getReference('SieAppWebBundle:PeriodoTipo', $institucionCurso->getPeriodoTipo()->getId()), 'empty_value' => 'Seleccionar...', 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('fechaInscripcion', 'text', array('label' => 'Fecha de Inscripción', 'data' => $inscripcion->getFechaInscripcion()->format('d-m-Y'), 'disabled' => true, 'attr' => array('class' => 'form-control')))
                        ->add('guardar', 'submit', array('label' => 'Guardar Cambios', 'attr' => array('class' => 'btn btn-primary')))
                        ->getForm();
    }

    public function updateAction(Request $request) {
        try {

            $form = $request->get('form');
            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($form['idInscripcion']);

            /* Obteniendo el id del curso */
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                'gestionTipo' => $form['gestion'],
                'institucioneducativa' => $form['idInstitucion'],
                'turnoTipo' => $form['turno'],
                'nivelTipo' => $form['nivel'],
                'gradoTipo' => $form['grado'],
                'paraleloTipo' => $form['paralelo']
            ));
            /**
             * Si no existe el curso seleccionado, redireccionamos y creamos el mensaje de error
             */
            if (!$institucionCurso) {
                $this->get('session')->getFlashBag()->add('updateError', 'No se pudieron modificar los datos, intentelo nuevamente.');
                return $this->redirect($this->generateUrl('estudianteinscripcion', array('op' => 'result')));
            }

            $inscripcion->setInstitucioneducativaCurso($institucionCurso);
            $em->flush();


            $this->get('session')->getFlashBag()->add('updateOk', 'El registro fue modificado correctamente');
            return $this->redirect($this->generateUrl('estudianteinscripcion', array('op' => 'result')));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('updateError', 'Error al modificar el registro');
            return $this->redirect($this->generateUrl('estudianteinscripcion', array('op' => 'result')));
        }
    }

    public function deleteAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($request->get('idInscripcion'));
            $em->remove($inscripcion);
            $em->flush();

            /*
             * verificamos si existen estudiante inscritos en el curso anterior del estudiante
             * si no hay estudiantes inscritos eliminamos el curso
             */
            $estudiantesAnteriorCurso = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativa' => $inscripcion->getInstitucioneducativa(), 'gestionTipo' => $inscripcion->getGestionTipo(), 'nivelTipo' => $inscripcion->getNivelTipo(), 'gradoTipo' => $inscripcion->getGradoTipo(), 'paraleloTipo' => $inscripcion->getParaleloTipo()));
            if (!$estudiantesCurso) {
                $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array('institucioneducativa' => $inscripcion->getInstitucioneducativa(), 'gestionTipo' => $inscripcion->getGestionTipo(), 'nivelTipo' => $inscripcion->getNivelTipo(), 'gradoTipo' => $inscripcion->getGradoTipo(), 'paraleloTipo' => $inscripcion->getParaleloTipo()));
                $em->remove($institucionCurso);
                $em->flush();
            }

            $this->get('session')->getFlashBag()->add('deleteOk', 'El registro fue eliminado exitosamente');
            return $this->redirect($this->generateUrl('estudianteinscripcion'));
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('deleteError', 'Error al eliminar el registro');
            return $this->redirect($this->generateUrl('estudianteinscripcion'));
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

}
