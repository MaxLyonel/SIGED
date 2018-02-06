<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4BanoTipo
 */
class InfraestructuraH4BanoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraBano;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var integer
     */
    private $gestionTipoId;

    public function __toString(){
        return $this->infraestructuraBano;
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
     * Set infraestructuraBano
     *
     * @param string $infraestructuraBano
     * @return InfraestructuraH4BanoTipo
     */
    public function setInfraestructuraBano($infraestructuraBano)
    {
        $this->infraestructuraBano = $infraestructuraBano;
    
        return $this;
    }

    /**
     * Get infraestructuraBano
     *
     * @return string 
     */
    public function getInfraestructuraBano()
    {
        return $this->infraestructuraBano;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH4BanoTipo
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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return InfraestructuraH4BanoTipo
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
