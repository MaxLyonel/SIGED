<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PermanenteSubAreaTipo
 */
class PermanenteSubAreaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $subArea;

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
     * Set subArea
     *
     * @param string $subArea
     * @return PermanenteSubAreaTipo
     */
    public function setSubArea($subArea)
    {
        $this->subArea = $subArea;
    
        return $this;
    }

    /**
     * Get subArea
     *
     * @return string 
     */
    public function getSubArea()
    {
        return $this->subArea;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return PermanenteSubAreaTipo
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
