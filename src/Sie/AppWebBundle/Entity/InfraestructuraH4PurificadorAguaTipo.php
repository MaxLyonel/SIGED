<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4PurificadorAguaTipo
 */
class InfraestructuraH4PurificadorAguaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraPurificadorAgua;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var integer
     */
    private $gestionTipoId;

    public function __toString(){
        return $this->infraestructuraPurificadorAgua;
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
     * Set infraestructuraPurificadorAgua
     *
     * @param string $infraestructuraPurificadorAgua
     * @return InfraestructuraH4PurificadorAguaTipo
     */
    public function setInfraestructuraPurificadorAgua($infraestructuraPurificadorAgua)
    {
        $this->infraestructuraPurificadorAgua = $infraestructuraPurificadorAgua;
    
        return $this;
    }

    /**
     * Get infraestructuraPurificadorAgua
     *
     * @return string 
     */
    public function getInfraestructuraPurificadorAgua()
    {
        return $this->infraestructuraPurificadorAgua;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH4PurificadorAguaTipo
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
     * @return InfraestructuraH4PurificadorAguaTipo
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
