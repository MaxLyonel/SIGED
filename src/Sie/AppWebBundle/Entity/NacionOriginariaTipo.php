<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NacionOriginariaTipo
 */
class NacionOriginariaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nacionOriginaria;

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
     * Set nacionOriginaria
     *
     * @param string $nacionOriginaria
     * @return NacionOriginariaTipo
     */
    public function setNacionOriginaria($nacionOriginaria)
    {
        $this->nacionOriginaria = $nacionOriginaria;
    
        return $this;
    }

    /**
     * Get nacionOriginaria
     *
     * @return string 
     */
    public function getNacionOriginaria()
    {
        return $this->nacionOriginaria;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return NacionOriginariaTipo
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
