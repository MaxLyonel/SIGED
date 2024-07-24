<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoEspecial;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;


/**
 * areas especial controller.
 *
 */
class AreasController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
    }
    
    public function searchAction(Request $request) { 
        // Verificacmos si existe la session de usuario
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();
        
        if ($request->getMethod() == 'POST') {
            $form = $request->get('form');
            $sie = $form['sie'];
            $gestion = $form['gestion'];
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['sie']);
        }
        else {
            $sie = null;
            $gestion = null;
            $institucion = array();
        }

        return $this->render('SieEspecialBundle:Areas:search.html.twig', array(
            'form' => $this->formSearch($request->getSession()->get('currentyear'), $sie, $gestion)->createView(),
            'gestion' => $gestion,
            'institucion' => $institucion
        ));
        
    }
    /**
     * Lista de estudiantes inscritos en la institucion educativa
     *
     */
    public function indexAction(Request $request, $op) { 
        // Verificacmos si existe la session de usuario
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            // generar los titulos para los diferentes sistemas

            $this->session = new Session();
            $tipoSistema = $request->getSession()->get('sysname');
            switch ($tipoSistema) {
                case 'REGULAR':
                    if($this->session->get('roluser') == 9){
                        $this->session->set('tituloTipo','Asignación de Maestros');
                    }else{
                        $this->session->set('tituloTipo', 'Adición/Eliminación de Áreas');
                    }
                    $this->session->set('layout', 'layoutRegular.html.twig');
                    break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Adición de Áreas y Asignacion de Docentes');
                    $this->session->set('layout', 'layoutAlternativa.html.twig');
                    break;
                default: $this->session->set('tituloTipo', 'Adicion');
                    $this->session->set('layout', 'layoutRegular.html.twig');
                    break;
            }

            ////////////////////////////////////////////////////
            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                $gestion = $form['gestion'];
                $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
                /**
                 * VErificamos si la gestion es 2015
                 */
                if ($form['gestion'] < 2013) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'La gestión ingresada no es válida.');
                    return $this->render('SieEspecialBundle:Areas:search.html.twig', array(
                        'form' => $this->formSearch($request->getSession()->get('currentyear'), $form['institucioneducativa'], $form['gestion'])->createView(),
                        'gestion' => $gestion,
                        'institucion' => $institucion
                    ));
                }
                /*
                 * verificamos si existe la unidad educativa
                 */
                $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
                if (!$institucioneducativa) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'El código ingresado no es válido');
                    return $this->render('SieEspecialBundle:Areas:search.html.twig', array(
                        'form' => $this->formSearch($request->getSession()->get('currentyear'), $form['institucioneducativa'], $form['gestion'])->createView(),
                        'gestion' => $gestion,
                        'institucion' => null
                    ));
                }
                /*
                 * verificamos si tiene tuicion
                 */
                $query = $em->getConnection()->prepare('SELECT get_ue_tuicion (:user_id::INT, :sie::INT, :rolId::INT)');
                $query->bindValue(':user_id', $this->session->get('userId'));
                $query->bindValue(':sie', $form['institucioneducativa']);
                $query->bindValue(':rolId', $this->session->get('roluser'));
                $query->execute();
                $aTuicion = $query->fetchAll();

                if ($aTuicion[0]['get_ue_tuicion']) {
                    $institucion = $form['institucioneducativa'];
                    $gestion = $form['gestion'];
                    $area = $form['area'];
                } else {
                    $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                    return $this->render('SieEspecialBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'), $form['institucioneducativa'], $form['gestion'])->createView()));
                }
            } else {
                $nivelUsuario = $request->getSession()->get('roluser');
                if ($nivelUsuario != 1) { // si no es estudiante
                    // formulario de busqueda de institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                        if ($op == 'search') {
                            return $this->render('SieEspecialBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'), $institucion, $gestion)->createView()));
                        }
                    } else {
                        return $this->render('SieEspecialBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'), $sesinst, $request->getSession()->get('currentyear'))->createView()));
                    }
                } else { // si es institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = date('Y') - 1;
                        $area = $request->getSession()->get('idArea');

                    } else {
                        $funcion = new \Sie\AppWebBundle\Controller\FuncionesController();
                        $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'), $request->getSession()->get('currentyear')); //5484231);
                        $gestion = date('Y') - 1;
                        $area = $request->getSession()->get('idArea');
                    }
                }
            }

            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);
            $request->getSession()->set('idArea', $area);
            // Lista de turnos validos para la unidad educativa
            $query = $em->createQuery(
                    'SELECT DISTINCT tt.id,tt.turno FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iec
                    JOIN iec.institucioneducativaCurso ie
                    JOIN ie.turnoTipo tt
                    WHERE ie.institucioneducativa = :idInstitucion
                    AND ie.gestionTipo = :gestion
                    AND iec.especialAreaTipo = :area')
                                ->setParameter('idInstitucion', $institucion)
                                ->setParameter('gestion', $gestion)
                                ->setParameter('area', $area);
                                $turnos = $query->getResult();
                                $turnosArray = array();
                                for ($i = 0; $i < count($turnos); $i++) {
                                    $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
                                }
            /**
             * Creamos el formulario de busqueda de turno nivel grado y paralelo
             */
            $form = $this->createFormBuilder()
                    ->add('idInstitucion', 'hidden', array('data' => $institucion))
                    ->add('idGestion', 'hidden', array('data' => $gestion))
                    ->add('idArea', 'hidden', array('data' => $area))
                    ->add('turno', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $turnosArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarNiveles()')))
                    ->add('nivel', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarGrados()')))
                    ->add('grado', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarParalelos()')))
                    ->add('paralelo', 'choice', array('label' => 'Paralelo/Servicio/Programa','required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'validateForm()')))
                    ->add('buscar', 'submit', array('label' => 'Buscar Curso', 'attr' => array('class' => 'btn btn-info btn-block')))
                    ->getForm();
            /*
             * obtenemos los datos de la unidad educativa
             */
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);
            $area = $em->getRepository('SieAppWebBundle:EspecialAreaTipo')->findOneById($area);
            /*
             * Listamos los maestros inscritos en la unidad educativa
             */


            $em->getConnection()->commit();

            return $this->render('SieEspecialBundle:Areas:index.html.twig', array(
                        'area' => $area,'turnos' => $turnosArray, 'institucion' => $institucion, 'gestion' => $gestion, 'form' => $form->createView()
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('noSearch', 'El código ingresado no es válido');
            return $this->render('SieEspecialBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'), null, null)->createView()));
        }
    }

    /*
     * Formulario de busqueda de institucion educativa
     */

    private function formSearch($gestionactual, $sie, $gestion) {
        $em = $this->getDoctrine()->getManager();
        $areas = array();
        $readonly = false;
        if($gestion){
            $gestiones = array($gestion => $gestion);
            $readonly = true;

            $repository = $em->getRepository('SieAppWebBundle:EspecialAreaTipo');
            $query = $repository->createQueryBuilder('eat')
            ->select('eat')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoEspecial', 'ice', 'WITH', 'ice.especialAreaTipo = eat.id')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ice.institucioneducativaCurso = ic.id')
            ->where('ic.institucioneducativa = :idInstitucion')
            ->andWhere('ic.gestionTipo = :gestion')
            ->andWhere('eat.id IN (:areas)')
            ->setParameter('idInstitucion', $sie)
            ->setParameter('gestion', $gestion);
            if($gestion < 2020){
                $query = $query
                ->setParameter('areas', array(1,2,3,5));
            }else{
                $query = $query
                ->setParameter('areas', array(1,2,3,4,5,6,7,10,12));
            }
            $query = $query
            //->addOrderBy('eat.id')
            ->addOrderBy('eat.areaEspecial')
            ->distinct()
            ->getQuery();

            $areas_result = $query->getResult();
            $areas = array();
            foreach ($areas_result as $a){
                $areas[$a->getId()] = $a->getAreaEspecial();
            }
        }
        else {
            $gestiones = array();
            for($i=$gestionactual;$i>=2013;$i--){
                $gestiones[$i] = $i;
            }

            $query = $em->createQuery('SELECT a FROM SieAppWebBundle:EspecialAreaTipo a
                                    WHERE a.id IN (:id) ORDER BY a.id')->setParameter('id',array(1,2,3,4,5,6));
            $areas_result = $query->getResult();
            $areas = array();
            foreach ($areas_result as $a){
                $areas[$a->getId()] = $a->getAreaEspecial();
            }
        }
        
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('area_especial'))
                ->add('institucioneducativa', 'text', array('data' => $sie, 'required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => 9, 'readonly' => $readonly)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones, 'attr' => array('readonly' => $readonly)))
                ->add('area','choice',array('label'=>'Area de Especial','required' => true, 'choices' => $areas,'empty_value'=>'Seleccionar...','attr'=>array('class'=>'form-control')))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    /**
     * Funcion para cargar los grados segun el nivel, para el nuevo curso
     */
    public function listargradosAction($nivel) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
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
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('listagrados' => $list));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /**
     * Funciones ajax para la seleccion de nivel grado y paralelo
     */

    /**
     * Funciones para cargar los turnos de la unidad educativa
     */
    public function cargarTurnosAction($idInstitucion, $gestion) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $query = $em->createQuery(
                            'SELECT DISTINCT tt.id,tt.turno
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.turnoTipo tt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    ORDER BY tt.id')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion);
            $turnos = $query->getResult();
            $turnosArray = array();
            for ($i = 0; $i < count($turnos); $i++) {
                $turnosArray[$turnos[$i]['id']] = $turnos[$i]['turno'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('turnos' => $turnosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Funciones para cargar los combos dependientes via ajax
     */

    public function cargarNivelesAction($idInstitucion, $gestion, $turno, $area) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $query = $em->createQuery(
                            'SELECT DISTINCT nt.id,nt.nivel
                    FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iece
                    JOIN iece.institucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.nivelTipo nt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    AND iec.turnoTipo = :turno
                    AND iece.especialAreaTipo = :area
                    AND iec.nivelTipo <> 405
                    ORDER BY nt.id')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('area', $area)
                    ->setParameter('turno', $turno);
            $niveles = $query->getResult();
            $nivelesArray = array();
            for ($i = 0; $i < count($niveles); $i++) {
                $nivelesArray[$niveles[$i]['id']] = $niveles[$i]['nivel'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('niveles' => $nivelesArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function cargarGradosAction($idInstitucion, $gestion, $turno, $area, $nivel) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $query = $em->createQuery(
                            'SELECT DISTINCT gt.id,gt.grado
                    FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iece
                    JOIN iece.institucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.gradoTipo gt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    AND iec.turnoTipo = :turno
                    AND iec.nivelTipo = :nivel
                    AND iece.especialAreaTipo = :area
                    ORDER BY gt.id')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('area', $area)
                    ->setParameter('turno', $turno)
                    ->setParameter('nivel', $nivel);
            $grados = $query->getResult();
            $gradosArray = array();
            for ($i = 0; $i < count($grados); $i++) {
                $gradosArray[$grados[$i]['id']] = $grados[$i]['grado'];
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('grados' => $gradosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    public function cargarParalelosAction($idInstitucion, $gestion, $turno, $area, $nivel, $grado) { 
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $query = $em->createQuery(
                            'SELECT DISTINCT iec.id,pt.paralelo,ser.servicio,pro.programa, mo.id as momento_id, mo.momento
                    FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iece
                    JOIN iece.institucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.paraleloTipo pt
                    JOIN iece.especialServicioTipo ser
                    JOIN iece.especialProgramaTipo pro
                    JOIN iece.especialMomentoTipo mo
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    AND iec.turnoTipo = :turno
                    AND iec.nivelTipo = :nivel
                    AND iec.gradoTipo = :grado
                    AND iece.especialAreaTipo = :area
                    AND pro.id NOT IN(:programa)
                    ORDER BY pt.paralelo')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('area', $area)
                    ->setParameter('turno', $turno)
                    ->setParameter('nivel', $nivel)
                    ->setParameter('grado', $grado)
                    ->setParameter('programa', array(100)); //antes 17?

            $paralelos = $query->getResult();

//dump($paralelos);die;
            $paralelosArray = array();

            for ($i = 0; $i < count($paralelos); $i++) {
                $momento_visual='';
                if( $paralelos[$i]['momento_id']!='')
                    $momento_visual='-'.$paralelos[$i]['momento'];
                $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'] . '/' . $paralelos[$i]['servicio'] . '/' . $paralelos[$i]['programa'].' '.$momento_visual;
            }
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('paralelos' => $paralelosArray));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Lista de areas segun el nivel
     * ventana modal
     */

    public function lista_areas_nivelAction($idNivel, $idCurso, $mTipo) { 
        //dump($idCurso);die;
        
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session();
            /*
             * Si el nivel es secundaria hacemos otras consultas 13
             */
            /**
             * Obtenemos las asignaturas humanisticas en funcion al nivel
             */
            $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            $institucionCursoEspecial = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativaCurso' => $institucionCurso));
            
            $idarea = $institucionCursoEspecial->getEspecialAreaTipo()->getId();
            $idmomento = $institucionCursoEspecial->getEspecialMomentoTipo()->getId();
            if($idarea == 2 or $idarea == 4){
                $esvisual = true;
            }else{
                $esvisual = false;
            }
            //dump($institucionCursoEspecial);die;
            $grado = $institucionCurso->getGradoTipo()->getId();
            $asignaturas = null;
            $programaServicio = null;
            $progSer = null;
            //dump($idNivel); dump($idCurso); dump($idarea); die;
            $todasLasAsignaturas = 'SI';
            switch($idNivel){
                case 400: //Independencia personal 
                case 401: switch ($grado) { //Independencia personal 1
                                case 1:
                                case 2:
                                    if ($this->session->get('idGestion') > 2022) {
                                        $asignaturas = array(1003,497,498,499);
                                    }
                                    else{
                                        $asignaturas = array(464,465,466,467);
                                    }
                                    $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.id IN (:ids)
                                    ORDER BY at.id ASC'
                                    )->setParameter('ids',$asignaturas)
                                    ->getResult();
                                    break;
                             /*  case 3:
                                case 4:
                                case 5:
                                    $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.id IN (:ids)
                                    ORDER BY at.id ASC'
                                    )->setParameter('ids',array(468,469,470,471,472,473,474))
                                    ->getResult();
                                    break;*/
                            }
                 break;
                 case 408: $asignaturas = $em->createQuery( //Independencia personal 2
                                'SELECT at
                                FROM SieAppWebBundle:AsignaturaTipo at
                                WHERE at.id IN (:ids)
                                ORDER BY at.id ASC'
                                )->setParameter('ids',array(468,469,470,471,472,473,474))
                                ->getResult();
                  break;
                case 412: //Independencia social 2023 
                case 402: 
                    if ($this->session->get('idGestion') > 2022) {
                        $asignaturas = array(415,464,416,417,418,2010,469,615);
                    }
                    else{
                        $asignaturas = array(468,469,470,471,472,473,474);
                    }
                        

                        $asignaturas = $em->createQuery( //Independencia Social
                            'SELECT at
                            FROM SieAppWebBundle:AsignaturaTipo at
                            WHERE at.id IN (:ids)
                            ORDER BY at.id ASC'
                            )->setParameter('ids',$asignaturas)
                            ->getResult();
                break;
                case 409: 
                    $asignaturas = array(32851, 32852, 32853);
                        $asignaturas = $em->createQuery( //Atención temprana
                            'SELECT at
                            FROM SieAppWebBundle:AsignaturaTipo at
                            WHERE at.id IN (:ids)
                            ORDER BY at.id ASC'
                            )->setParameter('ids',$asignaturas)
                            ->getResult();
                break;
                case 411:   $programa = $institucionCursoEspecial->getEspecialProgramaTipo()->getId(); //PROGRAMAS
                           // dump($programa);die;
                            switch($programa){
                                case 7:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(475,476,477))
                                    ->getResult();
                                    break;
                                case 8:
                                    if ($this->session->get('idGestion') < 2021) {
                                        $asignaturas = $em->createQuery(
                                                'SELECT at
                                                FROM SieAppWebBundle:AsignaturaTipo at
                                                WHERE at.id IN (:ids)
                                                ORDER BY at.id ASC'
                                        )->setParameter('ids',array(482,483,490,491))
                                        ->getResult();
                                        }else{
                                        $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                            )->setParameter('ids',array(482,483,496))  //---
                                            ->getResult();
    
                                        }
                                        break;
                                case 9:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(479))
                                    ->getResult();
                                    break;
                                case 10:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array())
                                    ->getResult();
                                    break;
                                 case 11:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array())
                                    ->getResult();
                                    break;
                                 case 12:
                                    $todasLasAsignaturas = 'NO';
                                    if ($this->session->get('idGestion') < 2021) {
                                        $asignaturas = $em->createQuery(
                                                'SELECT at
                                                FROM SieAppWebBundle:AsignaturaTipo at
                                                WHERE at.id IN (:ids)
                                                ORDER BY at.id ASC'
                                        )->setParameter('ids',array(493,485,486))
                                        ->getResult();
                                        }else{
                                            $asignaturas = $em->createQuery(
                                                'SELECT at
                                                FROM SieAppWebBundle:AsignaturaTipo at
                                                WHERE at.id IN (:ids)
                                                ORDER BY at.id ASC'
                                        )->setParameter('ids',array(457,485,486))
                                        ->getResult();
                                        }
                                        break;
                                case 14:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(480,481,482,483))
                                    ->getResult();
                                    break;
                                case 15:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(492))
                                    ->getResult();
                                    break;
                                case 16:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array())
                                    ->getResult();
                                    break;
                                case 19:
                                    //se quito area 32832  lenguaje de señas en todos los casos
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(464, 32836, 1018, 1016)) //32832
                                    ->getResult();
                                    break;
                                case 23: //---
                                        $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(850,851,852,853,854,855))
                                    ->getResult();
                                     break;
                                case 25: //---
                                        $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(458,459,482,483))
                                    ->getResult();
                                     break;
                                case 26: //--- VISULA MULTIPLE
                                    $asignaturas = $em->createQuery(
                                        'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.id IN (:ids)
                                        ORDER BY at.id ASC'
                                    )->setParameter('ids',array(4))
                                    ->getResult();
                                    $programaServicio = $institucionCursoEspecial->getEspecialProgramaTipo()->getPrograma();
                                    $esvisual = true;
                                    $progSer = "Programa";

                                    if ($idmomento==1) {

                                        if ($this->session->get('idGestion') > 2022) {
                                            $lasignaturas = array(32840,32832);
                                        }
                                        else{
                                            $lasignaturas = array(32832,3185,32833);
                                        }
                                        
                                        $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                        )->setParameter('ids',$lasignaturas)
                                        ->getResult();
                                        //dump($asignaturas);
                                        //dump(count($asignaturas));die;

                                    }
                                    if ($idmomento==2) {
                                        $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                        )->setParameter('ids',array(482,32834,32835))
                                        ->getResult();
                                        
                                    }
                                    if ($idmomento==3) {
                                        $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                        )->setParameter('ids',array(32867,32868))
                                        ->getResult();
                                        
                                    }

                                 break;
                                case 28: //---atención temprana
                                        $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(993,994,995))
                                    ->getResult();
                                    if ($this->session->get('idGestion') > 2023) {
                                        $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(482,32862,32863,32864,32865,32866))
                                    ->getResult();
                                    }
                                 break;
                                 case 29: //---
                                    $asignaturas = $em->createQuery(
                                        'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.id IN (:ids)
                                        ORDER BY at.id ASC'
                                )->setParameter('ids',array(479,492))
                                ->getResult();
                             break;
                                case 37: //---
                                    $todasLasAsignaturas = 'NO';
                                    $asignaturas = $em->createQuery(
                                        'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.id IN (:ids)
                                        ORDER BY at.id ASC'
                                )->setParameter('ids',array(415,464,416,417,418,2010,469,615,1003,497,498,499))
                                ->getResult();
                                break;
                                case 39: //---´prog atencion multiple auditiva
                                    $asignaturas = $em->createQuery(
                                        'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.id IN (:ids)
                                        ORDER BY at.id ASC'
                                )->setParameter('ids',array(32854,32855,32856))
                                ->getResult();
                                break;
                                case 41: //---´prog atencion multiple auditiva
                                    
                                    $asignaturas = $em->createQuery(
                                        'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.id IN (:ids)
                                        ORDER BY at.id ASC'
                                )->setParameter('ids',array(32857,32858))
                                ->getResult();
                                break;
                                case 43: //---´prog atencion multiple auditiva
                                    
                                    $asignaturas = $em->createQuery(
                                        'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.id IN (:ids)
                                        ORDER BY at.id ASC'
                                )->setParameter('ids',array(1035,525,1018,1044))
                                ->getResult();
                                break;
                                case 644: //---
                                    $todasLasAsignaturas = 'NO';
                                    $asignaturas = $em->createQuery(
                                        'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.id IN (:ids)
                                        ORDER BY at.id ASC'
                                )->setParameter('ids',array(32841,32842,32843,32844,32845,32846,32847,32848,32849,32850,))
                                ->getResult();
                             break;
                             case 22:
                                $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.id IN (:ids)
                                    ORDER BY at.id ASC'
                                    )->setParameter('ids',array(32859,32860,32861)) //especial
                                    ->getResult();

                             break;
                                default:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(4))
                                    ->getResult();
                                    $programaServicio = $institucionCursoEspecial->getEspecialProgramaTipo()->getPrograma();
                                    $esvisual = true;
                                    $progSer = "Programa";
                                    break;
                            }
                            break;
                case 410:   //servicio
                    
                            /*if($idarea==7){
                                $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.id IN (:ids)
                                    ORDER BY at.id ASC'
                            )->setParameter('ids',array(856,857,858,859))
                            ->getResult();                               
                            } else{
                                $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.id IN (:ids)
                                    ORDER BY at.id ASC'
                                    )->setParameter('ids',array(4)) //especial
                                    ->getResult();
                                $programaServicio = $institucionCursoEspecial->getEspecialServicioTipo()->getServicio();
                                $esvisual = true;
                                $progSer = "Servicio";

                            }*/
                            if($institucionCursoEspecial->getEspecialServicioTipo()->getId()==40){
                                
                                $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.id IN (:ids)
                                    ORDER BY at.id ASC'
                                    )->setParameter('ids',array(32859,32860,32861)) //especial
                                    ->getResult();
                            }else{
                            $asignaturas = $em->createQuery(
                                'SELECT at
                                FROM SieAppWebBundle:AsignaturaTipo at
                                WHERE at.id IN (:ids)
                                ORDER BY at.id ASC'
                                )->setParameter('ids',array(4)) //especial
                                ->getResult();
                                $programaServicio = $institucionCursoEspecial->getEspecialServicioTipo()->getServicio();
                                $esvisual = true;
                            $progSer = "Servicio";
                            }
                            
                            

                            break;                            
                case 11:   //Inicial
                     $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivel = :idNivel
                                    AND at.id IN (:ids)
                                    ORDER BY at.id ASC'
                            )->setParameter('idNivel', $idNivel)
                            ->setParameter('ids',array(1000,1001,1002,1003))
                            ->getResult();
                            break;
                case 403: //Educacion Inicial
                       $asignaturas = $em->createQuery(
                            'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivel IN (:idNivel)
                                    AND at.id IN (:ids)
                                    ORDER BY at.id ASC'
                                        )->setParameter('idNivel', array(11, 12))
                                    ->setParameter('ids',array(1000,1001,1002,1003))
                                    ->getResult();
                                    break;


                case 12:   //Primaria
                    
                     $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivel = :idNivel
                                    AND at.id IN (:ids)
                                    ORDER BY at.id ASC'
                            )->setParameter('idNivel', $idNivel)
                            ->setParameter('ids',array(1011,1012,1013,1014,1015,1016,1017,1018,1019))
                            ->getResult();
                            break;
                case 404: //Educación primaria
                      $rasignaturas = $em->createQuery(
                            'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.id IN (:ids)
                                    ORDER BY at.id ASC'
                                    )->setParameter('ids',array(464,1012,1013,1014,1015,1016,1017,1018,1019))
                                    ->getResult();
                            $asignaturas = array();
                            foreach ($rasignaturas as $item) {
                                if ($item->getAsignatura() == "COMUNICACIÓN Y LENGUAJES (CASTELLANA, ORIGINARIA Y LENGUA EXTRANJERA)") {
                                    $item->setAsignatura("COMUNICACIÓN Y LENGUAJES (CASTELLANA-ESCRITA, ORIGINARIA Y LENGUA EXTRANJERA)");
                                }
                                $asignaturas[] = $item;
                            }
                                break;


                case 13:   //Educ Secundaria
                     switch ($grado) {
                                case 1:
                                case 2:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.asignaturaNivel = :idNivel
                                            AND at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('idNivel', $idNivel)
                                    ->setParameter('ids',array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044))
                                    ->getResult();
                                    break;
                                case 3:
                                case 4:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.asignaturaNivel = :idNivel
                                            AND at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('idNivel', $idNivel)
                                    ->setParameter('ids',array(1031,1032,1033,1034,1035,1036,1037,1038,1040,1043,1044,1045))
                                    ->getResult();
                                    break;
                                case 5:
                                case 6: if($this->session->get('idGestion') < 2016){ // Para gestion 2015
                                            $asignaturas = $em->createQuery(
                                                    'SELECT at
                                                    FROM SieAppWebBundle:AsignaturaTipo at
                                                    WHERE at.asignaturaNivel = :idNivel
                                                    AND at.id IN (:ids)
                                                    ORDER BY at.id ASC'
                                            )->setParameter('idNivel', $idNivel)
                                            ->setParameter('ids',array(1031,1032,1033,1034,1035,1036,1037,1039,1040,1043,1044,1045))
                                            ->getResult();
                                        }else{ // Para gestion 2016
                                            $asignaturas = $em->createQuery(
                                                    'SELECT at
                                                    FROM SieAppWebBundle:AsignaturaTipo at
                                                    WHERE at.asignaturaNivel = :idNivel
                                                    AND at.id IN (:ids)
                                                    ORDER BY at.id ASC'
                                            )->setParameter('idNivel', $idNivel)
                                            ->setParameter('ids',array(1031,1032,1033,1034,1035,1036,1037,1040,1043,1044,1045))
                                            ->getResult();
                                        }
                                        break;

                            }
                            break;
            }
           
  //         dump($asignaturas);
