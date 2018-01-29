<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5MaterialPisoTipo
 */
class InfraestructuraH5MaterialPisoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraMaterialPiso;

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
     * Set infraestructuraMaterialPiso
     *
     * @param string $infraestructuraMaterialPiso
     * @return InfraestructuraH5MaterialPisoTipo
     */
    public function setInfraestructuraMaterialPiso($infraestructuraMaterialPiso)
    {
        $this->infraestructuraMaterialPiso = $infraestructuraMaterialPiso;
    
        return $this;
    }

    /**
     * Get infraestructuraMaterialPiso
     *
     * @return string 
     */
    public function getInfraestructuraMaterialPiso()
    {
        return $this->infraestructuraMaterialPiso;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH5MaterialPisoTipo
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
     * @return InfraestructuraH5MaterialPisoTipo
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
