<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2DocumentoTipo
 */
class InfraestructuraH2DocumentoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraDocumento;

    /**
     * @var string
     */
    private $obs;

    public function __toString(){
        return $this->infraestructuraDocumento;
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
     * Set infraestructuraDocumento
     *
     * @param string $infraestructuraDocumento
     * @return InfraestructuraH2DocumentoTipo
     */
    public function setInfraestructuraDocumento($infraestructuraDocumento)
    {
        $this->infraestructuraDocumento = $infraestructuraDocumento;
    
        return $this;
    }

    /**
     * Get infraestructuraDocumento
     *
     * @return string 
     */
    public function getInfraestructuraDocumento()
    {
        return $this->infraestructuraDocumento;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH2DocumentoTipo
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
     * @return InfraestructuraH2DocumentoTipo
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
