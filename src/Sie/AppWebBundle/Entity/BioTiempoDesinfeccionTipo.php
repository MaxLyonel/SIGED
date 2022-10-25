<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BioTiempoDesinfeccionTipo
 */
class BioTiempoDesinfeccionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tiempoDesinfeccion;

    /**
     * @var string
     */
    private $obs;


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
     * Set tiempoDesinfeccion
     *
     * @param string $tiempoDesinfeccion
     * @return BioTiempoDesinfeccionTipo
     */
    public function setTiempoDesinfeccion($tiempoDesinfeccion)
    {
        $this->tiempoDesinfeccion = $tiempoDesinfeccion;
    
        return $this;
    }

    /**
     * Get tiempoDesinfeccion
     *
     * @return string 
     */
    public function getTiempoDesinfeccion()
    {
        return $this->tiempoDesinfeccion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BioTiempoDesinfeccionTipo
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
