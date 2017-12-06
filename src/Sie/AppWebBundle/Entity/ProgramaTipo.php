<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProgramaTipo
 */
class ProgramaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $programa;

    /**
     * @var string
     */
    private $obs;

    public function __toString() {
        return $this->programa;
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
     * Set programa
     *
     * @param string $programa
     * @return ProgramaTipo
     */
    public function setPrograma($programa)
    {
        $this->programa = $programa;
    
        return $this;
    }

    /**
     * Get programa
     *
     * @return string 
     */
    public function getPrograma()
    {
        return $this->programa;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return ProgramaTipo
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
