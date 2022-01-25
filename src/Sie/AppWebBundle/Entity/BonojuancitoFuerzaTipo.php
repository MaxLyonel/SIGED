<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonojuancitoFuerzaTipo
 */
class BonojuancitoFuerzaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fuerza;

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
     * Set fuerza
     *
     * @param string $fuerza
     * @return BonojuancitoFuerzaTipo
     */
    public function setFuerza($fuerza)
    {
        $this->fuerza = $fuerza;
    
        return $this;
    }

    /**
     * Get fuerza
     *
     * @return string 
     */
    public function getFuerza()
    {
        return $this->fuerza;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BonojuancitoFuerzaTipo
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
