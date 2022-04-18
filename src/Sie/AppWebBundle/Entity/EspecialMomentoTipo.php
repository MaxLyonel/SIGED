<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialMomentoTipo
 */
class EspecialMomentoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $momento;

    /**
     * @var boolean
     */
    private $vigente;


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
     * Set momento
     *
     * @param string $momento
     * @return EspecialMomentoTipo
     */
    public function setMomento($momento)
    {
        $this->momento = $momento;
    
        return $this;
    }

    /**
     * Get momento
     *
     * @return string 
     */
    public function getMomento()
    {
        return $this->momento;
    }

    /**
     * Set vigente
     *
     * @param boolean $vigente
     * @return EspecialMomentoTipo
     */
    public function setVigente($vigente)
    {
        $this->vigente = $vigente;
    
        return $this;
    }

    /**
     * Get vigente
     *
     * @return boolean 
     */
    public function getVigente()
    {
        return $this->vigente;
    }
}
