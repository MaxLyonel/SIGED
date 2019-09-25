<?php

namespace Sie\JuegosBundle\Controller;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos;
use Sie\AppWebBundle\Entity\JdpEquipoEstudianteInscripcionJuegos;
use Sie\AppWebBundle\Entity\JdpPersonaInscripcionJuegos;
use Sie\JuegosBundle\Controller\RegistroController as registroController;
use Sie\JuegosBundle\Controller\ReglaController as reglaController;
use Sie\JuegosBundle\Controller\RegisterPersonStudentController as registerPersonStudentController;

class ClasificacionController extends Controller {

    public $session;
    public $idInstitucion;
    private $nivelId;
    private $save;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        //$this->aCursos = $this->fillCursos();
    }

    public function indexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */

    }

    public function representacionCulturalIndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        $this->session->set('save', false);
        return $this->render($this->session->get('pathSystem') . ':Clasificacion:culturalindex.html.twig', array(
        ));

    }

    public function representacionCulturalBuscaUEAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');

        $save = $this->session->get('save');


        if($save){
            $datosSave = $this->session->get('datosBusqueda');
            $sie = $datosSave['sie'];
        } else {
            $sie = $request->get('sie');
        }


        //die("$sie");

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $entidadUsuario = $this->buscaEntidadFase(3,$id_usuario);


        $queryEntidad = $em->getConnection()->prepare("
                select ie.id, ie.institucioneducativa as nombre
                from institucioneducativa as ie
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                where ie.id = ".$sie." and cast(left(cast(jg.distrito_tipo_id as varchar),1) as integer) = ".$entidadUsuario[0]['id']."
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();

        if (count($objEntidad) > 0){
            $sie = $objEntidad[0]['id'];
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe el código SIE, o no tiene tuición sobre la misma'));
            return $this->redirect($this->generateUrl('sie_juegos_representacion_cultural_index'));
        }

        $objUeducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->findOneBy(array('id' => $sie));

        $exist = true;

        $query = $em->getConnection()->prepare("
                  select distinct nt.id as nivelid, nt.nivel as nivel, gt.id as gradoid, gt.grado as grado, get.id as generoid, get.genero as genero from institucioneducativa_curso as iec
                    inner join nivel_tipo as nt on nt.id = iec.nivel_tipo_id
                    inner join grado_tipo as gt on gt.id = iec.grado_tipo_id
                    inner join estudiante_inscripcion as ei on ei.institucioneducativa_curso_id = iec.id
                    inner join estudiante as e on e.id = ei.estudiante_id
                    inner join genero_tipo as get on get.id = e.genero_tipo_id
                    where iec.institucioneducativa_id = " . $sie . " and gestion_tipo_id = " . $gestion . " and iec.nivel_tipo_id in (13)
                    order by nt.id, gt.id, get.id
                ");
        $query->execute();
        $entityNiveles = $query->fetchAll();

        if ($entityNiveles) {

            $aInfoUnidadEductiva = array();
            foreach ($entityNiveles as $uEducativa) {
                //get the literal data of unidad educativa
                $sinfoUeducativa = serialize(array(
                    'ueducativaInfo' => array('nivel' => $uEducativa['nivel'], 'grado' => $uEducativa['grado'], 'genero' => $uEducativa['genero']),
                    'ueducativaInfoId' => array('nivelId' => $uEducativa['nivelid'], 'gradoId' => $uEducativa['gradoid'], 'generoId' => $uEducativa['generoid']),
                    'requestUser' => array('sie' => $sie, 'gestion' => $gestion)
                ));

                //send the values to the next steps
                $aInfoUnidadEductiva[$uEducativa['nivel']][$uEducativa['grado']][$uEducativa['genero']] = array('infoUe' => $sinfoUeducativa);
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información de la Unidad Educativa ó Código SIE no existe '));
            return $this->redirect($this->generateUrl('sie_juegos_representacion_cultural_index'));
        }

        return $this->render($this->session->get('pathSystem') . ':Clasificacion:indexCultural.html.twig', array(
                    'infoUnidadEducativa' => $objUeducativa,
                    'infoNiveles' => $aInfoUnidadEductiva,
                    'sie' => $sie,
                    'gestion' => $gestion,
                    'exist' => $exist
        ));
    }

    /**
     * get inscritos
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function buscaInscritosCulturalAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;

        $em = $this->getDoctrine()->getManager();
        $fase = $request->get('form_fase');
        $ue = $request->get('form_ue');
        $cultural = $request->get('form_representacion');

        $entity = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->innerJoin('SieAppWebBundle:JdpPruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->innerJoin('SieAppWebBundle:JdpFaseTipo', 'ft', 'WITH', 'ft.id = eij.faseTipo')
                ->where('pt.id = 0')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ie.id = :ueId')
                ->andWhere('ft.id = 4')
                ->setParameter('ueId', $ue)
                ->setParameter('gestionId', $gestionActual)
                ->orderBy('pt.prueba', 'ASC')
                ->getQuery();
        $aInscritos = $query->getResult();
        if(count($aInscritos)>0){
            foreach ($aInscritos as $inscrito) {
                $ainscritos[$inscrito->getId()] = $inscrito->getEstudianteInscripcion()->getEstudiante()->getNombre().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getMaterno();
            }
        } else {
            $ainscritos = array();
        }
        $response = new JsonResponse();
        return $response->setData(array('ainscritos' => $ainscritos));
    }


    /**
     * get request
     * @param type $request
     * return list of students
     */
    public function representacionCulturalListaEstudiantesAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //get the info ue

        $infoUe = $request->get('infoUe');
        $aInfoUeducativa = unserialize($infoUe);
        //get the values throght the infoUe
        $sie = $aInfoUeducativa['requestUser']['sie'];
        //$iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivelId = $aInfoUeducativa['ueducativaInfoId']['nivelId'];
        $gradoId = $aInfoUeducativa['ueducativaInfoId']['gradoId'];
        $generoId = $aInfoUeducativa['ueducativaInfoId']['generoId'];
        $nivel = $aInfoUeducativa['ueducativaInfo']['nivel'];
        $grado = $aInfoUeducativa['ueducativaInfo']['grado'];
        $genero = $aInfoUeducativa['ueducativaInfo']['genero'];
        $gestion = $aInfoUeducativa['requestUser']['gestion'];
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        $objStudents = $em->getRepository('SieAppWebBundle:Institucioneducativa')->getListStudentPerNivelGradoGenero($sie, $gestion, $nivelId, $gradoId, $generoId);

        $exist = true;
        $aData = array();
        //check if the data exist
        if ($objStudents) {
            $aData = serialize(array('sie' => $sie, 'nivel' => $nivelId, 'grado' => $gradoId, 'genero' => $generoId));
            $exist = true;
        } else {
            $message = 'No existen estudiantes inscritos...';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }

        $forma = $this->creaFormularioRegistroCultural('sie_juegos_representacion_cultural_lista_estudiante_registro', $sie, $gestion, $nivelId, $gradoId, $generoId, 0)->createView();


        $entity = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->innerJoin('SieAppWebBundle:JdpPruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->innerJoin('SieAppWebBundle:JdpFaseTipo', 'ft', 'WITH', 'ft.id = eij.faseTipo')
                ->where('pt.id = 0')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ie.id = :ueId')
                ->andWhere('ft.id = 4')
                ->setParameter('ueId', $sie)
                ->setParameter('gestionId', $gestion)
                ->orderBy('pt.prueba', 'ASC')
                ->getQuery();
        $aInscritos = $query->getResult();
        if(count($aInscritos)>0){
            foreach ($aInscritos as $inscrito) {
                $ainscritos[base64_encode($inscrito->getId())] = $inscrito->getEstudianteInscripcion()->getEstudiante()->getNombre().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getMaterno();
            }
        } else {
            $ainscritos = array();
        }


        return $this->render($this->session->get('pathSystem') . ':Clasificacion:seeStudentsCultural.html.twig', array(
                    'form' => $this->creaFormularioRegistroCultural('sie_juegos_representacion_cultural_lista_estudiante_registro', $sie, $gestion, $nivelId, $gradoId, $generoId, 0)->createView(),
                    'objStudents' => $objStudents,
                    'objCultural' => $ainscritos,
                    'sie' => $sie,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'grado' => $grado,
                    'genero' => $genero,
                    'infoUe' => $infoUe,
                    'exist' => $exist
        ));
    }

    public function f1IndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');
        // $gestion = 2018;

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $fase = 1; // nombre de la fase actual

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);

        if (!$objEntidad) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información del Distrito'));
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        $codigoEntidad = $objEntidad[0]['id'];

        $exist = true;
        $query = $em->getConnection()->prepare("
                    select nt.id as nivelid, nt.nivel as nivel, dt.id as disciplinaid, dt.disciplina as disciplina, pt.id as pruebaid, pt.prueba as prueba, gt.id as generoid, gt.genero as genero, ppt.id as pruebatipoid, ppt.disciplina_participacion as pruebatipo from jdp_prueba_tipo as pt
                    inner join jdp_prueba_participacion_tipo as ppt on ppt.id = pt.prueba_participacion_tipo_id
                    inner join jdp_disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                    inner join genero_tipo as gt on gt.id = pt.genero_tipo_id
                    inner join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
                    where dt.estado = 't' and pt.esactivo = 't'
                    order by nt.id, dt.id, pt.id, gt.id
                ");
        $query->execute();
        $entityNiveles = $query->fetchAll();
        if ($entityNiveles) {
            $aInfoDeportes = array();
            foreach ($entityNiveles as $uDeporte) {
                //get the literal data of unidad educativa
                $sinfoDeportes = serialize(array(
                    'deportesInfo' => array('nivel' => $uDeporte['nivel'], 'disciplina' => $uDeporte['disciplina'], 'prueba' => $uDeporte['prueba'], 'genero' => $uDeporte['genero'], 'pruebaTipo' => $uDeporte['pruebatipo']),
                    'deportesInfoId' => array('nivelId' => $uDeporte['nivelid'], 'disciplinaId' => $uDeporte['disciplinaid'], 'pruebaId' => $uDeporte['pruebaid'], 'generoId' => $uDeporte['generoid'], 'pruebaTipoId' => $uDeporte['pruebatipoid']),
                    'requestUser' => array('codigoEntidad' => $codigoEntidad, 'gestion' => $gestion, 'fase' => $fase)
                ));

                //send the values to the next steps
                $aInfoDeportes[$uDeporte['nivel']][$uDeporte['disciplina']][$uDeporte['prueba']][$uDeporte['genero']] = array('infoDeportes' => $sinfoDeportes);
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información de las Disciplinas y Pruebas'));
            return $this->redirect($this->generateUrl('sie_juegos_homepage'));
        }

        // $objEstudiantesRegistrados = $this->buscaInscritosDistrito($codigoEntidad,2);
        // dump($objEstudiantesRegistrados);die;
        
        return $this->render($this->session->get('pathSystem') . ':Clasificacion:index.html.twig', array(
                    'infoEntidad' => $objEntidad,
                    // 'infoDeportistas' => $objEstudiantesRegistrados,
                    'infoNiveles' => $aInfoDeportes,
                    'gestion' => $gestion,
                    'fase' => $fase,
                    'exist' => $exist
        ));
    }

    public function f2IndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $fase = 2; // nombre de la fase actual
        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);

        if (!$objEntidad) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información de la Circunscripción'));
            return $this->redirect($this->generateUrl('sie_juegos_inscripcion_index'));
        }

        $codigoEntidad = $objEntidad[0]['id'];
        $exist = true;

        $query = $em->getConnection()->prepare("
                    select nt.id as nivelid, nt.nivel as nivel, dt.id as disciplinaid, dt.disciplina as disciplina, pt.id as pruebaid, pt.prueba as prueba, gt.id as generoid, gt.genero as genero, ppt.id as pruebatipoid, ppt.disciplina_participacion as pruebatipo from jdp_prueba_tipo as pt
                    inner join jdp_prueba_participacion_tipo as ppt on ppt.id = pt.prueba_participacion_tipo_id
                    inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                    inner join genero_tipo as gt on gt.id = pt.genero_tipo_id
                    inner join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
                    where dt.estado = 't' and pt.esactivo = 't'
                    order by nt.id, dt.id, pt.id, gt.id
                ");
        $query->execute();
        $entityNiveles = $query->fetchAll();
        if ($entityNiveles) {
            $aInfoDeportes = array();
            foreach ($entityNiveles as $uDeporte) {
                //get the literal data of unidad educativa
                $sinfoDeportes = serialize(array(
                    'deportesInfo' => array('nivel' => $uDeporte['nivel'], 'disciplina' => $uDeporte['disciplina'], 'prueba' => $uDeporte['prueba'], 'genero' => $uDeporte['genero'], 'pruebaTipo' => $uDeporte['pruebatipo']),
                    'deportesInfoId' => array('nivelId' => $uDeporte['nivelid'], 'disciplinaId' => $uDeporte['disciplinaid'], 'pruebaId' => $uDeporte['pruebaid'], 'generoId' => $uDeporte['generoid'], 'pruebaTipoId' => $uDeporte['pruebatipoid']),
                    'requestUser' => array('codigoEntidad' => $codigoEntidad, 'gestion' => $gestion, 'fase' => $fase)
                ));

                //send the values to the next steps
                $aInfoDeportes[$uDeporte['nivel']][$uDeporte['disciplina']][$uDeporte['prueba']][$uDeporte['genero']] = array('infoDeportes' => $sinfoDeportes);
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información de las Disciplinas y Pruebas'));
            return $this->redirect($this->generateUrl('sie_juegos_inscripcion_index'));
        }

        $objEstudiantesRegistrados = $this->buscaInscritosCircunscripcion($codigoEntidad,3);

        return $this->render($this->session->get('pathSystem') . ':Clasificacion:index.html.twig', array(
                    'infoEntidad' => $objEntidad,
                    'infoDeportistas' => $objEstudiantesRegistrados,
                    'infoNiveles' => $aInfoDeportes,
                    'gestion' => $gestion,
                    'fase' => $fase,
                    'exist' => $exist
        ));
    }

    public function f3IndexAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestion = date_format($fechaActual,'Y');

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $em = $this->getDoctrine()->getManager();

        $fase = 3; // nombre de la fase actual

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);

        if (!$objEntidad) {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información del Departamento'));
            return $this->redirect($this->generateUrl('sie_juegos_inscripcion_index'));
        }

        $codigoEntidad = $objEntidad[0]['id'];
        $exist = true;

        $query = $em->getConnection()->prepare("
            select nt.id as nivelid, nt.nivel as nivel, dt.id as disciplinaid, dt.disciplina as disciplina, pt.id as pruebaid, pt.prueba as prueba, gt.id as generoid, gt.genero as genero, ppt.id as pruebatipoid, ppt.disciplina_participacion as pruebatipo from jdp_prueba_tipo as pt
            inner join jdp_prueba_participacion_tipo as ppt on ppt.id = pt.prueba_participacion_tipo_id
            inner join jdp_disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
            inner join genero_tipo as gt on gt.id = pt.genero_tipo_id
            inner join nivel_tipo as nt on nt.id = dt.nivel_tipo_id
            where dt.estado = 't' and pt.esactivo = 't' --dt.nivel_tipo_id = 13
            order by nt.id, dt.id, pt.id, gt.id
        ");
        $query->execute();
        $entityNiveles = $query->fetchAll();
        if ($entityNiveles) {
            $aInfoDeportes = array();
            foreach ($entityNiveles as $uDeporte) {
                //get the literal data of unidad educativa
                $sinfoDeportes = serialize(array(
                    'deportesInfo' => array('nivel' => $uDeporte['nivel'], 'disciplina' => $uDeporte['disciplina'], 'prueba' => $uDeporte['prueba'], 'genero' => $uDeporte['genero'], 'pruebaTipo' => $uDeporte['pruebatipo']),
                    'deportesInfoId' => array('nivelId' => $uDeporte['nivelid'], 'disciplinaId' => $uDeporte['disciplinaid'], 'pruebaId' => $uDeporte['pruebaid'], 'generoId' => $uDeporte['generoid'], 'pruebaTipoId' => $uDeporte['pruebatipoid']),
                    'requestUser' => array('codigoEntidad' => $codigoEntidad, 'gestion' => $gestion, 'fase' => $fase)
                ));

                //send the values to the next steps
                $aInfoDeportes[$uDeporte['nivel']][$uDeporte['disciplina']][$uDeporte['prueba']][$uDeporte['genero']] = array('infoDeportes' => $sinfoDeportes);
            }
        } else {
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => 'No existe información de las Disciplinas y Pruebas'));
            return $this->redirect($this->generateUrl('sie_juegos_inscripcion_index'));
        }

        $objEstudiantesRegistrados = $this->buscaInscritosDepartamento($codigoEntidad,4);

        return $this->render($this->session->get('pathSystem') . ':Clasificacion:index.html.twig', array(
                    'infoEntidad' => $objEntidad,
                    'infoDeportistas' => $objEstudiantesRegistrados,
                    'infoNiveles' => $aInfoDeportes,
                    'gestion' => $gestion,
                    'fase' => $fase,
                    'exist' => $exist
        ));
    }


    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function listaDeportistasAction(Request $request) {
        /*
         * Define la zona horaria y halla la fecha actual
         */
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        //get the info ue
        $infoDeporte = $request->get('infoDeportes');
        $ainfoDeporte = unserialize($infoDeporte);
        //get the values throght the infoUe
        $codigoEntidad = $ainfoDeporte['requestUser']['codigoEntidad'];
        $gestion = $ainfoDeporte['requestUser']['gestion'];
        $fase = $ainfoDeporte['requestUser']['fase'];
        //$iecId = $aInfoUeducativa['ueducativaInfoId']['iecId'];
        $nivelId = $ainfoDeporte['deportesInfoId']['nivelId'];
        $disciplinaId = $ainfoDeporte['deportesInfoId']['disciplinaId'];
        $pruebaId = $ainfoDeporte['deportesInfoId']['pruebaId'];
        $pruebaTipoId = $ainfoDeporte['deportesInfoId']['pruebaTipoId'];
        $generoId = $ainfoDeporte['deportesInfoId']['generoId'];
        $nivel = $ainfoDeporte['deportesInfo']['nivel'];
        $disciplina = $ainfoDeporte['deportesInfo']['disciplina'];
        $prueba = $ainfoDeporte['deportesInfo']['prueba'];
        $pruebaTipo = $ainfoDeporte['deportesInfo']['pruebaTipo'];
        $genero = $ainfoDeporte['deportesInfo']['genero'];
        //get db connexion
        $em = $this->getDoctrine()->getManager();
        //die($codigoEntidad." - ".$gestion." - ".$fase." - ".$nivelId." - ".$disciplinaId." - ".$pruebaId." - ".$generoId);
        $objStudents = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->getListStudentPerFaseNivelDisciplinaPruebaGenero($codigoEntidad, $gestion, $fase, $nivelId, $disciplinaId, $pruebaId, $generoId);
        $objDeportistasFase = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->getListDeportistasPorFaseNivelDisciplinaPruebaGenero($codigoEntidad, $gestion, ($fase+1), $nivelId, $disciplinaId, $pruebaId, $generoId);
        $exist = true;
        $aData = array();
        //check if the data exist

        if ($objStudents) {
            $aData = serialize(array('codigoEntidad' => $codigoEntidad, 'nivel' => $nivelId, 'disciplina' => $disciplinaId, 'prueba' => $pruebaId, 'genero' => $generoId, 'pruebaTipo' => $pruebaTipoId));
            $exist = true;
        } else {
            $message = 'No existen estudiantes inscritos...';
            $this->addFlash('warninsueall', $message);
            $exist = false;
        }

        $conjunto = false;
        if($pruebaTipoId == 1){
            $conjunto = true;
        }

        $arrIdInscription = array();
        $ainscritos = array();

        foreach ($objDeportistasFase as $inscrito) {
            $inscritoId = (int)$inscrito['eInsJueId'];
            $inscritoPosicion = (int)$inscrito['posicion'];
            if($pruebaId == 89 or $pruebaId == 90){
                $inscritoEquipoId = (int)$inscrito['ue'];
            } else {
                $inscritoEquipoId = (int)$inscrito['equipoId'];
            }
            $inscritoNombre = trim('PUESTO '.$inscritoPosicion.' - '.$inscrito['paterno'].' '.$inscrito['materno'].' '.$inscrito['nombre']);
            $entrenadorNombre = trim($inscrito['paternoPersona'].' '.$inscrito['maternoPersona'].' '.$inscrito['nombrePersona']);
            if ($entrenadorNombre == "" or !isset($entrenadorNombre) or $entrenadorNombre == false){
                $entrenadorNombre = "Registrar entrenador";
            }
            $ainscritos[$inscritoEquipoId][$inscritoId] = array('estudiante'=>$inscritoNombre, 'entrenador'=>$entrenadorNombre);
        }
        
        $arrIdInscription=array();
        $arrNewTeam = array();
        $entrenador = "";

        foreach ($ainscritos as $key => $value) {
            if($key != 0 or $pruebaId == 89 or $pruebaId == 90){                
                foreach ($value as $idinscription => $student) {
                    $arrIdInscription[]=$idinscription;
                    $jsonIdInscription = json_encode($arrIdInscription);
                    $arrNewTeam[$key][base64_encode($idinscription)]=$student['estudiante'];
                    $entrenador = $student['entrenador'];
                }
                $arrNewTeam[$key]['option'] = base64_encode($jsonIdInscription);
                $arrNewTeam[$key]['entrenador'] = $entrenador;
                // array_push($arrNewTeam[$key], $jsonIdInscription);
                $ainscritos = $arrNewTeam;
                $arrIdInscription=array();
            }else{
                foreach ($value as $idinscription => $student) {
                    //$ainscritos[$key][]=array('nombre'=>$student, 'option'=>json_encode(array($idinscription)));      
                    $arrNewTeam[base64_encode($idinscription)] = array('estudiante'=>$student['estudiante'], 'entrenador'=>$student['entrenador'], 'option'=>base64_encode(json_encode(array($idinscription))));;  
                }
                $ainscritos = $arrNewTeam;
            }        	
        }

        if($pruebaId == 89 or $pruebaId == 90){
            $conjunto = true;
        }

         return $this->render($this->session->get('pathSystem') . ':Clasificacion:seeStudents.html.twig', array(
                    'form' => $this->creaFormularioRegistro('sie_juegos_clasificacion_lista_deportistas_registro', $fase, $nivelId, 0, $disciplinaId, 0, 0)->createView(),
                    'objStudents' => $objStudents,
                    'registrados' => $ainscritos,
                    'codigoEntidad' => $codigoEntidad,
                    'nivel' => $nivel,
                    'gestion' => $gestion,
                    'aData' => $aData,
                    'disciplina' => $disciplina,
                    'prueba' => $prueba,
                    'genero' => $genero,
                    'infoDeporte' => $infoDeporte,
                    'fase' => $fase,
                    'conjunto' => $conjunto,
                    'exist' => $exist
        ));
    }

    /**
     * get request
     * @param type $request, $fase
     * return list of students
     */
    public function buscaEntidadFase($fase,$usuario) {

        //get db connexion
        $em = $this->getDoctrine()->getManager();

        if ($fase == 1){
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.id, lt.lugar as nombre from usuario as u
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$usuario." and rol_tipo_id = 10
                ");
        }
        if ($fase == 2){
            $queryEntidad = $em->getConnection()->prepare("
                    select ct.id, ct.circunscripcion as nombre from usuario as u
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join circunscripcion_tipo as ct on ct.id = ur.circunscripcion_tipo_id
                    where u.id = ".$usuario." and rol_tipo_id = 6
                ");
        }
        if ($fase == 3){
            $queryEntidad = $em->getConnection()->prepare("
                    select lt.codigo as id, lt.lugar as nombre from usuario as u
                    inner join usuario_rol as ur on ur.usuario_id = u.id
                    inner join lugar_tipo as lt on lt.id = ur.lugar_tipo_id
                    where u.id = ".$usuario." and (rol_tipo_id = 7 or rol_tipo_id = 28)
                ");
        }
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();

        return $objEntidad;
    }


    /**
     * get Form
     * @param type $routing
     * @param type $value1
     * @param type $value2
     * @param type $value3
     * @param type $value4
     * @param type $value5
     * @param type $identificador
     * return form
     */
    private function creaFormularioRegistro($routing, $value1, $value2, $value3, $value4, $value5, $identificador) {
        $form = $this->createFormBuilder()
                //->setAction($this->generateUrl($routing))
                ->add('fase', 'hidden', array('attr' => array('value' => $value1)))
                ->add('posicion','choice',
                      array('label' => 'Lugar Obtenido',
                            'choices' => ($value2==12 or $value4==2)?(array('1' => 'Primer Lugar')):(array('1' => 'Primer Lugar','2' => 'Segundo Lugar')),
                            'data' => $value3,
                            'attr' => array('class' => 'form-control')))
                // ->add('submit', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-success col-sm-12', 'disabled' => 'disabled')))
                ->getForm();
        return $form;
    }


    /**
     * get Form
     * @param type $routing
     * @param type $value1
     * @param type $value2
     * @param type $value3
     * @param type $value4
     * @param type $value5
     * @param type $identificador
     * return form
     */
    private function creaFormularioRegistroCultural($routing, $value1, $value2, $value3, $value4, $value5, $identificador) {
        $form = $this->createFormBuilder()
                ->setAction($this->generateUrl($routing))
                ->add('fase', 'hidden', array('attr' => array('value' => 4)))
                ->add('ue', 'hidden', array('attr' => array('value' => $value1)))
                ->add('representacion','choice',
                      array('label' => 'Representación',
                            'choices' => (array('111' => 'Danza')),
                            'attr' => array('class' => 'form-control')))
                ->add('submit', 'submit', array('label' => 'Registrar', 'attr' => array('class' => 'btn btn-default')))
                ->getForm();
        return $form;
    }


    /**
     * get request
     * @param type $request
     * return registro de estudiantes incritos en fase previa
     */
    public function representacionCulturalListaEstudiantesRegistroAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $this->session->set('save', false);

        $fase = 4;

        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("select * from fase_tipo where id = 4");
        $query->execute();
        $faseTipoEntity = $query->fetchAll();

        //if (!$faseTipoEntity[0]['esactivo_secundaria']){
        //    $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Secundaria concluyeron"));
        //    return $this->redirectToRoute('sie_juegos_representacion_cultural_index');
        //}

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        if ($request->isMethod('POST')) {
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            if ($form) {
                $sie = $form['ue'];
                $fase = $form['fase'];
                $cultural = $form['representacion'];
                $deportistas = $request->get('deportistas');
                $listaDeportistas = "";


                if ($deportistas != ''){
                    foreach($deportistas as $deportista){
                        if ($listaDeportistas == ""){
                            $listaDeportistas = $deportista;
                        } else {
                            $listaDeportistas = $listaDeportistas.",".$deportista."";
                        }
                    }
                } else {
                    $listaDeportistas = "Sin deportistas";
                }

                try {
                    $pruebaEntity = $em->getRepository('SieAppWebBundle:JdpPruebaTipo')->findOneBy(array('id' => 0));
                    $gestionEntity = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $gestionActual));
                    $faseEntity = $em->getRepository('SieAppWebBundle:JdpFaseTipo')->findOneBy(array('id' => $fase));
                    $comisionEntity = $em->getRepository('SieAppWebBundle:JdpComisionTipo')->findOneBy(array('id' => (int)($cultural)));
                    $entidadUsuario = $this->buscaEntidadFase(3,$id_usuario);
                    $msgEstudiantesRegistrados = "";
                    $msgEstudiantesObservados = "";
                    foreach($deportistas as $deportista){
                        $msg = $this->validaInscripcionCulturalJuegos($deportista,$gestionActual,0,$fase,13,$entidadUsuario[0]["id"]); //validaInscripcionCulturalJuegos
                        if($msg[0]){
                            $estudianteInscripcionEntity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $deportista));
                            $estudianteInscripcionJuegos = new JdpEstudianteInscripcionJuegos();
                            $estudianteInscripcionJuegos->setEstudianteInscripcion($estudianteInscripcionEntity );
                            $estudianteInscripcionJuegos->setPruebaTipo($pruebaEntity);
                            $estudianteInscripcionJuegos->setGestionTipo($gestionEntity);
                            $estudianteInscripcionJuegos->setFaseTipo($faseEntity);
                            $estudianteInscripcionJuegos->setFechaRegistro($fechaActual);
                            $estudianteInscripcionJuegos->setFechaModificacion($fechaActual);
                            $estudianteInscripcionJuegos->setUsuarioId($id_usuario);
                            $estudianteInscripcionJuegos->setEsactivo(true);
                            $estudianteInscripcionJuegos->setObs($comisionEntity->getComision());
                            $em->persist($estudianteInscripcionJuegos);
                            $em->flush();
                            $estudianteInscripcionJuegosId = $estudianteInscripcionJuegos->getId();
                            if ($msgEstudiantesRegistrados == ""){
                                $msgEstudiantesRegistrados = $msg[1];
                            } else {
                                $msgEstudiantesRegistrados = $msgEstudiantesRegistrados.' - '.$msg[1];
                            }
                        } else {
                            if ($msgEstudiantesObservados == ""){
                                $msgEstudiantesObservados = $msg[1];
                            } else {
                                $msgEstudiantesObservados = $msgEstudiantesObservados.' - '.$msg[1];
                            }
                        }
                    }
                    if ($msgEstudiantesRegistrados != ""){
                        $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => "Estudiante (".$msgEstudiantesRegistrados.") registrado en la Prueba y Fase seleccionada"));
                    }
                    if ($msgEstudiantesObservados != ""){
                        $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => "Estudiante (".$msgEstudiantesObservados.") registrado en la Prueba y Fase seleccionada"));
                    }

                    $em->getConnection()->commit();
                    $this->session->set('datosBusqueda', array('sie' => $sie));
                    $this->session->set('save', true);
                    return $this->redirectToRoute('sie_juegos_representacion_cultural_busca_ue');
                } catch (Exception $e) {
                    $em->getConnection()->rollback();
                    $msg = "Error al registrar, intente nuevamente";
                    $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
                    $this->session->set('datosBusqueda', array('sie' => $sie));
                    $this->session->set('save', true);
                    return $this->redirectToRoute('sie_juegos_representacion_cultural_busca_ue');
                }
            } else {
                $em->getConnection()->rollback();
                $msg = "Datos no validos, intente nuevamente";
                $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
                return $this->redirectToRoute('sie_juegos_representacion_cultural_index');
            }
        } else {
            $msg = "Datos no validos, intente nuevamente";
            $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
            return $this->redirectToRoute('sie_juegos_representacion_cultural_index');
        }
        //return $this->render($this->session->get('pathSystem') . ':Inscripcion:seeStudents.html.twig');
    }


    /**
     * get request
     * @param type $request
     * return clasificacion de estudiantes incritos a la fase seleccionada
     */
    public function listaDeportistasRegistroAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');
        $id_usuario_lugar = $this->session->get('roluserlugarid');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $ainscritos = array();

        $response = new JsonResponse();

        $msgEstudiantesRegistrados = "";
        $msgEstudiantesObservados = "";
        $nivelId = 0;
        $disciplinaId = 0;
        $comisionId = 0;
        $pruebaId = 0;
        $generoId = 0;
        $conjunto = false;

        if ($request->isMethod('POST')) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            $form = $request->get('form');
            $deportistas = $request->get('deportistas');

            if ($deportistas) {
                // $posicion = $form['posicion'];  // fase actual
                // $fase = $form['fase'];  // fase actual
                $fase = $request->get('fase');
                $posicion = $request->get('posicion');
                $faseClasificacion = $fase + 1;  // fase siguiente a la cual se clasificara
                $deportistas = $request->get('deportistas');
                $listaDeportistas = "";

                if($fase == 2){
                    $entityUsuarioLugar = $this->buscaEntidadFase($fase,$id_usuario);
                    if(count($entityUsuarioLugar) > 0){
                        $id_usuario_lugar = $entityUsuarioLugar[0]['id'];
                    }
                }

                $entityDatos = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos')->findOneBy(array('id'=>$deportistas[0]));
                $nivelId = $entityDatos->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
                $disciplinaId = $entityDatos->getPruebaTipo()->getDisciplinaTipo()->getId();
                $pruebaId = $entityDatos->getPruebaTipo()->getId();
                $generoId = $entityDatos->getPruebaTipo()->getGeneroTipo()->getId();

                $registroController = new registroController();
                $registroController->setContainer($this->container);

                $faseActivo = $registroController->getFaseActivo($faseClasificacion, $nivelId, $fechaActual);
  
                    if (!$faseActivo) {
                        return $response->setData(array(
                            'msg_incorrecto' => 'Inscripción cerrada'
                        ));
                    }

                // $query = $em->getConnection()->prepare("select * from fase_tipo where id = ".($fase+1));
                // $query->execute();
                // $faseTipoEntity = $query->fetchAll();

                // if(count($faseTipoEntity)<1){
                //     $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "No se cuenta habilitado la inscripcion de deportistas para la Primera Fase"));
                //     return $this->redirectToRoute('sie_juegos_clasificacion_f'.$fase .'_index');
                // }

                // if($nivel == 12 and !$faseTipoEntity[0]['esactivo_primaria']){
                //     $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Primario concluyeron"));
                //     return $this->redirectToRoute('sie_juegos_clasificacion_f'.$fase .'_index');
                // }
                // if($nivel == 13 and !$faseTipoEntity[0]['esactivo_secundaria']){
                //     $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las inscripciones para el Nivel Secundaria concluyeron"));
                //     return $this->redirectToRoute('sie_juegos_clasificacion_f'.$fase .'_index');
                // }

                //$this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => "La clasificación de estudiantes del Nivel Secundario a la fase nacional se encuentra cerrada."));
                //return $this->redirectToRoute('sie_juegos_clasificacion_f'.$fase .'_index');

                if ($deportistas != ''){
                    foreach($deportistas as $deportista){
                        if ($listaDeportistas == ""){
                            $listaDeportistas = $deportista;
                        } else {
                            $listaDeportistas = $listaDeportistas.",".$deportista."";
                        }
                    }
                } else {
                    $listaDeportistas = "";
                }
                
                try {

                    $reglaController = new reglaController();
                    $reglaController->setContainer($this->container);

                    $registerPersonStudentController = new registerPersonStudentController();
                    $registerPersonStudentController->setContainer($this->container);

                    foreach($deportistas as $deportista){
                        $estudianteInscripcionJuegosEntity = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos')->findOneBy(array('id' => $deportista));                        
                                                
                        $deportistaGestion = $estudianteInscripcionJuegosEntity->getGestionTipo()->getId();
                        $deportistaEstudianteInscripcion = $estudianteInscripcionJuegosEntity->getEstudianteInscripcion()->getId();
                        $deportistaPrueba = $estudianteInscripcionJuegosEntity->getPruebaTipo()->getId();
                        $deportistaNivel = $estudianteInscripcionJuegosEntity->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
                        $entidadUsuario = $this->buscaEntidadFase($fase,$id_usuario);
                        $entidadUsuarioId =  $entidadUsuario[0]['id'];
                        $estadoEstudianteInscripcion = $this->verificaEstadoInscripcionEstudiante($deportistaEstudianteInscripcion);
                        $estadoEstudianteInscripcionJuegos = $estudianteInscripcionJuegosEntity->getEsactivo();
                        $nivel = $estudianteInscripcionJuegosEntity->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
                        $datosPrueba = $registroController->verificaTipoDisciplinaPrueba($deportistaPrueba);
                        $tipoPruebaParticipacionId = $datosPrueba['idTipoPrueba'];
                        $estudianteNombre = $estudianteInscripcionJuegosEntity->getEstudianteInscripcion()->getEstudiante()->getNombre();
                        $estudiantePaterno = $estudianteInscripcionJuegosEntity->getEstudianteInscripcion()->getEstudiante()->getPaterno();
                        $estudianteMaterno = $estudianteInscripcionJuegosEntity->getEstudianteInscripcion()->getEstudiante()->getMaterno();
                        $estudianteNombreApellido = $estudianteNombre . ' ' . $estudiantePaterno . ' ' . $estudianteMaterno;

                        if($tipoPruebaParticipacionId == 1){
                            $conjunto = true;
                            $equipoEstudianteInscripcionJuegosEntity = $em->getRepository('SieAppWebBundle:JdpEquipoEstudianteInscripcionJuegos')->findOneBy(array('estudianteInscripcionJuegos' => $deportista));
                            if(count($equipoEstudianteInscripcionJuegosEntity) > 0){
                                $equipoId = $equipoEstudianteInscripcionJuegosEntity->getEquipoId();
                                $equipoNombre = $equipoEstudianteInscripcionJuegosEntity->getEquipoNombre();
                            } else {
                                $equipoId = ((int)$registroController->getEquipoRegistradoMaximo())+1;
                                $equipoNombre = 'Equipo'.$equipoId;
                            }                            
                        } else {
                            $conjunto = false;
                            $equipoId = 0;
                            $equipoNombre = "";
                        }

                        if($estadoEstudianteInscripcionJuegos){
                            if($estadoEstudianteInscripcion){
                                $msg = $reglaController->valEstudianteClasificacionJuegos($deportistaEstudianteInscripcion, $gestionActual, $deportistaPrueba, $faseClasificacion, $equipoId, $id_usuario_lugar, $posicion);
                                // $msg = $this->validaInscripcionJuegos($deportistaEstudianteInscripcion,$deportistaGestion,$deportistaPrueba,$faseClasificacion,$deportistaNivel,$posicion,$entidadUsuarioId);
                                
                                if($msg[0]){

                                    $entrenadorEntity = $registerPersonStudentController->getEquipoCouch($deportista,$faseClasificacion);
                                    $entrenadorSave = false;
                                    if(count($entrenadorEntity) > 0){
                                        $entrenadorSave = true;
                                        // $personaId = $entrenadorEntity["personaId"];
                                    } else {
                                        $entrenadorEntity = $registerPersonStudentController->getEquipoCouch($deportista,$fase);
                                        if(count($entrenadorEntity) > 0){
                                            $entrenadorSave = true;
                                            // $personaId = $entrenadorEntity["personaId"];
                                        } else {
                                            $entrenadorSave = false;
                                        }
                                    }

                                    $pruebaEntity = $em->getRepository('SieAppWebBundle:JdpPruebaTipo')->findOneBy(array('id' => $deportistaPrueba));
                                    $gestionEntity = $em->getRepository('SieAppWebBundle:GestionTipo')->findOneBy(array('id' => $deportistaGestion));
                                    $faseEntity = $em->getRepository('SieAppWebBundle:JdpFaseTipo')->findOneBy(array('id' => $faseClasificacion));
                                    $estudianteInscripcionEntity = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneBy(array('id' => $deportistaEstudianteInscripcion));
                                     $estudianteInscripcionJuegos = new JdpEstudianteInscripcionJuegos();
                                    $estudianteInscripcionJuegos->setEstudianteInscripcion($estudianteInscripcionEntity);
                                    $estudianteInscripcionJuegos->setPruebaTipo($pruebaEntity);
                                    $estudianteInscripcionJuegos->setGestionTipo($gestionEntity);
                                    $estudianteInscripcionJuegos->setFaseTipo($faseEntity);
                                    $estudianteInscripcionJuegos->setPosicion($posicion);
                                    $estudianteInscripcionJuegos->setFechaRegistro($fechaActual);
                                    $estudianteInscripcionJuegos->setFechaModificacion($fechaActual);
                                    $estudianteInscripcionJuegos->setUsuarioId($id_usuario);
                                    $estudianteInscripcionJuegos->setEsactivo(true);
                                    $em->persist($estudianteInscripcionJuegos);

                                    if($equipoId > 0){
                                        $equipoEstudianteInscripcionJuegos = new JdpEquipoEstudianteInscripcionJuegos();
                                        $equipoEstudianteInscripcionJuegos->setEstudianteInscripcionJuegos($estudianteInscripcionJuegos);
                                        $equipoEstudianteInscripcionJuegos->setEquipoId($equipoId);
                                        $equipoEstudianteInscripcionJuegos->setEquipoNombre('Equipo'.$equipoId);
                                        $em->persist($equipoEstudianteInscripcionJuegos);
                                    }

                                    if($entrenadorSave){
                                        if($comisionId == 0){
                                            if($nivel== 12){
                                                $comisionId = 139;
                                            } else {
                                                $comisionId = 140;
                                            }
                                        }
                                        //dump($entrenadorEntity);die;
                                        foreach($entrenadorEntity as $entrenador){
                                            $personaId = $entrenador["personaId"];
                                            $comisionId = $entrenador["comisionId"];
                                            $personaInscripcionJuegos = new JdpPersonaInscripcionJuegos();
                                            $personaInscripcionJuegos->setEstudianteInscripcionJuegos($estudianteInscripcionJuegos);
                                            $personaInscripcionJuegos->setPersona($em->getRepository('SieAppWebBundle:Persona')->find($personaId));
                                            $personaInscripcionJuegos->setComisionTipo($em->getRepository('SieAppWebBundle:JdpComisionTipo')->find($comisionId));
                                            $em->persist($personaInscripcionJuegos);
                                        }
                                    }

                                    $em->flush();
                                    $estudianteInscripcionJuegosId = $estudianteInscripcionJuegos->getId();
                                    if ($msgEstudiantesRegistrados == ""){
                                        $msgEstudiantesRegistrados = $msg[1];
                                    } else {
                                        $msgEstudiantesRegistrados = $msgEstudiantesRegistrados.' - '.$msg[1];
                                    }
                                    //array_push($ainscritos,array('id'=>($estudianteInscripcionJuegosId), 'nombre'=>$estudianteNombreApellido, 'posicion'=> $posicion));
                                    
                                } else {
                                    if ($msgEstudiantesObservados == ""){
                                        $msgEstudiantesObservados = $msg[1];
                                    } else {
                                        $msgEstudiantesObservados = $msgEstudiantesObservados.' - '.$msg[1];
                                    }
                                }
                            } else {
                                // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => "El estudiante ya no se encuentra con el estado efectivo, favor de verificar la información actual del estudiante"));
                                $msgEstudiantesObservados = "El estudiante ".$estudianteNombreApellido." ya no se encuentra con el estado efectivo, favor de verificar la información actual del estudiante";
                            }
                        } else {
                            // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => "El estudiante ya no se encuentra con el estado efectivo, favor de verificar la información actual del estudiante"));
                            $msgEstudiantesObservados = "El estudiante ".$estudianteNombreApellido." fue reemplazado de acuerdo a solicitud, por lo cual, no puede seguir participando";
                        }
                    }
                    // if ($msgEstudiantesRegistrados != ""){
                    //     $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => "Estudiante (".$msgEstudiantesRegistrados.")"));
                    // }
                    // if ($msgEstudiantesObservados != ""){
                    //     $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => "Estudiante (".$msgEstudiantesObservados.")"));
                    // }
                    $em->getConnection()->commit();
                    // dump($id_usuario_lugar."-".$gestionActual."-".$faseClasificacion."-".$nivelId."-".$disciplinaId."-".$pruebaId."-".$generoId);die;
                    $objDeportistasFase = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos')->getListDeportistasPorFaseNivelDisciplinaPruebaGenero($id_usuario_lugar, $gestionActual, $faseClasificacion, $nivelId, $disciplinaId, $pruebaId, $generoId);
                    //dump($id_usuario_lugar); dump($gestionActual); dump($faseClasificacion); dump($nivelId); dump($disciplinaId); dump($pruebaId); dump($generoId);die;
                    //dump($objDeportistasFase[0]['eInsJueId']);die;
                    foreach ($objDeportistasFase as $inscrito) {
                        $inscritoId = (int)$inscrito['eInsJueId'];
                        $inscritoPosicion = (int)$inscrito['posicion'];
                        $inscritoEquipoId = (int)$inscrito['equipoId'];
                        if($pruebaId == 89 or $pruebaId == 90){
                            $inscritoEquipoId = (int)$inscrito['ue'];
                        } else {
                            $inscritoEquipoId = (int)$inscrito['equipoId'];
                        }
                        $inscritoNombre = trim('PUESTO '.$inscritoPosicion.' - '.$inscrito['paterno'].' '.$inscrito['materno'].' '.$inscrito['nombre']);
                        $entrenadorNombre = trim($inscrito['paternoPersona'].' '.$inscrito['maternoPersona'].' '.$inscrito['nombrePersona']);
                        if ($entrenadorNombre == "" or !isset($entrenadorNombre) or $entrenadorNombre == false){
                            $entrenadorNombre = "Registrar entrenador";
                        }
                        $ainscritos[$inscritoEquipoId][$inscritoId] = array('estudiante'=>$inscritoNombre, 'entrenador'=>$entrenadorNombre);
                    }

                    $arrIdInscription=array();
                    $arrNewTeam = array();
                    $entrenador = "";
                    foreach ($ainscritos as $key => $value) {
                        if($key != 0 or $pruebaId == 89 or $pruebaId == 90){
                            foreach ($value as $idinscription => $student) {
                                $arrIdInscription[]=$idinscription;
                                $jsonIdInscription = json_encode($arrIdInscription);
                                $arrNewTeam[$key][base64_encode($idinscription)]=$student['estudiante'];
                                $entrenador = $student['entrenador'];
                            }
                            $arrNewTeam[$key]['option'] = base64_encode($jsonIdInscription);
                            $arrNewTeam[$key]['entrenador'] = $entrenador;
                            // array_push($arrNewTeam[$key], $jsonIdInscription);
                            $ainscritos = $arrNewTeam;
                            $arrIdInscription=array();
                        }else{
                            foreach ($value as $idinscription => $student) {
                                //$ainscritos[$key][]=array('nombre'=>$student, 'option'=>json_encode(array($idinscription)));                                    
                                $arrNewTeam[base64_encode($idinscription)] = array('estudiante'=>$student['estudiante'], 'entrenador'=>$student['entrenador'], 'option'=>base64_encode(json_encode(array($idinscription))));;  
                            }
                            $ainscritos = $arrNewTeam;
                        }        	
                    }
                                       
                    if($pruebaId == 89 or $pruebaId == 90){
                        $conjunto = true;
                    }

                    // return $this->redirectToRoute('sie_juegos_clasificacion_f'.$fase .'_index');
                } catch (Exception $e) {
                    $em->getConnection()->rollback();
                    $msgEstudiantesObservados = "Inconsistencia, intente nuevamente";
                    // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
                    // return $this->redirectToRoute('sie_juegos_clasificacion_f'.$fase .'_index');
                }
            } else {
                $msgEstudiantesObservados = "Datos no validos, intente nuevamente";
                // $msg = "Datos no validos, intente nuevamente";
                // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
                // return $this->redirectToRoute('sie_juegos_inscripcion_index');
            }
        } else {
            $msgEstudiantesObservados = "Datos no validos, intente nuevamente";
            // $msg = "Datos no validos, intente nuevamente";
            // $this->session->getFlashBag()->set('danger', array('title' => 'Error', 'message' => $msg));
            // return $this->redirectToRoute('sie_juegos_inscripcion_index');
        }    
        // return $this->render($this->session->get('pathSystem') . ':Clasificacion:seeStudents.html.twig');
        return $response->setData(array(
            'registrados' => $ainscritos, 'msg_correcto' => $msgEstudiantesRegistrados, 'msg_incorrecto' => $msgEstudiantesObservados, 'conjunto' => $conjunto
        )); 
    }

    /**
     * get prueba
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function buscaPruebasAction($ue, $disciplina, $genero) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;

        $em = $this->getDoctrine()->getManager();
        //get grado
        $apruebas = array();
        $ainscritos = array();
        $entity = $em->getRepository('SieAppWebBundle:PruebaTipo');
        $query = $entity->createQueryBuilder('pt')
                ->innerJoin('SieAppWebBundle:DisciplinaTipo', 'dt', 'WITH', 'dt.id = pt.disciplinaTipo')
                ->innerJoin('SieAppWebBundle:GeneroTipo', 'gt', 'WITH', 'gt.id = pt.generoTipo')
                ->where('dt.id = :disciplinaId')
                ->andWhere('gt.id = :generoId')
                ->setParameter('disciplinaId', $disciplina)
                ->setParameter('generoId', $genero)
                ->distinct()
                ->orderBy('pt.prueba', 'ASC')
                ->getQuery();
        $aPruebas = $query->getResult();
        foreach ($aPruebas as $prueba) {
            $apruebas[$prueba->getId()] = $prueba->getPrueba();
        }

        if (count($apruebas) > 0){
            $aprueba01 = array_keys($apruebas)[0];
        } else {
            $aprueba01 = 0;
        }

        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->innerJoin('SieAppWebBundle:PruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->where('pt.id = :pruebaId')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ie.id = :ueId')
                ->setParameter('ueId', $ue)
                ->setParameter('pruebaId', $aprueba01)
                ->setParameter('gestionId', $gestionActual)
                ->orderBy('pt.prueba', 'ASC')
                ->getQuery();
        $aInscritos = $query->getResult();
        foreach ($aInscritos as $inscrito) {
            $ainscritos[$inscrito->getId()] = $inscrito->getEstudianteInscripcion()->getEstudiante()->getNombre().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getMaterno();
        }

        $response = new JsonResponse();
        return $response->setData(array('apruebas' => $apruebas,'ainscritos' => $ainscritos));
    }

    /**
     * get inscritos
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function buscaInscritosPruebaAction($prueba) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $ue = $this->session->get('userName');

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->innerJoin('SieAppWebBundle:PruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->where('pt.id = :pruebaId')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ie.id = :ueId')
                ->setParameter('ueId', $ue)
                ->setParameter('pruebaId', $prueba)
                ->setParameter('gestionId', $gestionActual)
                ->orderBy('pt.prueba', 'ASC')
                ->getQuery();
        $aInscritos = $query->getResult();
        if(count($aInscritos)>0){
            foreach ($aInscritos as $inscrito) {
                $ainscritos[$inscrito->getId()] = $inscrito->getEstudianteInscripcion()->getEstudiante()->getNombre().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getMaterno();
            }
        } else {
            $ainscritos = array();
        }
        $response = new JsonResponse();
        return $response->setData(array('ainscritos' => $ainscritos));
    }

    /**
     * get prueba
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function validaInscripcionJuegos($inscripcionEstudiante,$gestion,$prueba,$fase,$nivel,$posicion,$entidadUsuarioId) {
        $em = $this->getDoctrine()->getManager();
        if ($nivel == 12){
            $query = $em->getConnection()->prepare("select * from (
                select count(eij.id) as cant, eij.estudiante_inscripcion_id, COUNT(distinct disciplina) as cantdisc
                , sum(case when dt.id = 14 or dt.id = 15 or dt.id = 16 or dt.id = 17 then 1 else 0 end)  as cant_dis_conj
                , sum(case when dt.id <> 14 and dt.id <> 15 and dt.id <> 16 and dt.id <> 17 then 1 else 0 end)  as cant_dis_indi
                , sum(case when dt.id = 18 and (pt.id = 99 or pt.id = 100 or pt.id = 101 or pt.id = 102) then 1 else 0 end)  as cant_pru_nat
                , string_agg(distinct dt.disciplina,', ') as disciplinas
                , string_agg(distinct pt.prueba,', ') as pruebas
                from estudiante_inscripcion_juegos as eij
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                where eij.estudiante_inscripcion_id = ".$inscripcionEstudiante." and eij.gestion_tipo_id = ".$gestion." and eij.fase_tipo_id = ".$fase."
                group by eij.estudiante_inscripcion_id
                order by cant desc
                ) as v");
        } else {
            $query = $em->getConnection()->prepare("select * from (
                select count(eij.id) as cant, eij.estudiante_inscripcion_id, COUNT(distinct disciplina) as cantdisc
                , sum(case when dt.id = 3 or dt.id = 4 or dt.id = 5 or dt.id = 7 then 1 else 0 end)  as cant_dis_conj
                , sum(case when dt.id <> 3 and dt.id <> 4 and dt.id <> 5 and dt.id <> 7 then 1 else 0 end)  as cant_dis_indi
                , sum(case when dt.id = 2 and (pt.id = 174 or pt.id = 175 or pt.id = 200 or pt.id = 201) then 1 else 0 end)  as cant_pru_conj_atle
                , sum(case when dt.id = 2 and (pt.id <> 174 and pt.id <> 175 and pt.id <> 200 and pt.id <> 201) then 1 else 0 end)  as cant_pru_indi_atle
                , sum(case when dt.id = 8 and (pt.id = 63 or pt.id = 64 or pt.id = 75 or pt.id = 76) then 1 else 0 end)  as cant_pru_conj_nat
                , sum(case when dt.id = 8 and (pt.id <> 63 and pt.id <> 64 and pt.id <> 75 and pt.id <> 76) then 1 else 0 end)  as cant_pru_indi_nat
                , sum(case when dt.id = 6 then 1 else 0 end)  as cant_pru_cic
                , sum(case when dt.id = 9 then 1 else 0 end)  as cant_pru_raq
                , sum(case when dt.id = 11 then 1 else 0 end)  as cant_pru_ten
                , string_agg(distinct dt.disciplina,', ') as disciplinas
                , string_agg(distinct pt.prueba,', ') as pruebas
                from estudiante_inscripcion_juegos as eij
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                where eij.estudiante_inscripcion_id = ".$inscripcionEstudiante." and eij.gestion_tipo_id = ".$gestion." and eij.fase_tipo_id = ".$fase."
                group by eij.estudiante_inscripcion_id
                order by cant desc
                ) as v ");
        }
        $query->execute();
        $inscripcionEstudianteEntity = $query->fetchAll();
        $xCupo = 1;
        if ($fase == 2){
          if ($entidadUsuarioId == 31642){ // MAGDALENA/ BAURES/ HUACARAJE
                $xCupo = 3;
            }
            if ($entidadUsuarioId == 31637){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31639){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31640  ){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31552  ){
                $xCupo = 3;
            }
            if ($entidadUsuarioId == 31553  ){
                $xCupo = 3;
            }
            if ($entidadUsuarioId == 31613  ){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31590  ){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31612  ){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31610  ){
                $xCupo = 3;
            }
            if ($entidadUsuarioId == 31617  ){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31554  ){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31363  ){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31564  ){
                $xCupo = 2;
            }

            if ($entidadUsuarioId == 31458  ){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31459  ){
                $xCupo = 3;
            }
            if ($entidadUsuarioId == 79356  ){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31508  ){
                $xCupo = 3;
            }
            if ($entidadUsuarioId == 31622  ){ // SANTA CRUZ 1
                $xCupo = 7;
            }
            if ($entidadUsuarioId == 31623  ){ // SANTA CRUZ 2
                $xCupo = 6;
            }
            if ($entidadUsuarioId == 31624  ){ // SANTA CRUZ 3
                $xCupo = 5;
            }
            if ($entidadUsuarioId == 79359  ){ // PLAN TRES MILL (SANTA CRUZ 4)
                $xCupo = 6;
            }
            if ($entidadUsuarioId == 31504  ){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31505  ){
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31530  ){
                $xCupo = 2;
            }

            if ($entidadUsuarioId == 31455  ){ // LA PAZ 1
                $xCupo = 3;
            }
            if ($entidadUsuarioId == 31456  ){ // LA PAZ 2
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31457  ){ // LA PAZ 3
                $xCupo = 2;
            }

            if ($entidadUsuarioId == 31395  ){  // ACHACACHI
                $xCupo = 5;
            }
            if ($entidadUsuarioId == 31426  ){  // CAJUATA
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31398  ){  //  CAQUIAVIRI
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31454  ){  // CARANAVI
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31448  ){  // CHARAZANI
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31443  ){  // COLQUENCHA
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31397  ){  // CORO CORO
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31403  ){  // PUERTO ACOSTA
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31451  ){  // SAN PEDRO DE CURACHUARA
                $xCupo = 3;
            }
            if ($entidadUsuarioId == 31450  ){  // SAN PEDRO DE TIQUINA
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31411  ){  // TACACOMA
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 31422  ){  // YACO MALLA
                $xCupo = 2;
            }
            if ($entidadUsuarioId == 99226  ){  // VILLA ABECIA - LAS CARRERAS
                $xCupo = 2;
            }
        }

        $entidadPruebaTipo = $em->getRepository('SieAppWebBundle:PruebaTipo')->findOneBy(array('id' => $prueba));
        $disciplinaId = $entidadPruebaTipo->getDisciplinaTipo()->getId();

        if ($nivel == 12){
            $xCupo = $xCupo * 1;
        } else {
            if ($disciplinaId != 2){
                $xCupo = $xCupo * 2;
            }
        }

        $tipoDisciplinaPrueba = $this->verificaTipoDisciplinaPrueba($prueba,$xCupo);
        $inscripcionEstudianteGestionPruebaFase = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,$prueba,$fase);
        $inscripcionEstudianteGestionDisciplinaFase = $this->verificaInscripcionEstudianteGestionDisciplinaFase($inscripcionEstudiante,$gestion,$prueba,$fase);
        $estudiante = $this->verificaInscripcionEstudiante($inscripcionEstudiante);
        $cantidadIncritosGestionPruebaFase = $this->validaCantidadGestionPruebaFaseUe($gestion,$prueba,$fase,$entidadUsuarioId,$posicion);

        $registroValido = true;
        $msg = array('0'=>true, '1'=>$estudiante["nombre"]);

        if($nivel == 13){
            if($estudiante["gestion_nacimiento"] < 1999 or $estudiante["gestion_nacimiento"] > 2006){
                $msg = array('0'=>false, '1'=>$estudiante["nombre"]." (Edad y/o gestión del estudiante no válida para inscripción)");
                $edadValida = false;
            }
        }
        if($nivel == 12) {
            if($estudiante["gestion_nacimiento"] < 2006 or $estudiante["gestion_nacimiento"] > 2012){
                $msg = array('0'=>false, '1'=>$estudiante["nombre"]." (Edad y/o gestión del estudiante no válida para inscripción)");
                $edadValida = false;
            }
        }

        if($estudiante['ci']==""){
            $msg = array('0'=>false, '1'=>$estudiante["nombre"]." (no cuenta con C.I.)");
            $registroValido = false;
        }

        if ($registroValido){
            $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
            if ($nivel == 12){
                if ($tipoDisciplinaPrueba["idDisciplina"] == 19){ //gimnasia artistica
                    if ($prueba == 103 or $prueba == 104){ // nivel I
                        if ($estudiante["edad_gestion"] >= 6 and $estudiante["edad_gestion"]<=9){
                            $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                        } else {
                            $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 6 a 9 años)');
                        }
                    }
                    if ($prueba == 105 or $prueba == 106){ // nivel II
                        if ($estudiante["edad_gestion"] >= 10 and $estudiante["edad_gestion"]<=12){
                            $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                        } else {
                            $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 10 a 12 años)');
                        }
                    }
                }
            } else {
                if ($tipoDisciplinaPrueba["idDisciplina"] == 8){ //natacion
                    if ($prueba == 65 or $prueba == 66 or $prueba == 67 or $prueba == 68 or $prueba == 69 or $prueba == 70 or $prueba == 71 or $prueba == 72 or $prueba == 73 or $prueba == 74 or $prueba == 124 or $prueba == 125 or $prueba == 126 or $prueba == 127 or $prueba == 128 or $prueba == 129 or $prueba == 130 or $prueba == 131 or $prueba == 132 or $prueba == 133){ // promocional
                        if ($estudiante["gestion_nacimiento"] >= 2004 and $estudiante["gestion_nacimiento"]<=2006){
                            $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                        } else {
                            $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 12 a 14 años y 2004 a 2006 en el año de nacimiento)');
                        }
                    }
                    if ($prueba == 114 or $prueba == 115 or $prueba == 116 or $prueba == 117 or $prueba == 118 or $prueba == 119 or $prueba == 120 or $prueba == 121 or $prueba == 122 or $prueba == 123 or $prueba == 138 or $prueba == 139 or $prueba == 140 or $prueba == 141 or $prueba == 142 or $prueba == 143 or $prueba == 144 or $prueba == 145 or $prueba == 146 or $prueba == 147){ // avanzado
                        if ($estudiante["gestion_nacimiento"] >= 1999 and $estudiante["gestion_nacimiento"]<=2003){
                            $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                        } else {
                            $$inscripcionEstudianteEntitymsg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 15 a 19 años y 1999 a 2003 en el año de nacimiento)');
                        }
                    }
                }

                if ($tipoDisciplinaPrueba["idDisciplina"] == 2){ //atletismo
                    if ($prueba == 148 or $prueba == 149 or $prueba == 150 or $prueba == 151 or $prueba == 152 or $prueba == 153 or $prueba == 154 or $prueba == 155 or $prueba == 156 or $prueba == 157 or $prueba == 158 or $prueba == 159 or $prueba == 160 or $prueba == 161 or $prueba == 162 or $prueba == 163 or $prueba == 164 or $prueba == 165 or $prueba == 166 or $prueba == 167 or $prueba == 168 or $prueba == 169 or $prueba == 170 or $prueba == 171 or $prueba == 172 or $prueba == 173 or $prueba == 174 or $prueba == 175 or $prueba == 176 or $prueba == 177){ // 12-14
                        if ($estudiante["gestion_nacimiento"] >= 2004 and $estudiante["gestion_nacimiento"]<=2006){
                            $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                        } else {
                            $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 12 a 14 años y 2004 a 2006 en el año de nacimiento)');
                        }
                    }
                    if ($prueba == 178 or $prueba == 179 or $prueba == 180 or $prueba == 181 or $prueba == 182 or $prueba == 183 or $prueba == 184 or $prueba == 185 or $prueba == 186 or $prueba == 187 or $prueba == 188 or $prueba == 189 or $prueba == 190 or $prueba == 191 or $prueba == 192 or $prueba == 193 or $prueba == 194 or $prueba == 195 or $prueba == 196 or $prueba == 197 or $prueba == 198 or $prueba == 199 or $prueba == 200 or $prueba == 201 or $prueba == 202 or $prueba == 203 or $prueba == 204 or $prueba == 205 or $prueba == 206 or $prueba == 207 or $prueba == 208 or $prueba == 209 or $prueba == 210 or $prueba == 211 or $prueba == 212 or $prueba == 213){ // 15-19
                        if ($estudiante["gestion_nacimiento"] >= 1999 and $estudiante["gestion_nacimiento"]<=2003){
                            $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                        } else {
                            $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 15 a 19 años y 1999 a 2003 en el año de nacimiento)');
                        }
                    }
                }

                if ($tipoDisciplinaPrueba["idDisciplina"] == 6){ //ciclismo
                    if (count($inscripcionEstudianteEntity) > 0){
                        if($inscripcionEstudianteGestionDisciplinaFase[0]){
                            if ($prueba == 47 or $prueba == 48 or $prueba == 49 or $prueba == 50){ // rutera
                                $inscripcion110 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,110,$fase);
                                if ($inscripcion110[0]){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en montañera)');
                                }
                                $inscripcion111 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,111,$fase);
                                if ($inscripcion111[0]){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en montañera)');
                                }
                                $inscripcion112 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,112,$fase);
                                if ($inscripcion112[0]){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en montañera)');
                                }
                                $inscripcion113 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,113,$fase);
                                if ($inscripcion113[0]){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en montañera)');
                                }
                            }
                            if ($prueba == 110 or $prueba == 111 or $prueba == 112 or $prueba == 113){ // montañera
                                $inscripcion47 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,47,$fase);
                                if ($inscripcion47[0]){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en rutera)');
                                }
                                $inscripcion48 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,48,$fase);
                                if ($inscripcion48[0]){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en rutera)');
                                }
                                $inscripcion49 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,49,$fase);
                                if ($inscripcion49[0]){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en rutera)');
                                }
                                $inscripcion50 = $this->verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,50,$fase);
                                if ($inscripcion50[0]){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en rutera)');
                                }
                            }
                        }
                    }
                }
            }

            if ($cantidadIncritosGestionPruebaFase < $tipoDisciplinaPrueba["cupo"]){
                if($inscripcionEstudianteGestionPruebaFase[0]){
                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en: '.$tipoDisciplinaPrueba["prueba"].')');
                } else {
                    if(count($inscripcionEstudianteEntity) > 0){
                        if ($nivel == 12){
                            /**
                             ** Validación de nivel primario
                             */

                            if($tipoDisciplinaPrueba["tipoDisciplina"] == 'Individual'){
                                if($inscripcionEstudianteEntity[0]["cant_dis_indi"] > 0){
                                    switch ($tipoDisciplinaPrueba["idDisciplina"]) {
                                        case 18: //natacion
                                            if($inscripcionEstudianteGestionDisciplinaFase[0]){
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                            } else {
                                                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en una disciplina individual)');
                                            }
                                            break;
                                        default : //otros
                                            $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en una disciplina individual)');
                                            break;
                                    }
                                } else {
                                    $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                }
                                if ($tipoDisciplinaPrueba["idDisciplina"] == 19){ //gimnasia artistica
                                    if ($prueba == 103 or $prueba == 104){ // nivel I
                                        if ($estudiante["edad_gestion"] >= 6 and $estudiante["edad_gestion"]<=9){
                                            $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                        } else {
                                            $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 6 a 9 años)');
                                        }
                                    }
                                    if ($prueba == 105 or $prueba == 106){ // nivel II
                                        if ($estudiante["edad_gestion"] >= 10 and $estudiante["edad_gestion"]<=12){
                                            $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                        } else {
                                            $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 10 a 12 años)');
                                        }
                                    }
                                } else {
                                    $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                }
                            } else {
                                if($inscripcionEstudianteEntity[0]["cant_dis_conj"] > 0){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en una disciplina conjunto)');
                                } else {
                                    $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                }
                            }
                        } else {
                            /**
                             ** Validación de nivel secundario
                             */
                            if($tipoDisciplinaPrueba["tipoDisciplina"] == 'Individual'){
                                if($inscripcionEstudianteEntity[0]["cant_dis_indi"] > 0){
                                    if($inscripcionEstudianteGestionDisciplinaFase[0]){
                                        switch ($tipoDisciplinaPrueba["idDisciplina"]) {
                                            case 2: //atletismo
                                                if($tipoDisciplinaPrueba["tipoPrueba"] == 'Individual'){
                                                    if($inscripcionEstudianteEntity[0]["cant_pru_indi_atle"] >= 2){
                                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (1 no puede registrarse en mas pruebas individuales)');
                                                    } else {
                                                        $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                    }
                                                } else {
                                                    if($inscripcionEstudianteEntity[0]["cant_pru_conj_atle"] > 0){
                                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (no puede registrarse en mas pruebas conjunto)');
                                                    } else {
                                                        $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                    }
                                                }
                                                break;
                                            case 6: //ciclismo
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                break;
                                            case 8: //natacion
                                                if($tipoDisciplinaPrueba["tipoPrueba"] == 'Individual'){
                                                    if($inscripcionEstudianteEntity[0]["cant_pru_indi_nat"] >= 3){
                                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (no puede registrarse en mas pruebas individuales)');
                                                    } else {
                                                        $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                    }
                                                } else {
                                                    if($inscripcionEstudianteEntity[0]["cant_pru_conj_nat"] > 0){
                                                        $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (no puede registrarse en mas pruebas conjunto)');
                                                    } else {
                                                        $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                    }
                                                }
                                                break;
                                            case 9: //raquetbol
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                break;
                                            case 10: //raqueta fronton
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                break;
                                            case 11: //tenis de mesa
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                                break;
                                            default : //otros
                                                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en una disciplina individual)');
                                                break;
                                        }
                                    }
                                } else {
                                    $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                }
                                /*if ($tipoDisciplinaPrueba["idDisciplina"] == 8){ //natacion
                                    if ($prueba == 65 or $prueba == 66 or $prueba == 67 or $prueba == 68 or $prueba == 69 or $prueba == 70 or $prueba == 71 or $prueba == 72 or $prueba == 73 or $prueba == 74 or $prueba == 124 or $prueba == 125 or $prueba == 126 or $prueba == 127 or $prueba == 128 or $prueba == 129 or $prueba == 130 or $prueba == 131 or $prueba == 132 or $prueba == 133){ // promocional
                                        if ($estudiante["gestion_nacimiento"] >= 2003 and $estudiante["gestion_nacimiento"]<=2005){
                                            $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                        } else {
                                            $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 12 a 14 años y 2003 a 2005 en el año de nacimiento)');
                                        }
                                    }
                                    if ($prueba == 114 or $prueba == 115 or $prueba == 116 or $prueba == 117 or $prueba == 118 or $prueba == 119 or $prueba == 120 or $prueba == 121 or $prueba == 122 or $prueba == 123 or $prueba == 138 or $prueba == 139 or $prueba == 140 or $prueba == 141 or $prueba == 142 or $prueba == 143 or $prueba == 144 or $prueba == 145 or $prueba == 146 or $prueba == 147){ // avanzado
                                        if ($estudiante["gestion_nacimiento"] >= 1998 and $estudiante["gestion_nacimiento"]<=2002){
                                            $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                        } else {
                                            $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (Su edad debe estar en el rango de 10 a 12 años y 1998 a 2002 en el año de nacimiento)');
                                        }
                                    }
                                } else {
                                    $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                }
                                if ($tipoDisciplinaPrueba["idDisciplina"] == 6){ //ciclismo
                                    if (count($inscripcionEstudianteEntity) > 0){
                                        if ($prueba == 47 or $prueba == 48 or $prueba == 49 or $prueba == 50){ // rutera
                                            if($inscripcionEstudianteGestionDisciplinaFase[0]){
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                            } else {
                                                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en montañera)');
                                            }
                                        }
                                        if ($prueba == 110 or $prueba == 111 or $prueba == 112 or $prueba == 113){ // montañera
                                            if($inscripcionEstudianteGestionDisciplinaFase[0]){
                                                $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                            } else {
                                                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya cuenta se encuentra participando en rutera)');
                                            }
                                        }
                                    }
                                } else {
                                    $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                }*/
                            } else {
                                if($inscripcionEstudianteEntity[0]["cant_dis_conj"] > 0){
                                    $msg = array('0'=>false, '1'=>$estudiante["nombre"].' (ya registrado en una disciplina conjunto)');
                                } else {
                                    $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                                }
                            }
                        }
                    } else {
                        $msg = array('0'=>true, '1'=>$estudiante["nombre"]);
                    }
                }
            } else {
                $msg = array('0'=>false, '1'=>$estudiante["nombre"].' ('.$cantidadIncritosGestionPruebaFase.' estudiantes registrados, no es posible incluir mas)');
            }
        } else {
            $msg = array('0'=>false, '1'=>$estudiante["nombre"]." (Edad y/o gestión del estudiante no válida para inscripción)");
        }
        return $msg;
    }

    /**
     * get prueba
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function validaInscripcionCulturalJuegos($inscripcionEstudiante,$gestion,$prueba,$fase,$nivel,$entidadUsuarioId) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select * from jdp_estudiante_inscripcion_juegos as eij
            where eij.estudiante_inscripcion_id = ".$inscripcionEstudiante." and eij.gestion_tipo_id = ".$gestion." and eij.fase_tipo_id = ".$fase." and eij.prueba_tipo_id  = 0
             ");
        $query->execute();
        $inscripcionEstudianteEntity = $query->fetchAll();


        $query = $em->getConnection()->prepare("
            select eij.* as cantidad from jdp_estudiante_inscripcion_juegos as eij
            inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
            inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
            inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
            inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
            where eij.gestion_tipo_id = ".$gestion." and eij.fase_tipo_id = ".$fase." and eij.prueba_tipo_id  = 0 and cast(left(cast(jg.distrito_tipo_id as varchar),1) as integer) = ".$entidadUsuarioId."
             ");
        $query->execute();
        $inscripcionEstudianteComisionEntity = $query->fetchAll();

        if($entidadUsuarioId == 9){
            $cupo = 48;
        } else {
            $cupo = 48;
        }

        /*
        **************** INCLUIR LA VERIFICACION DEL CUPO ************************
        */

        $valida = true;

        $arrayEstudiante = $this->verificaInscripcionEstudiante($inscripcionEstudiante);

        if(count($inscripcionEstudianteEntity) > 0){
            $msg = array('0'=>false, '1'=>$arrayEstudiante["nombre"].' (ya registrado');
            $valida = false;
        }

        if($nivel == 12) {
            $msg = array('0'=>false, '1'=>$arrayEstudiante["nombre"].' no puede registrarse por pertenecer a educación Primaria');
            $valida = false;
        }

        if(count($inscripcionEstudianteComisionEntity) >= $cupo){
            $msg = array('0'=>false, '1'=>$arrayEstudiante["nombre"].' (cupo completo');
            $valida = false;
        }

        if($valida){
            $valida = true;
            $msg = array('0'=>true, '1'=>$arrayEstudiante["nombre"]);
        }

        return $msg;
    }



    /**
     * get verificacion inscripcion
     * @param type $inscripcionEstudiante
     * @param type $gestion
     * @param type $prueba
     * @param type $fase
     * return array validacion
     */
    public function verificaInscripcionEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,$prueba,$fase){
        $repositoryVerInsPru = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $queryVerInsPru = $repositoryVerInsPru->createQueryBuilder('eij')
            ->select('eij.id as inscripcionId, pt.id as pruebaId, dt.id as disciplinaId, e.paterno, e.materno, e.nombre')
            ->leftJoin('SieAppWebBundle:JdpPruebaTipo','pt','WITH','pt.id = eij.pruebaTipo')
            ->leftJoin('SieAppWebBundle:JdpDisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->leftJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
            ->leftJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
            ->where('eij.estudianteInscripcion = :codInscripcion')
            ->andwhere('eij.pruebaTipo = :codPrueba')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->setParameter('codInscripcion', $inscripcionEstudiante)
            ->setParameter('codPrueba', $prueba)
            ->setParameter('codGestion', $gestion)
            ->setParameter('codFase', $fase)
            ->getQuery();
        $verInsPru = $queryVerInsPru->getArrayResult();
        if (count($verInsPru) > 0){
            return array('0'=>true, '1'=>$verInsPru[0]["nombre"].' '.$verInsPru[0]["paterno"].' '.$verInsPru[0]["materno"]);
        } else {
            return array('0'=>false, '1'=>'');
        }
    }

    /**
     * get verificacion inscripcion
     * @param type $inscripcionEstudiante
     * @param type $gestion
     * @param type $prueba
     * @param type $fase
     * return array validacion
     */
    public function verificaInscripcionActivoEstudianteGestionPruebaFase($inscripcionEstudiante,$gestion,$prueba,$fase){
        $repositoryVerInsPru = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $queryVerInsPru = $repositoryVerInsPru->createQueryBuilder('eij')
            ->select('eij.id as inscripcionId, pt.id as pruebaId, dt.id as disciplinaId, e.paterno, e.materno, e.nombre')
            ->leftJoin('SieAppWebBundle:JdpPruebaTipo','pt','WITH','pt.id = eij.pruebaTipo')
            ->leftJoin('SieAppWebBundle:JdpDisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->leftJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
            ->leftJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
            ->where('eij.estudianteInscripcion = :codInscripcion')
            ->andwhere('eij.pruebaTipo = :codPrueba')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->andwhere('eij.esactivo = true')
            ->setParameter('codInscripcion', $inscripcionEstudiante)
            ->setParameter('codPrueba', $prueba)
            ->setParameter('codGestion', $gestion)
            ->setParameter('codFase', $fase)
            ->getQuery();
        $verInsPru = $queryVerInsPru->getArrayResult();
        if (count($verInsPru) > 0){
            return array('0'=>true, '1'=>$verInsPru[0]["nombre"].' '.$verInsPru[0]["paterno"].' '.$verInsPru[0]["materno"]);
        } else {
            return array('0'=>false, '1'=>'');
        }
    }

    /**
     * get verificacion inscripcion
     * @param type $inscripcionEstudiante
     * @param type $gestion
     * @param type $prueba
     * @param type $fase
     * return array validacion
     */
    public function verificaInscripcionEstudianteGestionDisciplinaFase($inscripcionEstudiante,$gestion,$prueba,$fase){
        $em = $this->getDoctrine()->getManager();
        $objEntidad = $em->getRepository('SieAppWebBundle:PruebaTipo')->findOneBy(array('id' => $prueba));
        $disciplina = $objEntidad->getDisciplinaTipo()->getId();
        $repositoryVerInsPru = $this->getDoctrine()->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos');
        $queryVerInsPru = $repositoryVerInsPru->createQueryBuilder('eij')
            ->select('eij.id as inscripcionId, pt.id as pruebaId, dt.id as disciplinaId, e.paterno, e.materno, e.nombre')
            ->leftJoin('SieAppWebBundle:PruebaTipo','pt','WITH','pt.id = eij.pruebaTipo')
            ->leftJoin('SieAppWebBundle:DisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->leftJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
            ->leftJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
            ->where('eij.estudianteInscripcion = :codInscripcion')
            ->andwhere('pt.disciplinaTipo = :codDisciplina')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->setParameter('codInscripcion', $inscripcionEstudiante)
            ->setParameter('codDisciplina', $disciplina)
            ->setParameter('codGestion', $gestion)
            ->setParameter('codFase', $fase)
            ->getQuery();
        $verInsPru = $queryVerInsPru->getArrayResult();
        if (count($verInsPru) > 0){
            return array('0'=>true, '1'=>$verInsPru[0]["nombre"].' '.$verInsPru[0]["paterno"].' '.$verInsPru[0]["materno"]);
        } else {
            return array('0'=>false, '1'=>'');
        }
    }

    /**
     * get estudiante
     * @param type $inscripcionEstudiante
     * return array validacion
     */
    public function verificaInscripcionEstudiante($inscripcionEstudiante){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select e.paterno, e.materno, e.nombre, e.carnet_identidad, case when e.carnet_identidad is null then '' when trim(e.carnet_identidad) = '' then '' when trim(e.complemento) = '0' then '' else e.complemento end as complemento, ie.id as sie, ie.institucioneducativa, to_char(e.fecha_nacimiento, 'yyyy') as gestion_nacimiento, to_char(e.fecha_nacimiento, 'MM') as mes_nacimiento, to_char(e.fecha_nacimiento, 'dd') as dia_nacimiento, (cast(to_char(cast('2019-03-30' as date),'yyyyMMdd') as integer) - cast(to_char(e.fecha_nacimiento,'yyyyMMdd') as integer))/10000 as edad, (cast(to_char(now(),'yyyy') as integer) - cast(to_char(e.fecha_nacimiento,'yyyy') as integer)) as edad_gestion
            from estudiante_inscripcion as ei
            inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
            inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
            inner join estudiante as e on e.id = ei.estudiante_id
            where ei.id = ". $inscripcionEstudiante ."
        ");
        $query->execute();
        $verInsPru = $query->fetchAll();

        if (count($verInsPru) > 0){
            return array('gestion_nacimiento'=>$verInsPru[0]["gestion_nacimiento"], 'edad'=>$verInsPru[0]["edad"], 'edad_gestion'=>$verInsPru[0]["edad_gestion"],'sie'=>$verInsPru[0]["sie"],'ue'=>$verInsPru[0]["institucioneducativa"],'nombre'=>$verInsPru[0]["nombre"].' '.$verInsPru[0]["paterno"].' '.$verInsPru[0]["materno"], 'ci'=>$verInsPru[0]["carnet_identidad"].(($verInsPru[0]["complemento"] == "") ? "-".$verInsPru[0]["complemento"] : ""));
        } else {
            return '';
        }
    }


    /**
     * get estudiante
     * @param type $inscripcionEstudiante
     * return array validacion
     */
    public function verificaEstadoInscripcionEstudiante($inscripcionEstudiante){
        $em = $this->getDoctrine()->getManager();
        $query = $em->getConnection()->prepare("
            select e.paterno, e.materno, e.nombre, e.carnet_identidad, e.complemento, ie.id as sie, ie.institucioneducativa, to_char(e.fecha_nacimiento, 'yyyy') as gestion_nacimiento, emt.id as estadomatricula_id, emt.estadomatricula
            from estudiante_inscripcion as ei
            inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
            inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
            inner join estudiante as e on e.id = ei.estudiante_id
            inner join estadomatricula_tipo as emt on emt.id = ei.estadomatricula_tipo_id
            where ei.id = ". $inscripcionEstudiante ." and ei.estadomatricula_tipo_id in (4,5,11,55,56,57,58,45,26,27,0)
        ");
        $query->execute();
        $verInsPru = $query->fetchAll();

        if (count($verInsPru) > 0){
            return true;
        } else {
            return false;
        }
    }

    /**
     * get verificacion inscripcion
     * @param type $inscripcionEstudiante
     * @param type $gestion
     * @param type $prueba
     * @param type $fase
     * return array validacion
     */
    public function verificaTipoDisciplinaPrueba($prueba, $xCupo){
        $repositoryVerInsPru = $this->getDoctrine()->getRepository('SieAppWebBundle:PruebaTipo');
        $queryVerDisPru = $repositoryVerInsPru->createQueryBuilder('pt')
            ->select('pt.id as prueba_id, pt.prueba, dt.id as disciplina_id, dt.disciplina, dt.cantidad, pt.clasificadorTipo as clasificador_tipo')
            ->leftJoin('SieAppWebBundle:DisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->where('pt.id = :codPrueba')
            ->setParameter('codPrueba', $prueba)
            ->getQuery();
        $verInsDisPru = $queryVerDisPru->getArrayResult();
        switch ($verInsDisPru[0]["disciplina_id"]) {
            case 3:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                $cupo = $verInsDisPru[0]["cantidad"];
                break;
            case 4:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                $cupo = $verInsDisPru[0]["cantidad"];
                break;
            case 5:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                $cupo = $verInsDisPru[0]["cantidad"];
                break;
            case 7:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                $cupo = $verInsDisPru[0]["cantidad"];
                break;
            case 14:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                $cupo = $verInsDisPru[0]["cantidad"];
                break;
            case 15:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                $cupo = $verInsDisPru[0]["cantidad"];
                break;
            case 16:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                $cupo = $verInsDisPru[0]["cantidad"];
                break;
            case 17:
                $tipoPrueba = "Conjunto";
                $tipoDisciplina = "Conjunto";
                $cupo = $verInsDisPru[0]["cantidad"];
                break;
            case 13:
                $tipoPrueba = "Individual";
                $tipoDisciplina = "Individual";
                $cupo = $verInsDisPru[0]["cantidad"];
                break;
            case 9:
                $tipoPrueba = "Individual";
                $tipoDisciplina = "Individual";
                if($verInsDisPru[0]["prueba_id"] == 79 or $verInsDisPru[0]["prueba_id"] == 80){
                    $cupo = 2;
                } else {
                    $cupo = 1;
                }
                break;
            case 10:
                $tipoPrueba = "Individual";
                $tipoDisciplina = "Individual";
                $cupo = 3;
                break;
            case 11:
                $tipoPrueba = "Individual";
                $tipoDisciplina = "Individual";
                if($verInsDisPru[0]["prueba_id"] == 85 or $verInsDisPru[0]["prueba_id"] == 86){
                    $cupo = 2;
                } else {
                    $cupo = 1;
                }
                break;
            default:
                $tipoPrueba = "Individual";
                $tipoDisciplina = "Individual";
                $cupo = 1;
                if ($verInsDisPru[0]["prueba_id"] == 27 or $verInsDisPru[0]["prueba_id"] == 28 or $verInsDisPru[0]["prueba_id"] == 63 or $verInsDisPru[0]["prueba_id"] == 64 or $verInsDisPru[0]["prueba_id"] == 75 or $verInsDisPru[0]["prueba_id"] == 76 or $verInsDisPru[0]["prueba_id"] == 174 or $verInsDisPru[0]["prueba_id"] == 175 or $verInsDisPru[0]["prueba_id"] == 200 or $verInsDisPru[0]["prueba_id"] == 201){
                    $cupo = 5;
                }
                break;
        }

        if($verInsDisPru[0]["clasificador_tipo"]=='G'){
            $tipoPrueba = "Conjunto";
        }
        $cupo = $cupo * $xCupo;
        $idPrueba = $verInsDisPru[0]["prueba_id"];
        $idDisciplina = $verInsDisPru[0]["disciplina_id"];
        $prueba = $verInsDisPru[0]["prueba"];
        $disciplina = $verInsDisPru[0]["disciplina"];
        return array('tipoDisciplina'=>$tipoDisciplina,'tipoPrueba'=>$tipoPrueba,'idPrueba'=>$idPrueba,'prueba'=>$prueba,'idDisciplina'=>$idDisciplina,'disciplina'=>$disciplina,'cupo'=>$cupo);
    }

    public function listaEstudiantesInscritosUeDescargaPdfAction($usuario) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $em = $this->getDoctrine()->getManager();

        $queryEntidad = $em->getConnection()->prepare("
                select ie.id, ie.institucioneducativa as nombre from institucioneducativa as ie where ie.id = ".$this->session->get('userName')."
            ");
        $queryEntidad->execute();
        $objEntidad = $queryEntidad->fetchAll();

        if (count($objEntidad) > 0){
        } else {
            $queryEntidad = $em->getConnection()->prepare("
                select ie.id, ie.institucioneducativa as nombre from usuario_rol as ur
                inner join usuario as u on u.id = ur.usuario_id
                inner join persona as p on p.id = u.persona_id
                inner join maestro_inscripcion as mi on mi.persona_id = p.id
                inner join institucioneducativa as ie on ie.id = mi.institucioneducativa_id
                where ur.usuario_id = ".$usuario." and ur.rol_tipo_id = 9 and mi.gestion_tipo_id = ".$gestionActual."
            ");
            $queryEntidad->execute();
            $objEntidad = $queryEntidad->fetchAll();
        }

        $sie = $objEntidad[0]['id'];

        $arch = $sie.'_'.$gestionActual.'_JUEGOS_FPREVIA_UE_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegos_Participaciones_previa_v1.rptdesign&__format=pdf&codue='.$sie.'&codges='.$gestionActual));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }



    public function listaDeportistasClasificadosNacionalDescargaPdfAction($fase,$usuario) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $em = $this->getDoctrine()->getManager();

        $objEntidad = $this->buscaEntidadFase($fase,$usuario);

        if(count($objEntidad)>0){
            $codigoEntidad = $objEntidad[0]['id'];
        } else {
            $codigoEntidad = 0;
        }        
        $nivelId = 13;

        //print_r($codigoEntidad);
        //dump($codigoEntidad);die;
        $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F'.$fase.'_'.date('YmdHis').'.xls';
        $response = new Response();
        //$response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        //$response->headers->set('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');  
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesAcompañantesJuegos_f3_v2.rptdesign&__format=xls&coddep='.$codigoEntidad.'&codges='.$gestionActual.'&codniv='.$nivelId));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }


    
    public function listaDeportistasClasificadosSeguimientoDescargaPdfAction($fase,$usuario) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $em = $this->getDoctrine()->getManager();

        $objEntidad = $this->buscaEntidadFase($fase,$usuario);

        if(count($objEntidad)>0){
            $codigoEntidad = $objEntidad[0]['id'];
        } else {
            $codigoEntidad = 0;
        }        
        $nivelId = 13;

        //print_r($codigoEntidad);
        //dump($codigoEntidad);die;
        $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F'.$fase.'_'.date('YmdHis').'.xls';
        $response = new Response();
        //$response->headers->set('Content-type', 'application/pdf');
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        //$response->headers->set('Content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');  
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_est_general_seguimiento_v1_rcm.rptdesign&__format=xls&dep='.$codigoEntidad.'&gestion='.$gestionActual.'&fase=4'));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function estadisticaRegistroUnidadesEducativasFasePreviaDepartamentoDescargaPdfAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $em = $this->getDoctrine()->getManager();

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $fase = 3;

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);

        $codigoEntidad = $objEntidad[0]['id'];

        //print_r($codigoEntidad);

        $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F'.$fase.'_'.date('YmdHis').'.pdf';
        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        //$response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_est_Estudiantes_Participaciones_fp_departamento_v1.rptdesign&__format=pdf&coddep='.$codigoEntidad.'&codges='.$gestionActual));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }



    public function estadisticaRegistroUnidadesEducativasFasePreviaDistritoDescargaPdfAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $em = $this->getDoctrine()->getManager();

        $sesion = $request->getSession();
        $id_usuario = $this->session->get('userId');

        //validation if the user is logged
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }

        $fase = 1;

        $objEntidad = $this->buscaEntidadFase($fase,$id_usuario);

        $codigoEntidad = $objEntidad[0]['id'];

        //print_r($codigoEntidad);

        $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F'.$fase.'_'.date('YmdHis').'.pdf';

        $response = new Response();
        $response->headers->set('Content-type', 'application/pdf');
        //$response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_est_Estudiantes_Participaciones_fp_distrito_v1.rptdesign&__format=pdf&coddis='.$codigoEntidad.'&codges='.$gestionActual));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function eliminaPruebaInscripcionAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $respuesta = array('registro'=>false, 'msg_correcto' => "", 'msg_incorrecto' => "");
        $inscripcion = $request->get('inscripcion');
        if ($inscripcion != ""){
            $inscripcion = base64_decode($inscripcion);
        }

        $response = new JsonResponse();
        try{
            $entityDatos = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos')->findOneBy(array('id'=>($inscripcion)));
            if ($entityDatos) {
                $borrar = true;
                $nivel = $entityDatos->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
                $fase = $entityDatos->getFaseTipo()->getId();
                $estudiante = $entityDatos->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$entityDatos->getEstudianteInscripcion()->getEstudiante()->getMaterno().' '.$entityDatos->getEstudianteInscripcion()->getEstudiante()->getNombre();
                $pruebaId = $entityDatos->getPruebaTipo()->getId();
                $estudianteInscripcionId = $entityDatos->getEstudianteInscripcion()->getId();

                $registroController = new registroController();
                $registroController->setContainer($this->container);

                $faseActivo = $registroController->getFaseActivo($fase, $nivel, $fechaActual);

                    if (!$faseActivo) {
                        return $response->setData(array(
                            'msg_incorrecto' => 'Inscripción cerrada'
                        ));
                    }

                // $query = $em->getConnection()->prepare("select * from fase_tipo where id = ".$fase);
                // $query->execute();
                // $faseTipoEntity = $query->fetchAll();                

                // if ($nivel == 12 and !$faseTipoEntity[0]['esactivo_primaria']){
                //     $borrar = false;
                // }

                // if ($nivel == 13 and !$faseTipoEntity[0]['esactivo_secundaria']){
                //     $borrar = false;
                // }                

                if($borrar){

                    // $entityDatosFaseSuperior = $this->verificaInscripcionEstudianteGestionDisciplinaFase($estudianteInscripcion,$gestionActual,$pruebaId,($fase+1));
                    $entityDatosFaseSuperior = $this->verificaInscripcionEstudianteGestionPruebaFase($estudianteInscripcionId,$gestionActual,$pruebaId,($fase+1));
                    if (!$entityDatosFaseSuperior[0]){
                        $entityEquipoDatos = $em->getRepository('SieAppWebBundle:JdpEquipoEstudianteInscripcionJuegos')->findOneBy(array('estudianteInscripcionJuegos'=>($inscripcion)));
                        if(count($entityEquipoDatos) > 0){
                            $em->remove($entityEquipoDatos);
                        }
                        $entityEntrenadorDatos = $em->getRepository('SieAppWebBundle:JdpPersonaInscripcionJuegos')->findBy(array('estudianteInscripcionJuegos'=>($inscripcion)));
                        
                        if(count($entityEntrenadorDatos) > 0){
                            foreach ($entityEntrenadorDatos as $entrenadorRegistrado) {
                                $em->remove($entrenadorRegistrado);
                            }                            
                        }
                        $em->remove($entityDatos);
                        $em->flush();
                        $em->getConnection()->commit();
                        // $respuesta = array('0'=>true);
                        // $this->session->getFlashBag()->set('success', array('title' => 'Eliminado', 'message' => "Estudiante ".$estudiante." eliminado"));
                        $respuesta = array('registro'=>true, 'msg_correcto' => "Estudiante ".$estudiante." eliminado", 'msg_incorrecto' => "");
                    } else {
                        $respuesta = array('registro'=>false, 'msg_correcto' => "", 'msg_incorrecto' => 'No puede eliminarse a '.$estudiante.' (clasificado en la fase '.($fase).')');
                        // $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Estudiante ".$estudiante." no puede ser eliminado debido a que se encuentra clasificado en la fase ".$fase));
                    }                    
                } else {
                    //$this->session->getFlashBag()->set('warning', array('title' => 'Error', 'message' => "Las listas estan cerradas, no puede eliminar estudiantes"));
                    $respuesta = array('registro'=>false, 'msg_correcto' => "", 'msg_incorrecto' => 'Las listas estan cerradas, no puede eliminar estudiantes');
                }
            } else {
                $respuesta = array('registro'=>false, 'msg_correcto' => "", 'msg_incorrecto' => 'No se encontro el registro');
            }
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            $respuesta = array('registro'=>false, 'msg_correcto' => "", 'msg_incorrecto' => 'No se puede eliminar la inscripcion del estudiante');
        }
        return $response->setData($respuesta);
    }


    /**
     * get prueba
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function validaCantidadGestionPruebaFaseUe($gestion,$prueba,$fase,$codigoEntidad,$posicion) {
        $em = $this->getDoctrine()->getManager();
        if ($fase == 1){
            $query = $em->getConnection()->prepare("
                select count(eij.id) as cantidad
                from estudiante_inscripcion_juegos as eij
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                where ie.id = ". $codigoEntidad ." and pt.id = ". $prueba ." and eij.gestion_tipo_id = ". $gestion ." and eij.fase_tipo_id = ". $fase ."
            ");
        }
        if ($fase == 2){
            $query = $em->getConnection()->prepare("
                select count(eij.id) as cantidad
                from estudiante_inscripcion_juegos as eij
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
                inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                where lt.id = ". $codigoEntidad ." and pt.id = ". $prueba ." and eij.gestion_tipo_id = ". $gestion ." and eij.fase_tipo_id = ". $fase ." and eij.posicion = ". $posicion ."
            ");
        }
        if ($fase == 3){
            $query = $em->getConnection()->prepare("
                select count(eij.id) as cantidad
                from estudiante_inscripcion_juegos as eij
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
                inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                where jg.circunscripcion_tipo_id = ". $codigoEntidad ." and pt.id = ". $prueba ." and eij.gestion_tipo_id = ". $gestion ." and eij.fase_tipo_id = ". $fase ." and eij.posicion = ". $posicion ."
            ");
        }
        if ($fase == 4){
            $query = $em->getConnection()->prepare("
                select count(eij.id) as cantidad
                from estudiante_inscripcion_juegos as eij
                inner join prueba_tipo as pt on pt.id = eij.prueba_tipo_id
                inner join disciplina_tipo as dt on dt.id = pt.disciplina_tipo_id
                inner join estudiante_inscripcion as ei on ei.id = eij.estudiante_inscripcion_id
                inner join institucioneducativa_curso as iec on iec.id = ei.institucioneducativa_curso_id
                inner join institucioneducativa as ie on ie.id = iec.institucioneducativa_id
                inner join jurisdiccion_geografica as jg on jg.id = ie.le_juridicciongeografica_id
                inner join lugar_tipo as lt on lt.id = jg.lugar_tipo_id_distrito
                inner join lugar_tipo as lt1 on lt1.id = jg.lugar_tipo_id_localidad
                inner join lugar_tipo as lt2 on lt2.id = lt1.lugar_tipo_id
                inner join lugar_tipo as lt3 on lt3.id = lt2.lugar_tipo_id
                inner join lugar_tipo as lt4 on lt4.id = lt3.lugar_tipo_id
                inner join lugar_tipo as lt5 on lt5.id = lt4.lugar_tipo_id
                where lt5.codigo = '". $codigoEntidad ."' and pt.id = ". $prueba ." and eij.gestion_tipo_id = ". $gestion ." and eij.fase_tipo_id = ". $fase ." and eij.posicion = ". $posicion ."
            ");
        }
        $query->execute();
        $inscripcionEstudianteEntity = $query->fetchAll();
        //print_r($inscripcionEstudianteEntity);
        if (count($inscripcionEstudianteEntity) > 0){
            return $inscripcionEstudianteEntity[0]["cantidad"];
        } else {
            return 0;
        }
    }

    public function listaDeportistasClasificadosDescargaPdfAction($fase,$usuario) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $em = $this->getDoctrine()->getManager();

        if (isset($_POST['id'])){
          $codigoEntidad = $_POST['id'];
        } else {
          $objEntidad = $this->buscaEntidadFase($fase,$usuario);
          $codigoEntidad = $objEntidad[0]['id'];
        }

        if (isset($_POST['id'])){
            $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F'.$fase.'_'.date('YmdHis').'.pdf';
            $response = new Response();
            $response->headers->set('Content-type', 'application/pdf');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
            if($fase == 0){
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegos_Participaciones_f0_v2.rptdesign&__format=pdf&codue='.$codigoEntidad.'&codges='.$gestionActual));
            }
            if($fase == 1){
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegos_Participaciones_f1_v1.rptdesign&__format=pdf&coddiseeee='.$codigoEntidad.'&codges='.$gestionActual));
            }
            if($fase == 2){
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegos_Participaciones_f2_v1.rptdesign&__format=pdf&codcir='.$codigoEntidad.'&codges='.$gestionActual));
            }
            if($fase == 3){
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJueogs_Participaciones_foto_v1.rptdesign&__format=pdf&depto='.$codigoEntidad.'&codges='.$gestionActual));
            }
        } else {
            $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F'.$fase.'_'.date('YmdHis').'.xls';
            $response = new Response();
            $response->headers->set('Content-type', 'application/vnd.ms-excel');
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));

            if($fase == 1){
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_est_Estudiantes_Participaciones_f0_distrito_v1.rptdesign&__format=pdf&coddis='.$codigoEntidad.'&codges='.$gestionActual));
            }
            if($fase == 2){
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_est_Estudiantes_Participaciones_f1_circunscripcion_v1.rptdesign&__format=pdf&codcir='.$codigoEntidad.'&codges='.$gestionActual));
            }
            if($fase == 3){
                $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_est_Estudiantes_Participaciones_f2_departamento_v1.rptdesign&__format=pdf&coddep='.$codigoEntidad.'&codges='.$gestionActual));
            }
        }

        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    public function listaDeportistasAspirantesDescargaPdfAction($fase,$usuario) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $em = $this->getDoctrine()->getManager();

        $objEntidad = $this->buscaEntidadFase($fase,$usuario);

        $codigoEntidad = $objEntidad[0]['id'];

        $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F'.$fase.'_UE_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        if($fase == 1){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesAcompañantesJuegos_Aspirantes_f1_v1.rptdesign&__format=xls&coddis='.$codigoEntidad.'&codges='.$gestionActual));
        }
        if($fase == 2){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesAcompañantesJuegos_Aspirantes_f2_v1.rptdesign&__format=xls&codcir='.$codigoEntidad.'&codges='.$gestionActual));
        }
        if($fase == 3){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesAcompañantesJuegos_Aspirantes_f3_v1.rptdesign&__format=xls&coddep='.$codigoEntidad.'&codges='.$gestionActual));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }



    public function estadisticaDeportistasAspirantesDescargaPdfAction($fase,$usuario) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $em = $this->getDoctrine()->getManager();

        $objEntidad = $this->buscaEntidadFase($fase,$usuario);

        $codigoEntidad = $objEntidad[0]['id'];

        $arch = $codigoEntidad.'_'.$gestionActual.'_JUEGOS_F'.$fase.'_UE_'.date('YmdHis').'.xls';
        $response = new Response();
        $response->headers->set('Content-type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', $arch));
        if($fase == 1){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegos_Aspirantes_f1_v1.rptdesign&__format=xls&coddis='.$codigoEntidad.'&codges='.$gestionActual));
        }
        if($fase == 2){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegos_Aspirantes_f2_v1.rptdesign&__format=xls&codcir='.$codigoEntidad.'&codges='.$gestionActual));
        }
        if($fase == 3){
            $response->setContent(file_get_contents($this->container->getParameter('urlreportweb') . 'jdp_lst_EstudiantesJuegos_Aspirantes_f3_v1.rptdesign&__format=xls&coddep='.$codigoEntidad.'&codges='.$gestionActual));
        }
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * get inscritos
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function buscaInscritosDistrito($distrito,$fase) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        // // $gestionActual = 2018;

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->select("eij.id as eInsId, pat.paralelo, pt.prueba as prueba, dt.disciplina as disciplina, e.paterno, e.materno, e.nombre, e.fechaNacimiento, e.codigoRude, e.carnetIdentidad, e.complemento, lt6.id as distritoId, lt6.lugar as distrito, ie.institucioneducativa, get.genero, coalesce(eeij.equipoId,0) as equipoId, coalesce(eeij.equipoNombre,'') as equipoNombre")
                ->innerJoin('SieAppWebBundle:JdpPruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:GeneroTipo', 'get', 'WITH', 'get.id = pt.generoTipo')
                ->innerJoin('SieAppWebBundle:JdpDisciplinaTipo', 'dt', 'WITH', 'dt.id = pt.disciplinaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->innerJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->innerJoin('SieAppWebBundle:JurisdiccionGeografica','jg','WITH','jg.id = ie.leJuridicciongeografica')
                ->innerJoin('SieAppWebBundle:ParaleloTipo','pat','WITH','pat.id = iec.paraleloTipo')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->innerJoin('SieAppWebBundle:JdpFaseTipo', 'ft', 'WITH', 'ft.id = eij.faseTipo')
                //->innerJoin('SieAppWebBundle:LugarTipo','lt1','WITH','lt1.id = jg.lugarTipoLocalidad')
                //->innerJoin('SieAppWebBundle:LugarTipo','lt2','WITH','lt2.id = lt1.lugarTipo')
                //->innerJoin('SieAppWebBundle:LugarTipo','lt3','WITH','lt3.id = lt2.lugarTipo')
                //->innerJoin('SieAppWebBundle:LugarTipo','lt4','WITH','lt4.id = lt3.lugarTipo')
                //->innerJoin('SieAppWebBundle:LugarTipo','lt5','WITH','lt5.id = lt4.lugarTipo')
                ->innerJoin('SieAppWebBundle:LugarTipo','lt6','WITH','lt6.id = jg.lugarTipoIdDistrito')
                ->leftJoin('SieAppWebBundle:JdpEquipoEstudianteInscripcionJuegos','eeij','WITH','eeij.estudianteInscripcionJuegos = eij.id')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ft.id = :faseId')
                ->andWhere('lt6.id = :distritoId')
                ->setParameter('distritoId', $distrito)
                ->setParameter('faseId', $fase)
                ->setParameter('gestionId', $gestionActual)
                ->orderBy('ie.institucioneducativa, dt.disciplina, pt.prueba, e.generoTipo, e.paterno, e.materno, e.nombre')
                ->getQuery();
        $aInscritos = $query->getResult();
        //if(count($aInscritos)>0){
        //    foreach ($aInscritos as $inscrito) {
        //        $ainscritos[$inscrito->getId()] = $inscrito->getEstudianteInscripcion()->getEstudiante()->getNombre().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getMaterno();
        //    }
        //} else {
        //    $ainscritos = array();
        //}
        //$response = new JsonResponse();
        //return $response->setData(array('ainscritos' => $ainscritos));
        return $aInscritos;
    }

        /**
     * get inscritos
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function buscaInscritosCircunscripcion($circunscripcion,$fase) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->select('eij.id as eInsId, pat.paralelo, pt.prueba as prueba, dt.disciplina as disciplina, e.paterno, e.materno, e.nombre, e.fechaNacimiento, e.codigoRude, e.carnetIdentidad, e.complemento, ct.id as circunscripcionId, ct.circunscripcion as circunscripcion, ie.institucioneducativa, get.genero')
                ->innerJoin('SieAppWebBundle:PruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:GeneroTipo', 'get', 'WITH', 'get.id = pt.generoTipo')
                ->innerJoin('SieAppWebBundle:DisciplinaTipo', 'dt', 'WITH', 'dt.id = pt.disciplinaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->leftJoin('SieAppWebBundle:JurisdiccionGeografica','jg','WITH','jg.id = ie.leJuridicciongeografica')
                ->leftJoin('SieAppWebBundle:CircunscripcionTipo','ct','WITH','ct.id = jg.circunscripcionTipo')
                ->innerJoin('SieAppWebBundle:ParaleloTipo','pat','WITH','pat.id = iec.paraleloTipo')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->innerJoin('SieAppWebBundle:FaseTipo', 'ft', 'WITH', 'ft.id = eij.faseTipo')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ft.id = :faseId')
                ->andWhere('ct.id = :circunscripcionId')
                ->setParameter('circunscripcionId', $circunscripcion)
                ->setParameter('faseId', $fase)
                ->setParameter('gestionId', $gestionActual)
                ->orderBy('e.paterno, e.materno, e.nombre, dt.disciplina, pt.prueba')
                ->getQuery();
        $aInscritos = $query->getResult();
        //if(count($aInscritos)>0){
        //    foreach ($aInscritos as $inscrito) {
        //        $ainscritos[$inscrito->getId()] = $inscrito->getEstudianteInscripcion()->getEstudiante()->getNombre().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getMaterno();
        //    }
        //} else {
        //    $ainscritos = array();
        //}
        //$response = new JsonResponse();
        //return $response->setData(array('ainscritos' => $ainscritos));
        return $aInscritos;
    }

        /**
     * get inscritos
     * @param type $nivelId
     * @param type $generoId
     * return list of pruebas
     */
    public function buscaInscritosDepartamento($departamento,$fase) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;

        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('SieAppWebBundle:EstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
                ->select('eij.id as eInsId, pat.paralelo, pt.prueba as prueba, dt.disciplina as disciplina, e.paterno, e.materno, e.nombre, e.fechaNacimiento, e.codigoRude, e.carnetIdentidad, e.complemento, lt5.id as departamentoId, lt5.lugar as departamento, ie.institucioneducativa, get.genero')
                ->innerJoin('SieAppWebBundle:PruebaTipo', 'pt', 'WITH', 'pt.id = eij.pruebaTipo')
                ->innerJoin('SieAppWebBundle:GeneroTipo', 'get', 'WITH', 'get.id = pt.generoTipo')
                ->innerJoin('SieAppWebBundle:DisciplinaTipo', 'dt', 'WITH', 'dt.id = pt.disciplinaTipo')
                ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
                ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
                ->leftJoin('SieAppWebBundle:Institucioneducativa', 'ie', 'WITH', 'ie.id = iec.institucioneducativa')
                ->leftJoin('SieAppWebBundle:JurisdiccionGeografica','jg','WITH','jg.id = ie.leJuridicciongeografica')
                ->leftJoin('SieAppWebBundle:LugarTipo','lt1','WITH','lt1.id = jg.lugarTipoLocalidad')
                ->leftJoin('SieAppWebBundle:LugarTipo','lt2','WITH','lt2.id = lt1.lugarTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo','lt3','WITH','lt3.id = lt2.lugarTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo','lt4','WITH','lt4.id = lt3.lugarTipo')
                ->leftJoin('SieAppWebBundle:LugarTipo','lt5','WITH','lt5.id = lt4.lugarTipo')
                ->innerJoin('SieAppWebBundle:ParaleloTipo','pat','WITH','pat.id = iec.paraleloTipo')
                ->innerJoin('SieAppWebBundle:Estudiante','e','WITH','e.id = ei.estudiante')
                ->innerJoin('SieAppWebBundle:GestionTipo', 'gt', 'WITH', 'gt.id = eij.gestionTipo')
                ->innerJoin('SieAppWebBundle:FaseTipo', 'ft', 'WITH', 'ft.id = eij.faseTipo')
                ->andWhere('gt.id = :gestionId')
                ->andWhere('ft.id = :faseId')
                ->andWhere('dt.nivelTipo = 13')
                ->andWhere('lt5.codigo = :departamentoId')
                ->setParameter('departamentoId', $departamento)
                ->setParameter('faseId', $fase)
                ->setParameter('gestionId', $gestionActual)
                ->orderBy('ie.id, dt.disciplina, pt.prueba, e.paterno, e.materno, e.nombre')
                ->getQuery();
        $aInscritos = $query->getResult();
        //if(count($aInscritos)>0){
        //    foreach ($aInscritos as $inscrito) {
        //        $ainscritos[$inscrito->getId()] = $inscrito->getEstudianteInscripcion()->getEstudiante()->getNombre().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$inscrito->getEstudianteInscripcion()->getEstudiante()->getMaterno();
        //    }
        //} else {
        //    $ainscritos = array();
        //}
        //$response = new JsonResponse();
        //return $response->setData(array('ainscritos' => $ainscritos));
        return $aInscritos;
    }

    public function eliminaInscripcionAction(Request $request) {
        date_default_timezone_set('America/La_Paz');
        $fechaActual = new \DateTime(date('Y-m-d'));
        $gestionActual = date_format($fechaActual,'Y');
        // $gestionActual = 2018;
        $em = $this->getDoctrine()->getManager();
        $em->getConnection()->beginTransaction();
        $respuesta = array();

        $borrar = true;
        $inscripcion = base64_decode($request->get('inscripcion'));
        $fase = $request->get('fase');

        try{

            $registroController = new registroController();
            $registroController->setContainer($this->container);

            $faseActivo = $registroController->getFaseActivo($faseClasificacion, $nivelId, $fechaActual);

                if (!$faseActivo) {
                    return $response->setData(array(
                        'msg_incorrecto' => 'Inscripción cerrada'
                    ));
                }

            $entityDatos = $em->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos')->findOneBy(array('id'=>$inscripcion));
            if ($entityDatos) {

                $nivel = $entityDatos->getPruebaTipo()->getDisciplinaTipo()->getNivelTipo()->getId();
                $fase = $entityDatos->getFaseTipo()->getId();
                $estudiante = $entityDatos->getEstudianteInscripcion()->getEstudiante()->getPaterno().' '.$entityDatos->getEstudianteInscripcion()->getEstudiante()->getMaterno().' '.$entityDatos->getEstudianteInscripcion()->getEstudiante()->getNombre();
                $estudianteInscripcion = $entityDatos->getEstudianteInscripcion()->getId();
                $pruebaId = $entityDatos->getPruebaTipo()->getId();

                // $query = $em->getConnection()->prepare("select * from fase_tipo where id = ".$fase);
                // $query->execute();
                // $faseTipoEntity = $query->fetchAll();

                // if ($nivel == 12 and !$faseTipoEntity[0]['esactivo_primaria']){
                //     $borrar = false;
                // }

                // if ($nivel == 13 and !$faseTipoEntity[0]['esactivo_secundaria']){
                //     $borrar = false;
                // }

                if($borrar){
                    $entityDatosFaseSuperior = $this->verificaInscripcionEstudianteGestionPruebaFase($estudianteInscripcion,$gestionActual,$pruebaId,($fase+1));
                    if (!$entityDatosFaseSuperior[0]){
                        $em->remove($entityDatos);
                        $em->flush();
                        $em->getConnection()->commit();
                        $respuesta = array('0'=>true);
                        return $response->setData(array(
                            'registro'=>true, 'msg_correcto' => "Estudiante ".$estudiante." eliminado"
                        ));
                        // $this->session->getFlashBag()->set('success', array('title' => 'Correcto', 'message' => "Estudiante ".$estudiante." eliminado"));
                    } else {
                        // $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Estudiante ".$estudiante." no puede ser eliminado debido a que se encuentra clasificado en la fase ".$fase));
                        return $response->setData(array(
                            'registro'=>false, 'msg_correcto' => "", 'msg_incorrecto' => "Estudiante ".$estudiante." no puede ser eliminado debido a que se encuentra clasificado en la fase ".$fase
                        ));
                    }
                } else {
                    // $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "Las listas estan cerradas, no puede eliminar estudiantes"));
                    return $response->setData(array(
                        'registro'=>false, 'msg_correcto' => "", 'msg_incorrecto' => "Las listas estan cerradas, no puede eliminar estudiantes"
                    ));
                }
                // return $this->redirectToRoute('sie_juegos_clasificacion_f'.($fase-1).'_index');
            } else {
                $em->getConnection()->rollback();
                // $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "No se puede eliminar la inscripcion del estudiante"));
                // return $this->redirectToRoute('sie_juegos_clasificacion_f'.($fase).'_index');
                return $response->setData(array(
                    'registro'=>false, 'msg_correcto' => "", 'msg_incorrecto' => 'No se puede eliminar la inscripcion del estudiante'
                ));
            }
        } catch (Exception $e) {
            $em->getConnection()->rollback();
            // $this->session->getFlashBag()->set('warning', array('title' => 'Alerta', 'message' => "No se puede eliminar la inscripcion del estudiante"));
            // return $this->redirectToRoute('sie_juegos_clasificacion_f'.($fase).'_index');
            return $response->setData(array(
                'registro'=>false, 'msg_correcto' => "", 'msg_incorrecto' => 'No se puede eliminar la inscripcion del estudiante'
            ));
        }
    }
}
