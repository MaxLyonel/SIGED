<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH3PeriodicidadTipo
 */
class InfraestructuraH3PeriodicidadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraEliminacionBasura;

    /**
     * @var string
     */
    private $obs;

    public function __toString(){
        return $this->infraestructuraEliminacionBasura;
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
     * Set infraestructuraEliminacionBasura
     *
     * @param string $infraestructuraEliminacionBasura
     * @return InfraestructuraH3PeriodicidadTipo
     */
    public function setInfraestructuraEliminacionBasura($infraestructuraEliminacionBasura)
    {
        $this->infraestructuraEliminacionBasura = $infraestructuraEliminacionBasura;
    
        return $this;
    }

    /**
     * Get infraestructuraEliminacionBasura
     *
     * @return string 
     */
    public function getInfraestructuraEliminacionBasura()
    {
        return $this->infraestructuraEliminacionBasura;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH3PeriodicidadTipo
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
     * @return InfraestructuraH3PeriodicidadTipo
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
