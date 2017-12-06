<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NivelacreditacionTipo
 */
class NivelacreditacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nivelacreditacion;

    /**
     * @var string
     */
    private $obs;

    public function __toString() {
        return $this->nivelacreditacion;
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
     * Set nivelacreditacion
     *
     * @param string $nivelacreditacion
     * @return NivelacreditacionTipo
     */
    public function setNivelacreditacion($nivelacreditacion)
    {
        $this->nivelacreditacion = $nivelacreditacion;
    
        return $this;
    }

    /**
     * Get nivelacreditacion
     *
     * @return string 
     */
    public function getNivelacreditacion()
    {
        return $this->nivelacreditacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return NivelacreditacionTipo
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
}
