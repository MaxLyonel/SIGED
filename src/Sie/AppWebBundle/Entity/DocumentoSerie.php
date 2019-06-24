<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentoSerie
 */
class DocumentoSerie
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var boolean
     */
    private $esanulado;

    /**
     * @var string
     */
    private $observacionAnulado;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\DepartamentoTipo
     */
    private $departamentoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestion;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @return string 
     */
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }

    /**
     * Set esanulado
     *
     * @param boolean $esanulado
     * @return DocumentoSerie
     */
    public function setEsanulado($esanulado)
    {
        $this->esanulado = $esanulado;
    
        return $this;
    }

    /**
     * Get esanulado
     *
     * @return boolean 
     */
    public function getEsanulado()
    {
        return $this->esanulado;
    }

    /**
     * Set observacionAnulado
     *
     * @param string $observacionAnulado
     * @return DocumentoSerie
     */
    public function setObservacionAnulado($observacionAnulado)
    {
        $this->observacionAnulado = $observacionAnulado;
    
        return $this;
    }

    /**
     * Get observacionAnulado
     *
     * @return string 
     */
    public function getObservacionAnulado()
    {
        return $this->observacionAnulado;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return DocumentoSerie
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
     * Set departamentoTipo
     *
     * @param \Sie\AppWebBundle\Entity\DepartamentoTipo $departamentoTipo
     * @return DocumentoSerie
     */
    public function setDepartamentoTipo(\Sie\AppWebBundle\Entity\DepartamentoTipo $departamentoTipo = null)
    {
        $this->departamentoTipo = $departamentoTipo;
    
        return $this;
    }

    /**
     * Get departamentoTipo
     *
     * @return \Sie\AppWebBundle\Entity\DepartamentoTipo 
     */
    public function getDepartamentoTipo()
    {
        return $this->departamentoTipo;
    }

    /**
     * Set gestion
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestion
     * @return DocumentoSerie
     */
    public function setGestion(\Sie\AppWebBundle\Entity\GestionTipo $gestion = null)
    {
        $this->gestion = $gestion;
    
        return $this;
    }

    /**
     * Get gestion
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestion()
    {
        return $this->gestion;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\DocumentoTipo
     */
    private $documentoTipo;


    /**
     * Set documentoTipo
     *
     * @param \Sie\AppWebBundle\Entity\DocumentoTipo $documentoTipo
     * @return DocumentoSerie
     */
    public function setDocumentoTipo(\Sie\AppWebBundle\Entity\DocumentoTipo $documentoTipo = null)
    {
        $this->documentoTipo = $documentoTipo;
    
        return $this;
    }

    /**
     * Get documentoTipo
     *
     * @return \Sie\AppWebBundle\Entity\DocumentoTipo 
     */
    public function getDocumentoTipo()
    {
        return $this->documentoTipo;
    }
}
