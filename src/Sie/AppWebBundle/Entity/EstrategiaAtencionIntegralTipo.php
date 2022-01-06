<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstrategiaAtencionIntegralTipo
 */
class EstrategiaAtencionIntegralTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $estrategiaatencion;

    /**
     * @var string
     */
    private $obs;


    /**
     * Set id
     *
     * @param integer $id
     * @return EstrategiaAtencionIntegralTipo
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
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
     * Set estrategiaatencion
     *
     * @param string $estrategiaatencion
     * @return EstrategiaAtencionIntegralTipo
     */
    public function setEstrategiaatencion($estrategiaatencion)
    {
        $this->estrategiaatencion = $estrategiaatencion;
    
        return $this;
    }

    /**
     * Get estrategiaatencion
     *
     * @return string 
     */
    public function getEstrategiaatencion()
    {
        return $this->estrategiaatencion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstrategiaAtencionIntegralTipo
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
