<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GestionTipo
 */
class GestionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $gestion;

    /**
     * @var string
     */
    private $obs;

    
    public function __toString() {
        return $this->gestion;
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
     * Set gestion
     *
     * @param string $gestion
     * @return GestionTipo
     */
    public function setGestion($gestion)
    {
        $this->gestion = $gestion;

        return $this;
    }

    /**
     * Get gestion
     *
     * @return string 
     */
    public function getGestion()
    {
        return $this->gestion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return GestionTipo
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
