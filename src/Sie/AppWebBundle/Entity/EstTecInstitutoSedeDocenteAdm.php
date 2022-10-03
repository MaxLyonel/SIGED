<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstTecInstitutoSedeDocenteAdm
 */
class EstTecInstitutoSedeDocenteAdm
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
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecCargoTipo
     */
    private $estTecCargoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecSede
     */
    private $estTecSede;


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
     * @return EstTecInstitutoSedeDocenteAdm
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
     * @return EstTecInstitutoSedeDocenteAdm
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
     * @return EstTecInstitutoSedeDocenteAdm
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return EstTecInstitutoSedeDocenteAdm
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
     * Set estTecCargoTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecCargoTipo $estTecCargoTipo
     * @return EstTecInstitutoSedeDocenteAdm
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
     * @return EstTecInstitutoSedeDocenteAdm
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
     * Set estTecSede
     *
     * @param \Sie\AppWebBundle\Entity\EstTecSede $estTecSede
     * @return EstTecInstitutoSedeDocenteAdm
     */
    public function setEstTecSede(\Sie\AppWebBundle\Entity\EstTecSede $estTecSede = null)
    {
        $this->estTecSede = $estTecSede;
    
        return $this;
    }

    /**
     * Get estTecSede
     *
     * @return \Sie\AppWebBundle\Entity\EstTecSede 
     */
    public function getEstTecSede()
    {
        return $this->estTecSede;
    }
}
