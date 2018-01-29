<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4AccesoAguaTipo
 */
class InfraestructuraH4AccesoAguaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraAccesoAgua;

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
     * Set infraestructuraAccesoAgua
     *
     * @param string $infraestructuraAccesoAgua
     * @return InfraestructuraH4AccesoAguaTipo
     */
    public function setInfraestructuraAccesoAgua($infraestructuraAccesoAgua)
    {
        $this->infraestructuraAccesoAgua = $infraestructuraAccesoAgua;
    
        return $this;
    }

    /**
     * Get infraestructuraAccesoAgua
     *
     * @return string 
     */
    public function getInfraestructuraAccesoAgua()
    {
        return $this->infraestructuraAccesoAgua;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH4AccesoAguaTipo
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
     * @return InfraestructuraH4AccesoAguaTipo
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
