<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaCurso
 */
class InstitucioneducativaCurso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $multigrado;

    /**
     * @var integer
     */
    private $desayunoEscolar;

    /**
     * @var integer
     */
    private $modalidadEnsenanza;

    /**
     * @var integer
     */
    private $idiomaMasHabladoTipoId;

    /**
     * @var integer
     */
    private $idiomaRegHabladoTipoId;

    /**
     * @var integer
     */
    private $idiomaMenHabladoTipoId;

    /**
     * @var integer
     */
    private $priLenEnsenanzaTipoId;

    /**
     * @var integer
     */
    private $segLenEnsenanzaTipoId;

    /**
     * @var integer
     */
    private $terLenEnsenanzaTipoId;

    /**
     * @var integer
     */
    private $finDesEscolarTipoId;

    /**
     * @var integer
     */
    private $nroMaterias;

    /**
     * @var integer
     */
    private $consolidado;

    /**
     * @var integer
     */
    private $periodicidadTipoId;

    /**
     * @var integer
     */
    private $resolucion;

    /**
     * @var string
     */
    private $periodicidad;

    /**
     * @var integer
     */
    private $carreraespecialidadTipoId;

    /**
     * @var integer
     */
    private $modalidadTipoId;

    /**
     * @var integer
     */
    private $programaTipoId;

    /**
     * @var integer
     */
    private $nivelacreditacionTipoId;

    /**
     * @var integer
     */
    private $lugartipoId;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var \DateTime
     */
    private $fecharegistroCuso;

    /**
     * @var string
     */
    private $lugar;

    /**
     * @var integer
     */
    private $duracionhoras;

    /**
     * @var integer
     */
    private $numeroperiodo;

    /**
     * @var string
     */
    private $programaTipoOtros;

    /**
     * @var string
     */
    private $facilitador;

    /**
     * @var \Sie\AppWebBundle\Entity\CicloTipo
     */
    private $cicloTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GradoTipo
     */
    private $gradoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\NivelTipo
     */
    private $nivelTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ParaleloTipo
     */
    private $paraleloTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\PeriodoTipo
     */
    private $periodoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\SucursalTipo
     */
    private $sucursalTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TurnoTipo
     */
    private $turnoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\NotaPeriodoTipo
     */
    private $notaPeriodoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\MaestroInscripcion
     */
    private $maestroInscripcionAsesor;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set multigrado
     *
     * @param integer $multigrado
     * @return InstitucioneducativaCurso
     */
    public function setMultigrado($multigrado)
    {
        $this->multigrado = $multigrado;
    
        return $this;
    }

    /**
     * Get multigrado
     *
     * @return integer 
     */
    public function getMultigrado()
    {
        return $this->multigrado;
    }

    /**
     * Set desayunoEscolar
     *
     * @param integer $desayunoEscolar
     * @return InstitucioneducativaCurso
     */
    public function setDesayunoEscolar($desayunoEscolar)
    {
        $this->desayunoEscolar = $desayunoEscolar;
    
        return $this;
    }

    /**
     * Get desayunoEscolar
     *
     * @return integer 
     */
    public function getDesayunoEscolar()
    {
        return $this->desayunoEscolar;
    }

    /**
     * Set modalidadEnsenanza
     *
     * @param integer $modalidadEnsenanza
     * @return InstitucioneducativaCurso
     */
    public function setModalidadEnsenanza($modalidadEnsenanza)
    {
        $this->modalidadEnsenanza = $modalidadEnsenanza;
    
        return $this;
    }

    /**
     * Get modalidadEnsenanza
     *
     * @return integer 
     */
    public function getModalidadEnsenanza()
    {
        return $this->modalidadEnsenanza;
    }

    /**
     * Set idiomaMasHabladoTipoId
     *
     * @param integer $idiomaMasHabladoTipoId
     * @return InstitucioneducativaCurso
     */
    public function setIdiomaMasHabladoTipoId($idiomaMasHabladoTipoId)
    {
        $this->idiomaMasHabladoTipoId = $idiomaMasHabladoTipoId;
    
        return $this;
    }

    /**
     * Get idiomaMasHabladoTipoId
     *
     * @return integer 
     */
    public function getIdiomaMasHabladoTipoId()
    {
        return $this->idiomaMasHabladoTipoId;
    }

    /**
     * Set idiomaRegHabladoTipoId
     *
     * @param integer $idiomaRegHabladoTipoId
     * @return InstitucioneducativaCurso
     */
    public function setIdiomaRegHabladoTipoId($idiomaRegHabladoTipoId)
    {
        $this->idiomaRegHabladoTipoId = $idiomaRegHabladoTipoId;
    
        return $this;
    }

    /**
     * Get idiomaRegHabladoTipoId
     *
     * @return integer 
     */
    public function getIdiomaRegHabladoTipoId()
    {
        return $this->idiomaRegHabladoTipoId;
    }

    /**
     * Set idiomaMenHabladoTipoId
     *
     * @param integer $idiomaMenHabladoTipoId
     * @return InstitucioneducativaCurso
     */
    public function setIdiomaMenHabladoTipoId($idiomaMenHabladoTipoId)
    {
        $this->idiomaMenHabladoTipoId = $idiomaMenHabladoTipoId;
    
        return $this;
    }

    /**
     * Get idiomaMenHabladoTipoId
     *
     * @return integer 
     */
    public function getIdiomaMenHabladoTipoId()
    {
        return $this->idiomaMenHabladoTipoId;
    }

    /**
     * Set priLenEnsenanzaTipoId
     *
     * @param integer $priLenEnsenanzaTipoId
     * @return InstitucioneducativaCurso
     */
    public function setPriLenEnsenanzaTipoId($priLenEnsenanzaTipoId)
    {
        $this->priLenEnsenanzaTipoId = $priLenEnsenanzaTipoId;
    
        return $this;
    }

    /**
     * Get priLenEnsenanzaTipoId
     *
     * @return integer 
     */
    public function getPriLenEnsenanzaTipoId()
    {
        return $this->priLenEnsenanzaTipoId;
    }

    /**
     * Set segLenEnsenanzaTipoId
     *
     * @param integer $segLenEnsenanzaTipoId
     * @return InstitucioneducativaCurso
     */
    public function setSegLenEnsenanzaTipoId($segLenEnsenanzaTipoId)
    {
        $this->segLenEnsenanzaTipoId = $segLenEnsenanzaTipoId;
    
        return $this;
    }

    /**
     * Get segLenEnsenanzaTipoId
     *
     * @return integer 
     */
    public function getSegLenEnsenanzaTipoId()
    {
        return $this->segLenEnsenanzaTipoId;
    }

    /**
     * Set terLenEnsenanzaTipoId
     *
     * @param integer $terLenEnsenanzaTipoId
     * @return InstitucioneducativaCurso
     */
    public function setTerLenEnsenanzaTipoId($terLenEnsenanzaTipoId)
    {
        $this->terLenEnsenanzaTipoId = $terLenEnsenanzaTipoId;
    
        return $this;
    }

    /**
     * Get terLenEnsenanzaTipoId
     *
     * @return integer 
     */
    public function getTerLenEnsenanzaTipoId()
    {
        return $this->terLenEnsenanzaTipoId;
    }

    /**
     * Set finDesEscolarTipoId
     *
     * @param integer $finDesEscolarTipoId
     * @return InstitucioneducativaCurso
     */
    public function setFinDesEscolarTipoId($finDesEscolarTipoId)
    {
        $this->finDesEscolarTipoId = $finDesEscolarTipoId;
    
        return $this;
    }

    /**
     * Get finDesEscolarTipoId
     *
     * @return integer 
     */
    public function getFinDesEscolarTipoId()
    {
        return $this->finDesEscolarTipoId;
    }

    /**
     * Set nroMaterias
     *
     * @param integer $nroMaterias
     * @return InstitucioneducativaCurso
     */
    public function setNroMaterias($nroMaterias)
    {
        $this->nroMaterias = $nroMaterias;
    
        return $this;
    }

    /**
     * Get nroMaterias
     *
     * @return integer 
     */
    public function getNroMaterias()
    {
        return $this->nroMaterias;
    }

    /**
     * Set consolidado
     *
     * @param integer $consolidado
     * @return InstitucioneducativaCurso
     */
    public function setConsolidado($consolidado)
    {
        $this->consolidado = $consolidado;
    
        return $this;
    }

    /**
     * Get consolidado
     *
     * @return integer 
     */
    public function getConsolidado()
    {
        return $this->consolidado;
    }

    /**
     * Set periodicidadTipoId
     *
     * @param integer $periodicidadTipoId
     * @return InstitucioneducativaCurso
     */
    public function setPeriodicidadTipoId($periodicidadTipoId)
    {
        $this->periodicidadTipoId = $periodicidadTipoId;
    
        return $this;
    }

    /**
     * Get periodicidadTipoId
     *
     * @return integer 
     */
    public function getPeriodicidadTipoId()
    {
        return $this->periodicidadTipoId;
    }

    /**
     * Set resolucion
     *
     * @param integer $resolucion
     * @return InstitucioneducativaCurso
     */
    public function setResolucion($resolucion)
    {
        $this->resolucion = $resolucion;
    
        return $this;
    }

    /**
     * Get resolucion
     *
     * @return integer 
     */
    public function getResolucion()
    {
        return $this->resolucion;
    }

    /**
     * Set periodicidad
     *
     * @param string $periodicidad
     * @return InstitucioneducativaCurso
     */
    public function setPeriodicidad($periodicidad)
    {
        $this->periodicidad = $periodicidad;
    
        return $this;
    }

    /**
     * Get periodicidad
     *
     * @return string 
     */
    public function getPeriodicidad()
    {
        return $this->periodicidad;
    }

    /**
     * Set carreraespecialidadTipoId
     *
     * @param integer $carreraespecialidadTipoId
     * @return InstitucioneducativaCurso
     */
    public function setCarreraespecialidadTipoId($carreraespecialidadTipoId)
    {
        $this->carreraespecialidadTipoId = $carreraespecialidadTipoId;
    
        return $this;
    }

    /**
     * Get carreraespecialidadTipoId
     *
     * @return integer 
     */
    public function getCarreraespecialidadTipoId()
    {
        return $this->carreraespecialidadTipoId;
    }

    /**
     * Set modalidadTipoId
     *
     * @param integer $modalidadTipoId
     * @return InstitucioneducativaCurso
     */
    public function setModalidadTipoId($modalidadTipoId)
    {
        $this->modalidadTipoId = $modalidadTipoId;
    
        return $this;
    }

    /**
     * Get modalidadTipoId
     *
     * @return integer 
     */
    public function getModalidadTipoId()
    {
        return $this->modalidadTipoId;
    }

    /**
     * Set programaTipoId
     *
     * @param integer $programaTipoId
     * @return InstitucioneducativaCurso
     */
    public function setProgramaTipoId($programaTipoId)
    {
        $this->programaTipoId = $programaTipoId;
    
        return $this;
    }

    /**
     * Get programaTipoId
     *
     * @return integer 
     */
    public function getProgramaTipoId()
    {
        return $this->programaTipoId;
    }

    /**
     * Set nivelacreditacionTipoId
     *
     * @param integer $nivelacreditacionTipoId
     * @return InstitucioneducativaCurso
     */
    public function setNivelacreditacionTipoId($nivelacreditacionTipoId)
    {
        $this->nivelacreditacionTipoId = $nivelacreditacionTipoId;
    
        return $this;
    }

    /**
     * Get nivelacreditacionTipoId
     *
     * @return integer 
     */
    public function getNivelacreditacionTipoId()
    {
        return $this->nivelacreditacionTipoId;
    }

    /**
     * Set lugartipoId
     *
     * @param integer $lugartipoId
     * @return InstitucioneducativaCurso
     */
    public function setLugartipoId($lugartipoId)
    {
        $this->lugartipoId = $lugartipoId;
    
        return $this;
    }

    /**
     * Get lugartipoId
     *
     * @return integer 
     */
    public function getLugartipoId()
    {
        return $this->lugartipoId;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return InstitucioneducativaCurso
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    
        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime 
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     * @return InstitucioneducativaCurso
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    
        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime 
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set fecharegistroCuso
     *
     * @param \DateTime $fecharegistroCuso
     * @return InstitucioneducativaCurso
     */
    public function setFecharegistroCuso($fecharegistroCuso)
    {
        $this->fecharegistroCuso = $fecharegistroCuso;
    
        return $this;
    }

    /**
     * Get fecharegistroCuso
     *
     * @return \DateTime 
     */
    public function getFecharegistroCuso()
    {
        return $this->fecharegistroCuso;
    }

    /**
     * Set lugar
     *
     * @param string $lugar
     * @return InstitucioneducativaCurso
     */
    public function setLugar($lugar)
    {
        $this->lugar = $lugar;
    
        return $this;
    }

    /**
     * Get lugar
     *
     * @return string 
     */
    public function getLugar()
    {
        return $this->lugar;
    }

    /**
     * Set duracionhoras
     *
     * @param integer $duracionhoras
     * @return InstitucioneducativaCurso
     */
    public function setDuracionhoras($duracionhoras)
    {
        $this->duracionhoras = $duracionhoras;
    
        return $this;
    }

    /**
     * Get duracionhoras
     *
     * @return integer 
     */
    public function getDuracionhoras()
    {
        return $this->duracionhoras;
    }

    /**
     * Set numeroperiodo
     *
     * @param integer $numeroperiodo
     * @return InstitucioneducativaCurso
     */
    public function setNumeroperiodo($numeroperiodo)
    {
        $this->numeroperiodo = $numeroperiodo;
    
        return $this;
    }

    /**
     * Get numeroperiodo
     *
     * @return integer 
     */
    public function getNumeroperiodo()
    {
        return $this->numeroperiodo;
    }

    /**
     * Set programaTipoOtros
     *
     * @param string $programaTipoOtros
     * @return InstitucioneducativaCurso
     */
    public function setProgramaTipoOtros($programaTipoOtros)
    {
        $this->programaTipoOtros = $programaTipoOtros;
    
        return $this;
    }

    /**
     * Get programaTipoOtros
     *
     * @return string 
     */
    public function getProgramaTipoOtros()
    {
        return $this->programaTipoOtros;
    }

    /**
     * Set facilitador
     *
     * @param string $facilitador
     * @return InstitucioneducativaCurso
     */
    public function setFacilitador($facilitador)
    {
        $this->facilitador = $facilitador;
    
        return $this;
    }

    /**
     * Get facilitador
     *
     * @return string 
     */
    public function getFacilitador()
    {
        return $this->facilitador;
    }

    /**
     * Set cicloTipo
     *
     * @param \Sie\AppWebBundle\Entity\CicloTipo $cicloTipo
     * @return InstitucioneducativaCurso
     */
    public function setCicloTipo(\Sie\AppWebBundle\Entity\CicloTipo $cicloTipo = null)
    {
        $this->cicloTipo = $cicloTipo;
    
        return $this;
    }

    /**
     * Get cicloTipo
     *
     * @return \Sie\AppWebBundle\Entity\CicloTipo 
     */
    public function getCicloTipo()
    {
        return $this->cicloTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return InstitucioneducativaCurso
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }

    /**
     * Set gradoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GradoTipo $gradoTipo
     * @return InstitucioneducativaCurso
     */
    public function setGradoTipo(\Sie\AppWebBundle\Entity\GradoTipo $gradoTipo = null)
    {
        $this->gradoTipo = $gradoTipo;
    
        return $this;
    }

    /**
     * Get gradoTipo
     *
     * @return \Sie\AppWebBundle\Entity\GradoTipo 
     */
    public function getGradoTipo()
    {
        return $this->gradoTipo;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaCurso
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set nivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\NivelTipo $nivelTipo
     * @return InstitucioneducativaCurso
     */
    public function setNivelTipo(\Sie\AppWebBundle\Entity\NivelTipo $nivelTipo = null)
    {
        $this->nivelTipo = $nivelTipo;
    
        return $this;
    }

    /**
     * Get nivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\NivelTipo 
     */
    public function getNivelTipo()
    {
        return $this->nivelTipo;
    }

    /**
     * Set paraleloTipo
     *
     * @param \Sie\AppWebBundle\Entity\ParaleloTipo $paraleloTipo
     * @return InstitucioneducativaCurso
     */
    public function setParaleloTipo(\Sie\AppWebBundle\Entity\ParaleloTipo $paraleloTipo = null)
    {
        $this->paraleloTipo = $paraleloTipo;
    
        return $this;
    }

    /**
     * Get paraleloTipo
     *
     * @return \Sie\AppWebBundle\Entity\ParaleloTipo 
     */
    public function getParaleloTipo()
    {
        return $this->paraleloTipo;
    }

    /**
     * Set periodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\PeriodoTipo $periodoTipo
     * @return InstitucioneducativaCurso
     */
    public function setPeriodoTipo(\Sie\AppWebBundle\Entity\PeriodoTipo $periodoTipo = null)
    {
        $this->periodoTipo = $periodoTipo;
    
        return $this;
    }

    /**
     * Get periodoTipo
     *
     * @return \Sie\AppWebBundle\Entity\PeriodoTipo 
     */
    public function getPeriodoTipo()
    {
        return $this->periodoTipo;
    }

    /**
     * Set sucursalTipo
     *
     * @param \Sie\AppWebBundle\Entity\SucursalTipo $sucursalTipo
     * @return InstitucioneducativaCurso
     */
    public function setSucursalTipo(\Sie\AppWebBundle\Entity\SucursalTipo $sucursalTipo = null)
    {
        $this->sucursalTipo = $sucursalTipo;
    
        return $this;
    }

    /**
     * Get sucursalTipo
     *
     * @return \Sie\AppWebBundle\Entity\SucursalTipo 
     */
    public function getSucursalTipo()
    {
        return $this->sucursalTipo;
    }

    /**
     * Set turnoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TurnoTipo $turnoTipo
     * @return InstitucioneducativaCurso
     */
    public function setTurnoTipo(\Sie\AppWebBundle\Entity\TurnoTipo $turnoTipo = null)
    {
        $this->turnoTipo = $turnoTipo;
    
        return $this;
    }

    /**
     * Get turnoTipo
     *
     * @return \Sie\AppWebBundle\Entity\TurnoTipo 
     */
    public function getTurnoTipo()
    {
        return $this->turnoTipo;
    }

    /**
     * Set notaPeriodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\NotaPeriodoTipo $notaPeriodoTipo
     * @return InstitucioneducativaCurso
     */
    public function setNotaPeriodoTipo(\Sie\AppWebBundle\Entity\NotaPeriodoTipo $notaPeriodoTipo = null)
    {
        $this->notaPeriodoTipo = $notaPeriodoTipo;
    
        return $this;
    }

    /**
     * Get notaPeriodoTipo
     *
     * @return \Sie\AppWebBundle\Entity\NotaPeriodoTipo 
     */
    public function getNotaPeriodoTipo()
    {
        return $this->notaPeriodoTipo;
    }

    /**
     * Set maestroInscripcionAsesor
     *
     * @param \Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcionAsesor
     * @return InstitucioneducativaCurso
     */
    public function setMaestroInscripcionAsesor(\Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcionAsesor = null)
    {
        $this->maestroInscripcionAsesor = $maestroInscripcionAsesor;
    
        return $this;
    }

    /**
     * Get maestroInscripcionAsesor
     *
     * @return \Sie\AppWebBundle\Entity\MaestroInscripcion 
     */
    public function getMaestroInscripcionAsesor()
    {
        return $this->maestroInscripcionAsesor;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaPeriodo
     */
    private $superiorInstitucioneducativaPeriodo;


    /**
     * Set superiorInstitucioneducativaPeriodo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaPeriodo $superiorInstitucioneducativaPeriodo
     * @return InstitucioneducativaCurso
     */
    public function setSuperiorInstitucioneducativaPeriodo(\Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaPeriodo $superiorInstitucioneducativaPeriodo = null)
    {
        $this->superiorInstitucioneducativaPeriodo = $superiorInstitucioneducativaPeriodo;
    
        return $this;
    }

    /**
     * Get superiorInstitucioneducativaPeriodo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaPeriodo 
     */
    public function getSuperiorInstitucioneducativaPeriodo()
    {
        return $this->superiorInstitucioneducativaPeriodo;
    }
    /**
     * @var string
     */
    private $obs;


    /**
     * Set obs
     *
     * @param string $obs
     * @return InstitucioneducativaCurso
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\FinanciamientoTipo
     */
    private $finDesEscolarTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $terLenEnsenanzaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $segLenEnsenanzaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $priLenEnsenanzaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaMenHabladoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaRegHabladoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaMasHabladoTipo;


    /**
     * Set finDesEscolarTipo
     *
     * @param \Sie\AppWebBundle\Entity\FinanciamientoTipo $finDesEscolarTipo
     * @return InstitucioneducativaCurso
     */
    public function setFinDesEscolarTipo(\Sie\AppWebBundle\Entity\FinanciamientoTipo $finDesEscolarTipo = null)
    {
        $this->finDesEscolarTipo = $finDesEscolarTipo;
    
        return $this;
    }

    /**
     * Get finDesEscolarTipo
     *
     * @return \Sie\AppWebBundle\Entity\FinanciamientoTipo 
     */
    public function getFinDesEscolarTipo()
    {
        return $this->finDesEscolarTipo;
    }

    /**
     * Set terLenEnsenanzaTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $terLenEnsenanzaTipo
     * @return InstitucioneducativaCurso
     */
    public function setTerLenEnsenanzaTipo(\Sie\AppWebBundle\Entity\IdiomaTipo $terLenEnsenanzaTipo = null)
    {
        $this->terLenEnsenanzaTipo = $terLenEnsenanzaTipo;
    
        return $this;
    }

    /**
     * Get terLenEnsenanzaTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getTerLenEnsenanzaTipo()
    {
        return $this->terLenEnsenanzaTipo;
    }

    /**
     * Set segLenEnsenanzaTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $segLenEnsenanzaTipo
     * @return InstitucioneducativaCurso
     */
    public function setSegLenEnsenanzaTipo(\Sie\AppWebBundle\Entity\IdiomaTipo $segLenEnsenanzaTipo = null)
    {
        $this->segLenEnsenanzaTipo = $segLenEnsenanzaTipo;
    
        return $this;
    }

    /**
     * Get segLenEnsenanzaTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getSegLenEnsenanzaTipo()
    {
        return $this->segLenEnsenanzaTipo;
    }

    /**
     * Set priLenEnsenanzaTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $priLenEnsenanzaTipo
     * @return InstitucioneducativaCurso
     */
    public function setPriLenEnsenanzaTipo(\Sie\AppWebBundle\Entity\IdiomaTipo $priLenEnsenanzaTipo = null)
    {
        $this->priLenEnsenanzaTipo = $priLenEnsenanzaTipo;
    
        return $this;
    }

    /**
     * Get priLenEnsenanzaTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getPriLenEnsenanzaTipo()
    {
        return $this->priLenEnsenanzaTipo;
    }

    /**
     * Set idiomaMenHabladoTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaMenHabladoTipo
     * @return InstitucioneducativaCurso
     */
    public function setIdiomaMenHabladoTipo(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaMenHabladoTipo = null)
    {
        $this->idiomaMenHabladoTipo = $idiomaMenHabladoTipo;
    
        return $this;
    }

    /**
     * Get idiomaMenHabladoTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaMenHabladoTipo()
    {
        return $this->idiomaMenHabladoTipo;
    }

    /**
     * Set idiomaRegHabladoTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaRegHabladoTipo
     * @return InstitucioneducativaCurso
     */
    public function setIdiomaRegHabladoTipo(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaRegHabladoTipo = null)
    {
        $this->idiomaRegHabladoTipo = $idiomaRegHabladoTipo;
    
        return $this;
    }

    /**
     * Get idiomaRegHabladoTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaRegHabladoTipo()
    {
        return $this->idiomaRegHabladoTipo;
    }

    /**
     * Set idiomaMasHabladoTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaMasHabladoTipo
     * @return InstitucioneducativaCurso
     */
    public function setIdiomaMasHabladoTipo(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaMasHabladoTipo = null)
    {
        $this->idiomaMasHabladoTipo = $idiomaMasHabladoTipo;
    
        return $this;
    }

    /**
     * Get idiomaMasHabladoTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaMasHabladoTipo()
    {
        return $this->idiomaMasHabladoTipo;
    }
}
