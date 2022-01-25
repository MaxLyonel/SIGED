<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApoderadoActividadTipo
 */
class ApoderadoActividadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $actividad;

    /**
     * @var string
     */
    private $obs;

    public function __toString(){
        return $this->actividad;
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
     * Set actividad
     *
     * @param string $actividad
     * @return ApoderadoActividadTipo
     */
    public function setActividad($actividad)
    {
        $this->actividad = $actividad;
    
        return $this;
    }

    /**
     * Get actividad
     *
     * @return string 
     */
    public function getActividad()
    {
        return $this->actividad;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return ApoderadoActividadTipo
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
