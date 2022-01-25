<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentoFirmaAutorizada
 */
class DocumentoFirmaAutorizada
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $maximo;

    /**
     * @var integer
     */
    private $usado;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaCierre;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;


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
     * Set maximo
     *
     * @param integer $maximo
     * @return DocumentoFirmaAutorizada
     */
    public function setMaximo($maximo)
    {
        $this->maximo = $maximo;
    
        return $this;
    }

    /**
     * Get maximo
     *
     * @return integer 
     */
    public function getMaximo()
    {
        return $this->maximo;
    }

    /**
     * Set usado
     *
     * @param integer $usado
     * @return DocumentoFirmaAutorizada
     */
    public function setUsado($usado)
    {
        $this->usado = $usado;
    
        return $this;
    }

    /**
     * Get usado
     *
     * @return integer 
     */
    public function getUsado()
    {
        return $this->usado;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return DocumentoFirmaAutorizada
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    
        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime 
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaCierre
     *
     * @param \DateTime $fechaCierre
     * @return DocumentoFirmaAutorizada
     */
    public function setFechaCierre($fechaCierre)
    {
        $this->fechaCierre = $fechaCierre;
    
        return $this;
    }

    /**
     * Get fechaCierre
     *
     * @return \DateTime 
     */
    public function getFechaCierre()
    {
        return $this->fechaCierre;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return DocumentoFirmaAutorizada
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return DocumentoFirmaAutorizada
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return DocumentoFirmaAutorizada
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
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return DocumentoFirmaAutorizada
     */
    public function setPersona(\Sie\AppWebBundle\Entity\Persona $persona = null)
    {
        $this->persona = $persona;
    
        return $this;
    }

    /**
     * Get persona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getPersona()
    {
        return $this->persona;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return DocumentoFirmaAutorizada
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
     * @var \Sie\AppWebBundle\Entity\DocumentoTipo
     */
    private $documentoTipo;


    /**
     * Set documentoTipo
     *
     * @param \Sie\AppWebBundle\Entity\DocumentoTipo $documentoTipo
     * @return DocumentoFirmaAutorizada
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
