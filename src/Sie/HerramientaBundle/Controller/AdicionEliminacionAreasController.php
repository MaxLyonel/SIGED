<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityRepository;
use Sie\AppWebBundle\Entity\InstitucioneducativaCurso;
use Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;

/**
 * EstudianteInscripcion controller.
 *
 */
class AdicionEliminacionAreasController extends Controller {

    public $session;
    public $idInstitucion;

    public function __construct() {
        $this->session = new Session();
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
                case 'REGULAR': $this->session->set('tituloTipo', 'Adición/Eliminación de Áreas');
                    $this->session->set('layout', 'layoutRegular.html.twig');
                    break;
                case 'ALTERNATIVA': $this->session->set('tituloTipo', 'Adición de Áreas y Asignacion de Docentes');
                    $this->session->set('layout', 'layoutAlternativa.html.twig');
                    break;
                default: $this->session->set('tituloTipo', 'Áreas');
                    $this->session->set('layout', 'layoutRegular.html.twig');
                    break;
            }

            ////////////////////////////////////////////////////
            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                /**
                 * VErificamos si la gestion es 2015 
                 */
                if ($form['gestion'] != 2015) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'La gestión ingresada no es válida.');
                    return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                /*
                 * verificamos si existe la unidad educativa
                 */
                $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
                if (!$institucioneducativa) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                    return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                } else {
                    $this->get('session')->getFlashBag()->add('noTuicion', 'No tiene tuición sobre la unidad educativa');
                    return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
            } else {
                $nivelUsuario = $request->getSession()->get('roluser');
                if ($nivelUsuario != 9 && $nivelUsuario != 5) { // si no es institucion educativa 5 o administrativo 9
                    // formulario de busqueda de institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');

                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                        if ($op == 'search') {
                            return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                        }
                    } else {

                        return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                } else { // si es institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = $request->getSession()->get('idGestion');
                    } else {
                        $funcion = new \Sie\AppWebBundle\Controller\FuncionesController();
                        $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'), $request->getSession()->get('currentyear')); //5484231);
                        $gestion = $request->getSession()->get('currentyear');
                    }
                }
            }

            // creamos variables de sesion de la institucion educativa y gestion
            $request->getSession()->set('idInstitucion', $institucion);
            $request->getSession()->set('idGestion', $gestion);

            // Lista de turnos validos para la unidad educativa
            $query = $em->createQuery(
                            'SELECT DISTINCT tt.id,tt.turno
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.turnoTipo tt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    ORDER BY tt.id')
                    ->setParameter('id', $institucion)
                    ->setParameter('gestion', $gestion);
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
                    ->add('turno', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'choices' => $turnosArray, 'attr' => array('class' => 'form-control', 'onchange' => 'cargarNiveles()')))
                    ->add('nivel', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarGrados()')))
                    ->add('grado', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control', 'onchange' => 'cargarParalelos()')))
                    ->add('paralelo', 'choice', array('required' => true, 'empty_value' => 'Seleccionar...', 'attr' => array('class' => 'form-control','onchange' => 'validateForm()')))
                    ->add('buscar', 'submit', array('label' => 'Buscar Curso', 'attr' => array('class' => 'btn btn-info btn-block')))
                    ->getForm();
            /*
             * obtenemos los datos de la unidad educativa
             */
            $institucion = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($institucion);

            /*
             * Listasmos los maestros inscritos en la unidad educativa
             */
            $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $institucion, 'gestionTipo' => $gestion, 'cargoTipo' => 0));

            $em->getConnection()->commit();



            return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:index.html.twig', array(
                        'turnos' => $turnosArray, 'institucion' => $institucion, 'gestion' => $gestion, 'maestros' => $maestros, 'form' => $form->createView()
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }

    /*
     * Formulario de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {
        $gestiones = array($gestionactual => $gestionactual,$gestionactual-1 => $gestionactual-1);
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('adicioneliminacionareas'))
                ->add('institucioneducativa', 'text', array('required' => true, 'attr' => array('autocomplete' => 'off', 'maxlength' => 8)))
                ->add('gestion', 'choice', array('required' => true, 'choices' => $gestiones))
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

    public function cargarNivelesAction($idInstitucion, $gestion, $turno) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $query = $em->createQuery(
                            'SELECT DISTINCT nt.id,nt.nivel
                    FROM SieAppWebBundle:InstitucioneducativaCurso iec
                    JOIN iec.institucioneducativa ie
                    JOIN iec.nivelTipo nt
                    WHERE ie.id = :id
                    AND iec.gestionTipo = :gestion
                    AND iec.turnoTipo = :turno
                    ORDER BY nt.id')
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
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

    public function cargarGradosAction($idInstitucion, $gestion, $turno, $nivel) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
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
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
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

    public function cargarParalelosAction($idInstitucion, $gestion, $turno, $nivel, $grado) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
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
                    ->setParameter('id', $idInstitucion)
                    ->setParameter('gestion', $gestion)
                    ->setParameter('turno', $turno)
                    ->setParameter('nivel', $nivel)
                    ->setParameter('grado', $grado);
            $paralelos = $query->getResult();
            $paralelosArray = array();
            for ($i = 0; $i < count($paralelos); $i++) {
                $paralelosArray[$paralelos[$i]['id']] = $paralelos[$i]['paralelo'];
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

    public function lista_areas_nivelAction($idNivel, $idCurso) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $this->session = new Session();
            /*
             * Si el nivel es secundaria hacemos otras consultas 13
             */
            if ($idNivel == 13) {
                $institucionCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
                $grado = $institucionCurso->getGradoTipo()->getId();
                switch ($grado) {
                    case 1:
                    case 2: $consulta = 'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.asignaturaNivel = :idNivel
                                        AND at.id != 1045
                                        AND at.id != 1039
                                        AND at.id != 1041
                                        AND at.id != 1042
                                        ORDER BY at.id ASC';
                        break;
                    case 3:
                    case 4: $consulta = 'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.asignaturaNivel = :idNivel
                                        AND at.id != 1039
                                        AND at.id != 1041
                                        AND at.id != 1042
                                        ORDER BY at.id ASC';
                        break;
                    case 5:
                    case 6: $consulta = 'SELECT at
                                        FROM SieAppWebBundle:AsignaturaTipo at
                                        WHERE at.asignaturaNivel = :idNivel
                                        AND at.id != 1038
                                        AND at.id != 1041
                                        AND at.id != 1042
                                        ORDER BY at.id ASC';
                        break;
                }
                $query = $em->createQuery($consulta)->setParameter('idNivel', $idNivel);
                $areasNivel = $query->getResult();
            } else {
                $query = $em->createQuery(
                                'SELECT at
                                FROM SieAppWebBundle:AsignaturaTipo at
                                WHERE at.asignaturaNivel = :idNivel
                                ORDER BY at.id ASC')
                        ->setParameter('idNivel', $idNivel);
                $areasNivel = $query->getResult();
            }
            $areasCurso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findBy(array('insitucioneducativaCurso' => $idCurso));

            $areasArray = array();
            for ($i = 0; $i < count($areasNivel); $i++) {
                $check = '';
                $bloqueado = '';
                for ($j = 0; $j < count($areasCurso); $j++) {
                    if ($areasNivel[$i]->getId() == $areasCurso[$j]->getAsignaturaTipo()->getId()) {
                        $check = 'checked';
                        $bloqueado = 'disabled';
                    }
                }
                // Armamos el array solo con las areas que se pueden adicionar
                if ($check != 'checked') {
                    $areasArray[] = array('marcado' => $check, 'bloqueado' => $bloqueado, 'campo' => $areasNivel[$i]->getAreaTipo()->getArea(), 'codigo' => $areasNivel[$i]->getId(), 'asignatura' => $areasNivel[$i]->getAsignatura());
                }
            }
            $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $this->session->get('idInstitucion'), 'gestionTipo' => $this->session->get('idGestion'), 'cargoTipo' => 0));
            $em->getConnection()->commit();
            return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:listaAreas.html.twig', array('areasNivel' => $areasArray, 'maestros' => $maestros));
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
            //echo $request->get('divResultado')."<br>";
            //echo $request->get('idInstitucionCurso');
            $form = $request->get('form');
            if ($form['nivel'] != 11) {
                $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->findOneBy(array(
                    'institucioneducativa' => $form['idInstitucion'],
                    'gestionTipo' => $form['idGestion'],
                    'turnoTipo' => $form['turno'],
                    'nivelTipo' => $form['nivel'],
                    'gradoTipo' => $form['grado'],
                    'paraleloTipo' => $form['paralelo']));
                if ($curso) {
                    $idCurso = $curso->getId();
                    $mensaje = '';
                } else {
                    $mensaje = "No hay asignaturas";
                    //return $mensaje;
                }

                $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $request->getSession()->get('idInstitucion'), 'gestionTipo' => $request->getSession()->get('idGestion'), 'cargoTipo' => 0));
                $areasCurso = $em->createQuery(
                                        'SELECT DISTINCT at.id FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                    INNER JOIN ieco.asignaturaTipo at
                    WHERE ieco.insitucioneducativaCurso = :iecId
                    ORDER BY at.id ASC')
                                ->setParameter('iecId', $idCurso)->getResult();

                $totalAreasCurso = $em->createQueryBuilder()
                        ->select('ieco.id, areat.area, at.id as idAsignatura, at.asignatura, mi.id as idMaestro')
                        ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                        ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                        ->innerJoin('SieAppWebBundle:AreaTipo', 'areat', 'WITH', 'at.areaTipo = areat.id')
                        ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'ieco.maestroInscripcion = mi.id')
                        ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                        ->where('ieco.insitucioneducativaCurso = :idCurso')
                        ->setParameter('idCurso', $idCurso)
                        ->getQuery()
                        ->getResult();
                $array = array();

                foreach ($totalAreasCurso as $tac) {
                    $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                        'area' => $tac['area'],
                        'idAsignatura' => $tac['idAsignatura'],
                        'asignatura' => $tac['asignatura'],
                        'idMaestro' => $tac['idMaestro']);
                }
                $areasCurso = $array;

                $em->getConnection()->commit();
                return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:listaAreasCurso.html.twig', array(
                            'areasCurso' => $areasCurso,
                            'maestros' => $maestros,
                            'curso' => $curso,
                            'mensaje' => $mensaje,
                            'form' => $this->createFormToBuild($form['idInstitucion'], $form['idGestion'], '4')->createView()
                ));
            } else {
                echo "La adición de areas no se puede aplicar a nivel Inicial";
                die;
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
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);
            /*
             * Areas a registrar nuevos
             */
            $areas = $request->get('areas');
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
                if ($existe == 'no') {
                    //echo $areas[$i]." - ".$request->get('idInstitucionCurso')."<br>";
                    $newArea = new InstitucioneducativaCursoOferta();
                    $newArea->setAsignaturaTipo($em->getRepository('SieAppWebBundle:AsignaturaTipo')->find($areas[$i]));
                    $newArea->setInsitucioneducativaCurso($em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($request->get('idInstitucionCurso')));
                    if ($request->get($areas[$i])) {
                        $idMaestroInscripcion = $request->get($areas[$i]);
                        $newArea->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idMaestroInscripcion));
                    }
                    $newArea->setHorasmes(0);
                    $newArea->setAsignaturaEspecialidadTipo($em->getRepository('SieAppWebBundle:AsignaturaEspecialidadTipo')->find(0));
                    $newArea->setNivelIdTec(30);
                    $newArea->setCicloIdTec(0);
                    $em->persist($newArea);
                    $em->flush();


                    // Actualizamos el id de la tabla estudiante asignatura
                    $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_asignatura');");
                    $query->execute();
                    // Listamos los estudinates inscritos
                    // para registrar el curso a los estudiantes
                    $inscritos = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findBy(array('institucioneducativaCurso' => $idCurso));
                    foreach ($inscritos as $ins) {
                        // Verificamos si el estudiante ya tiene la asignatura
                        $estInscripcion = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneBy(array('estudianteInscripcion'=>$ins->getId(),'institucioneducativaCursoOferta'=>$newArea->getId()));
                        if(!$estInscripcion){
                            //echo "agregando notas a ". $ins->getEstudiante()->getNombre() ." <br>";
                            
                            $estAsignaturaNew = new EstudianteAsignatura();
                            $estAsignaturaNew->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->find($this->session->get('idGestion')));
                            $estAsignaturaNew->setFerchaLastUpdate(new \DateTime('now'));
                            $estAsignaturaNew->setVersion(0);
                            $estAsignaturaNew->setRevisionId(0);
                            $estAsignaturaNew->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($ins->getId()));
                            $estAsignaturaNew->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($newArea->getId()));
                            $em->persist($estAsignaturaNew);
                            $em->flush();

                            // Actualizamos el id de la tabla estudiante asignatura
                            $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('estudiante_nota');");
                            $query->execute();
                            // Registramos las notas cuantitativas hasta el tercer bimestre
                            for($j=1;$j<=3;$j++){
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
                            }
                        }
                    }
                }
            }
            $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $request->getSession()->get('idInstitucion'), 'gestionTipo' => $request->getSession()->get('idGestion'), 'cargoTipo' => 0));
            $areasCurso = $em->createQuery(
                                    'SELECT DISTINCT at.id FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                INNER JOIN ieco.asignaturaTipo at
                WHERE ieco.insitucioneducativaCurso = :iecId
                ORDER BY at.id ASC')
                            ->setParameter('iecId', $idCurso)->getResult();

            $totalAreasCurso = $em->createQueryBuilder()
                    ->select('ieco.id, areat.area, at.id as idAsignatura, at.asignatura, mi.id as idMaestro')
                    ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                    ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                    ->innerJoin('SieAppWebBundle:AreaTipo', 'areat', 'WITH', 'at.areaTipo = areat.id')
                    ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'ieco.maestroInscripcion = mi.id')
                    ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                    ->where('ieco.insitucioneducativaCurso = :idCurso')
                    ->setParameter('idCurso', $idCurso)
                    ->getQuery()
                    ->getResult();
            $array = array();

            foreach ($totalAreasCurso as $tac) {
                $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                    'area' => $tac['area'],
                    'idAsignatura' => $tac['idAsignatura'],
                    'asignatura' => $tac['asignatura'],
                    'idMaestro' => $tac['idMaestro']);
            }
            $areasCurso = $array;

            $em->getConnection()->commit();
            return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:listaAreasCurso.html.twig', array('areasCurso' => $areasCurso, 'maestros' => $maestros, 'curso' => $curso, 'mensaje' => '','form' => $this->createFormToBuild($this->session->get('idInstitucion'), $this->session->get('idGestion'), '4')->createView()));
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
            /**
             * Si existe el curso y el curso oferta entonces eliminamos el curso oferta
             */
            if ($cursoOferta and $curso) {
                //Verificamos si el curso oferta no tiene estudidantes asociados en la tabla estudiante asignatura
                /* $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findBy(array('institucioneducativaCursoOferta'=>$cursoOferta->getId()));
                  if($estudianteAsignatura){
                  $mensaje='No se puede eliminar el área, porque tiene estudiantes asociados';
                  $eliminar='no';
                  } */


                // VErificamos si tiene maestro asignado
                /* if($cursoOferta->getMaestroInscripcion() != null){ 
                  $mensaje = 'No se puede eliminar el área si tiene un maestro asignado, primero debe quitar el maestro';
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

                    // Registramos en la tabla de control
                    // Eliminamos el curso oferta

                    $cursoOfertaEliminar = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($co->getId());
                    $em->remove($cursoOfertaEliminar);
                    
                    
                }
                $em->flush();
                $mensaje = 'Se elimino el área del curso';
            } else {
                $mensaje = 'No se puede eliminar el área';
            }
            
            $this->session = new Session();

            $maestros = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->findBy(array('institucioneducativa' => $curso->getInstitucioneducativa()->getId(), 'gestionTipo' => $this->session->get('idGestion'), 'cargoTipo' => 0));
            $areasCurso = $em->createQuery(
                                    'SELECT DISTINCT at.id FROM SieAppWebBundle:InstitucioneducativaCursoOferta ieco
                INNER JOIN ieco.asignaturaTipo at
                WHERE ieco.insitucioneducativaCurso = :iecId
                ORDER BY at.id ASC')
                            ->setParameter('iecId', $curso->getId())->getResult();

            $totalAreasCurso = $em->createQueryBuilder()
                    ->select('ieco.id, areat.area, at.id as idAsignatura, at.asignatura, mi.id as idMaestro')
                    ->from('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ieco')
                    ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at', 'WITH', 'ieco.asignaturaTipo = at.id')
                    ->innerJoin('SieAppWebBundle:AreaTipo', 'areat', 'WITH', 'at.areaTipo = areat.id')
                    ->innerJoin('SieAppWebBundle:MaestroInscripcion', 'mi', 'WITH', 'ieco.maestroInscripcion = mi.id')
                    ->innerJoin('SieAppWebBundle:Persona', 'p', 'WITH', 'mi.persona = p.id')
                    ->where('ieco.insitucioneducativaCurso = :idCurso')
                    ->setParameter('idCurso', $curso->getId())
                    ->getQuery()
                    ->getResult();
            $array = array();

            foreach ($totalAreasCurso as $tac) {
                $array[$tac['idAsignatura']] = array('id' => $tac['id'],
                    'area' => $tac['area'],
                    'idAsignatura' => $tac['idAsignatura'],
                    'asignatura' => $tac['asignatura'],
                    'idMaestro' => $tac['idMaestro']);
            }
            $areasCurso = $array;

            $em->getConnection()->commit();
            return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:listaAreasCurso.html.twig', array('areasCurso' => $areasCurso, 'maestros' => $maestros, 'curso' => $curso, 'mensaje' => $mensaje,'form' => $this->createFormToBuild($this->session->get('idInstitucion'), $this->session->get('idGestion'), '4')->createView()));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Asignar maestro al area
     */

    public function asignar_maestroAction($idCursoOferta, $idMaestro) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $cursoOferta = $em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idCursoOferta);
            //$cursoOferta = new InstitucioneducativaCursoOferta();
            if ($idMaestro != 'ninguno') {
                $maestro = $em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idMaestro);
                $cursoOferta->setMaestroInscripcion($maestro);
                $nombreMaestro = $maestro->getPersona()->getPaterno() . ' ' . $maestro->getPersona()->getMaterno() . ' ' . $maestro->getPersona()->getNombre();
            } else {
                $cursoOferta->setMaestroInscripcion(null);
                $nombreMaestro = '';
            }
            $em->flush();

            $curso = $cursoOferta->getAsignaturaTipo()->getAsignatura();
            $em->getConnection()->commit();
            $response = new JsonResponse();
            return $response->setData(array('maestro' => $nombreMaestro, 'curso' => $curso));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
            return $ex;
        }
    }

    public function buildAction(Request $request) {

        $form['sie'] = $request->get('sie');
        $form['gestion'] = $request->get('gestion');
        $form['bimestre'] = $request->get('bimestre');

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        try {
            //get the content of directory 
            $aDirectoryContent = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';

            //generate to file with thwe sql process
            $operativo = $form['bimestre'] + 1;
            $query = $em->getConnection()->prepare("select * from sp_genera_arch_regular_txt('" . $form['sie'] . "','" . $form['gestion'] . "','" . $operativo . "','" . $form['bimestre'] . "');");
            $query->execute();
            $em->getConnection()->commit();
            $em->flush();
            $em->clear();

            //todo the connexion to the server
            $connection = ssh2_connect('172.20.0.103', 22);
            ssh2_auth_password($connection, 'root', 'ASDFqwe12.103');
            $sftp = ssh2_sftp($connection);
            //get the path server
            $path = '../bajada_local/';
            //ssh2_exec($connection, 'iconv -f UTF-8  -t ISO-8859-1 ' . $path . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie  >> ' . $path . 'ee' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
            ssh2_exec($connection, 'base64  ' . $path . '' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie  >> ' . $path . 'e' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
            //ssh2_exec($connection, 'cp ' . $path . '' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie   ' . $path . 'e' . $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B.sie');
            /////////////////////////////////
            $server = "172.20.0.103"; //address of ftp server (leave out ftp://)
            $ftp_user_name = "regulardb"; // Username
            $ftp_user_pass = "regular2015v4azx-"; // Password

            $mode = "FTP_BINARY";
            $conn = ftp_connect($server, 21);
            $login = ftp_login($conn, $ftp_user_name, $ftp_user_pass);

            if (!$conn || !$login) {
                die("Connection attempt failed!");
            }
            // try to download $server_file and save to $local_file
            $newGenerateFile = $form['sie'] . '-' . $form['gestion'] . '-' . date('m-d') . '_' . $form['bimestre'] . 'B';
            $local_file = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/' . 'e' . $newGenerateFile . '.sie';
            $server_file = 'e' . $newGenerateFile . '.sie';

            if (ftp_get($conn, $local_file, $server_file, FTP_BINARY)) {
                //echo "generado correctamente to $local_file\n";
            } else {
                echo "There was a problem in conexion server\n :(";
            }
            $dir = $this->get('kernel')->getRootDir() . '/../web/downloadempfiles/';

            exec('zip -P 3I35I3Client ' . $dir . $newGenerateFile . '.zip ' . $dir . 'e' . $newGenerateFile . '.sie');
            exec('mv ' . $dir . $newGenerateFile . '.zip ' . $dir . $newGenerateFile . '.igm ');
            ssh2_sftp_unlink($sftp, '/bajada_local/' . $server_file);
            //system('rm -fr ' . $dir . $newGenerateFile . '.igm ');
            system('rm -fr ' . $dir . $server_file);
            ftp_close($conn);

            //echo "done";
            $objUe = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getUnidadEducativaInfo($form['sie']);

            return $this->render('SieHerramientaBundle:AdicionEliminacionAreas:fileDownload.html.twig', array(
                        'uEducativa' => $objUe[0],
                        'file' => $newGenerateFile . '.igm',
                            //'form' => $this->createFormToBuild($form['sie'], $form['gestion'], $form['bimestre'])->createView()
            ));
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            $em->getConnection()->rollback();
            $em->close();
            throw $e;
        }
    }

}
