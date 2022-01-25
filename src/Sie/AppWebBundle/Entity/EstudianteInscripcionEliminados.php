<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionEliminados
 */
class EstudianteInscripcionEliminados
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $estadomatriculaTipoId;

    /**
     * @var integer
     */
    private $estudianteId;

    /**
     * @var integer
     */
    private $numMatricula;

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
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var string
     */
    private $organizacion;

    /**
     * @var string
     */
    private $facilitadorpermanente;

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
     * @var string
     */
    private $lugarcurso;

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
     * @var integer
     */
    private $codUeProcedenciaId;

    /**
     * @var integer
     */
    private $institucioneducativaCursoId;

    /**
     * @var integer
     */
    private $estadomatriculaInicioTipoId;

    /**
     * @var string
     */
    private $obsEliminacion;

    /**
     * @var integer
     */
    private $usuarioId;

    /**
     * @var \DateTime
     */
    private $fechaEliminacion;

    /**
     * @var boolean
     */
    private $doc;


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
     * Set estadomatriculaTipoId
     *
     * @param integer $estadomatriculaTipoId
     * @return EstudianteInscripcionEliminados
     */
    public function setEstadomatriculaTipoId($estadomatriculaTipoId)
    {
        $this->estadomatriculaTipoId = $estadomatriculaTipoId;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipoId
     *
     * @return integer 
     */
    public function getEstadomatriculaTipoId()
    {
        return $this->estadomatriculaTipoId;
    }

    /**
     * Set estudianteId
     *
     * @param integer $estudianteId
     * @return EstudianteInscripcionEliminados
     */
    public function setEstudianteId($estudianteId)
    {
        $this->estudianteId = $estudianteId;
    
        return $this;
    }

    /**
     * Get estudianteId
     *
     * @return integer 
     */
    public function getEstudianteId()
    {
        return $this->estudianteId;
    }

    /**
     * Set numMatricula
     *
     * @param integer $numMatricula
     * @return EstudianteInscripcionEliminados
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
     * Set observacionId
     *
     * @param integer $observacionId
     * @return EstudianteInscripcionEliminados
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
     * @return EstudianteInscripcionEliminados
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
     * @return EstudianteInscripcionEliminados
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
     * @return EstudianteInscripcionEliminados
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
     * @return EstudianteInscripcionEliminados
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteInscripcionEliminados
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
     * Set organizacion
     *
     * @param string $organizacion
     * @return EstudianteInscripcionEliminados
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
     * Set facilitadorpermanente
     *
     * @param string $facilitadorpermanente
     * @return EstudianteInscripcionEliminados
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
     * Set modalidadTipoId
     *
     * @param integer $modalidadTipoId
     * @return EstudianteInscripcionEliminados
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
     * @return EstudianteInscripcionEliminados
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
     * @return EstudianteInscripcionEliminados
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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return EstudianteInscripcionEliminados
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
     * @return EstudianteInscripcionEliminados
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
     * @return EstudianteInscripcionEliminados
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
     * @return EstudianteInscripcionEliminados
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
     * Set lugarcurso
     *
     * @param string $lugarcurso
     * @return EstudianteInscripcionEliminados
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
     * Set facilitadorcurso
     *
     * @param string $facilitadorcurso
     * @return EstudianteInscripcionEliminados
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
     * @return EstudianteInscripcionEliminados
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
     * @return EstudianteInscripcionEliminados
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
     * Set codUeProcedenciaId
     *
     * @param integer $codUeProcedenciaId
     * @return EstudianteInscripcionEliminados
     */
    public function setCodUeProcedenciaId($codUeProcedenciaId)
    {
        $this->codUeProcedenciaId = $codUeProcedenciaId;
    
        return $this;
    }

    /**
     * Get codUeProcedenciaId
     *
     * @return integer 
     */
    public function getCodUeProcedenciaId()
    {
        return $this->codUeProcedenciaId;
    }

    /**
     * Set institucioneducativaCursoId
     *
     * @param integer $institucioneducativaCursoId
     * @return EstudianteInscripcionEliminados
     */
    public function setInstitucioneducativaCursoId($institucioneducativaCursoId)
    {
        $this->institucioneducativaCursoId = $institucioneducativaCursoId;
    
        return $this;
    }

    /**
     * Get institucioneducativaCursoId
     *
     * @return integer 
     */
    public function getInstitucioneducativaCursoId()
    {
        return $this->institucioneducativaCursoId;
    }

    /**
     * Set estadomatriculaInicioTipoId
     *
     * @param integer $estadomatriculaInicioTipoId
     * @return EstudianteInscripcionEliminados
     */
    public function setEstadomatriculaInicioTipoId($estadomatriculaInicioTipoId)
    {
        $this->estadomatriculaInicioTipoId = $estadomatriculaInicioTipoId;
    
        return $this;
    }

    /**
     * Get estadomatriculaInicioTipoId
     *
     * @return integer 
     */
    public function getEstadomatriculaInicioTipoId()
    {
        return $this->estadomatriculaInicioTipoId;
    }

    /**
     * Set obsEliminacion
     *
     * @param string $obsEliminacion
     * @return EstudianteInscripcionEliminados
     */
    public function setObsEliminacion($obsEliminacion)
    {
        $this->obsEliminacion = $obsEliminacion;
    
        return $this;
    }

    /**
     * Get obsEliminacion
     *
     * @return string 
     */
    public function getObsEliminacion()
    {
        return $this->obsEliminacion;
    }

    /**
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return EstudianteInscripcionEliminados
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

    /**
     * Set fechaEliminacion
     *
     * @param \DateTime $fechaEliminacion
     * @return EstudianteInscripcionEliminados
     */
    public function setFechaEliminacion($fechaEliminacion)
    {
        $this->fechaEliminacion = $fechaEliminacion;
    
        return $this;
    }

    /**
     * Get fechaEliminacion
     *
     * @return \DateTime 
     */
    public function getFechaEliminacion()
    {
        return $this->fechaEliminacion;
    }

    /**
     * Set doc
     *
     * @param boolean $doc
     * @return EstudianteInscripcionEliminados
     */
    public function setDoc($doc)
    {
        $this->doc = $doc;
    
        return $this;
    }

    /**
     * Get doc
     *
     * @return boolean 
     */
    public function getDoc()
    {
        return $this->doc;
    }
    /**
     * @var integer
     */
    private $estudianteInscripcionId;


    /**
     * Set estudianteInscripcionId
     *
     * @param integer $estudianteInscripcionId
     * @return EstudianteInscripcionEliminados
     */
    public function setEstudianteInscripcionId($estudianteInscripcionId)
    {
        $this->estudianteInscripcionId = $estudianteInscripcionId;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionId
     *
     * @return integer 
     */
    public function getEstudianteInscripcionId()
    {
        return $this->estudianteInscripcionId;
    }
}
