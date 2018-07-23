<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimNivelMatematicaTipo
 */
class OlimNivelMatematicaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nivelMatematica;

    /**
     * @var string
     */
    private $observacion;


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
     * Set nivelMatematica
     *
     * @param string $nivelMatematica
     * @return OlimNivelMatematicaTipo
     */
    public function setNivelMatematica($nivelMatematica)
    {
        $this->nivelMatematica = $nivelMatematica;
    
        return $this;
    }

    /**
     * Get nivelMatematica
     *
     * @return string 
     */
    public function getNivelMatematica()
    {
        return $this->nivelMatematica;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return OlimNivelMatematicaTipo
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
}
