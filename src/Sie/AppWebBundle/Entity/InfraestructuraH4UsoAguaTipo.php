<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4UsoAguaTipo
 */
class InfraestructuraH4UsoAguaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraUsoAgua;

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
     * Set infraestructuraUsoAgua
     *
     * @param string $infraestructuraUsoAgua
     * @return InfraestructuraH4UsoAguaTipo
     */
    public function setInfraestructuraUsoAgua($infraestructuraUsoAgua)
    {
        $this->infraestructuraUsoAgua = $infraestructuraUsoAgua;
    
        return $this;
    }

    /**
     * Get infraestructuraUsoAgua
     *
     * @return string 
     */
    public function getInfraestructuraUsoAgua()
    {
        return $this->infraestructuraUsoAgua;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH4UsoAguaTipo
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
     * @return InfraestructuraH4UsoAguaTipo
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
