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
    private $institucioneducativaId;

    /**
     * Constructor
     */
    public function __construct() {
        $this->session = new Session();
        $this->institucioneducativaId = $this->session->get('ie_id');
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
        
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->institucioneducativaId);

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
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->institucioneducativaId);
        
        if ($estudiante) {
            $inscripcionEstudiante = $this->getInscripcionEstudianteUE($estudiante);
            
            if($inscripcionEstudiante) {
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
            } else {
                $mensaje = 'La/el Estudiante con Código RUDE: ' . $codigoRude . ', no presenta registro de inscripciones en esta Unidad Educativa.';
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
            'sw' => $sw,
            'institucioneducativa' => $institucioneducativa
        ));
    }

    public function resultAreasEstudianteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $inscripcionid = trim(strtoupper($request->get('inscripcionid')));
        $estudianteid = trim(strtoupper($request->get('estudianteid')));
        $gestion = trim(strtoupper($request->get('gestion')));
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($estudianteid);

        $areasEstudiante = $this->getAreasEstudiante($inscripcionid);
        $areasCurso = $this->getAreasCurso($inscripcionid);
        
        return $this->render($this->session->get('pathSystem') . ':GestionesPasadasAreasEstudiante:areas.html.twig', array(
            'estudiante' => $estudiante,
            'areasEstudiante' => $areasEstudiante,
            'areasCurso' => $areasCurso            
        ));
    }

    private function getAreasEstudiante($inscripcionid) {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $repository->createQueryBuilder('ei')
            ->select('ic.id icId, ico.id icoId,at2.id atId, at2.asignatura')
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

        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');
        $query = $repository->createQueryBuilder('ic')
            ->select('ic.id icId, ico.id icoId,at2.id atId, at2.asignatura')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ico', 'WITH', 'ico.insitucioneducativaCurso = ic.id')
            ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at2', 'WITH', 'ico.asignaturaTipo = at2.id')
            ->where('ic.id = :institucioneducativaCurso')
            ->setParameter('institucioneducativaCurso', $inscripcion->getInstitucioneducativaCurso()->getId())
            ->orderBy('at2.id')
            ->getQuery();

        $areas = $query->getResult();

        return $areas;
    }

    private function getInscripcionEstudianteUE($estudiante) {
        $em = $this->getDoctrine()->getManager();
        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->institucioneducativaId);

        $repository = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $repository->createQueryBuilder('ei')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ei.institucioneducativaCurso = ic.id')
            ->where('ei.estudiante = :estudiante')
            ->andWhere('ic.institucioneducativa = :institucioneducativa')
            ->setParameter('estudiante', $estudiante)
            ->setParameter('institucioneducativa', $institucioneducativa)
            ->getQuery();

        $inscripcionEstudiante = $query->getResult();

        return $inscripcionEstudiante;
    }
}
