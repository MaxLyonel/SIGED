<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecOperativoTipo
 */
class TtecOperativoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $operativo;

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
     * Set operativo
     *
     * @param string $operativo
     * @return TtecOperativoTipo
     */
    public function setOperativo($operativo)
    {
        $this->operativo = $operativo;
    
        return $this;
    }

    /**
     * Get operativo
     *
     * @return string 
     */
    public function getOperativo()
    {
        return $this->operativo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return TtecOperativoTipo
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
