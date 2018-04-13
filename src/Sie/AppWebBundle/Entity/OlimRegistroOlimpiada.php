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
     * @var integer
     */
    private $gestionTipoId;

    public function __toString(){
        return $this->nombreOlimpiada;
    }

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
        $this->nombreOlimpiada = mb_strtoupper($nombreOlimpiada,'utf-8');
    
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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return OlimRegistroOlimpiada
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


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
