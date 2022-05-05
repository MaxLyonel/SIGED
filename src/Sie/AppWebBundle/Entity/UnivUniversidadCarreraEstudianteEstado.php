<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivUniversidadCarreraEstudianteEstado
 */
class UnivUniversidadCarreraEstudianteEstado
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $cantidad;

    /**
     * @var \DateTime
     */
    private $fechaCreacion;

    /**
     * @var \DateTime
     */
    private $fechaActualizacion;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivPeriodoAcademicoTipo
     */
    private $univPeriodoAcademicoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivEstadomatriculaTipo
     */
    private $univEstadomatriculaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivUniversidadCarrera
     */
    private $univUniversidadCarrera;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


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
     * Set cantidad
     *
     * @param integer $cantidad
     * @return UnivUniversidadCarreraEstudianteEstado
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;
    
        return $this;
    }

    /**
     * Get cantidad
     *
     * @return integer 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     * @return UnivUniversidadCarreraEstudianteEstado
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    
        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime 
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set fechaActualizacion
     *
     * @param \DateTime $fechaActualizacion
     * @return UnivUniversidadCarreraEstudianteEstado
     */
    public function setFechaActualizacion($fechaActualizacion)
    {
        $this->fechaActualizacion = $fechaActualizacion;
    
        return $this;
    }

    /**
     * Get fechaActualizacion
     *
     * @return \DateTime 
     */
    public function getFechaActualizacion()
    {
        return $this->fechaActualizacion;
    }

    /**
     * Set univPeriodoAcademicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivPeriodoAcademicoTipo $univPeriodoAcademicoTipo
     * @return UnivUniversidadCarreraEstudianteEstado
     */
    public function setUnivPeriodoAcademicoTipo(\Sie\AppWebBundle\Entity\UnivPeriodoAcademicoTipo $univPeriodoAcademicoTipo = null)
    {
        $this->univPeriodoAcademicoTipo = $univPeriodoAcademicoTipo;
    
        return $this;
    }

    /**
     * Get univPeriodoAcademicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivPeriodoAcademicoTipo 
     */
    public function getUnivPeriodoAcademicoTipo()
    {
        return $this->univPeriodoAcademicoTipo;
    }

    /**
     * Set univEstadomatriculaTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivEstadomatriculaTipo $univEstadomatriculaTipo
     * @return UnivUniversidadCarreraEstudianteEstado
     */
    public function setUnivEstadomatriculaTipo(\Sie\AppWebBundle\Entity\UnivEstadomatriculaTipo $univEstadomatriculaTipo = null)
    {
        $this->univEstadomatriculaTipo = $univEstadomatriculaTipo;
    
        return $this;
    }

    /**
     * Get univEstadomatriculaTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivEstadomatriculaTipo 
     */
    public function getUnivEstadomatriculaTipo()
    {
        return $this->univEstadomatriculaTipo;
    }

    /**
     * Set generoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GeneroTipo $generoTipo
     * @return UnivUniversidadCarreraEstudianteEstado
     */
    public function setGeneroTipo(\Sie\AppWebBundle\Entity\GeneroTipo $generoTipo = null)
    {
        $this->generoTipo = $generoTipo;
    
        return $this;
    }

    /**
     * Get generoTipo
     *
     * @return \Sie\AppWebBundle\Entity\GeneroTipo 
     */
    public function getGeneroTipo()
    {
        return $this->generoTipo;
    }

    /**
     * Set univUniversidadCarrera
     *
     * @param \Sie\AppWebBundle\Entity\UnivUniversidadCarrera $univUniversidadCarrera
     * @return UnivUniversidadCarreraEstudianteEstado
     */
    public function setUnivUniversidadCarrera(\Sie\AppWebBundle\Entity\UnivUniversidadCarrera $univUniversidadCarrera = null)
    {
        $this->univUniversidadCarrera = $univUniversidadCarrera;
    
        return $this;
    }

    /**
     * Get univUniversidadCarrera
     *
     * @return \Sie\AppWebBundle\Entity\UnivUniversidadCarrera 
     */
    public function getUnivUniversidadCarrera()
    {
        return $this->univUniversidadCarrera;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return UnivUniversidadCarreraEstudianteEstado
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
}
