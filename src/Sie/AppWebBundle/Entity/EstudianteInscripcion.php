<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcion
 */
class EstudianteInscripcion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $numMatricula;

    /**
     * @var string
     */
    private $codUeProcedenciaId;

    /**
     * @var integer
     */
    private $observacionId;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \DateTime
     */
    private $fechaInscripcion;

    /**
     * @var string
     */
    private $apreciacionFinal;

    /**
     * @var integer
     */
    private $operativoId;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadomatriculaTipo
     */
    private $estadomatriculaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ParaleloTipo
     */
    private $paraleloTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\CicloTipo
     */
    private $cicloTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set numMatricula
     *
     * @param integer $numMatricula
     * @return EstudianteInscripcion
     */
    public function setNumMatricula($numMatricula)
    {
        $this->numMatricula = $numMatricula;

        return $this;
    }

    /**
     * Get numMatricula
     *
     * @return integer 
     */
    public function getNumMatricula()
    {
        return $this->numMatricula;
    }

    /**
     * Set codUeProcedenciaId
     *
     * @param string $codUeProcedenciaId
     * @return EstudianteInscripcion
     */
    public function setCodUeProcedenciaId($codUeProcedenciaId)
    {
        $this->codUeProcedenciaId = $codUeProcedenciaId;

        return $this;
    }

    /**
     * Get codUeProcedenciaId
     *
     * @return string 
     */
    public function getCodUeProcedenciaId()
    {
        return $this->codUeProcedenciaId;
    }

    /**
     * Set observacionId
     *
     * @param integer $observacionId
     * @return EstudianteInscripcion
     */
    public function setObservacionId($observacionId)
    {
        $this->observacionId = $observacionId;

        return $this;
    }

    /**
     * Get observacionId
     *
     * @return integer 
     */
    public function getObservacionId()
    {
        return $this->observacionId;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return EstudianteInscripcion
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;

        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set fechaInscripcion
     *
     * @param \DateTime $fechaInscripcion
     * @return EstudianteInscripcion
     */
    public function setFechaInscripcion($fechaInscripcion)
    {
        $this->fechaInscripcion = $fechaInscripcion;

        return $this;
    }

    /**
     * Get fechaInscripcion
     *
     * @return \DateTime 
     */
    public function getFechaInscripcion()
    {
        return $this->fechaInscripcion;
    }

    /**
     * Set apreciacionFinal
     *
     * @param string $apreciacionFinal
     * @return EstudianteInscripcion
     */
    public function setApreciacionFinal($apreciacionFinal)
    {
        $this->apreciacionFinal = $apreciacionFinal;

        return $this;
    }

    /**
     * Get apreciacionFinal
     *
     * @return string 
     */
    public function getApreciacionFinal()
    {
        return $this->apreciacionFinal;
    }

    /**
     * Set operativoId
     *
     * @param integer $operativoId
     * @return EstudianteInscripcion
     */
    public function setOperativoId($operativoId)
    {
        $this->operativoId = $operativoId;

        return $this;
    }

    /**
     * Get operativoId
     *
     * @return integer 
     */
    public function getOperativoId()
    {
        return $this->operativoId;
    }

    /**
     * Set estadomatriculaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipo
     * @return EstudianteInscripcion
     */
    public function setEstadomatriculaTipo(\Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipo = null)
    {
        $this->estadomatriculaTipo = $estadomatriculaTipo;

        return $this;
    }

    /**
     * Get estadomatriculaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstadomatriculaTipo 
     */
    public function getEstadomatriculaTipo()
    {
        return $this->estadomatriculaTipo;
    }

    /**
     * Set paraleloTipo
     *
     * @param \Sie\AppWebBundle\Entity\ParaleloTipo $paraleloTipo
     * @return EstudianteInscripcion
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
     * Set cicloTipo
     *
     * @param \Sie\AppWebBundle\Entity\CicloTipo $cicloTipo
     * @return EstudianteInscripcion
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
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return EstudianteInscripcion
     */
    public function setEstudiante(\Sie\AppWebBundle\Entity\Estudiante $estudiante = null)
    {
        $this->estudiante = $estudiante;

        return $this;
    }

    /**
     * Get estudiante
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getEstudiante()
    {
        return $this->estudiante;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return EstudianteInscripcion
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
     * @return EstudianteInscripcion
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
     * @return EstudianteInscripcion
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
     * @return EstudianteInscripcion
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
     * Set periodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\PeriodoTipo $periodoTipo
     * @return EstudianteInscripcion
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
     * @return EstudianteInscripcion
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
     * @return EstudianteInscripcion
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
     * @var integer
     */
    private $modalidadTipoId;

    /**
     * @var integer
     */
    private $acreditacionnivelTipoId;

    /**
     * @var integer
     */
    private $permanenteprogramaTipoId;

    /**
     * @var string
     */
    private $lugarcurso;

    /**
     * @var string
     */
    private $organizacion;

    /**
     * @var string
     */
    private $facilitadorcurso;

    /**
     * @var \DateTime
     */
    private $fechainiciocurso;

    /**
     * @var \DateTime
     */
    private $fechafincurso;


    /**
     * Set modalidadTipoId
     *
     * @param integer $modalidadTipoId
     * @return EstudianteInscripcion
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
     * Set acreditacionnivelTipoId
     *
     * @param integer $acreditacionnivelTipoId
     * @return EstudianteInscripcion
     */
    public function setAcreditacionnivelTipoId($acreditacionnivelTipoId)
    {
        $this->acreditacionnivelTipoId = $acreditacionnivelTipoId;

        return $this;
    }

    /**
     * Get acreditacionnivelTipoId
     *
     * @return integer 
     */
    public function getAcreditacionnivelTipoId()
    {
        return $this->acreditacionnivelTipoId;
    }

    /**
     * Set permanenteprogramaTipoId
     *
     * @param integer $permanenteprogramaTipoId
     * @return EstudianteInscripcion
     */
    public function setPermanenteprogramaTipoId($permanenteprogramaTipoId)
    {
        $this->permanenteprogramaTipoId = $permanenteprogramaTipoId;

        return $this;
    }

    /**
     * Get permanenteprogramaTipoId
     *
     * @return integer 
     */
    public function getPermanenteprogramaTipoId()
    {
        return $this->permanenteprogramaTipoId;
    }

    /**
     * Set lugarcurso
     *
     * @param string $lugarcurso
     * @return EstudianteInscripcion
     */
    public function setLugarcurso($lugarcurso)
    {
        $this->lugarcurso = $lugarcurso;

        return $this;
    }

    /**
     * Get lugarcurso
     *
     * @return string 
     */
    public function getLugarcurso()
    {
        return $this->lugarcurso;
    }

    /**
     * Set organizacion
     *
     * @param string $organizacion
     * @return EstudianteInscripcion
     */
    public function setOrganizacion($organizacion)
    {
        $this->organizacion = $organizacion;

        return $this;
    }

    /**
     * Get organizacion
     *
     * @return string 
     */
    public function getOrganizacion()
    {
        return $this->organizacion;
    }

    /**
     * Set facilitadorcurso
     *
     * @param string $facilitadorcurso
     * @return EstudianteInscripcion
     */
    public function setFacilitadorcurso($facilitadorcurso)
    {
        $this->facilitadorcurso = $facilitadorcurso;

        return $this;
    }

    /**
     * Get facilitadorcurso
     *
     * @return string 
     */
    public function getFacilitadorcurso()
    {
        return $this->facilitadorcurso;
    }

    /**
     * Set fechainiciocurso
     *
     * @param \DateTime $fechainiciocurso
     * @return EstudianteInscripcion
     */
    public function setFechainiciocurso($fechainiciocurso)
    {
        $this->fechainiciocurso = $fechainiciocurso;

        return $this;
    }

    /**
     * Get fechainiciocurso
     *
     * @return \DateTime 
     */
    public function getFechainiciocurso()
    {
        return $this->fechainiciocurso;
    }

    /**
     * Set fechafincurso
     *
     * @param \DateTime $fechafincurso
     * @return EstudianteInscripcion
     */
    public function setFechafincurso($fechafincurso)
    {
        $this->fechafincurso = $fechafincurso;

        return $this;
    }

    /**
     * Get fechafincurso
     *
     * @return \DateTime 
     */
    public function getFechafincurso()
    {
        return $this->fechafincurso;
    }
    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var string
     */
    private $facilitadorpermanente;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var string
     */
    private $cursonombre;

    /**
     * @var string
     */
    private $lugar;

    /**
     * @var \Sie\AppWebBundle\Entity\ModalidadTipo
     */
    private $modalidadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\AcreditacionnivelTipo
     */
    private $acreditacionnivelTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\PermanenteProgramaTipo
     */
    private $permanenteprogramaTipo;


    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteInscripcion
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set facilitadorpermanente
     *
     * @param string $facilitadorpermanente
     * @return EstudianteInscripcion
     */
    public function setFacilitadorpermanente($facilitadorpermanente)
    {
        $this->facilitadorpermanente = $facilitadorpermanente;
    
        return $this;
    }

    /**
     * Get facilitadorpermanente
     *
     * @return string 
     */
    public function getFacilitadorpermanente()
    {
        return $this->facilitadorpermanente;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return EstudianteInscripcion
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
     * @return EstudianteInscripcion
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
     * Set cursonombre
     *
     * @param string $cursonombre
     * @return EstudianteInscripcion
     */
    public function setCursonombre($cursonombre)
    {
        $this->cursonombre = $cursonombre;
    
        return $this;
    }

    /**
     * Get cursonombre
     *
     * @return string 
     */
    public function getCursonombre()
    {
        return $this->cursonombre;
    }

    /**
     * Set lugar
     *
     * @param string $lugar
     * @return EstudianteInscripcion
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
     * Set modalidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\ModalidadTipo $modalidadTipo
     * @return EstudianteInscripcion
     */
    public function setModalidadTipo(\Sie\AppWebBundle\Entity\ModalidadTipo $modalidadTipo = null)
    {
        $this->modalidadTipo = $modalidadTipo;
    
        return $this;
    }

    /**
     * Get modalidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\ModalidadTipo 
     */
    public function getModalidadTipo()
    {
        return $this->modalidadTipo;
    }

    /**
     * Set acreditacionnivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\AcreditacionnivelTipo $acreditacionnivelTipo
     * @return EstudianteInscripcion
     */
    public function setAcreditacionnivelTipo(\Sie\AppWebBundle\Entity\AcreditacionnivelTipo $acreditacionnivelTipo = null)
    {
        $this->acreditacionnivelTipo = $acreditacionnivelTipo;
    
        return $this;
    }

    /**
     * Get acreditacionnivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\AcreditacionnivelTipo 
     */
    public function getAcreditacionnivelTipo()
    {
        return $this->acreditacionnivelTipo;
    }

    /**
     * Set permanenteprogramaTipo
     *
     * @param \Sie\AppWebBundle\Entity\PermanenteProgramaTipo $permanenteProgramaTipo
     * @return EstudianteInscripcion
     */
    public function setPermanenteprogramaTipo(\Sie\AppWebBundle\Entity\PermanenteProgramaTipo $permanenteprogramaTipo = null)
    {
        $this->permanenteprogramaTipo = $permanenteprogramaTipo;
    
        return $this;
    }

    /**
     * Get permanenteprogramaTipo
     *
     * @return \Sie\AppWebBundle\Entity\PermanenteProgramaTipo
     */
    public function getPermanenteprogramaTipo()
    {
        return $this->permanenteprogramaTipo;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaCurso
     */
    private $institucioneducativaCurso;


    /**
     * Set institucioneducativaCurso
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCurso $institucioneducativaCurso
     * @return EstudianteInscripcion
     */
    public function setInstitucioneducativaCurso(\Sie\AppWebBundle\Entity\InstitucioneducativaCurso $institucioneducativaCurso = null)
    {
        $this->institucioneducativaCurso = $institucioneducativaCurso;
    
        return $this;
    }

    /**
     * Get institucioneducativaCurso
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCurso 
     */
    public function getInstitucioneducativaCurso()
    {
        return $this->institucioneducativaCurso;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\EstadomatriculaTipo
     */
    private $estadomatriculaInicioTipo;


    /**
     * Set estadomatriculaInicioTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaInicioTipo
     * @return EstudianteInscripcion
     */
    public function setEstadomatriculaInicioTipo(\Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaInicioTipo = null)
    {
        $this->estadomatriculaInicioTipo = $estadomatriculaInicioTipo;
    
        return $this;
    }

    /**
     * Get estadomatriculaInicioTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstadomatriculaTipo 
     */
    public function getEstadomatriculaInicioTipo()
    {
        return $this->estadomatriculaInicioTipo;
    }

    /**
     * @var integer
     */
    private $usuarioId;


    /**
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return EstudianteInscripcion
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }
}
