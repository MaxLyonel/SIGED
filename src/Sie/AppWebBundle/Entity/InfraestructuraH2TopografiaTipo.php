<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2TopografiaTipo
 */
class InfraestructuraH2TopografiaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraTopografia;

    /**
     * @var string
     */
    private $obs;

    public function __toString(){
        return $this->infraestructuraTopografia;
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
     * Set infraestructuraTopografia
     *
     * @param string $infraestructuraTopografia
     * @return InfraestructuraH2TopografiaTipo
     */
    public function setInfraestructuraTopografia($infraestructuraTopografia)
    {
        $this->infraestructuraTopografia = $infraestructuraTopografia;
    
        return $this;
    }

    /**
     * Get infraestructuraTopografia
     *
     * @return string 
     */
    public function getInfraestructuraTopografia()
    {
        return $this->infraestructuraTopografia;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH2TopografiaTipo
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
    /**
     * @var integer
     */
    private $gestionTipoId;


    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return InfraestructuraH2TopografiaTipo
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }
}
