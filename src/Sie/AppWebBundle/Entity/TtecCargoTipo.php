<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecCargoTipo
 */
class TtecCargoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $cargo;

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
     * Set cargo
     *
     * @param string $cargo
     * @return TtecCargoTipo
     */
    public function setCargo($cargo)
    {
        $this->cargo = $cargo;
    
        return $this;
    }

    /**
     * Get cargo
     *
     * @return string 
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return TtecCargoTipo
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