//dump($programaServicio);die;
            $areasNivel = $asignaturas;
            
            $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso' => $idCurso));

            $areasArray = array();

            for ($i = 0; $i < count($areasNivel); $i++) {
                $check = '';
                $bloqueado = '';
                if ($areasCurso) { 
                    for ($j = 0; $j < count($areasCurso); $j++) {
                        if ($areasNivel[$i]->getId() == $areasCurso[$j]->getAsignaturaTipo()->getId()) {
                            $check = 'checked';
                            $bloqueado = 'disabled';
                        }
                    }
                }

                // Armamos el array solo con las areas que se pueden adicionar
                if ($check != 'checked') { 
                    //dump($idmomento);die;
                    
                    if($idmomento<4){
                        $areasArray[] = array('marcado' => $check, 'bloqueado' => $bloqueado, 'campo' => ($areasNivel[$i]->getAreaTipo()) ? $areasNivel[$i]->getAreaTipo()->getArea() : "", 'codigo' => $areasNivel[$i]->getId(), 'asignatura' => $areasNivel[$i]->getAsignatura(),'programaServicio'=>'');
                    }else{
                        $areasArray[] = array('marcado' => $check, 'bloqueado' => $bloqueado, 'campo' => ($areasNivel[$i]->getAreaTipo()) ? $areasNivel[$i]->getAreaTipo()->getArea() : "", 'codigo' => $areasNivel[$i]->getId(), 'asignatura' => $areasNivel[$i]->getAsignatura(),'programaServicio'=>$programaServicio);
                    }
                   // if()
                }
            }
            if ($mTipo == 15) {
                $cargoMaestro = array(0, 15);
            } else {
                $cargoMaestro = array(0);
            }

            $maestros = $em->createQueryBuilder()
                           ->select('mi.id, p.paterno, p.materno, p.nombre, p.carnet')
                           ->from('SieAppWebBundle:MaestroInscripcion','mi')
                           ->innerJoin('SieAppWebBundle:Persona','p','with','mi.persona = p.id')
                           ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','mi.institucioneducativa = ie.id')
                           ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','mi.gestionTipo = gt.id')
                           ->innerJoin('SieAppWebBundle:CargoTipo','ct','with','mi.cargoTipo = ct.id')
                           ->innerJoin('SieAppWebBundle:RolTipo','rt','with','ct.rolTipo = rt.id')
                           ->where('ie.id = :idInstitucion')
                           ->andWhere('gt.id = :gestion')
                           ->andWhere('rt.id = :rol')
                           ->andWhere('ct.id in (:cargoTipo)')                           
                           ->setParameter('idInstitucion',$this->session->get('idInstitucion'))
                           ->setParameter('gestion',$this->session->get('idGestion'))
                           ->setParameter('rol',2)
                           ->setParameter('cargoTipo',$cargoMaestro)                           
                           ->orderBy('p.paterno','asc')
                           ->addOrderBy('p.materno','asc')
                           ->addOrderBy('p.nombre','asc')
                           ->getQuery()
                           ->getResult();
            $em->getConnection()->commit();
          //dump($areasArray);die;
            return $this->render('SieEspecialBundle:Areas:listaAreas.html.twig', array('areasNivel' => $areasArray, 'maestros' => $maestros,'esvisual'=>$esvisual,'progSer'=>$progSer, 'todasLasAsignaturas'=>$todasLasAsignaturas));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Registrar las areas seleccionadas y listar las nuevas areas del curso
     */

    public function lista_areas_cursoAction(Request $request) { //dump("1");die;
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            $progSer = null;
           //dump($form);die;
            if($form['idArea'] == 2 or $form['idArea'] == 4 or $form['idArea'] == 6 or $form['idArea'] == 7 or $form['nivel'] == 410 or $form['nivel'] == 411){
                $esvisual = true;
            }else{
                $esvisual = false;
            }

            if ($form['nivel'] != 405) { /**405 formacion tecnica */
                $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($form['paralelo']);
               
                $gestion = $curso->getGestionTipo()->getId();
                $cursoEspecial = '';
                if ($curso) {
                    $idCurso = $curso->getId();
                    $cursoEspecial = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativaCurso'=>$idCurso));
                    $mensaje = '';
                } else {
                    $mensaje = "No hay asignaturas";
                    //return $mensaje;
                }
                $queryCargos = $em->createQuery(
                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.rolTipo = 2');
                $cargos = $queryCargos->getResult();
                $cargosArray = array();

                foreach ($cargos as $c) {
                    $cargosArray[$c->getId()] = $c->getId();
                }

                $areasCurso = $em->createQuery(
                                        'SELECT DISTINCT at.id FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                    INNER JOIN ieco.asignaturaTipo at
                    WHERE ieco.insitucioneducativaCurso = :iecId
                    ORDER BY at.id ASC')->setParameter('iecId', $idCurso)->getResult();

                $totalAreasCurso = $em->createQueryBuilder()
                        ->select('ieco.id, areat.area, at.id as idAsignatura, at.asignatura,ept.id as idPrograma,ept.programa,est.id as idServicio,est.servicio')
                        ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoEspecial', 'iece', 'WITH', 'iece.institucioneducativaCurso = ieco.insitucioneducativaCurso')
                        ->leftJoin('SieAppWebBundle:EspecialProgramaTipo', 'ept', 'WITH', 'ept.id = iece.especialProgramaTipo')
                        ->leftJoin('SieAppWebBundle:EspecialServicioTipo', 'est', 'WITH', 'est.id = iece.especialServicioTipo')
                        ->leftJoin('SieAppWebBundle:AreaTipo', 'areat', 'WITH', 'at.areaTipo = areat.id')
                        ->where('ieco.insitucioneducativaCurso = :idCurso')
                        ->setParameter('idCurso', $idCurso)
                        ->orderBy('at.id','ASC')
                        ->getQuery()
                        ->getResult();
                $array = array();
                  // dump($totalAreasCurso);die;
                foreach ($totalAreasCurso as $tac) {
                    //dump($tac['id']);die;

                    $cursoOfertaMaestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
                    $query = $cursoOfertaMaestro->createQueryBuilder('iecom')
                            ->where('iecom.institucioneducativaCursoOferta = :institucionEducativaCursoOfertaId')
                            ->andwhere('iecom.esVigenteAdministrativo = true')
                            ->andWhere('iecom.esVigenteMaestro = true')
                            ->andWhere('iecom.horasMes > 0')
                            ->setParameter('institucionEducativaCursoOfertaId', $tac['id']);
                    $cursoOfertaMaestro = $query->getQuery()->getResult();

                    $totalMaestro = count($cursoOfertaMaestro);

                    if($tac['idAsignatura']==4){
                        if($tac['idPrograma']==99){
                            $programaServicio = $tac['servicio'];
                            $progSer = "Servicio";
                        }else{
                            $programaServicio = $tac['programa'];
                            $progSer = "Programa";
                        }
                    }else{
                        $programaServicio = null;
                    }
                    $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                        'area' => ($tac['area']) ? $tac['area'] : "",
                        'idAsignatura' => $tac['idAsignatura'],
                        'asignatura' => $tac['asignatura'],
                        'programaServicio' => $programaServicio,
                        'totalMaestro' => $totalMaestro)
                        ;
                }
                
                $areasCurso = $array;
               // dump($cursoEspecial);die;
                $em->getConnection()->commit();
                return $this->render('SieEspecialBundle:Areas:listaAreasCurso.html.twig', array(
                            'areasCurso' => $areasCurso,
                            'curso' => $curso,
                            'cursoEspecial' => $cursoEspecial,
                            'mensaje' => $mensaje,
                            'esvisual' => $esvisual,
                            'progSer' => $progSer,
                            'gestion' => $gestion,
                            'form' => $this->createFormToBuild($form['idInstitucion'], $form['idGestion'], '4')->createView()
                ));
            } else {
                echo "La adición de áreas no se puede aplicar a nivel Inicial y formación Técnica";
            }
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    private function createFormToBuild($sie, $gestion, $bim) {
        return $this->createFormBuilder()
                        //->setAction($this->generateUrl('build_file'))
                        ->add('sie', 'hidden', array('data' => $sie))
                        ->add('gestion', 'hidden', array('data' => $gestion))
                        ->add('bimestre', 'hidden', array('data' => $bim))
                        ->add('generar', 'button', array('label' => 'Generar Archivo', 'attr' => array('class' => 'btn btn-success', 'onclick' => 'buildAgain()')))
                        ->getForm()
        ;
    }

    /**
     * Fcunrion para eadicionar y elimiar areas
     */
    public function lista_areas_curso_adicionar_eliminarAction(Request $request) {

        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session;
            $idCurso = $request->get('idInstitucionCurso');
           // dump($idCurso);
            $idMaestroResponsable = $request->get('maestro_responsable');            
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            $gestion = $curso->getGestionTipo()->getId();
            if ($idMaestroResponsable!=null) {
                $curso->setMaestroInscripcionAsesor($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idMaestroResponsable));
                $em->persist($curso);
                $em->flush();
            }

            /*
             * obtenemos id de area especial
             */
            $cursoEspecial = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativaCurso'=>$idCurso));
            $idEspecialArea = $cursoEspecial->getEspecialAreaTipo()->getId();
            $nivel = $curso->getNivelTipo()->getId();
            $progSer = null;
            if($idEspecialArea == 2 or $idEspecialArea == 4 or $idEspecialArea == 6 or $idEspecialArea == 7 or $nivel == 410 or $nivel == 411){
                $esvisual = true;
            }else{
                $esvisual = false;
            }

            /*
             * Areas a registrar nuevos
             */
            $areas = $request->get('areas');
            /*
             * Areas registradas anteriormente
             */

            // dump($areas);die;
            $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso' => $idCurso));
            /**
             * Aplicamos la funcion para actualizar el primary key
             */
            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta');")->execute();

            for ($i = 0; $i < count($areas); $i++) {
                $existe = 'no';
                for ($j = 0; $j < count($areasCurso); $j++) {
                    if ($areas[$i] == $areasCurso[$j]->getAsignaturaTipo()->getId()) {
                        $existe = 'si';
                    }
                }
                
                if($this->session->get('idGestion') == 2016 and $areas[$i] == 1039){
                    $existe = 'si';
                }
                if ($existe == 'no') {  
                    $newArea = new InstitucioneducativaCursoOferta();
                    $newArea->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($areas[$i]));
                    $newArea->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idInstitucionCurso')));
                    $newArea->setHorasmes(0);
                    $em->persist($newArea);
                    $em->flush();

                    // Verificamos si registraron el maestro pra registrarlo en la tabla curso oferta maestro
                    if ($request->get($areas[$i])) {
                         $idMaestroInscripcion = $request->get($areas[$i]);
                         $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
                         $nuevoCOM = new InstitucioneducativaCursoOfertaMaestro();
                         $nuevoCOM->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($newArea->getId()));
                         $nuevoCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idMaestroInscripcion));
                         $nuevoCOM->setHorasMes(0);
                         $nuevoCOM->setFechaRegistro(new \DateTime('now'));
                         $nuevoCOM->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(0));
                         $nuevoCOM->setEsVigenteMaestro('t');
                         $nuevoCOM->setEsVigenteAdministrativo('t');
                         $em->persist($nuevoCOM);
                         $em->flush();
                    }

                    // Listamos los estudinates inscritos
                    // para registrar el curso a los estudiantes
                    $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso' => $idCurso, 'estadomatriculaTipo'=>array(4,5,11,68,7)));
                    foreach ($inscritos as $ins) {
                        // Verificamos si el estudiante ya tiene la asignatura
                        $estInscripcion = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion'=>$ins->getId(),'institucioneducativaCursoOferta'=>$newArea->getId()));
                        if(!$estInscripcion){
                            // Actualizamos el id de la tabla estudiante asignatura
                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
                            $query->execute();
                            $estAsignaturaNew = new EstudianteAsignatura();
                            $estAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('idGestion')));
                            $estAsignaturaNew->setFechaRegistro(new \DateTime('now'));
                            $estAsignaturaNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($ins->getId()));
                            $estAsignaturaNew->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($newArea->getId()));
                            $em->persist($estAsignaturaNew);
                            $em->flush();

                            // Actualizamos el id de la tabla estudiante asignatura
                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');");
                            $query->execute();
                            /*
                            // Verificamos si las notas son bimestrales o trimestrales
                            if(($curso->getGestionTipo()->getId() < 2013) or ($curso->getGestionTipo()->getId() == 2013 and $curso->getGradoTipo()->getId() > 1 )){
                                if($curso->getNivelTipo()->getId() == 11){
                                    $inicio = 6;
                                    $fin = 8;
                                }else{
                                    $inicio = 6;
                                    $fin = 9;
                                }
                            }else{
                                if($curso->getNivelTipo()->getId() == 11){
                                    $inicio = 1;
                                    $fin = 4;
                                }else{
                                    $inicio = 1;
                                    $fin = 5;
                                }
                            }

                            // Registramos las notas cuantitativas hasta el tercer bimestre
                            for($j=$inicio;$j<=$fin;$j++){
                                $estNotaCuantitativa = new EstudianteNota();
                                $estNotaCuantitativa->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($j));
                                $estNotaCuantitativa->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($estAsignaturaNew->getId()));
                                $estNotaCuantitativa->setNotaCuantitativa(0);
                                $estNotaCuantitativa->setNotaCualitativa('');
                                $estNotaCuantitativa->setRecomendacion('');
                                $estNotaCuantitativa->setUsuarioId($this->session->get('userId'));
                                $estNotaCuantitativa->setFechaRegistro(new \DateTime('now'));
                                $estNotaCuantitativa->setFechaModificacion(new \DateTime('now'));
                                $estNotaCuantitativa->setObs('');
                                $em->persist($estNotaCuantitativa);
                                $em->flush();
                            }*/
                        }
                    }
                }
            }

            $queryCargos = $em->createQuery(
                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.rolTipo = 2');
            $cargos = $queryCargos->getResult();
            $cargosArray = array();

            foreach ($cargos as $c) {
                $cargosArray[$c->getId()] = $c->getId();
            }

            $areasCurso = $em->createQuery(
                                    'SELECT DISTINCT at.id FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                INNER JOIN ieco.asignaturaTipo at
                WHERE ieco.insitucioneducativaCurso = :iecId
                ORDER BY at.id ASC')
                            ->setParameter('iecId', $idCurso)->getResult();

            $totalAreasCurso = $em->createQueryBuilder()
                    ->select('ieco.id, areat.area, at.id as idAsignatura, at.asignatura,ept.id as idPrograma,ept.programa,est.id as idServicio,est.servicio')
                    ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                    ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoEspecial', 'iece', 'WITH', 'iece.institucioneducativaCurso = ieco.insitucioneducativaCurso')
                    ->leftJoin('SieAppWebBundle:EspecialProgramaTipo', 'ept', 'WITH', 'ept.id = iece.especialProgramaTipo')
                    ->leftJoin('SieAppWebBundle:EspecialServicioTipo', 'est', 'WITH', 'est.id = iece.especialServicioTipo')
                    ->innerJoin('SieAppWebBundle:AreaTipo', 'areat', 'WITH', 'at.areaTipo = areat.id')
                    ->where('ieco.insitucioneducativaCurso = :idCurso')
                    ->setParameter('idCurso', $idCurso)
                    ->orderBy('at.id','ASC')
                    ->getQuery()
                    ->getResult();
            $array = array();
            
            foreach ($totalAreasCurso as $tac) {

                $cursoOfertaMaestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
                $query = $cursoOfertaMaestro->createQueryBuilder('iecom')
                        ->where('iecom.institucioneducativaCursoOferta = :institucionEducativaCursoOfertaId')
                        ->andwhere('iecom.esVigenteAdministrativo = true')
                        ->andWhere('iecom.esVigenteMaestro = true')
                        ->andWhere('iecom.horasMes > 0')
                        ->setParameter('institucionEducativaCursoOfertaId', $tac['id']);
                $cursoOfertaMaestro = $query->getQuery()->getResult();
                $totalMaestro = count($cursoOfertaMaestro);
                if($tac['idAsignatura']==4){
                    if($tac['idPrograma']==99){
                        $programaServicio = $tac['servicio'];
                        $progSer = "Servicio";
                    }else{
                        $programaServicio = $tac['programa'];
                        $progSer = "Programa";
                    }
                }else{
                    $programaServicio = null;
                }
                $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                    'area' => $tac['area'],
                    'idAsignatura' => $tac['idAsignatura'],
                    'asignatura' => $tac['asignatura'],
                    'programaServicio' => $programaServicio,
                    'totalMaestro' => $totalMaestro);
            }
            $areasCurso = $array;

            $em->getConnection()->commit();
            return $this->render('SieEspecialBundle:Areas:listaAreasCurso.html.twig', array(
                    'areasCurso'    => $areasCurso, 
                    'curso'         => $curso, 
                    'cursoEspecial'         => $cursoEspecial, 
                    'mensaje'       => '',
                    'esvisual'      => $esvisual,
                    'progSer'       => $progSer,
                    'gestion'       => $gestion,
                    'form'          => $this->createFormToBuild($this->session->get('idInstitucion'), $this->session->get('idGestion'), '4')->createView()));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /**
     * Eliminar un area
     */
    public function deleteAction($idCursoOferta) {
        
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        try {
            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($cursoOferta->getInsitucioneducativaCurso()->getId());
            $gestion = $curso->getGestionTipo()->getId();
            $cursoEspecial = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoEspecial')->findOneBy(array('institucioneducativaCurso'=>$curso->getId()));
            $idEspecialArea = $cursoEspecial->getEspecialAreaTipo()->getId();
            if($idEspecialArea == 2 or $idEspecialArea == 4){
                $esvisual = true;
            }else{
                $esvisual = false;
            }            
            /**
             * Si existe el curso y el curso oferta entonces eliminamos el curso oferta
             */
            if ($cursoOferta and $curso) {
                //Verificamos si el curso oferta no tiene estudidantes asociados en la tabla estudiante asignatura
                /* $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta'=>$cursoOferta->getId()));
                  if($estudianteAsignatura){
                  $mensaje='No se puede eliminar el Ã¡rea, porque tiene estudiantes asociados';
                  $eliminar='no';
                  } */


                // VErificamos si tiene maestro asignado
                /* if($cursoOferta->getMaestroInscripcion() != null){
                  $mensaje = 'No se puede eliminar el Ã¡rea si tiene un maestro asignado, primero debe quitar el maestro';
                  $eliminar='no';
                  } */

                $cursosOfertasSimilares = $em->createQuery(
                                'SELECT ieco FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                    INNER JOIN ieco.insitucioneducativaCurso iec
                    INNER JOIN ieco.asignaturaTipo at
                    WHERE iec.id = :idCurso
                    AND at.id = :idAsignatura')
                        ->setParameter('idCurso', $curso->getId())
                        ->setParameter('idAsignatura', $cursoOferta->getAsignaturaTipo()->getId())
                        ->getResult();


                foreach ($cursosOfertasSimilares as $co) {
                    $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta' => $co->getId()));

                    foreach ($estudianteAsignatura as $ea) {
                        // Eliminamos las notas
                        $em->createQuery(
                                        'DELETE FROM SieAppWebBundle:EstudianteNota en
                            WHERE en.estudianteAsignatura = :idEstAsig')
                                ->setParameter('idEstAsig', $ea->getId())->execute();
                    }

                    // Eliminamos en la tabla estudiante asignatura
                    $em->createQuery(
                                    'DELETE FROM SieAppWebBundle:EstudianteAsignatura ea
                        WHERE ea.institucioneducativaCursoOferta = :idCO')
                            ->setParameter('idCO', $co->getId())->execute();

                    // Eliminamos los registros de maestros
                    $em->createQuery(
                                    'DELETE FROM SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro iecom
                        WHERE iecom.institucioneducativaCursoOferta = :idCO')
                            ->setParameter('idCO', $co->getId())->execute();

                    // Registramos en la tabla de control
                    // Eliminamos el curso oferta

                    $cursoOfertaEliminar = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId());
                    $em->remove($cursoOfertaEliminar);


                }
                $em->flush();
                $mensaje = 'Se elimino el Área del curso';
            } else {
                $mensaje = 'No se puede eliminar el Área';
            }

            $this->session = new Session();

            $queryCargos = $em->createQuery(
                    'SELECT ct FROM SieAppWebBundle:CargoTipo ct
                     WHERE ct.rolTipo = 2');
            $cargos = $queryCargos->getResult();
            $cargosArray = array();
            $progSer = null;

            foreach ($cargos as $c) {
                $cargosArray[$c->getId()] = $c->getId();
            }

            $areasCurso = $em->createQuery(
                                    'SELECT DISTINCT at.id FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                INNER JOIN ieco.asignaturaTipo at
                WHERE ieco.insitucioneducativaCurso = :iecId
                ORDER BY at.id ASC')
                            ->setParameter('iecId', $curso->getId())->getResult();

            $totalAreasCurso = $em->createQueryBuilder()
                        ->select('ieco.id, areat.area, at.id as idAsignatura, at.asignatura')
                        ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                        ->innerJoin('SieAppWebBundle:AreaTipo', 'areat', 'WITH', 'at.areaTipo = areat.id')
                        ->where('ieco.insitucioneducativaCurso = :idCurso')
                        ->setParameter('idCurso', $curso->getId())
                        ->orderBy('at.id','ASC')
                        ->getQuery()
                        ->getResult();
                $array = array();
                foreach ($totalAreasCurso as $tac) {
                    
                    $cursoOfertaMaestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
                    $query = $cursoOfertaMaestro->createQueryBuilder('iecom')
                            ->where('iecom.institucioneducativaCursoOferta = :institucionEducativaCursoOfertaId')
                            ->andwhere('iecom.esVigenteAdministrativo = true')
                            ->andWhere('iecom.esVigenteMaestro = true')
                            ->andWhere('iecom.horasMes > 0')
                            ->setParameter('institucionEducativaCursoOfertaId', $tac['id']);
                    $cursoOfertaMaestro = $query->getQuery()->getResult();

                    $totalMaestro = count($cursoOfertaMaestro);
                    if($tac['idAsignatura']==4){
                        if($tac['idPrograma']==99){
                            $programaServicio = $tac['servicio'];
                            $progSer = "Servicio";
                        }else{
                            $programaServicio = $tac['programa'];
                            $progSer = "Programa";
                        }
                    }else{
                        $programaServicio = null;
                    }
                    $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                        'area' => $tac['area'],
                        'idAsignatura' => $tac['idAsignatura'],
                        'asignatura' => $tac['asignatura'],
                        'programaServicio' => $programaServicio,
                        'totalMaestro' => $totalMaestro);
                }
                $areasCurso = $array;

            $em->getConnection()->commit();
            return $this->render('SieEspecialBundle:Areas:listaAreasCurso.html.twig', array(
                'areasCurso'    => $areasCurso, 
                'curso'         => $curso, 
                'mensaje'       => $mensaje,
                'esvisual'      => $esvisual,
                'progSer'       => $progSer,
                'gestion'       => $gestion,
                'form'          => $this->createFormToBuild($this->session->get('idInstitucion'), $this->session->get('idGestion'), '4')->createView()));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Asignar maestro al area
     */
    public function maestrosAction(Request $request){
        $ieco = $request->get('idco');
        //dump($ieco);die;
        $em = $this->getDoctrine()->getManager();
        $maestrosMateria = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta'=>$ieco));

        // Obtener datos del curso
        $curso = $em->createQueryBuilder()
                    ->select('ie.id as sie, gt.id as gestion,iec.lugar, iec as maestro')
                    ->from('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco')
                    ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ieco.insitucioneducativaCurso = iec.id')
                    ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
                    ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
                    ->where('ieco.id = :idCursoOferta')
                    ->setParameter('idCursoOferta',$ieco)
                    ->getQuery()
                    ->getResult();

        $sie = $curso[0]['sie'];
        $gestion = $curso[0]['gestion'];

        $arrayMaestros = array();

        if( $gestion >= 2021 or $gestion <= 2012 or ($gestion == 2013 and $grado > 1 and $nivel != 11) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11) ){
            // trimestrales
            $inicio = 6;
            $fin = 8;
        }else{
            // Bimestrales
            $inicio = 0;
            $operativo = $this->operativo($sie,$gestion);
            
            if($operativo == 5){
                $fin = 4; //4;
            }else{
                $fin = $operativo;
            }
        }
        
        for($i=$inicio;$i<=$fin;$i++){
            $existe = false;
            foreach ($maestrosMateria as $mm) {
                if($mm->getNotaTipo()->getId() == $i){
                    $arrayMaestros[] = array(
                                            'id'=>$mm->getId(),
                                            'idmi'=>$mm->getMaestroInscripcion()->getId(),
                                            'horas'=>$mm->getHorasMes(),
                                            'idNotaTipo'=>$mm->getNotaTipo()->getId(),
                                            'periodo'=>$this->literal($i),
                                            'idco'=>$ieco);
                    $existe = true;
                    break;
                }
            }
            if($existe == false){
                $arrayMaestros[] = array(
                                            'id'=>'nuevo',
                                            'idmi'=>'',
                                            'horas'=>'',
                                            'idNotaTipo'=>$i,
                                            'periodo'=>$this->literal($i),
                                            'idco'=>$ieco);
            }
        }
        if($curso[0]['lugar']){
            $idcargo = array(0,15);
        }else{
            $idcargo = array(0);
        }

        $maestros = $em->createQueryBuilder()
                        ->select('mi')
                        ->from('SieAppWebBundle:MaestroInscripcion','mi')
                        ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','WITH','mi.institucioneducativa = ie.id')
                        ->innerJoin('SieAppWebBundle:GestionTipo','gt','WITH','mi.gestionTipo = gt.id')
                        ->innerJoin('SieAppWebBundle:Persona','p','WITH','mi.persona = p.id')
                        ->innerJoin('SieAppWebBundle:CargoTipo','ct','with','mi.cargoTipo = ct.id')
                        ->innerJoin('SieAppWebBundle:RolTipo','rt','with','ct.rolTipo = rt.id')
                        ->where('ie.id = :sie')
                        ->andWhere('gt.id = :gestion')
                        ->andWhere('rt.id = 2')
                        ->andWhere('ct.id in (:idcargo)')                        
                        ->orderBy('p.paterno','ASC')
                        ->addOrderBy('p.materno','ASC')
                        ->addOrderBy('p.nombre','ASC')
                        ->setParameter('sie',$sie)
                        ->setParameter('gestion',$gestion)
                        ->setParameter('idcargo',$idcargo)                        
                        ->getQuery()
                        ->getResult();
        $responsable = '';
        if ($curso[0]['maestro']->getMaestroInscripcionAsesor()!=null) {
            $maestroResponsable = $em->createQueryBuilder()
                        ->select('CONCAT(p.paterno, \' \', p.materno, \' \', p.nombre) AS responsable')
                        ->from('SieAppWebBundle:MaestroInscripcion','mi')
                        ->innerJoin('SieAppWebBundle:Persona','p','WITH','mi.persona = p.id')
                        ->where('mi.id = :mins')
                        ->setParameter('mins',$curso[0]['maestro']->getMaestroInscripcionAsesor()->getId())
                        ->getQuery()
                        ->getResult();
            if ($maestroResponsable) {
                $responsable = $maestroResponsable[0]['responsable'];
            }
        }
        $operativo = $this->operativo($sie,$gestion);
        //dump($arrayMaestros);die;
        //TODO en Array Maestos se dibuja  la lista por el tipo de Nota por defecto entra como 0
        return $this->render('SieEspecialBundle:Areas:maestros.html.twig',
        array('maestrosCursoOferta'=>$arrayMaestros, 
               'maestros'=>$maestros,
               'ieco'=>$ieco,
               'operativo'=>$operativo, 
               'responsable'=>$responsable));
    }

    
    /*
     * Asignar maestro al area
     * Autor: Cristina Vallejos
     * Fecha: 07/03/2022
     */
    public function maestrosEspecialAction(Request $request){
        date_default_timezone_set('America/La_Paz');
        $ieco = $request->get('idco');
        $em = $this->getDoctrine()->getManager();
       // dump($ieco);die;
        $maestroInscripcionId = 0;
        $item = "";
        $fechaInicio = "";
        $fechaFin = "";
        $financiamientoTipoEntity = array();
        $maestroInscripcioEntity = array();
        $formEnable = true;
        
        $listaMaestros = $this->getMaestrosInstitucionEducativaCursoOferta($ieco,0);
        $detalleCursoOferta = $this->getInstitucionEducativaCursoOferta($ieco);
        if(count($detalleCursoOferta )>0){
            $detalleCursoOferta = $detalleCursoOferta[0];
        }

        $curso = $em->createQueryBuilder()
        ->select('ie.id as sie, gt.id as gestion,iec.lugar, iec as maestro')
        ->from('SieAppWebBundle:InstitucioneducativaCursoOferta','ieco')
        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso','iec','with','ieco.insitucioneducativaCurso = iec.id')
        ->innerJoin('SieAppWebBundle:Institucioneducativa','ie','with','iec.institucioneducativa = ie.id')
        ->innerJoin('SieAppWebBundle:GestionTipo','gt','with','iec.gestionTipo = gt.id')
        ->where('ieco.id = :idCursoOferta')
        ->setParameter('idCursoOferta',$ieco)
        ->getQuery()
        ->getResult();
        
        $sie = $curso[0]['sie'];
        $gestion = $curso[0]['gestion'];
       
        $ofertaMaestro='';

        $maestroInscripcionLista = $this->listaMaestroInscripcion($sie,$gestion);
        $arrayMaestroInscripcionLista = array();
        $arrayMaestroInscripcionLista[base64_encode(0)] = 'SIN ASIGNACIÓN DOCENTE';
        foreach ($maestroInscripcionLista as $data) {
            if($data['complemento'] != ""){
                $arrayMaestroInscripcionLista[base64_encode($data['maestroInscripcionId'])] = $data['paterno']." ".$data['materno']." ".$data['nombre']." (C.I.: ".$data['carnetIdentidad']."-".$data['complemento'].")";
            } else {
                $arrayMaestroInscripcionLista[base64_encode($data['maestroInscripcionId'])] = $data['paterno']." ".$data['materno']." ".$data['nombre']." (C.I.: ".$data['carnetIdentidad'].")";
            }
        }

        $form = $this->getFormRegistroMaestro($item, $fechaInicio, $fechaFin, $financiamientoTipoEntity, $arrayMaestroInscripcionLista, 0, $gestion, $ieco, $ofertaMaestro);
        $arrayRangoFecha = array('inicio'=>"01-01-".$gestion,'final'=>"31-12-".$gestion);
        $arrayFormulario = array('titulo' => "Registro / Modificación de maestro", 'detalleCursoOferta' => $detalleCursoOferta, 'listaMaestros' => $listaMaestros, 'formNuevo'=>$form, 'formEnable'=>$formEnable, 'rangoFecha'=>$arrayRangoFecha);
     
        return $this->render('SieEspecialBundle:Areas:asignacionMateriaFormulario.html.twig', $arrayFormulario);
    }

    public function getFormRegistroMaestro($item, $fechaInicio, $fechaFin, $financiamientoTipoEntity, $maestroInscripcionLista, $maestroInscripcionId, $gestion, $ieco, $ofertaMaestro){
        $form = $this->createFormBuilder()
        ->add('ofertaMaestro', 'hidden', array('label' => 'Info', 'attr' => array('value' => $ofertaMaestro)))
        ->add('ieco', 'hidden', array('label' => 'Info', 'attr' => array('value' => $ieco)))
        ->add('horasMes', 'text', array('label' => 'Carga Horaria', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => '', 'style' => 'text-transform:uppercase', 'placeholder' => 'Horas' , 'maxlength' => 3, 'required' => true)))
        ->add('item', 'text', array('label' => 'Item', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => '', 'style' => 'text-transform:uppercase', 'placeholder' => 'Item' , 'maxlength' => 5, 'required' => false)))
        ->add('fechaInicio', 'text', array('label' => 'Fecha inicio de asignación (ej.: 01-01-'.$gestion.')', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaInicio, 'style' => 'text-transform:uppercase', 'placeholder' => 'Fecha inicio de asignación' , 'maxlength' => 10, 'required' => true)))
        ->add('fechaFin', 'text', array('label' => 'Fecha fin de asignación (ej.: 31-12-'.$gestion.')', 'invalid_message' => 'campo obligatorio', 'attr' => array('value' => $fechaFin, 'style' => 'text-transform:uppercase', 'placeholder' => 'Fecha fin de asignación' , 'maxlength' => 10, 'required' => true)))
        ->add('maestro', 'choice', array('choices' => $maestroInscripcionLista, 'label' => 'Maestro', 'empty_value' => 'Seleccione Maestro', 'data' => $maestroInscripcionId, 'attr' => array()))
        ->add('financiamiento', 'entity', array('data' => $financiamientoTipoEntity, 'label' => 'Financiamiento', 'empty_value' => 'Seleccione Financiamiento', 'class' => 'Sie\AppWebBundle\Entity\FinanciamientoTipo',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('ft')
                        ->orderBy('ft.id', 'ASC');
            },
        ))
        ->getForm()->createView();
        return $form;
    }

    public function maestrosAsignarAction(Request $request){//dump($request);die; //guardar maestro-asig
        $iecom = $request->get('iecom');
        $ieco = $request->get('ieco');
        $idmi = $request->get('idmi');
        $idnt = $request->get('idnt');
        $horas = $request->get('horas');

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
        for($i=0;$i<count($iecom);$i++){
            if($horas[$i] == ''){
                $horasNum = 0;
            }else{
                $horasNum = $horas[$i];
            }
            if($iecom[$i] == 'nuevo' and $idmi[$i] != ''){
                $newCOM = new InstitucioneducativaCursoOfertaMaestro();
                $newCOM->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($ieco[$i]));
                $newCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                $newCOM->setHorasMes($horasNum);
                $newCOM->setFechaRegistro(new \DateTime('now'));
                $newCOM->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find($idnt[$i]));
                $newCOM->setEsVigenteMaestro('t');
                $em->persist($newCOM);
                $em->flush();
            }else{
                if($idmi[$i] != ''){
                    $updateCOM = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($iecom[$i]);
                    $updateCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idmi[$i]));
                    $updateCOM->setHorasMes($horasNum);
                    $updateCOM->setFechaModificacion(new \DateTime('now'));
                    $updateCOM->setEsVigenteMaestro('t');
                    $em->flush();
                }
            }
        }

        $response = new JsonResponse();
        return $response->setData(array('ieco'=>$ieco[0]));
    }

    //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que guardar la asignacion del maestro
    // PARAMETROS: institucioneducativaCursoOfertaId, maestroInscripcionId
    // AUTOR: CVALLEJOS
    //****************************************************************************************************
    public function asignarMaestroMateriaGuardarAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');

        if(isset($form['maestro'])){
            $maestroInscripcionId = base64_decode($form['maestro']);
        } else {
            $maestroInscripcionId = $info['maestro_inscripcion_id'];
        }
        
        if($form['ofertaMaestro']){
            $id_iecom = base64_decode($form['ofertaMaestro']);
            $datoInstitucioneducativaCursoOfertaMaestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($id_iecom);//'notaTipo' => $notaTipoId
            $maestroInscripcionId = $datoInstitucioneducativaCursoOfertaMaestro->getMaestroInscripcion()->getId();
        }

        $institucioneducativaCursoOfertaId = $form['ieco'];
        $horasMes = $form['horasMes'];
        $item = $form['item'];

        $response = new JsonResponse();
        
        if($form['horasMes'] == ""){
            $msg = "Debe ingresar las horas asignadas";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        } else {
            $horasMes = $form['horasMes'];
        }        
        
        if($form['financiamiento'] == ""){
            $msg = "Debe ingresar el financiamiento del item";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        } else {
            $financiamientoId = $form['financiamiento'];
        }
        if($form['fechaInicio'] == ""){
            $msg = "Debe ingresar la fecha inicial de la asignación";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        } else {
            $fechaInicio = new \DateTime($form['fechaInicio']);
        }
        if($form['fechaFin'] == ""){
            $msg = "Debe ingresar la fecha final de la asignación";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        } else {
            $fechaFin = new \DateTime($form['fechaFin']);
        }
        

        if($fechaInicio > $fechaFin){
            $msg = "Rango de fechas (".date_format($fechaInicio,'d-m-Y')." al ".date_format($fechaFin,'d-m-Y').") no valido, intente nuevamente";
            return $response->setData(array('estado'=>false, 'msg'=>$msg));
        }
        
        $msg = "";
        $estado = true;
       

        $em->getConnection()->beginTransaction();
        try {                

            $institucioneducativaCursoOfertaEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($institucioneducativaCursoOfertaId);
            if(count($institucioneducativaCursoOfertaEntity) <= 0){
                $msg = "No existe el curso al cual quiere registrar, intente nuevamente";
                return $response->setData(array('estado'=>false, 'msg'=>$msg));
            }
            
            $gestionId = $institucioneducativaCursoOfertaEntity->getInsitucioneducativaCurso()->getGestionTipo()->getId();
            $institucionEducativaId = $institucioneducativaCursoOfertaEntity->getInsitucioneducativaCurso()->getInstitucioneducativa()->getId();
            $asignaturaId = $institucioneducativaCursoOfertaEntity->getAsignaturaTipo()->getId();
            if($maestroInscripcionId == 0){
                $msg = "Debe seleccionar un maestro";
                return $response->setData(array('estado'=>false, 'msg'=>$msg));
            } else {
                $maestroInscripcionEntity = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($maestroInscripcionId);
            }
    
           /* 
           $notaTipoEntity = $em->getRepository('SieAppWebBundle:NotaTipo')->find($notaTipoId);
            if(count($notaTipoEntity) <= 0){
                $msg = "No existe el tipo de nota al cual quiere registrar, intente nuevamente";
                return $response->setData(array('estado'=>false, 'msg'=>$msg));
            }
            */
            $financiamientoTipoEntity = $em->getRepository('SieAppWebBundle:FinanciamientoTipo')->find($financiamientoId);
            if(count($financiamientoTipoEntity) <= 0){
                $msg = "No existe el tipo de financiamiento al cual quiere registrar, intente nuevamente";
                return $response->setData(array('estado'=>false, 'msg'=>$msg));
            }
    
            $institucioneducativaCursoOfertaMaestroEntity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findOneBy(array('maestroInscripcion' => $maestroInscripcionId, 'institucioneducativaCursoOferta' => $institucioneducativaCursoOfertaId ));//'notaTipo' => $notaTipoId
            
            if(count($institucioneducativaCursoOfertaMaestroEntity) > 0){ 
                $institucioneducativaCursoOfertaMaestroEntity->setFechaModificacion($fechaActual);    
                $institucioneducativaCursoOfertaMaestroEntity->setFechaModificacion($fechaActual);
            }
            else{ 
                $institucioneducativaCursoOfertaMaestroEntity = new InstitucioneducativaCursoOfertaMaestro();     
                $institucioneducativaCursoOfertaMaestroEntity->setInstitucionEducativaCursoOferta($institucioneducativaCursoOfertaEntity);
                $institucioneducativaCursoOfertaMaestroEntity->setMaestroInscripcion($maestroInscripcionEntity);           
                $institucioneducativaCursoOfertaMaestroEntity->setFechaRegistro($fechaActual);
                $institucioneducativaCursoOfertaMaestroEntity->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(0));
            }
            $institucioneducativaCursoOfertaMaestroEntity->setItem($item);
            $institucioneducativaCursoOfertaMaestroEntity->setHorasMes($horasMes);
            $institucioneducativaCursoOfertaMaestroEntity->setEsVigenteMaestro(true);
            $institucioneducativaCursoOfertaMaestroEntity->setFinanciamientoTipo($financiamientoTipoEntity);
            $institucioneducativaCursoOfertaMaestroEntity->setAsignacionFechaInicio($fechaInicio);
            $institucioneducativaCursoOfertaMaestroEntity->setAsignacionFechaFin($fechaFin);
            $institucioneducativaCursoOfertaMaestroEntity->setEsVigenteAdministrativo(true);
            $em->persist($institucioneducativaCursoOfertaMaestroEntity);
            $em->flush();
            $em->getConnection()->commit();

            $msg = "Datos registrados correctamente";
            $estado = true;
            $maestroAsignadoNombre = $institucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getNombre();
            $maestroAsignadoPaterno = $institucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getPaterno();
            $maestroAsignadoMaterno = $institucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getMaterno();
            $maestroAsignadoCI = $institucioneducativaCursoOfertaMaestroEntity->getMaestroInscripcion()->getPersona()->getCarnet();
            $maestroAsignadoId = base64_encode($institucioneducativaCursoOfertaMaestroEntity->getId());
            $maestroAsignadoItem = $institucioneducativaCursoOfertaMaestroEntity->getItem();
            $maestroAsignadoFinanciamiento = $institucioneducativaCursoOfertaMaestroEntity->getFinanciamientoTipo()->getFinanciamiento();
            $maestroAsignadoFinanciamientoId = $institucioneducativaCursoOfertaMaestroEntity->getFinanciamientoTipo()->getId();
            $maestroAsignadoFechaInicio = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaInicio(),'d-m-yy');
            $maestroAsignadoFechaFin = date_format($institucioneducativaCursoOfertaMaestroEntity->getAsignacionFechaFin(),'d-m-yy');
            $maestroAsignado = array('maestroOferta'=>$maestroAsignadoId,'maestroItem'=>$maestroAsignadoItem,'maestroNombre'=>$maestroAsignadoNombre,'maestroPaterno'=>$maestroAsignadoPaterno,'maestroMaterno'=>$maestroAsignadoMaterno,'maestroCI'=>$maestroAsignadoCI,'maestroFinanciamientoId'=>$maestroAsignadoFinanciamientoId,'maestroFinanciamiento'=>$maestroAsignadoFinanciamiento,'maestroFechaInicio'=>$maestroAsignadoFechaInicio,'maestroFechaFin'=>$maestroAsignadoFechaFin, 'maestroInscripcionId'=>base64_encode($maestroInscripcionId));
            return $response->setData(array('estado'=>$estado, 'msg'=>$msg, 'maestro'=>$maestroAsignado));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $msg = "Dificultades al realizar el registro, intente nuevamente";
            $estado = false;
            return $response->setData(array('estado'=>$estado, 'msg'=>$msg));
        }
    }

     //****************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que elimina la inscripcion del maestro en caso de no asignar a ningun paralelo
    // PARAMETROS: maestroInscripcionId
    // AUTOR: CVALLEJOS
    //****************************************************************************************************
    public function asignarMaestroMateriaEliminarAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $response = new JsonResponse();
        $em = $this->getDoctrine()->getManager();
        $id_iecom = base64_decode($request->get('ofertaMaestro'));
        $datoInstitucioneducativaCursoOfertaMaestro = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->find($id_iecom);//'notaTipo' => $notaTipoId

        if($datoInstitucioneducativaCursoOfertaMaestro){
            $datoInstitucioneducativaCursoOfertaMaestro->setEsVigenteAdministrativo(false);
            $datoInstitucioneducativaCursoOfertaMaestro->setEsVigenteMaestro(false);
            $datoInstitucioneducativaCursoOfertaMaestro->setFechaModificacion($fechaActual);
            $em->persist($datoInstitucioneducativaCursoOfertaMaestro);
            $em->flush();
            $msg = "El maestro ya no esta asignado";
            return $response->setData(array('estado'=>true, 'msg'=>$msg));
        }
        
    }

    

    public function operativo($sie,$gestion){
        $em = $this->getDoctrine()->getManager();
        // Obtenemos el operativo para bloquear los controles
        $registroOperativo = $em->createQueryBuilder()
                        ->select('rc.bim1,rc.bim2,rc.bim3,rc.bim4')
                        ->from('SieAppWebBundle:RegistroConsolidacion','rc')
                        ->where('rc.unidadEducativa = :ue')
                        ->andWhere('rc.gestion = :gestion')
                        ->setParameter('ue',$sie)
                        ->setParameter('gestion',$gestion)
                        ->getQuery()
                        ->getResult();
        if(!$registroOperativo){
            // Si no existe es operativo inicio de gestion
            $operativo = 1;
        }else{
            if($registroOperativo[0]['bim1'] == 0 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 1; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] == 0 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 2; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] == 0 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 3; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] == 0){
                $operativo = 4; // Primer Bimestre
            }
            if($registroOperativo[0]['bim1'] >= 1 and $registroOperativo[0]['bim2'] >= 1 and $registroOperativo[0]['bim3'] >= 1 and $registroOperativo[0]['bim4'] >= 1){
                $operativo = 5; // Fin de gestion o cerrado
            }
        }
        return $operativo;
    }

    public function literal($num){
        switch ($num) {
            case '0': $lit = 'Inicio de gestión'; break;
            case '1': $lit = '1er Bimestre'; break;
            case '2': $lit = '2do Bimestre'; break;
            case '3': $lit = '3er Bimestre'; break;
            case '4': $lit = '4to Bimestre'; break;
            case '6': $lit = '1er Trimestre'; break;
            case '7': $lit = '2do Trimestre'; break;
            case '8': $lit = '3er Trimestre'; break;
            case '18': $lit = 'Informe Final Inicial'; break;
            case '55': $lit = '1er Semestre'; break;
            case '56': $lit = '2do Semestre'; break;
            case '57': $lit = 'Anual'; break;
        }
        return $lit;
    }

    public function maestrosResponsableAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $gestion = $this->session->get('idGestion');
        $institucioneducativa = $request->get('centro_educativo');
        $maestrosArray = array();
        $ieresult = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id'=>$institucioneducativa, 'institucioneducativaTipo'=>4));
        if ($ieresult) {
            $ieactual = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id'=>$this->session->get('ie_id'), 'institucioneducativaTipo'=>4));
            //Validación de Distrito comentado
            // if ($ieresult->getLeJuridicciongeografica()->getLugarTipoIdDistrito() == $ieactual->getLeJuridicciongeografica()->getLugarTipoIdDistrito()) {
                $query = $em->getConnection()->prepare('SELECT mins.id, pers.nombre, pers.paterno, pers.materno FROM maestro_inscripcion mins
                INNER JOIN persona pers ON pers.id = mins.persona_id WHERE mins.estadomaestro_id = :estado
                AND mins.gestion_tipo_id = :gestion AND mins.institucioneducativa_id = :institucioneducativa and cargo_tipo_id=:cargo ORDER BY pers.paterno');
                $query->bindValue('estado', 1);
                $query->bindValue('gestion', $gestion);
                $query->bindValue('institucioneducativa', $institucioneducativa);
                $query->bindValue('cargo', 0);
                $query->execute();
                $maestros = $query->fetchAll();
            
                for ($i = 0; $i < count($maestros); $i++) {
                    $maestrosArray[$maestros[$i]['id']] = $maestros[$i]['paterno'].' '.$maestros[$i]['materno'].' '.$maestros[$i]['nombre'];
                }
                $msg = 'exito';
            // } else {
            //     $msg = 'nodist';
            // }
        } else {
            $msg = 'noinst';
        }
        $response = new JsonResponse();
        return $response->setData(array('msg' => $msg , 'maestros' => $maestrosArray));
    }

    public function getMaestrosInstitucionEducativaCursoOferta($institucionEducativaCursoOfertaId, $notaTipoId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
        $query = $entity->createQueryBuilder('iecom')
                ->select("distinct iecom.id as institucioneducativaCursoOfertaMaestroId, mi.id as maestroInscripcionId, mi.item as itemMaestro, nota.notaTipo, p.nombre, p.paterno, p.materno, p.carnet as carnetIdentidad, p.complemento, ft.financiamiento, ft.id as financiamientoId, iecom.asignacionFechaInicio, iecom.asignacionFechaFin, iecom.item, iecom.horasMes")                
                ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'mi.id = iecom.maestroInscripcion')
                ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'p.id = mi.persona')
                ->innerJoin('SieAppWebBundle:NotaTipo', 'nota', 'WITH', 'nota.id = iecom.notaTipo')
                ->leftJoin('SieAppWebBundle:FinanciamientoTipo', 'ft', 'WITH', 'ft.id = iecom.financiamientoTipo')
                ->where('iecom.institucioneducativaCursoOferta = :institucionEducativaCursoOfertaId')
                ->andWhere('nota.id = :notaTipoId')
              //  ->andWhere('iecom.esVigenteAdministrativo = true')
                ->setParameter('institucionEducativaCursoOfertaId', $institucionEducativaCursoOfertaId)
                ->setParameter('notaTipoId', $notaTipoId)
                ->orderBy('iecom.id', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

    public function getInstitucionEducativaCursoOferta($institucionEducativaCursoOfertaId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta');
        $query = $entity->createQueryBuilder('ieco')
                ->select("distinct ie.id as institucioneducativaId, ie.institucioneducativa, tt.turno, nt.nivel, gt.grado, pt.paralelo, at.asignatura, ept.programa, est.servicio, emt.modalidad, eat.areaEspecial")
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ieco.insitucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoEspecial', 'iece', 'WITH', 'iec.id = iece.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'at.id = ieco.asignaturaTipo')
                ->innerJoin('SieAppWebBundle:NivelTipo', 'nt', 'WITH', 'nt.id = iec.nivelTipo')
                ->innerJoin('SieAppWebBundle:TurnoTipo', 'tt', 'WITH', 'tt.id = iec.turnoTipo')
                ->innerJoin('SieAppWebBundle:GradoTipo', 'gt', 'WITH', 'gt.id = iec.gradoTipo')
                ->innerJoin('SieAppWebBundle:ParaleloTipo', 'pt', 'WITH', 'pt.id = iec.paraleloTipo')
                ->innerJoin('SieAppWebBundle:EspecialProgramaTipo', 'ept', 'WITH', 'ept.id = iece.especialProgramaTipo')
                ->innerJoin('SieAppWebBundle:EspecialServicioTipo', 'est', 'WITH', 'est.id = iece.especialServicioTipo')
                ->innerJoin('SieAppWebBundle:EspecialAreaTipo', 'eat', 'WITH', 'eat.id = iece.especialAreaTipo')
                ->innerJoin('SieAppWebBundle:EspecialModalidadTipo', 'emt', 'WITH', 'emt.id = iece.especialModalidadTipo')
                ->where('ieco.id = :institucionEducativaCursoOfertaId')
                ->setParameter('institucionEducativaCursoOfertaId', $institucionEducativaCursoOfertaId);
        $entity = $query->getQuery()->getResult();
        return $entity;
    }
    public function listaMaestroInscripcion($institucionEducativaId, $gestionId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $entity->createQueryBuilder('mi')
            ->select("distinct mi.id as maestroInscripcionId, p.nombre, p.paterno, p.materno, p.carnet as carnetIdentidad, p.complemento")                
            ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'p.id = mi.persona')             
            ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = mi.institucioneducativa')
            ->where('ie.id = :institucionEducativaId')
            ->andWhere('mi.gestionTipo = :gestionId')
            ->andWhere('mi.cargoTipo = 0')
            ->andWhere('p.id != 0')
            ->setParameter('institucionEducativaId', $institucionEducativaId)
            ->setParameter('gestionId', $gestionId)
            ->orderBy('p.paterno', 'ASC', 'p.materno', 'ASC', 'p.nombre', 'ASC');
        $entity = $query->getQuery()->getResult();
        return $entity;
    }
    public function getMaestroInscripcion($maestroInscripcioId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:MaestroInscripcion');
        $query = $entity->createQueryBuilder('mi')
            ->select("distinct mi.id as maestroInscripcionId, p.nombre, p.paterno, p.materno, p.carnet as carnetIdentidad, p.complemento")                
            ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'p.id = mi.persona')
            ->where('mi.id = :maestroInscripcionId')
            ->setParameter('maestroInscripcionId', $maestroInscripcioId);
        $entity = $query->getQuery()->getResult();
        return $entity;
    }
    public function getInstitucionEducativaCursoOfertaMaestroGestion($maestroInscripcioId, $gestionId){
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro');
        $query = $entity->createQueryBuilder('iecom')
                ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'mi.id = iecom.maestroInscripcion')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco', 'WITH', 'ieco.id = iecom.institucioneducativaCursoOferta')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ieco.insitucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = iec.gestionTipo')
                ->where('mi.id = :maestroInscripcioId')
                ->andWhere('gt.id = :gestionId')
                ->setParameter('maestroInscripcioId', $maestroInscripcioId)
                ->setParameter('gestionId', $gestionId);
        $entity = $query->getQuery()->getResult();
        return $entity;
    }

} 
