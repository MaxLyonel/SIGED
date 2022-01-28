<?php

namespace Sie\HerramientaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sie\AppWebBundle\Entity\EstudianteAsignatura;
use Sie\AppWebBundle\Entity\EstudianteNota;
use Sie\AppWebBundle\Entity\EstudianteInscripcionHumnisticoTecnico;

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
        $esGestionVigente = 0;
        
        if (!isset($id_usuario)) {
            return $this->redirect($this->generateUrl('login'));
        }
        
        if (!$this->session->get('userId')) {
            return $this->redirect($this->generateUrl('login'));
        }
    
        if ($request->get('op')) {
            $esGestionVigente = 1;
        }

        $institucioneducativa = $em->getRepository('SieAppWebBundle:Institucioneducativa')->find($this->institucioneducativaId);

        return $this->render($this->session->get('pathSystem') . ':GestionesPasadasAreasEstudiante:index.html.twig', array(
            'institucioneducativa' => $institucioneducativa,
            'form' => $this->formSearch($esGestionVigente)->createView()
        ));
    }

    private function formSearch($esGestionVigente) {
        $form = $this->createFormBuilder()
            ->add('codigoRude', 'text', array('required' => true))
            ->add('esGestionVigente', 'hidden', array('data' => $esGestionVigente))
            ->add('buscar', 'submit', array('label' => 'Buscar'))
            ->getForm();
        return $form;
    }

    public function resultHistorialAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $request->get('form');
        $codigoRude = trim(strtoupper($form['codigoRude']));
        $esGestionVigente = trim($form['esGestionVigente']);
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
            'institucioneducativa' => $institucioneducativa,
            'esGestionVigente' => $esGestionVigente
        ));
    }

    public function resultAreasEstudianteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $esGestionVigente = trim(strtoupper($request->get('esGestionVigente')));
        $inscripcionid = trim(strtoupper($request->get('inscripcionid')));
        $estudianteid = trim(strtoupper($request->get('estudianteid')));
        $gestion = trim(strtoupper($request->get('gestion')));
        $evaluarEstadomatricula = $this->evaluarEstadomatricula($inscripcionid);
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($estudianteid);
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionid);
        $areasEstudiante = $this->getAreasEstudiante($inscripcionid);
        $areasCurso = $this->getAreasCurso($inscripcionid);
        $areasAsignar = $this->getAreasAsignar($inscripcionid, $areasEstudiante);

        return $this->render($this->session->get('pathSystem') . ':GestionesPasadasAreasEstudiante:areas.html.twig', array(
            'estudiante' => $estudiante,
            'inscripcion' => $inscripcion,
            'areasEstudiante' => $areasEstudiante,
            'areasCurso' => $areasCurso,
            'areasAsignar' => $areasAsignar,
            'estudianteid' => $estudianteid,
            'inscripcionid' => $inscripcionid,
            'gestion' => $gestion,
            'esGestionVigente' => $esGestionVigente
        ));
    }

    private function getAreasEstudiante($inscripcionid) {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository('SieAppWebBundle:EstudianteInscripcion');
        $query = $repository->createQueryBuilder('ei')
            ->select('ea.id eaId, ic.id icId, ico.id icoId,at2.id atId, at2.asignatura')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ei.institucioneducativaCurso = ic.id')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ico', 'WITH', 'ico.insitucioneducativaCurso = ic.id')
            ->innerJoin('SieAppWebBundle:EstudianteAsignatura', 'ea', 'WITH', 'ea.estudianteInscripcion = ei.id and ea.institucioneducativaCursoOferta = ico.id')
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
        $areas = null;

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

    private function getAreasAsignar($inscripcionid, $areasEstudiante) {
        $em = $this->getDoctrine()->getManager();
        $areasActuales = [];

        foreach ($areasEstudiante as $key => $area) {
            $areasActuales[] = $area['atId'];
        }
        
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionid);

        $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaCurso');

        if($areasActuales) {
            $query = $repository->createQueryBuilder('ic')
            ->select('ic.id icId, ico.id icoId,at2.id atId, at2.asignatura')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ico', 'WITH', 'ico.insitucioneducativaCurso = ic.id')
            ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at2', 'WITH', 'ico.asignaturaTipo = at2.id')
            ->where('ic.id = :institucioneducativaCurso')
            ->andWhere('at2.id not in (:areasActuales)')
            ->setParameter('institucioneducativaCurso', $inscripcion->getInstitucioneducativaCurso()->getId())
            ->setParameter('areasActuales', $areasActuales)
            ->orderBy('at2.id')
            ->getQuery();            
        } else {
            $query = $repository->createQueryBuilder('ic')
            ->select('ic.id icId, ico.id icoId,at2.id atId, at2.asignatura')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCursoOferta', 'ico', 'WITH', 'ico.insitucioneducativaCurso = ic.id')
            ->innerJoin('SieAppWebBundle:AsignaturaTipo', 'at2', 'WITH', 'ico.asignaturaTipo = at2.id')
            ->where('ic.id = :institucioneducativaCurso')
            ->setParameter('institucioneducativaCurso', $inscripcion->getInstitucioneducativaCurso()->getId())
            ->orderBy('at2.id')
            ->getQuery();
        }

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

    public function eliminarAreaEstudianteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $esGestionVigente = trim(strtoupper($request->get('esGestionVigente')));
        $areaid = trim(strtoupper($request->get('areaid')));
        $inscripcionid = trim(strtoupper($request->get('inscripcionid')));
        $estudianteid = trim(strtoupper($request->get('estudianteid')));
        $gestion = trim(strtoupper($request->get('gestion')));
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($estudianteid);
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionid);
        $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneById($areaid);
        $em->getConnection()->beginTransaction();

        try {
            if($estudianteAsignatura) {
                if($estudianteAsignatura->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId() == 1039) {
                    $especialidadEstudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion' => $inscripcion));
        
                    if($especialidadEstudiante) {
                        $em->remove($especialidadEstudiante);
                        $em->flush();
                    }
                }

                $estudianteNota = $em->getRepository('SieAppWebBundle:EstudianteNota')->findBy(array('estudianteAsignatura'=>$estudianteAsignatura));
                if($estudianteNota) {
                    foreach ($estudianteNota as $key => $nota) {
                        $notaAntLog = [];
                        $notaAntLog['id'] = $nota->getId();
                        $notaAntLog['notaTipo'] = $nota->getNotaTipo()->getId();
                        $notaAntLog['estudianteAsignatura'] = $nota->getEstudianteAsignatura()->getId();
                        $notaAntLog['notaCuantitativa'] = $nota->getNotaCuantitativa();
                        $notaAntLog['usuario'] = $nota->getUsuarioId();

                        $this->get('funciones')->setLogTransaccion(
                            $nota->getId(),
                            'estudiante_nota',
                            'D',
                            '',
                            '',
                            $notaAntLog,
                            'Academico',
                            json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ )));
                            
                        $em->remove($nota);
                        $em->flush();
                    }
                }

                $eaAntLog = [];
                $eaAntLog['id'] = $estudianteAsignatura->getId();
                $eaAntLog['gestionTipo'] = $estudianteAsignatura->getGestionTipo()->getId();
                $eaAntLog['estudianteInscripcion'] = $estudianteAsignatura->getEstudianteInscripcion()->getId();
                $eaAntLog['institucioneducativaCursoOferta'] = $estudianteAsignatura->getInstitucioneducativaCursoOferta()->getId();

                $this->get('funciones')->setLogTransaccion(
                    $estudianteAsignatura->getId(),
                    'estudiante_asignatura',
                    'D',
                    '',
                    '',
                    $eaAntLog,
                    'Academico',
                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ )));
                
                $em->remove($estudianteAsignatura);
                $em->flush();
            }

            $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionid);

            $eiAntLog = [];
            $eiAntLog['id'] = $inscripcion->getId();
            $eiAntLog['estadomatriculaTipo'] = $inscripcion->getEstadomatriculaTipo()->getId();

            $inscripcion->setEstadomatriculaTipo($em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById(4));
            $em->persist($inscripcion);
            $em->flush();
            
            $eiNuevoLog = [];
            $eiNuevoLog['id'] = $inscripcion->getId();
            $eiNuevoLog['estadomatriculaTipo'] = $inscripcion->getEstadomatriculaTipo()->getId();

            $this->get('funciones')->setLogTransaccion(
                $inscripcion->getId(),
                'estudiante_inscripcion',
                'U',
                '',
                $eiNuevoLog,
                $eiAntLog,
                'Academico',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ )));
            
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }        

        $areasEstudiante = $this->getAreasEstudiante($inscripcionid);
        $areasCurso = $this->getAreasCurso($inscripcionid);
        $areasAsignar = $this->getAreasAsignar($inscripcionid, $areasEstudiante);
        
        return $this->render($this->session->get('pathSystem') . ':GestionesPasadasAreasEstudiante:areas.html.twig', array(
            'estudiante' => $estudiante,
            'inscripcion' => $inscripcion,
            'areasEstudiante' => $areasEstudiante,
            'areasCurso' => $areasCurso,
            'areasAsignar' => $areasAsignar,
            'estudianteid' => $estudianteid,
            'inscripcionid' => $inscripcionid,
            'gestion' => $gestion,
            'esGestionVigente' => $esGestionVigente
        ));
    }

    public function agregarAreaEstudianteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $esGestionVigente = trim(strtoupper($request->get('esGestionVigente')));
        $ofertaid = trim(strtoupper($request->get('ofertaid')));
        $inscripcionid = trim(strtoupper($request->get('inscripcionid')));
        $estudianteid = trim(strtoupper($request->get('estudianteid')));
        $gestion = trim(strtoupper($request->get('gestion')));
        $estudiante = $em->getRepository('SieAppWebBundle:Estudiante')->findOneById($estudianteid);
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionid);

        $em->getConnection()->beginTransaction();

        try {
            $estudianteAsignatura = new EstudianteAsignatura();
            $estudianteAsignatura->setGestionTipo($em->getRepository('SieAppWebBundle:GestionTipo')->findOneById($gestion));
            $estudianteAsignatura->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionid));
            $estudianteAsignatura->setInstitucioneducativaCursoOferta($em->getRepository('SieAppWebBundle:InstitucioneducativaCursoOferta')->findOneById($ofertaid));
            $estudianteAsignatura->setFechaRegistro(new \DateTime('now'));
            $em->persist($estudianteAsignatura);
            $em->flush();

            $eaNuevoLog = [];
            $eaNuevoLog['id'] = $estudianteAsignatura->getId();
            $eaNuevoLog['gestionTipo'] = $estudianteAsignatura->getGestionTipo()->getId();
            $eaNuevoLog['estudianteInscripcion'] = $estudianteAsignatura->getEstudianteInscripcion()->getId();
            $eaNuevoLog['institucioneducativaCursoOferta'] = $estudianteAsignatura->getInstitucioneducativaCursoOferta()->getId();
            
            $this->get('funciones')->setLogTransaccion(
                $estudianteAsignatura->getId(),
                'estudiante_asignatura',
                'C',
                '',
                $eaNuevoLog,
                '',
                'Academico',
                json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ )));

            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $em->getConnection()->rollback();
        }  
        
        $areasEstudiante = $this->getAreasEstudiante($inscripcionid);
        $areasCurso = $this->getAreasCurso($inscripcionid);
        $areasAsignar = $this->getAreasAsignar($inscripcionid, $areasEstudiante);
        
        return $this->render($this->session->get('pathSystem') . ':GestionesPasadasAreasEstudiante:areas.html.twig', array(
            'estudiante' => $estudiante,
            'inscripcion' => $inscripcion,
            'areasEstudiante' => $areasEstudiante,
            'areasCurso' => $areasCurso,
            'areasAsignar' => $areasAsignar,
            'estudianteid' => $estudianteid,
            'inscripcionid' => $inscripcionid,
            'gestion' => $gestion,
            'esGestionVigente' => $esGestionVigente
        ));
    }

    public function calificacionesAreaEstudianteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $esSexto = false;
        $tiene_especialidad = false;
        $especialidades = null;
        $areaid = trim(strtoupper($request->get('areaid')));
        $inscripcionid = trim(strtoupper($request->get('inscripcionid')));
        $estudianteid = trim(strtoupper($request->get('estudianteid')));
        $gestion = trim(strtoupper($request->get('gestion')));        
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionid);
        $estudianteAsignatura = $em->getRepository('SieAppWebBundle:EstudianteAsignatura')->findOneById($areaid);

        $libreta = $em->getRepository('SieAppWebBundle:CatalogoLibretaTipo')->findBy(
            array('gestionTipoId' => $gestion, 'nivelTipoId' => $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId(), 'gradoTipoId' => $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId()),
            array('orden' => 'ASC'
        ));

        $repository = $em->getRepository('SieAppWebBundle:EstudianteNota');
        $query = $repository->createQueryBuilder('en')
            ->select('nt.id ntId, en.notaCuantitativa')
            ->innerJoin('SieAppWebBundle:NotaTipo', 'nt', 'WITH', 'nt.id = en.notaTipo')
            ->where('en.estudianteAsignatura = :estudianteAsignatura')
            ->setParameter('estudianteAsignatura', $estudianteAsignatura)
            ->getQuery();

        $estudianteNota = $query->getResult();

        $estudianteNotaArray = [];
        foreach ($estudianteNota as $key => $nota) {
            $estudianteNotaArray[$nota['ntId']] = $nota['notaCuantitativa'];
        }

        $especialidadEstudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion' => $inscripcion));
        if(!$especialidadEstudiante) {
            if($estudianteAsignatura->getInstitucioneducativaCursoOferta()->getAsignaturaTipo()->getId() == 1039) {
            
                if($inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId() == 13 and $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId() == 6) {
                    $esSexto = true;
                    $repository = $em->getRepository('SieAppWebBundle:Estudiante');
                    $query = $repository->createQueryBuilder('e')
                        ->select('eiht.institucioneducativaHumanisticoId')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcion', 'ei', 'WITH', 'e.id = ei.estudiante')
                        ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'ic', 'WITH', 'ic.id = ei.institucioneducativaCurso')
                        ->innerJoin('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico', 'eiht', 'WITH', 'ei.id = eiht.estudianteInscripcion')
                        ->where('ic.gestionTipo = :gestion')
                        ->andWhere('e.id = :estudiante')
                        ->andWhere('ei.estadomatriculaTipo = :matricula')
                        ->setParameter('gestion', $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId() - 1)
                        ->setParameter('estudiante', $estudianteid)
                        ->setParameter('matricula', $em->getRepository('SieAppWebBundle:EstadomatriculaTipo')->findOneById(5))
                        ->getQuery();
    
                    $especialidadAnterior = $query->getResult();
    
                    if(count($especialidadAnterior) > 0) {
                        $institucionEspecialidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->find($especialidadAnterior[0]['institucioneducativaHumanisticoId']);                    
                        $especialidadEstudiante = new EstudianteInscripcionHumnisticoTecnico();
                        $especialidadEstudiante->setInstitucioneducativaHumanisticoId($institucionEspecialidad->getId());
                        $especialidadEstudiante->setEstudianteInscripcion($inscripcion);
                        $especialidadEstudiante->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($institucionEspecialidad->getEspecialidadTecnicoHumanisticoTipo()->getId()));
                        $especialidadEstudiante->setHoras(0);
                        $em->persist($especialidadEstudiante);
                        $em->flush();
                    }
                }
    
                $repository = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico');
                $query = $repository->createQueryBuilder('ieth')
                    ->select('ieth.id iethId, etht.id ethtId, etht.especialidad')
                    ->innerJoin('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo', 'etht', 'WITH', 'ieth.especialidadTecnicoHumanisticoTipo = etht.id')
                    ->where('ieth.institucioneducativa = :institucioneducativa')
                    ->andWhere('ieth.gestionTipo = :gestion')
                    ->andWhere('etht.esVigente = :estado')
                    ->setParameter('institucioneducativa', $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId())
                    ->setParameter('gestion', $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId())
                    ->setParameter('estado', 't')
                    ->getQuery();
    
                $especialidades = $query->getResult();
            }
        }

        $especialidadEstudiante = $em->getRepository('SieAppWebBundle:EstudianteInscripcionHumnisticoTecnico')->findOneBy(array('estudianteInscripcion' => $inscripcion));
        
        if($especialidadEstudiante) {
            $tiene_especialidad = true;
        }
        
        return $this->render($this->session->get('pathSystem') . ':GestionesPasadasAreasEstudiante:form_calificaciones.html.twig', array(
            'libreta' => $libreta,
            'estudianteNotaArray' => $estudianteNotaArray,
            'estudianteid' => $estudianteid,
            'inscripcionid' => $inscripcionid,
            'areaid' => $areaid,
            'gestion' => $gestion,
            'nivelid' => $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId(),
            'gradoid' => $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId(),
            'especialidades' => $especialidades,
            'tiene_especialidad' => $tiene_especialidad,
            'especialidadEstudiante' => $especialidadEstudiante,
            'estudianteAsignatura' => $estudianteAsignatura,
            'esSexto' => $esSexto
        ));
    }
    
    public function calificacionesGuardarAreaEstudianteAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $areaid = $request->get('areaid');
        $inscripcionid = $request->get('inscripcionid');
        $formCalificaciones = $request->get('formCalificaciones');
        $especialidades = $request->get('especialidades');
        $mensaje = 'Se guardaron las calificaciones exitosamente.';
        $em->getConnection()->beginTransaction();

        try {
            if($especialidades != 0) {
                $institucionEspecialidad = $em->getRepository('SieAppWebBundle:InstitucioneducativaEspecialidadTecnicoHumanistico')->find($especialidades);
                $especialidadEstudiante = new EstudianteInscripcionHumnisticoTecnico();
                $especialidadEstudiante->setInstitucioneducativaHumanisticoId($institucionEspecialidad->getId());
                $especialidadEstudiante->setEstudianteInscripcion($em->getRepository('SieAppWebBundle:EstudianteInscripcion')->find($inscripcionid));
                $especialidadEstudiante->setEspecialidadTecnicoHumanisticoTipo($em->getRepository('SieAppWebBundle:EspecialidadTecnicoHumanisticoTipo')->find($institucionEspecialidad->getEspecialidadTecnicoHumanisticoTipo()->getId()));
                $especialidadEstudiante->setHoras(0);
                $em->persist($especialidadEstudiante);
                $em->flush();
            }

            foreach ($formCalificaciones as $key => $nota) {
                $porciones = explode("_", $key);
                $estudianteNota = new EstudianteNota();
                $estudianteNota->setNotaTipo($em->getRepository('SieAppWebBundle:NotaTipo')->findOneById($porciones[0]));
                $estudianteNota->setEstudianteAsignatura($em->getRepository('SieAppWebBundle:EstudianteAsignatura')->find($areaid));
                $estudianteNota->setNotaCuantitativa(intval($nota));
                $estudianteNota->setNotaCualitativa('');
                $estudianteNota->setRecomendacion('');
                $estudianteNota->setUsuarioId($this->session->get('userId'));
                $estudianteNota->setFechaRegistro(new \DateTime('now'));
                $estudianteNota->setFechaModificacion(new \DateTime('now'));
                $estudianteNota->setObs('');
                $em->persist($estudianteNota);
                $em->flush();

                $enNuevoLog = [];
                $enNuevoLog['id'] = $estudianteNota->getId();
                $enNuevoLog['notaTipo'] = $estudianteNota->getNotaTipo()->getId();
                $enNuevoLog['estudianteAsignatura'] = $estudianteNota->getEstudianteAsignatura()->getId();
                $enNuevoLog['notaCuantitativa'] = $estudianteNota->getNotaCuantitativa();
                $enNuevoLog['notaCualitativa'] = $estudianteNota->getNotaCualitativa();
                $enNuevoLog['usuario'] = $estudianteNota->getUsuarioId();
                
                $this->get('funciones')->setLogTransaccion(
                    $estudianteNota->getId(),
                    'estudiante_asignatura',
                    'C',
                    '',
                    $enNuevoLog,
                    '',
                    'Academico',
                    json_encode(array( 'file' => basename(__FILE__, '.php'), 'function' => __FUNCTION__ )));
            }
            
            $evaluarEstadomatricula = $this->evaluarEstadomatricula($inscripcionid);
            $em->getConnection()->commit();
        } catch (Exception $ex) {
            $mensaje = 'No se guardaron las calificaciones.';
            $em->getConnection()->rollback();
        }

        $response = new JsonResponse();
        return $response->setData(array('mensaje' => $mensaje));
    }

    private function evaluarEstadomatricula($inscripcionid) {
        $em = $this->getDoctrine()->getManager();            
        
        $inscripcion = $em->getRepository('SieAppWebBundle:EstudianteInscripcion')->findOneById($inscripcionid);
        
        $igestion = $inscripcion->getInstitucioneducativaCurso()->getGestionTipo()->getId();
        $iinstitucioneducativa_id = $inscripcion->getInstitucioneducativaCurso()->getInstitucioneducativa()->getId();
        $inivel_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getNivelTipo()->getId();
        $igrado_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getGradoTipo()->getId();
        $iturno_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getTurnoTipo()->getId();
        $iparalelo_tipo_id = $inscripcion->getInstitucioneducativaCurso()->getParaleloTipo()->getId();
        $icodigo_rude = $inscripcion->getEstudiante()->getCodigoRude();
        $complementario = "";
        $estado_inicial = $inscripcion->getEstadomatriculaTipo()->getEstadomatricula();

        if($igestion == 2013) {
            if($inivel_tipo_id == 12) {
                if($igrado_tipo_id == 1) {
                    $complementario = "'(1,2,3)','(1,2,3,4,5)','(5)','51'";
                } else {
                    $complementario = "'(6,7)','(6,7,8)','(9,11)','36'";
                }
            } else if($inivel_tipo_id == 13) {
                if($igrado_tipo_id == 1) {
                    $complementario = "'(1,2,3)','(1,2,3,4,5)','(5)','51'";
                } else {
                    $complementario = "'(6,7)','(6,7,8)','(9,11)','36'";
                }
            }
        } else if($igestion < 2013) {
            $complementario = "'(6,7)','(6,7,8)','(9,11)','36'";
        } else if($igestion > 2013 && $igestion < 2020) {
            $complementario = "'(1,2,3)','(1,2,3,4,5)','(5)','51'";
        } else if($igestion == 2020) {
            if($inivel_tipo_id == 12) {
                if($igrado_tipo_id > 1) {
                    $complementario = "'(6,7)','(6,7,8)','(9)','51'";
                }
            } else if($inivel_tipo_id == 13) {
                if($igrado_tipo_id >= 1) {
                    $complementario = "'(6,7)','(6,7,8)','(9)','51'";
                }
            }
        } else if($igestion == 2021) {            
            $complementario = "'(6,7)','(6,7,8)','(9)','51'";            
        }

        $query = $em->getConnection()->prepare("select * from sp_genera_evaluacion_estado_estudiante_regular('".$igestion."','".$iinstitucioneducativa_id."','".$inivel_tipo_id."','".$igrado_tipo_id."','".$iturno_tipo_id."','".$iparalelo_tipo_id."','".$icodigo_rude."',".$complementario.")");
        $query->execute();        
        $resultado = $query->fetchAll();

        return $resultado;
    }
}