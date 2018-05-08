<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimRegistroOlimpiada
 */
class OlimRegistroOlimpiada
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombreOlimpiada;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

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
     * Set nombreOlimpiada
     *
     * @param string $nombreOlimpiada
     * @return OlimRegistroOlimpiada
     */
    public function setNombreOlimpiada($nombreOlimpiada)
    {
        $this->nombreOlimpiada = $nombreOlimpiada;
    
        return $this;
    }

    /**
     * Get nombreOlimpiada
     *
     * @return string 
     */
    public function getNombreOlimpiada()
    {
        return $this->nombreOlimpiada;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return OlimRegistroOlimpiada
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
     * @return OlimRegistroOlimpiada
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return OlimRegistroOlimpiada
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
