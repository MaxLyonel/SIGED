<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5AmbienteTipo
 */
class InfraestructuraH5AmbienteTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraAmbiente;

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
     * Set infraestructuraAmbiente
     *
     * @param string $infraestructuraAmbiente
     * @return InfraestructuraH5AmbienteTipo
     */
    public function setInfraestructuraAmbiente($infraestructuraAmbiente)
    {
        $this->infraestructuraAmbiente = $infraestructuraAmbiente;
    
        return $this;
    }

    /**
     * Get infraestructuraAmbiente
     *
     * @return string 
     */
    public function getInfraestructuraAmbiente()
    {
        return $this->infraestructuraAmbiente;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH5AmbienteTipo
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
     * @return InfraestructuraH5AmbienteTipo
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
