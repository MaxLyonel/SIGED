<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4EliminacionBasuraTipo
 */
class InfraestructuraH4EliminacionBasuraTipo
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

    /**
     * @var integer
     */
    private $gestionTipoId;


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
     * @return InfraestructuraH4EliminacionBasuraTipo
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
     * @return InfraestructuraH4EliminacionBasuraTipo
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
     * @return InfraestructuraH4EliminacionBasuraTipo
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
