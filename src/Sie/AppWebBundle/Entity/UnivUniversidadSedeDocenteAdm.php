<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivUniversidadSedeDocenteAdm
 */
class UnivUniversidadSedeDocenteAdm
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
     * @var \Sie\AppWebBundle\Entity\UnivCargoTipo
     */
    private $univCargoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivSede
     */
    private $univSede;


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
     * @return UnivUniversidadSedeDocenteAdm
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
     * @return UnivUniversidadSedeDocenteAdm
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
     * @return UnivUniversidadSedeDocenteAdm
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
     * @return UnivUniversidadSedeDocenteAdm
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
     * Set univCargoTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivCargoTipo $univCargoTipo
     * @return UnivUniversidadSedeDocenteAdm
     */
    public function setUnivCargoTipo(\Sie\AppWebBundle\Entity\UnivCargoTipo $univCargoTipo = null)
    {
        $this->univCargoTipo = $univCargoTipo;
    
        return $this;
    }

    /**
     * Get univCargoTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivCargoTipo 
     */
    public function getUnivCargoTipo()
    {
        return $this->univCargoTipo;
    }

    /**
     * Set generoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GeneroTipo $generoTipo
     * @return UnivUniversidadSedeDocenteAdm
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
     * Set univSede
     *
     * @param \Sie\AppWebBundle\Entity\UnivSede $univSede
     * @return UnivUniversidadSedeDocenteAdm
     */
    public function setUnivSede(\Sie\AppWebBundle\Entity\UnivSede $univSede = null)
    {
        $this->univSede = $univSede;
    
        return $this;
    }

    /**
     * Get univSede
     *
     * @return \Sie\AppWebBundle\Entity\UnivSede 
     */
    public function getUnivSede()
    {
        return $this->univSede;
    }
}
