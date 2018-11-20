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
    /**
     * @var string
     */
    private $tokenPrivado;

    /**
     * @var string
     */
    private $tokenPublico;

    /**
     * @var string
     */
    private $url;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\DocumentoFirma
     */
    private $documentoFirma;


    /**
     * Set tokenPrivado
     *
     * @param string $tokenPrivado
     * @return Documento
     */
    public function setTokenPrivado($tokenPrivado)
    {
        $this->tokenPrivado = $tokenPrivado;
    
        return $this;
    }

    /**
     * Get tokenPrivado
     *
     * @return string 
     */
    public function getTokenPrivado()
    {
        return $this->tokenPrivado;
    }

    /**
     * Set tokenPublico
     *
     * @param string $tokenPublico
     * @return Documento
     */
    public function setTokenPublico($tokenPublico)
    {
        $this->tokenPublico = $tokenPublico;
    
        return $this;
    }

    /**
     * Get tokenPublico
     *
     * @return string 
     */
    public function getTokenPublico()
    {
        return $this->tokenPublico;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return Documento
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return Documento
     */
    public function setUsuario(\Sie\AppWebBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set documentoFirma
     *
     * @param \Sie\AppWebBundle\Entity\DocumentoFirma $documentoFirma
     * @return Documento
     */
    public function setDocumentoFirma(\Sie\AppWebBundle\Entity\DocumentoFirma $documentoFirma = null)
    {
        $this->documentoFirma = $documentoFirma;
    
        return $this;
    }

    /**
     * Get documentoFirma
     *
     * @return \Sie\AppWebBundle\Entity\DocumentoFirma 
     */
    public function getDocumentoFirma()
    {
        return $this->documentoFirma;
    }
    /**
     * @var string
     */
    private $tokenImpreso;


    /**
     * Set tokenImpreso
     *
     * @param string $tokenImpreso
     * @return Documento
     */
    public function setTokenImpreso($tokenImpreso)
    {
        $this->tokenImpreso = $tokenImpreso;
    
        return $this;
    }

    /**
     * Get tokenImpreso
     *
     * @return string 
     */
    public function getTokenImpreso()
    {
        return $this->tokenImpreso;
    }
}
