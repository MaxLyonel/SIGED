<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4DisponibilidadTipo
 */
class InfraestructuraH4DisponibilidadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraDisponibilidad;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var integer
     */
    private $gestionTipoId;

    public function __toString(){
        return $this->infraestructuraDisponibilidad;
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
     * Set infraestructuraDisponibilidad
     *
     * @param string $infraestructuraDisponibilidad
     * @return InfraestructuraH4DisponibilidadTipo
     */
    public function setInfraestructuraDisponibilidad($infraestructuraDisponibilidad)
    {
        $this->infraestructuraDisponibilidad = $infraestructuraDisponibilidad;
    
        return $this;
    }

    /**
     * Get infraestructuraDisponibilidad
     *
     * @return string 
     */
    public function getInfraestructuraDisponibilidad()
    {
        return $this->infraestructuraDisponibilidad;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH4DisponibilidadTipo
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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return InfraestructuraH4DisponibilidadTipo
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
}
