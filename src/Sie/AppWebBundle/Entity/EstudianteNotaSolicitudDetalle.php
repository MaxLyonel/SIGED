<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteNotaSolicitudDetalle
 */
class EstudianteNotaSolicitudDetalle
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $estudianteNotaId;

    /**
     * @var string
     */
    private $asignatura;

    /**
     * @var string
     */
    private $periodo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var integer
     */
    private $notaCuantitativaPrev;

    /**
     * @var integer
     */
    private $notaCuantitativaNew;

    /**
     * @var string
     */
    private $notaCualitativaPrev;

    /**
     * @var string
     */
    private $notaCualitativaNew;

    /**
     * @var string
     */
    private $valoracionTipo;

    /**
     * @var integer
     */
    private $notaTipoId;

    /**
     * @var integer
     */
    private $estudianteAsignaturaId;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteNotaSolicitud
     */
    private $estudianteNotaSolicitud;


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
     * Set estudianteNotaId
     *
     * @param integer $estudianteNotaId
     * @return EstudianteNotaSolicitudDetalle
     */
    public function setEstudianteNotaId($estudianteNotaId)
    {
        $this->estudianteNotaId = $estudianteNotaId;
    
        return $this;
    }

    /**
     * Get estudianteNotaId
     *
     * @return integer 
     */
    public function getEstudianteNotaId()
    {
        return $this->estudianteNotaId;
    }

    /**
     * Set asignatura
     *
     * @param string $asignatura
     * @return EstudianteNotaSolicitudDetalle
     */
    public function setAsignatura($asignatura)
    {
        $this->asignatura = $asignatura;
    
        return $this;
    }

    /**
     * Get asignatura
     *
     * @return string 
     */
    public function getAsignatura()
    {
        return $this->asignatura;
    }

    /**
     * Set periodo
     *
     * @param string $periodo
     * @return EstudianteNotaSolicitudDetalle
     */
    public function setPeriodo($periodo)
    {
        $this->periodo = $periodo;
    
        return $this;
    }

    /**
     * Get periodo
     *
     * @return string 
     */
    public function getPeriodo()
    {
        return $this->periodo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteNotaSolicitudDetalle
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
     * Set notaCuantitativaPrev
     *
     * @param integer $notaCuantitativaPrev
     * @return EstudianteNotaSolicitudDetalle
     */
    public function setNotaCuantitativaPrev($notaCuantitativaPrev)
    {
        $this->notaCuantitativaPrev = $notaCuantitativaPrev;
    
        return $this;
    }

    /**
     * Get notaCuantitativaPrev
     *
     * @return integer 
     */
    public function getNotaCuantitativaPrev()
    {
        return $this->notaCuantitativaPrev;
    }

    /**
     * Set notaCuantitativaNew
     *
     * @param integer $notaCuantitativaNew
     * @return EstudianteNotaSolicitudDetalle
     */
    public function setNotaCuantitativaNew($notaCuantitativaNew)
    {
        $this->notaCuantitativaNew = $notaCuantitativaNew;
    
        return $this;
    }

    /**
     * Get notaCuantitativaNew
     *
     * @return integer 
     */
    public function getNotaCuantitativaNew()
    {
        return $this->notaCuantitativaNew;
    }

    /**
     * Set notaCualitativaPrev
     *
     * @param string $notaCualitativaPrev
     * @return EstudianteNotaSolicitudDetalle
     */
    public function setNotaCualitativaPrev($notaCualitativaPrev)
    {
        $this->notaCualitativaPrev = $notaCualitativaPrev;
    
        return $this;
    }

    /**
     * Get notaCualitativaPrev
     *
     * @return string 
     */
    public function getNotaCualitativaPrev()
    {
        return $this->notaCualitativaPrev;
    }

    /**
     * Set notaCualitativaNew
     *
     * @param string $notaCualitativaNew
     * @return EstudianteNotaSolicitudDetalle
     */
    public function setNotaCualitativaNew($notaCualitativaNew)
    {
        $this->notaCualitativaNew = $notaCualitativaNew;
    
        return $this;
    }

    /**
     * Get notaCualitativaNew
     *
     * @return string 
     */
    public function getNotaCualitativaNew()
    {
        return $this->notaCualitativaNew;
    }

    /**
     * Set valoracionTipo
     *
     * @param string $valoracionTipo
     * @return EstudianteNotaSolicitudDetalle
     */
    public function setValoracionTipo($valoracionTipo)
    {
        $this->valoracionTipo = $valoracionTipo;
    
        return $this;
    }

    /**
     * Get valoracionTipo
     *
     * @return string 
     */
    public function getValoracionTipo()
    {
        return $this->valoracionTipo;
    }

    /**
     * Set notaTipoId
     *
     * @param integer $notaTipoId
     * @return EstudianteNotaSolicitudDetalle
     */
    public function setNotaTipoId($notaTipoId)
    {
        $this->notaTipoId = $notaTipoId;
    
        return $this;
    }

    /**
     * Get notaTipoId
     *
     * @return integer 
     */
    public function getNotaTipoId()
    {
        return $this->notaTipoId;
    }

    /**
     * Set estudianteAsignaturaId
     *
     * @param integer $estudianteAsignaturaId
     * @return EstudianteNotaSolicitudDetalle
     */
    public function setEstudianteAsignaturaId($estudianteAsignaturaId)
    {
        $this->estudianteAsignaturaId = $estudianteAsignaturaId;
    
        return $this;
    }

    /**
     * Get estudianteAsignaturaId
     *
     * @return integer 
     */
    public function getEstudianteAsignaturaId()
    {
        return $this->estudianteAsignaturaId;
    }

    /**
     * Set estudianteNotaSolicitud
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteNotaSolicitud $estudianteNotaSolicitud
     * @return EstudianteNotaSolicitudDetalle
     */
    public function setEstudianteNotaSolicitud(\Sie\AppWebBundle\Entity\EstudianteNotaSolicitud $estudianteNotaSolicitud = null)
    {
        $this->estudianteNotaSolicitud = $estudianteNotaSolicitud;
    
        return $this;
    }

    /**
     * Get estudianteNotaSolicitud
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteNotaSolicitud 
     */
    public function getEstudianteNotaSolicitud()
    {
        return $this->estudianteNotaSolicitud;
    }
}
