<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperiorTurnoTipo
 */
class SuperiorTurnoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $turnoSuperior;

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
     * Set turnoSuperior
     *
     * @param string $turnoSuperior
     * @return SuperiorTurnoTipo
     */
    public function setTurnoSuperior($turnoSuperior)
    {
        $this->turnoSuperior = $turnoSuperior;
    
        return $this;
    }

    /**
     * Get turnoSuperior
     *
     * @return string 
     */
    public function getTurnoSuperior()
    {
        return $this->turnoSuperior;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return SuperiorTurnoTipo
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
