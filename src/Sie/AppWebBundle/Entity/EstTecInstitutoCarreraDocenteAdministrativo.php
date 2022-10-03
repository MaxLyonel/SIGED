<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstTecInstitutoCarreraDocenteAdministrativo
 */
class EstTecInstitutoCarreraDocenteAdministrativo
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
     * @var \Sie\AppWebBundle\Entity\EstTecPeriodoAcademicoTipo
     */
    private $estTecPeriodoAcademicoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecCargoTipo
     */
    private $estTecCargoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecGradoAcademicoTipo
     */
    private $estTecGradoAcademicoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecModalidadEnsenanzaTipo
     */
    private $estTecModalidadEnsenanzaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecInstitutoCarrera
     */
    private $estTecInstitutoCarrera;

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
     * @return EstTecInstitutoCarreraDocenteAdministrativo
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
     * @return EstTecInstitutoCarreraDocenteAdministrativo
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
     * @return EstTecInstitutoCarreraDocenteAdministrativo
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
     * Set estTecPeriodoAcademicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecPeriodoAcademicoTipo $estTecPeriodoAcademicoTipo
     * @return EstTecInstitutoCarreraDocenteAdministrativo
     */
    public function setEstTecPeriodoAcademicoTipo(\Sie\AppWebBundle\Entity\EstTecPeriodoAcademicoTipo $estTecPeriodoAcademicoTipo = null)
    {
        $this->estTecPeriodoAcademicoTipo = $estTecPeriodoAcademicoTipo;
    
        return $this;
    }

    /**
     * Get estTecPeriodoAcademicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstTecPeriodoAcademicoTipo 
     */
    public function getEstTecPeriodoAcademicoTipo()
    {
        return $this->estTecPeriodoAcademicoTipo;
    }

    /**
     * Set estTecCargoTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecCargoTipo $estTecCargoTipo
     * @return EstTecInstitutoCarreraDocenteAdministrativo
     */
    public function setEstTecCargoTipo(\Sie\AppWebBundle\Entity\EstTecCargoTipo $estTecCargoTipo = null)
    {
        $this->estTecCargoTipo = $estTecCargoTipo;
    
        return $this;
    }

    /**
     * Get estTecCargoTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstTecCargoTipo 
     */
    public function getEstTecCargoTipo()
    {
        return $this->estTecCargoTipo;
    }

    /**
     * Set generoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GeneroTipo $generoTipo
     * @return EstTecInstitutoCarreraDocenteAdministrativo
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
     * Set estTecGradoAcademicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecGradoAcademicoTipo $estTecGradoAcademicoTipo
     * @return EstTecInstitutoCarreraDocenteAdministrativo
     */
    public function setEstTecGradoAcademicoTipo(\Sie\AppWebBundle\Entity\EstTecGradoAcademicoTipo $estTecGradoAcademicoTipo = null)
    {
        $this->estTecGradoAcademicoTipo = $estTecGradoAcademicoTipo;
    
        return $this;
    }

    /**
     * Get estTecGradoAcademicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstTecGradoAcademicoTipo 
     */
    public function getEstTecGradoAcademicoTipo()
    {
        return $this->estTecGradoAcademicoTipo;
    }

    /**
     * Set estTecModalidadEnsenanzaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecModalidadEnsenanzaTipo $estTecModalidadEnsenanzaTipo
     * @return EstTecInstitutoCarreraDocenteAdministrativo
     */
    public function setEstTecModalidadEnsenanzaTipo(\Sie\AppWebBundle\Entity\EstTecModalidadEnsenanzaTipo $estTecModalidadEnsenanzaTipo = null)
    {
        $this->estTecModalidadEnsenanzaTipo = $estTecModalidadEnsenanzaTipo;
    
        return $this;
    }

    /**
     * Get estTecModalidadEnsenanzaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstTecModalidadEnsenanzaTipo 
     */
    public function getEstTecModalidadEnsenanzaTipo()
    {
        return $this->estTecModalidadEnsenanzaTipo;
    }

    /**
     * Set estTecInstitutoCarrera
     *
     * @param \Sie\AppWebBundle\Entity\EstTecInstitutoCarrera $estTecInstitutoCarrera
     * @return EstTecInstitutoCarreraDocenteAdministrativo
     */
    public function setEstTecInstitutoCarrera(\Sie\AppWebBundle\Entity\EstTecInstitutoCarrera $estTecInstitutoCarrera = null)
    {
        $this->estTecInstitutoCarrera = $estTecInstitutoCarrera;
    
        return $this;
    }

    /**
     * Get estTecInstitutoCarrera
     *
     * @return \Sie\AppWebBundle\Entity\EstTecInstitutoCarrera 
     */
    public function getEstTecInstitutoCarrera()
    {
        return $this->estTecInstitutoCarrera;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return EstTecInstitutoCarreraDocenteAdministrativo
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
