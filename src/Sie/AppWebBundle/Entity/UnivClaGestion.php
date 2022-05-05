<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivClaGestion
 */
class UnivClaGestion
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $gestion;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set gestion
     *
     * @param string $gestion
     * @return UnivClaGestion
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
}
