<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperiorPeriodoTipo
 */
class SuperiorPeriodoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $periodoSuperior;

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
     * Set periodoSuperior
     *
     * @param string $periodoSuperior
     * @return SuperiorPeriodoTipo
     */
    public function setPeriodoSuperior($periodoSuperior)
    {
        $this->periodoSuperior = $periodoSuperior;
    
        return $this;
    }

    /**
     * Get periodoSuperior
     *
     * @return string 
     */
    public function getPeriodoSuperior()
    {
        return $this->periodoSuperior;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return SuperiorPeriodoTipo
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
