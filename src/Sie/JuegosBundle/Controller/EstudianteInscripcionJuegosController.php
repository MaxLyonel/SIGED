<?php

namespace Sie\JuegosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class EstudianteInscripcionJuegosController extends Controller
{
	public $session;

    /**
     * the class constructor
     */
    public function __construct() {
        //init the session values
        $this->session = new Session();
        //$this->aCursos = $this->fillCursos();
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las participaciones de una unidad educativa según la prueba, gestion y fase
    // PARAMETROS: por POST  disciplinaId, generoId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function getEstudianteInscripcionInstitucionGestionFasePrueba($institucionEducativaId, $pruebaId, $gestionId, $faseId){            
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')                
            ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
            ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
            ->where('iec.institucioneducativa = :codInstitucion')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->andwhere('eij.pruebaTipo = :codPrueba')
            ->setParameter('codInstitucion', $institucionEducativaId)
            ->setParameter('codGestion', $gestionId)
            ->setParameter('codFase', $faseId)
            ->setParameter('codPrueba', $pruebaId)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity; 
        } else {
            return array(); 
        } 
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las equipos de una unidad educativa según la prueba, gestion y fase
    // PARAMETROS: por POST  disciplinaId, generoId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function getEquipoInscripcionInstitucionGestionFasePrueba($institucionEducativaId, $pruebaId, $gestionId, $faseId){            
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEquipoEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eeij')  
            ->select('distinct eeij.equipoId, eeij.equipoNombre')              
            ->leftJoin('SieAppWebBundle:JdpEstudianteInscripcionJuegos','eij','WITH','eij.id = eeij.estudianteInscripcionJuegos')
            ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
            ->leftJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
            ->where('iec.institucioneducativa = :codInstitucion')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->andwhere('eij.pruebaTipo = :codPrueba')
            ->setParameter('codInstitucion', $institucionEducativaId)
            ->setParameter('codGestion', $gestionId)
            ->setParameter('codFase', $faseId)
            ->setParameter('codPrueba', $pruebaId)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity; 
        } else {
            return array(); 
        } 
    }


    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las participaciones de una unidad educativa por equipo según la prueba, gestion y fase
    // PARAMETROS: por POST  disciplinaId, generoId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function getEquipoEstudianteInscripcionInstitucionGestionFasePrueba($institucionEducativaId, $pruebaId, $gestionId, $faseId, $equipoId){            
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEquipoEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eeij')              
            ->innerJoin('SieAppWebBundle:JdpEstudianteInscripcionJuegos','eij','WITH','eij.id = eeij.estudianteInscripcionJuegos')
            ->innerJoin('SieAppWebBundle:EstudianteInscripcion','ei','WITH','ei.id = eij.estudianteInscripcion')
            ->innerJoin('SieAppWebBundle:InstitucioneducativaCurso', 'iec', 'WITH', 'iec.id = ei.institucioneducativaCurso')
            ->where('iec.institucioneducativa = :codInstitucion')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->andwhere('eij.pruebaTipo = :codPrueba')
            ->andwhere('eeij.equipoId = :codEquipo')
            ->setParameter('codInstitucion', $institucionEducativaId)
            ->setParameter('codGestion', $gestionId)
            ->setParameter('codFase', $faseId)
            ->setParameter('codPrueba', $pruebaId)
            ->setParameter('codEquipo', $equipoId)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity; 
        } else {
            return array(); 
        } 
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las participacion de un estudiante deportistas según la prueba, gestion y fase
    // PARAMETROS: por POST  disciplinaId, generoId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function getEstudianteInscripcionGestionFasePrueba($estudianteInscripcionId, $pruebaId, $gestionId, $faseId){            
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
            ->innerJoin('SieAppWebBundle:JdpPruebaTipo','pt','WITH','pt.id = eij.pruebaTipo')
            ->innerJoin('SieAppWebBundle:JdpDisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->innerJoin('SieAppWebBundle:JdpDisciplinaParticipacionTipo','dpt','WITH','dpt.id = dt.disciplinaParticipacionTipo')
            ->where('eij.estudianteInscripcion = :codInscripcion')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->andwhere('eij.pruebaTipo = :codPrueba')
            ->setParameter('codInscripcion', $estudianteInscripcionId)
            ->setParameter('codGestion', $gestionId)
            ->setParameter('codFase', $faseId)
            ->setParameter('codPrueba', $pruebaId)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity; 
        } else {
            return array(); 
        } 
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las participacion de un estudiante deportistas según la disciplina, modalidad, gestion y fase
    // PARAMETROS: por POST  disciplinaId, generoId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function getEstudianteInscripcionGestionFaseDisciplinaGeneroModalidad($estudianteInscripcionId, $disciplinaId, $gestionId, $faseId, $generoId, $modalidadId){            
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
            ->innerJoin('SieAppWebBundle:JdpPruebaTipo','pt','WITH','pt.id = eij.pruebaTipo')
            ->where('eij.estudianteInscripcion = :codInscripcion')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->andwhere('pt.disciplinaTipo = :codDisciplina')
            ->andwhere('pt.generoTipo = :codGenero')
            ->setParameter('codInscripcion', $estudianteInscripcionId)
            ->setParameter('codGestion', $gestionId)
            ->setParameter('codFase', $faseId)
            ->setParameter('codDisciplina', $disciplinaId)
            ->setParameter('codGenero', $generoId)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity; 
        } else {
            return array(); 
        } 
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las disciplinas de un estudiante deportistas según el tipo de participacion, gestion y fase
    // PARAMETROS: por POST  disciplinaId, generoId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function valDisciplinaEstudianteInscripcion($estudianteInscripcionId, $gestionId, $faseId){            
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
            ->select('distinct dpt')
            ->innerJoin('SieAppWebBundle:JdpPruebaTipo','pt','WITH','pt.id = eij.pruebaTipo')
            ->innerJoin('SieAppWebBundle:JdpDisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->innerJoin('SieAppWebBundle:JdpDisciplinaParticipacionTipo','dpt','WITH','dpt.id = dt.disciplinaParticipacionTipo')
            ->where('eij.estudianteInscripcion = :codInscripcion')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            // ->andwhere('dt.disciplinaParticipacionTipo = :codParticipacion')
            ->setParameter('codInscripcion', $estudianteInscripcionId)
            ->setParameter('codGestion', $gestionId)
            ->setParameter('codFase', $faseId)
            // ->setParameter('codParticipacion', $participacionId)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity; 
        } else {
            return array(); 
        } 
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las disciplinas de un estudiante deportistas según el tipo de participacion, gestion y fase
    // PARAMETROS: por POST  disciplinaId, generoId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function valDisciplinaParticipacionEstudianteInscripcion($estudianteInscripcionId, $participacionId, $gestionId, $faseId){            
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
            ->select('distinct dpt')
            ->innerJoin('SieAppWebBundle:JdpPruebaTipo','pt','WITH','pt.id = eij.pruebaTipo')
            ->innerJoin('SieAppWebBundle:JdpDisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->innerJoin('SieAppWebBundle:JdpDisciplinaParticipacionTipo','dpt','WITH','dpt.id = dt.disciplinaParticipacionTipo')
            ->where('eij.estudianteInscripcion = :codInscripcion')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->andwhere('dt.disciplinaParticipacionTipo = :codParticipacion')
            ->setParameter('codInscripcion', $estudianteInscripcionId)
            ->setParameter('codGestion', $gestionId)
            ->setParameter('codFase', $faseId)
            ->setParameter('codParticipacion', $participacionId)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity; 
        } else {
            return array(); 
        } 
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las pruebas de un estudiante deportistas según el tipo de participacion, gestion y fase
    // PARAMETROS: por POST  disciplinaId, generoId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function valPruebaParticipacionEstudianteInscripcion($estudianteInscripcionId, $participacionId, $gestionId, $faseId, $disciplinaId){            
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
            ->select('distinct pt')
            ->innerJoin('SieAppWebBundle:JdpPruebaTipo','pt','WITH','pt.id = eij.pruebaTipo')
            ->innerJoin('SieAppWebBundle:JdpPruebaParticipacionTipo','ppt','WITH','ppt.id = pt.pruebaParticipacionTipo')
            ->where('eij.estudianteInscripcion = :codInscripcion')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->andwhere('pt.pruebaParticipacionTipo = :codParticipacion')
            ->andwhere('pt.disciplinaTipo = :codDisciplina')
            ->setParameter('codInscripcion', $estudianteInscripcionId)
            ->setParameter('codGestion', $gestionId)
            ->setParameter('codFase', $faseId)
            ->setParameter('codParticipacion', $participacionId)
            ->setParameter('codDisciplina', $disciplinaId)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity; 
        } else {
            return array(); 
        } 
    }

    //********************************************************************************************************************************
    // DESCRIPCION DEL METODO:
    // Funcion que lista las disciplinas de un estudiante deportistas según el prueba, gestion y fase
    // PARAMETROS: por POST  disciplinaId, generoId
    // AUTOR: RCANAVIRI
    //********************************************************************************************************************************
    public function getDisciplinaEstudianteInscripcion($estudianteInscripcionId, $gestionId, $faseId){            
        $em = $this->getDoctrine()->getManager();
        $entity = $this->getDoctrine()->getRepository('SieAppWebBundle:JdpEstudianteInscripcionJuegos');
        $query = $entity->createQueryBuilder('eij')
            ->select('distinct dt')
            ->innerJoin('SieAppWebBundle:JdpPruebaTipo','pt','WITH','pt.id = eij.pruebaTipo')
            ->innerJoin('SieAppWebBundle:JdpDisciplinaTipo','dt','WITH','dt.id = pt.disciplinaTipo')
            ->where('eij.estudianteInscripcion = :codInscripcion')
            ->andwhere('eij.gestionTipo = :codGestion')
            ->andwhere('eij.faseTipo = :codFase')
            ->setParameter('codInscripcion', $estudianteInscripcionId)
            ->setParameter('codGestion', $gestionId)
            ->setParameter('codFase', $faseId)
            ->getQuery();
        $entity = $query->getResult();
        if (count($entity) > 0){
            return $entity; 
        } else {
            return array(); 
        } 
    }


}
