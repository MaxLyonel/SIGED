<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BioPorqueNoRecepcionTipo
 */
class BioPorqueNoRecepcionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $porqueNoRecepcion;

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
     * Set porqueNoRecepcion
     *
     * @param string $porqueNoRecepcion
     * @return BioPorqueNoRecepcionTipo
     */
    public function setPorqueNoRecepcion($porqueNoRecepcion)
    {
        $this->porqueNoRecepcion = $porqueNoRecepcion;
    
        return $this;
    }

    /**
     * Get porqueNoRecepcion
     *
     * @return string 
     */
    public function getPorqueNoRecepcion()
    {
        return $this->porqueNoRecepcion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BioPorqueNoRecepcionTipo
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
