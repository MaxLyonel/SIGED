<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2PisoMaterialTipo
 */
class InfraestructuraH2PisoMaterialTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraPisoMaterial;

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
     * Set infraestructuraPisoMaterial
     *
     * @param string $infraestructuraPisoMaterial
     * @return InfraestructuraH2PisoMaterialTipo
     */
    public function setInfraestructuraPisoMaterial($infraestructuraPisoMaterial)
    {
        $this->infraestructuraPisoMaterial = $infraestructuraPisoMaterial;
    
        return $this;
    }

    /**
     * Get infraestructuraPisoMaterial
     *
     * @return string 
     */
    public function getInfraestructuraPisoMaterial()
    {
        return $this->infraestructuraPisoMaterial;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH2PisoMaterialTipo
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
     * @return InfraestructuraH2PisoMaterialTipo
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
