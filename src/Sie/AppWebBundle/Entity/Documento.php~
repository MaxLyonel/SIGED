<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Documento
 */
class Documento
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $documento;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var integer
     */
    private $usuarioId;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaImpresion;

    /**
     * @var \Sie\AppWebBundle\Entity\DocumentoTipo
     */
    private $documentoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\DocumentoSerie
     */
    private $documentoSerie;

    /**
     * @var \Sie\AppWebBundle\Entity\Tramite
     */
    private $tramite;

    /**
     * @var \Sie\AppWebBundle\Entity\DocumentoEstado
     */
    private $documentoEstado;


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
     * Set documento
     *
     * @param string $documento
     * @return Documento
     */
    public function setDocumento($documento)
    {
        $this->documento = $documento;
    
        return $this;
    }

    /**
     * Get documento
     *
     * @return string 
     */
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return Documento
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
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return Documento
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return Documento
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set fechaImpresion
     *
     * @param \DateTime $fechaImpresion
     * @return Documento
     */
    public function setFechaImpresion($fechaImpresion)
    {
        $this->fechaImpresion = $fechaImpresion;
    
        return $this;
    }

    /**
     * Get fechaImpresion
     *
     * @return \DateTime 
     */
    public function getFechaImpresion()
    {
        return $this->fechaImpresion;
    }

    /**
     * Set documentoTipo
     *
     * @param \Sie\AppWebBundle\Entity\DocumentoTipo $documentoTipo
     * @return Documento
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

    /**
     * Set documentoSerie
     *
     * @param \Sie\AppWebBundle\Entity\DocumentoSerie $documentoSerie
     * @return Documento
     */
    public function setDocumentoSerie(\Sie\AppWebBundle\Entity\DocumentoSerie $documentoSerie = null)
    {
        $this->documentoSerie = $documentoSerie;
    
        return $this;
    }

    /**
     * Get documentoSerie
     *
     * @return \Sie\AppWebBundle\Entity\DocumentoSerie 
     */
    public function getDocumentoSerie()
    {
        return $this->documentoSerie;
    }

    /**
     * Set tramite
     *
     * @param \Sie\AppWebBundle\Entity\Tramite $tramite
     * @return Documento
     */
    public function setTramite(\Sie\AppWebBundle\Entity\Tramite $tramite = null)
    {
        $this->tramite = $tramite;
    
        return $this;
    }

    /**
     * Get tramite
     *
     * @return \Sie\AppWebBundle\Entity\Tramite 
     */
    public function getTramite()
    {
        return $this->tramite;
    }

    /**
     * Set documentoEstado
     *
     * @param \Sie\AppWebBundle\Entity\DocumentoEstado $documentoEstado
     * @return Documento
     */
    public function setDocumentoEstado(\Sie\AppWebBundle\Entity\DocumentoEstado $documentoEstado = null)
    {
        $this->documentoEstado = $documentoEstado;
    
        return $this;
    }

    /**
     * Get documentoEstado
     *
     * @return \Sie\AppWebBundle\Entity\DocumentoEstado 
     */
    public function getDocumentoEstado()
    {
        return $this->documentoEstado;
    }
}
