<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4MedioAguaTipo
 */
class InfraestructuraH4MedioAguaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraMedioAgua;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var integer
     */
    private $gestionTipoId;

    public function __toString(){
        return $this->infraestructuraMedioAgua;
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
     * Set infraestructuraMedioAgua
     *
     * @param string $infraestructuraMedioAgua
     * @return InfraestructuraH4MedioAguaTipo
     */
    public function setInfraestructuraMedioAgua($infraestructuraMedioAgua)
    {
        $this->infraestructuraMedioAgua = $infraestructuraMedioAgua;
    
        return $this;
    }

    /**
     * Get infraestructuraMedioAgua
     *
     * @return string 
     */
    public function getInfraestructuraMedioAgua()
    {
        return $this->infraestructuraMedioAgua;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH4MedioAguaTipo
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
     * @return InfraestructuraH4MedioAguaTipo
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
