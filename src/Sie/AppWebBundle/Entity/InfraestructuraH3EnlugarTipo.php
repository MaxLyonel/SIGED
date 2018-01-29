<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH3EnlugarTipo
 */
class InfraestructuraH3EnlugarTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraEnlugar;

    /**
     * @var string
     */
    private $obs;

    public function __toString(){
        return $this->infraestructuraEnlugar;
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
     * Set infraestructuraEnlugar
     *
     * @param string $infraestructuraEnlugar
     * @return InfraestructuraH3EnlugarTipo
     */
    public function setInfraestructuraEnlugar($infraestructuraEnlugar)
    {
        $this->infraestructuraEnlugar = $infraestructuraEnlugar;
    
        return $this;
    }

    /**
     * Get infraestructuraEnlugar
     *
     * @return string 
     */
    public function getInfraestructuraEnlugar()
    {
        return $this->infraestructuraEnlugar;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH3EnlugarTipo
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
     * @return InfraestructuraH3EnlugarTipo
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
