<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpCircunscripcionTipo
 */
class JdpCircunscripcionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $circunscripcion;

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
     * Set circunscripcion
     *
     * @param string $circunscripcion
     * @return JdpCircunscripcionTipo
     */
    public function setCircunscripcion($circunscripcion)
    {
        $this->circunscripcion = $circunscripcion;
    
        return $this;
    }

    /**
     * Get circunscripcion
     *
     * @return string 
     */
    public function getCircunscripcion()
    {
        return $this->circunscripcion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return JdpCircunscripcionTipo
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
