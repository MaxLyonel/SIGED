<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * GestionesPasadasAreasEstudianteController.php
 * Módulo para regularizar áreas -> estudiantes en gestiones pasadas a la 2020
 */
class GestionesPasadasAreasEstudianteController extends Controller {

    private $session;

    /**
     * Constructor
     */
    public function __construct() {
        $this->session = new Session();
    }

    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $this->session = $request->getSession();
        $id_usuario = $this->session->get('userId');
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }

        $insId = $this->session->get('ie_id');
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($insId);

        return $this->render($this->session->get('pathSystem') . ':GestionesPasadasAreasEstudiante:index.html.twig', array(
            'institucioneducativa' => $institucioneducativa,
            'form' => $this->formSearch()->createView()
        ));
    }

    private function formSearch() {
        $form = $this->createFormBuilder()
                ->add('codigoRude', 'text', array('required' => true))
                ->add('buscar', 'submit', array('label' => 'Buscar'))
                ->getForm();
        return $form;
    }

    public function resultHistorialAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $codigoRude = trim(strtoupper($request->get('codigoRude')));
        $historialRegular = array();
        $sw = false;

        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneBy(array('codigoRude' => $codigoRude));
        
        if ($estudiante) {
            $query = $em->getConnection()->prepare("select * from sp_genera_estudiante_historial('" . $codigoRude . "') order by gestion_tipo_id_raep desc, estudiante_inscripcion_id_raep desc;");
            $query->execute();
            $historial = $query->fetchAll();

            if ($historial) {
                foreach ($historial as $key => $inscripcion) {
                    switch ($inscripcion['institucioneducativa_tipo_id_raep']) {
                    case '1':
                        $historialRegular[$key] = $inscripcion;
                        break;
                    }
                }
                $sw = true;
                $mensaje = "La búsqueda fue satisfactoria, se encontraron los siguientes resultados.";
            } else {
                $mensaje = 'La/el Estudiante con Código RUDE: ' . $codigoRude . ', no presenta registro de inscripciones.';
                $sw = false;
            }            
        }else{
            $mensaje = 'La/el Estudiante con Código RUDE: ' . $codigoRude . ', no se encuentra en la base de datos del SIE.';
            $sw = false;
        }

        return $this->render($this->session->get('pathSystem') . ':GestionesPasadasAreasEstudiante:historial.html.twig', array(
            'estudiante' => $estudiante,
            'historialRegular' => $historialRegular,
            'mensaje' => $mensaje,
            'sw' => $sw
        ));
    }

    public function resultAreasEstudianteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $inscripcionid = trim(strtoupper($request->get('inscripcionid')));
        $estudianteid = trim(strtoupper($request->get('estudianteid')));
        $gestion = trim(strtoupper($request->get('gestion')));

        $areasEstudiante = $this->getAreasEstudiante($inscripcionid);
        $areasCurso = $this->getAreasCurso($inscripcionid);
        dump($areasEstudiante,$areasCurso);die;
    }

    private function getAreasEstudiante($inscripcionid) {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $repository->createQueryBuilder('ei')
            ->select('at2.id, at2.asignatura')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ei.institucioneducativaCurso = ic.id')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ico', 'WITH', 'ico.insitucioneducativaCurso = ic.id')
            ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at2', 'WITH', 'ico.asignaturaTipo = at2.id')
            ->where('ei.id = :inscripcionid')
            ->setParameter('inscripcionid', $inscripcionid)
            ->orderBy('at2.id')
            ->getQuery();

        $areas = $query->getResult();

        return $areas;
    }

    private function getAreasCurso($inscripcionid) {
        $em = $this->getDoctrine()->getManager();
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionid);

        $repository = $em->getRepository('SieAppWebBundle:TmpAsignaturaHistorico');
        $query = $repository->createQueryBuilder('tah')
            ->select('at2.id, at2.asignatura')
            ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at2', 'WITH', 'tah.asignaturaTipo = at2.id')
            ->where('tah.gestionTipoId = :gestion')
            ->andWhere('tah.nivelTipoId = :nivel')
            ->andWhere('tah.gradoTipoId = :grado')
            ->setParameter('gestion', $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId())
            ->setParameter('nivel', $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId())
            ->setParameter('grado', $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId())
            ->orderBy('at2.id')
            ->getQuery();

        $areas = $query->getResult();

        return $areas;
    }
}
