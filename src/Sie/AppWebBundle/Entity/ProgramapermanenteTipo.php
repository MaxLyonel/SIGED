<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProgramapermanenteTipo
 */
class ProgramapermanenteTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $permanenteprograma;

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
     * Set permanenteprograma
     *
     * @param string $permanenteprograma
     * @return ProgramapermanenteTipo
     */
    public function setPermanenteprograma($permanenteprograma)
    {
        $this->permanenteprograma = $permanenteprograma;
    
        return $this;
    }

    /**
     * Get permanenteprograma
     *
     * @return string 
     */
    public function getPermanenteprograma()
    {
        return $this->permanenteprograma;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return ProgramapermanenteTipo
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
