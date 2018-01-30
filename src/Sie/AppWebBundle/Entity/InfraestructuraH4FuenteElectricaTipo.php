<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4FuenteElectricaTipo
 */
class InfraestructuraH4FuenteElectricaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraFuenteelectrica;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var integer
     */
    private $gestionTipoId;

    public function __toString(){
        return $this->infraestructuraFuenteelectrica;
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
     * Set infraestructuraFuenteelectrica
     *
     * @param string $infraestructuraFuenteelectrica
     * @return InfraestructuraH4FuenteElectricaTipo
     */
    public function setInfraestructuraFuenteelectrica($infraestructuraFuenteelectrica)
    {
        $this->infraestructuraFuenteelectrica = $infraestructuraFuenteelectrica;
    
        return $this;
    }

    /**
     * Get infraestructuraFuenteelectrica
     *
     * @return string 
     */
    public function getInfraestructuraFuenteelectrica()
    {
        return $this->infraestructuraFuenteelectrica;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH4FuenteElectricaTipo
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
     * @return InfraestructuraH4FuenteElectricaTipo
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
