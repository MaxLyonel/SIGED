<?php

namespace Sie\RegularBundle\Controller;

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
 * EstudianteInscripcion controller.
 *
 */
class AreasController extends Controller {

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

            if ($request->getMethod() == 'POST') { // si los valores fueron enviados desde el formulario
                $form = $request->get('form');
                /**
                 * VErificamos si la gestion es 2015
                 */
                if ($form['gestion'] < 2008 or $form['gestion'] > 2017) {
                
                    $this->get('session')->getFlashBag()->add('noSearch', 'La gestión ingresada no es válida.');
                    return $this->render('SieRegularBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                }
                /*
                 * verificamos si existe la unidad educativa
                 */
                $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneById($form['institucioneducativa']);
                if (!$institucioneducativa) {
                    $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
                    return $this->render('SieRegularBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                    return $this->render('SieRegularBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
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
                            return $this->render('SieRegularBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                        }
                    } else {

                        return $this->render('SieRegularBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
                    }
                } else { // si es institucion educativa
                    $sesinst = $request->getSession()->get('idInstitucion');
                    if ($sesinst) {
                        $institucion = $sesinst;
                        $gestion = date('Y') - 1;
                    } else {
                        $funcion = new \Sie\AppWebBundle\Controller\FuncionesController();
                        $institucion = $funcion->ObtenerUnidadEducativaAction($request->getSession()->get('userId'), $request->getSession()->get('currentyear')); //5484231);
                        $gestion = date('Y') - 1;
                        //echo $institucion;die;
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
            $tipoUE = $this->get('funciones')->getTipoUE($institucion,$gestion);

            $em->getConnection()->commit();



            return $this->render('SieRegularBundle:Areas:index.html.twig', array(
                        'turnos' => $turnosArray, 'institucion' => $institucion, 'gestion' => $gestion, 'tipoUE'=>$tipoUE , 'form' => $form->createView()
            ));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
            $this->get('session')->getFlashBag()->add('noSearch', 'El codigo ingresado no es válido');
            return $this->render('SieRegularBundle:Areas:search.html.twig', array('form' => $this->formSearch($request->getSession()->get('currentyear'))->createView()));
        }
    }

    /*
     * Formulario de busqueda de institucion educativa
     */

    private function formSearch($gestionactual) {
        $gestiones = array();
        for($i=$gestionactual;$i>=2008;$i--){
            $gestiones[$i] = $i;
        }
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl('areas'))
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
     * Registrar las areas seleccionadas y listar las nuevas areas del curso
     */

    public function lista_areas_cursoAction(Request $request) {
        try {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            //echo $request->get('divResultado')."<br>";
            //echo $request->get('idInstitucionCurso');
            $form = $request->get('form');
            if ($form['nivel'] != 10) {
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

                $areasCurso = $this->get('areas')->getAreas($idCurso);
                $operativo = $this->get('funciones')->obtenerOperativo($form['idInstitucion'],$form['idGestion']);

                $em->getConnection()->commit();
                return $this->render('SieRegularBundle:Areas:listaAreasCurso.html.twig', array(
                            'areas' => $areasCurso,
                            'curso' => $curso,
                            'mensaje' => $mensaje,
                            'operativo'=>$operativo
                ));
            } else {
                echo "La adición de areas no se puede aplicar a nivel Inicial";
                die;
            }
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

            $areas = $this->get('areas')->getAreas($idCurso);
            $idsAsignaturas = $areas['asignaturas'];

            $asignaturas = $em->createQuery(
                    'SELECT at
                    FROM SieAppWebBundle:AsignaturaTipo at
                    WHERE at.id IN (:ids)
                    ORDER BY at.id ASC'
            )->setParameter('ids',$idsAsignaturas)
            ->getResult();

            $areasNivel = $asignaturas;
            $areasCurso = $areas['cursoOferta'];

            $areasArray = array();
            for ($i = 0; $i < count($areasNivel); $i++) {
                $check = '';
                $bloqueado = '';
                for ($j = 0; $j < count($areasCurso); $j++) {
                    if ($areasNivel[$i]->getId() == $areasCurso[$j]['idAsignatura']) {
                        $check = 'checked';
                        $bloqueado = 'disabled';
                    }
                }
                // Armamos el array solo con las areas que se pueden adicionar
                if ($check != 'checked') {
                    $areasArray[] = array('marcado' => $check, 'bloqueado' => $bloqueado, 'codigo' => $areasNivel[$i]->getId(), 'asignatura' => $areasNivel[$i]->getAsignatura());
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
                           ->setParameter('idInstitucion',$this->session->get('idInstitucion'))
                           ->setParameter('gestion',$this->session->get('idGestion'))
                           ->setParameter('rol',2)
                           ->orderBy('p.paterno','asc')
                           ->addOrderBy('p.materno','asc')
                           ->addOrderBy('p.nombre','asc')
                           ->getQuery()
                           ->getResult();

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:Areas:listaAreas.html.twig', array('areasNivel' => $areasArray, 'maestros' => $maestros));
            //return $this->render('SieRegularBundle:Areas:show.html.twig', array('areasNivel' => $areasArray, 'maestros' => $maestros));
        } catch (Exception $ex) {
            //$em->getConnection()->rollback();
        }
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
                /*if($this->session->get('idGestion') == 2016 and $areas[$i] == 1039){
                    $existe = 'si';
                }*/
                if ($existe == 'no') {

                    // Registro de la nueva area
                    $nuevoRegistro = $this->get('areas')->nuevo($request->get('idInstitucionCurso'),$areas[$i]);

                    $data = $nuevoRegistro->getContent();
                    $data = json_decode($data,true);

                    $idNewArea = $data['idNewArea'];



                    // Verificamos si registraron el maestro pra registrarlo en la tabla curso oferta maestro
                    if ($request->get($areas[$i])) {
                         $idMaestroInscripcion = $request->get($areas[$i]);

                         $query = $em->getConnection()->prepare("select * from sp_reinicia_secuencia('institucioneducativa_curso_oferta_maestro');")->execute();
                         $nuevoCOM = new InstitucioneducativaCursoOfertaMaestro();
                         $nuevoCOM->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->find($idNewArea));
                         $nuevoCOM->setMaestroInscripcion($em->getRepository('SieAppWebBundle:MaestroInscripcion')->find($idMaestroInscripcion));
                         $nuevoCOM->setHorasMes(0);
                         $nuevoCOM->setFechaRegistro(new \DateTime('now'));
                         $nuevoCOM->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->find(0));
                         $nuevoCOM->setEsVigenteMaestro('t');
                         $em->persist($nuevoCOM);
                         $em->flush();
                    }
                }
            }

            $areasCurso = $this->get('areas')->getAreas($idCurso);
            $operativo = $this->get('funciones')->obtenerOperativo($curso->getInstitucioneducativa()->getId(),$curso->getGestionTipo()->getId());

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:Areas:listaAreasCurso.html.twig', array(
                        'areas' => $areasCurso,
                        'curso' => $curso,
                        'mensaje' => '',
                        'operativo'=>$operativo
            ));

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:Areas:listaAreasCurso.html.twig', array('areasCurso' => $areasCurso, 'curso' => $curso, 'mensaje' => ''));
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    public function nuevaAction($idCurso,$idAsignatura){
        try {
            $em = $this->getDoctrine()->getManager();
            $curso = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso')->find($idCurso);

            $nuevoRegistro = $this->get('areas')->nuevo($idCurso,$idAsignatura);

            $areasCurso = $this->get('areas')->getAreas($idCurso);

            $operativo = $this->get('funciones')->obtenerOperativo($curso->getInstitucioneducativa()->getId(),$curso->getGestionTipo()->getId());

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:Areas:listaAreasCurso.html.twig', array(
                        'areas' => $areasCurso,
                        'curso' => $curso,
                        'mensaje' => '',
                        'operativo'=>$operativo
            ));

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:Areas:listaAreasCurso.html.twig', array('areasCurso' => $areasCurso, 'curso' => $curso, 'mensaje' => ''));
        } catch (Exception $e) {
            return null;
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

            $eliminar = $this->get('areas')->delete($idCursoOferta);

            $idCurso = $curso->getId();

            $areasCurso = $this->get('areas')->getAreas($idCurso);
            $operativo = $this->get('funciones')->obtenerOperativo($curso->getInstitucioneducativa()->getId(),$curso->getGestionTipo()->getId());

            $em->getConnection()->commit();
            return $this->render('SieRegularBundle:Areas:listaAreasCurso.html.twig', array(
                        'areas' => $areasCurso,
                        'curso' => $curso,
                        'mensaje' => '',
                        'operativo'=>$operativo
            ));

        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }
    }

    /*
     * Asignar maestro al area
     */

    public function maestrosAction(Request $request){
        $ieco = $request->get('idco');

        $maestros = $this->get('maestroAsignacion')->listar($ieco);

        return $this->render('SieRegularBundle:Areas:maestros.html.twig',array('maestros'=>$maestros));
    }

    public function maestrosAsignarAction(Request $request){

        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();

        $idco = $this->get('maestroAsignacion')->asignar($request);
        $idco = $idco->getContent();
        $idco = json_decode($idco,true);
        $idco = $idco['ieco'];

        $em->getConnection()->commit();
        $response = new JsonResponse();
        return $response->setData(array('ieco'=>$idco,'mensaje'=>''));
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
            //dump($registroOperativo);die;
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

}
