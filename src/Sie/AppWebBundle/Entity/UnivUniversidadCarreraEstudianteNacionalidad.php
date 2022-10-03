<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivUniversidadCarreraEstudianteNacionalidad
 */
class UnivUniversidadCarreraEstudianteNacionalidad
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
     * @var \Sie\AppWebBundle\Entity\UnivUniversidadCarrera
     */
    private $univUniversidadCarrera;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivPeriodoAcademicoTipo
     */
    private $univPeriodoAcademicoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivMatriculaNacionalidadBecaTipo
     */
    private $univMatriculaNacionalidadBecaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;

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
     * @return UnivUniversidadCarreraEstudianteNacionalidad
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
     * @return UnivUniversidadCarreraEstudianteNacionalidad
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
     * @return UnivUniversidadCarreraEstudianteNacionalidad
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
     * Set univUniversidadCarrera
     *
     * @param \Sie\AppWebBundle\Entity\UnivUniversidadCarrera $univUniversidadCarrera
     * @return UnivUniversidadCarreraEstudianteNacionalidad
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
     * Set univPeriodoAcademicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivPeriodoAcademicoTipo $univPeriodoAcademicoTipo
     * @return UnivUniversidadCarreraEstudianteNacionalidad
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
     * Set univMatriculaNacionalidadBecaTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivMatriculaNacionalidadBecaTipo $univMatriculaNacionalidadBecaTipo
     * @return UnivUniversidadCarreraEstudianteNacionalidad
     */
    public function setUnivMatriculaNacionalidadBecaTipo(\Sie\AppWebBundle\Entity\UnivMatriculaNacionalidadBecaTipo $univMatriculaNacionalidadBecaTipo = null)
    {
        $this->univMatriculaNacionalidadBecaTipo = $univMatriculaNacionalidadBecaTipo;
    
        return $this;
    }

    /**
     * Get univMatriculaNacionalidadBecaTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivMatriculaNacionalidadBecaTipo 
     */
    public function getUnivMatriculaNacionalidadBecaTipo()
    {
        return $this->univMatriculaNacionalidadBecaTipo;
    }

    /**
     * Set generoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GeneroTipo $generoTipo
     * @return UnivUniversidadCarreraEstudianteNacionalidad
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return UnivUniversidadCarreraEstudianteNacionalidad
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
