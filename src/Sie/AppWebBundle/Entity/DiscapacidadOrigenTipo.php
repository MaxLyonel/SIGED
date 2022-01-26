<?php

#namespace Sie\EspecialBundle\Entity;
namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DiscapacidadOrigenTipo
 */
class DiscapacidadOrigenTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $origen;

    /**
     * @var string
     */
    private $obs;


    /**
     * Set id
     *
     * @param integer $id
     * @return DiscapacidadOrigenTipo
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
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
     * Set origen
     *
     * @param string $origen
     * @return DiscapacidadOrigenTipo
     */
    public function setOrigen($origen)
    {
        $this->origen = $origen;
    
        return $this;
    }

    /**
     * Get origen
     *
     * @return string 
     */
    public function getOrigen()
    {
        return $this->origen;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return DiscapacidadOrigenTipo
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

    public function __toString() 
    {
        return (string) $this->origen; 
    }
}
