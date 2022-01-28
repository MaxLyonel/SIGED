<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstruccionTipo
 */
class InstruccionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $instruccion;

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
     * Set instruccion
     *
     * @param string $instruccion
     * @return InstruccionTipo
     */
    public function setInstruccion($instruccion)
    {
        $this->instruccion = $instruccion;
    
        return $this;
    }

    /**
     * Get instruccion
     *
     * @return string 
     */
    public function getInstruccion()
    {
        return $this->instruccion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InstruccionTipo
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
    
    public function __toString() {
        return $this->instruccion;
    }
}
