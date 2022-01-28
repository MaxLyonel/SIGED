<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraGenAvanceTipo
 */
class InfraestructuraGenAvanceTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraAvance;

    /**
     * @var string
     */
    private $obs;

    public function __toString(){
        return $this->infraestructuraAvance;
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
     * Set infraestructuraAvance
     *
     * @param string $infraestructuraAvance
     * @return InfraestructuraGenAvanceTipo
     */
    public function setInfraestructuraAvance($infraestructuraAvance)
    {
        $this->infraestructuraAvance = $infraestructuraAvance;
    
        return $this;
    }

    /**
     * Get infraestructuraAvance
     *
     * @return string 
     */
    public function getInfraestructuraAvance()
    {
        return $this->infraestructuraAvance;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraGenAvanceTipo
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
     * @return InfraestructuraGenAvanceTipo
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
