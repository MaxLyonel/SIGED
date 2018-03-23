<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimPeriodoTipo
 */
class OlimPeriodoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $perido;

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
     * Set perido
     *
     * @param string $perido
     * @return OlimPeriodoTipo
     */
    public function setPerido($perido)
    {
        $this->perido = $perido;
    
        return $this;
    }

    /**
     * Get perido
     *
     * @return string 
     */
    public function getPerido()
    {
        return $this->perido;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return OlimPeriodoTipo
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
