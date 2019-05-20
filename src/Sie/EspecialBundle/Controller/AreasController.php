<?php

namespace Sie\EspecialBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
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
            ->setParameter('gestion', $gestion)
            // ->setParameter('areas', array(1,2,3,5))
            ->setParameter('areas', array(1,2,3,4,5,6))
            ->addOrderBy('eat.id')
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
                            'SELECT DISTINCT iec.id,pt.paralelo,ser.servicio,pro.programa
                    FROM SieAppWebBundle:InstitucioneducativaCursoEspecial iece
            		JOIN iece.institucioneducativaCurso iec
            		JOIN iec.institucioneducativa ie
                    JOIN iec.paraleloTipo pt
            		JOIN iece.especialServicioTipo ser
            		JOIN iece.especialProgramaTipo pro
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
                    ->setParameter('programa', array(17));
                    
            $paralelos = $query->getResult();

            $paralelosArray = array();

            for ($i = 0; $i < count($paralelos); $i++) {
                $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'] . '/' . $paralelos[$i]['servicio'] . '/' . $paralelos[$i]['programa'];
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
            if($idarea == 2 or $idarea == 4){
                $esvisual = true;
            }else{
                $esvisual = false;
            }
            //dump($institucionCursoEspecial);die;
            $grado = $institucionCurso->getGradoTipo()->getId();
            $asignaturas = null;
            
            switch($idNivel){
                case 401:   switch ($grado) {
                                case 1:
                                case 2:
                                    $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.id IN (:ids)
                                    ORDER BY at.id ASC'
                                    )->setParameter('ids',array(464,465,466,467))
                                    ->getResult();
                                    break;
                             /*    case 3:
                                case 4:
                                case 5:
                                    $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.id IN (:ids)
                                    ORDER BY at.id ASC'
                                    )->setParameter('ids',array(468,469,470,471,472,473,474))
                                    ->getResult();
                                    break; */
                            }
                            break;
                case 402: $asignaturas = $em->createQuery(
                            'SELECT at
                            FROM SieAppWebBundle:AsignaturaTipo at
                            WHERE at.id IN (:ids)
                            ORDER BY at.id ASC'
                            )->setParameter('ids',array(468,469,470,471,472,473,474))
                            ->getResult();
                            break;
            	case 411:   $programa = $institucionCursoEspecial->getEspecialProgramaTipo()->getId();
                            switch($programa){
                                case 7:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(475,476,477,483))
                                    ->getResult();
                                    break;
                                case 8:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(482,483,490,491))
                                    ->getResult();
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
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(493,485,486))
                                    ->getResult();
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
                                case 18:
                                    $asignaturas = $em->createQuery(
                                            'SELECT at
                                            FROM SieAppWebBundle:AsignaturaTipo at
                                            WHERE at.id IN (:ids)
                                            ORDER BY at.id ASC'
                                    )->setParameter('ids',array(496))
                                    ->getResult();
                                    break;
                            }
                            break;
                case 410:   $servicio = $institucionCursoEspecial->getEspecialServicioTipo()->getId();
                            switch($servicio){
                                case 20:    $asignaturas = $em->createQuery(
                                                'SELECT at
                                                FROM SieAppWebBundle:AsignaturaTipo at
                                                WHERE at.id IN (:ids)
                                                ORDER BY at.id ASC'
                                                )->setParameter('ids',array(497))
                                                ->getResult();
                                            break;
                                case 21:    $asignaturas = $em->createQuery(
                                                'SELECT at
                                                FROM SieAppWebBundle:AsignaturaTipo at
                                                WHERE at.id IN (:ids)
                                                ORDER BY at.id ASC'
                                                )->setParameter('ids',array(498))
                                                ->getResult();
                                            break;
                            }
                            break;
            	case 11:    $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivel = :idNivel
                                    AND at.id IN (:ids)
                                    ORDER BY at.id ASC'
                            )->setParameter('idNivel', $idNivel)
                            ->setParameter('ids',array(1000,1001,1002,1003))
                            ->getResult();
                            break;
                case 403:    $asignaturas = $em->createQuery(
                            'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivel IN (:idNivel)
                                    AND at.id IN (:ids)
                                    ORDER BY at.id ASC'
                            		)->setParameter('idNivel', array(11, 12))
                            		->setParameter('ids',array(1000,1001,1002,1003,32832))
                            		->getResult();
                            		break;


                case 12:    $asignaturas = $em->createQuery(
                                    'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivel = :idNivel
                                    AND at.id IN (:ids)
                                    ORDER BY at.id ASC'
                            )->setParameter('idNivel', $idNivel)
                            ->setParameter('ids',array(1011,1012,1013,1014,1015,1016,1017,1018,1019))
                            ->getResult();
                            break;
                case 404:   $asignaturas = $em->createQuery(
                            'SELECT at
                                    FROM SieAppWebBundle:AsignaturaTipo at
                                    WHERE at.asignaturaNivel = :idNivel
                                    AND at.id IN (:ids)
                                    ORDER BY at.id ASC'
                            		)->setParameter('idNivel', 12)
                            		->setParameter('ids',array(1011,1012,1013,1014,1015,1016,1017,1018,1019,32832))
                            		->getResult();
                            		break;


                case 13:    switch ($grado) {
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
           ;


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
                    $areasArray[] = array('marcado' => $check, 'bloqueado' => $bloqueado, 'campo' => ($areasNivel[$i]->getAreaTipo()) ? $areasNivel[$i]->getAreaTipo()->getArea() : "", 'codigo' => $areasNivel[$i]->getId(), 'asignatura' => $areasNivel[$i]->getAsignatura());
                }
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
                           ->andWhere('ct.id = :cargoTipo')
                           ->setParameter('idInstitucion',$this->session->get('idInstitucion'))
                           ->setParameter('gestion',$this->session->get('idGestion'))
                           ->setParameter('rol',2)
                           ->setParameter('cargoTipo',$mTipo)
                           ->orderBy('p.paterno','asc')
                           ->addOrderBy('p.materno','asc')
                           ->addOrderBy('p.nombre','asc')
                           ->getQuery()
                           ->getResult();
            $em->getConnection()->commit();
            return $this->render('SieEspecialBundle:Areas:listaAreas.html.twig', array('areasNivel' => $areasArray, 'maestros' => $maestros,'esvisual'=>$esvisual));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
    }

    /*
     * Registrar las areas seleccionadas y listar las nuevas areas del curso
     */

    public function lista_areas_cursoAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            //dump($form);die;
            if($form['idArea'] == 2 or $form['idArea'] == 4){
                $esvisual = true;
            }else{
                $esvisual = false;
            }
            
            if ($form['nivel'] != 10) {
                $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneById($form['paralelo']);
                if ($curso) {
                    $idCurso = $curso->getId();
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
                        ->select('ieco.id, areat.area, at.id as idAsignatura, at.asignatura')
                        ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                        ->leftJoin('SieAppWebBundle:AreaTipo', 'areat', 'WITH', 'at.areaTipo = areat.id')
                        ->where('ieco.insitucioneducativaCurso = :idCurso')
                        ->setParameter('idCurso', $idCurso)
                        ->orderBy('at.id','ASC')
                        ->getQuery()
                        ->getResult();

                $array = array();
                foreach ($totalAreasCurso as $tac) {
                    $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                        'area' => ($tac['area']) ? $tac['area'] : "",
                        'idAsignatura' => $tac['idAsignatura'],
                        'asignatura' => $tac['asignatura']);
                }
                $areasCurso = $array;
                $em->getConnection()->commit();
                return $this->render('SieEspecialBundle:Areas:listaAreasCurso.html.twig', array(
                            'areasCurso' => $areasCurso,
                            'curso' => $curso,
                            'mensaje' => $mensaje,
                            'esvisual' => $esvisual,
                            'form' => $this->createFormToBuild($form['idInstitucion'], $form['idGestion'], '4')->createView()
                ));
            } else {
                echo "La adición de áreas no se puede aplicar a nivel Inicial";
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
            $idMaestroResponsable = $request->get('maestro_responsable');
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
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
            
            if($idEspecialArea == 2 or $idEspecialArea == 4){
                $esvisual = true;
            }else{
                $esvisual = false;
            }
            
            /*
             * Areas a registrar nuevos
             */
            $areas = $request->get('areas');//
            //dump($areas);die;
            /*
             * Areas registradas anteriormente
             */
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
                         $em->persist($nuevoCOM);
                         $em->flush();
                    }

                    // Listamos los estudinates inscritos
                    // para registrar el curso a los estudiantes
                    $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso' => $idCurso, 'estadomatriculaTipo'=>array(4,5,11)));
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
                    ->select('ieco.id, areat.area, at.id as idAsignatura, at.asignatura')
                    ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                    ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                    ->innerJoin('SieAppWebBundle:AreaTipo', 'areat', 'WITH', 'at.areaTipo = areat.id')
                    ->where('ieco.insitucioneducativaCurso = :idCurso')
                    ->setParameter('idCurso', $idCurso)
                    ->orderBy('at.id','ASC')
                    ->getQuery()
                    ->getResult();
            $array = array();
            foreach ($totalAreasCurso as $tac) {
                $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                    'area' => $tac['area'],
                    'idAsignatura' => $tac['idAsignatura'],
                    'asignatura' => $tac['asignatura']);
            }
            $areasCurso = $array;

            $em->getConnection()->commit();
            return $this->render('SieEspecialBundle:Areas:listaAreasCurso.html.twig', array(
                    'areasCurso'    => $areasCurso, 
                    'curso'         => $curso, 
                    'mensaje'       => '',
                    'esvisual'      => $esvisual,
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
                    $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                        'area' => $tac['area'],
                        'idAsignatura' => $tac['idAsignatura'],
                        'asignatura' => $tac['asignatura']);
                }
                $areasCurso = $array;

            $em->getConnection()->commit();
            return $this->render('SieEspecialBundle:Areas:listaAreasCurso.html.twig', array(
                'areasCurso'    => $areasCurso, 
                'curso'         => $curso, 
                'mensaje'       => $mensaje,
                'esvisual'      => $esvisual,
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

        $em = $this->getDoctrine()->getManager();
        $maestrosMateria = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOfertaMaestro')->findBy(array('institucioneducativaCursoOferta'=>$ieco));

        // Obtener datos del curso
        $curso = $em->createQueryBuilder()
                    ->select('ie.id as sie, gt.id as gestion,iec.lugar')
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
        if($gestion <= 2012 or ($gestion == 2013 and $grado > 1 and $nivel != 11) or ($gestion == 2013 and $grado == (1 or 2 ) and $nivel == 11) ){
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
            $idcargo = 15;
        }else{
            $idcargo = 0;
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
                        ->andWhere('ct.id = :idcargo')
                        ->orderBy('p.paterno','ASC')
                        ->addOrderBy('p.materno','ASC')
                        ->addOrderBy('p.nombre','ASC')
                        ->setParameter('sie',$sie)
                        ->setParameter('gestion',$gestion)
                        ->setParameter('idcargo',$idcargo)
                        ->getQuery()
                        ->getResult();

        $operativo = $this->operativo($sie,$gestion);

        return $this->render('SieEspecialBundle:Areas:maestros.html.twig',array('maestrosCursoOferta'=>$arrayMaestros, 'maestros'=>$maestros,'ieco'=>$ieco,'operativo'=>$operativo));
    }

    public function maestrosAsignarAction(Request $request){
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
            $operativo = 0;
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
        }
        $response = new JsonResponse();
        return $response->setData(array('maestros' => $maestrosArray));
    }
}
