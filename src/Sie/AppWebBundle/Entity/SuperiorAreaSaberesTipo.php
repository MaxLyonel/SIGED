<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperiorAreaSaberesTipo
 */
class SuperiorAreaSaberesTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $areaSuperior;

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
     * Set areaSuperior
     *
     * @param string $areaSuperior
     * @return SuperiorAreaSaberesTipo
     */
    public function setAreaSuperior($areaSuperior)
    {
        $this->areaSuperior = $areaSuperior;
    
        return $this;
    }

    /**
     * Get areaSuperior
     *
     * @return string 
     */
    public function getAreaSuperior()
    {
        return $this->areaSuperior;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return SuperiorAreaSaberesTipo
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
