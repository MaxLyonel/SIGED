<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatHabAcademico
 */
class UnivDatHabAcademico
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $periodoInicioId;

    /**
     * @var string
     */
    private $gestionInicioId;

    /**
     * @var string
     */
    private $periodoFinId;

    /**
     * @var string
     */
    private $gestionFinId;

    /**
     * @var string
     */
    private $nResolucionCarrera;

    /**
     * @var string
     */
    private $fechaResolucionCarrera;

    /**
     * @var string
     */
    private $serieCertificadoCarrera;

    /**
     * @var string
     */
    private $serieCertificadoVerano;

    /**
     * @var string
     */
    private $serieHistorial;

    /**
     * @var string
     */
    private $fechaHistorial;

    /**
     * @var string
     */
    private $habilitacionId;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var string
     */
    private $updatedAt;


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
     * Set periodoInicioId
     *
     * @param string $periodoInicioId
     * @return UnivDatHabAcademico
     */
    public function setPeriodoInicioId($periodoInicioId)
    {
        $this->periodoInicioId = $periodoInicioId;
    
        return $this;
    }

    /**
     * Get periodoInicioId
     *
     * @return string 
     */
    public function getPeriodoInicioId()
    {
        return $this->periodoInicioId;
    }

    /**
     * Set gestionInicioId
     *
     * @param string $gestionInicioId
     * @return UnivDatHabAcademico
     */
    public function setGestionInicioId($gestionInicioId)
    {
        $this->gestionInicioId = $gestionInicioId;
    
        return $this;
    }

    /**
     * Get gestionInicioId
     *
     * @return string 
     */
    public function getGestionInicioId()
    {
        return $this->gestionInicioId;
    }

    /**
     * Set periodoFinId
     *
     * @param string $periodoFinId
     * @return UnivDatHabAcademico
     */
    public function setPeriodoFinId($periodoFinId)
    {
        $this->periodoFinId = $periodoFinId;
    
        return $this;
    }

    /**
     * Get periodoFinId
     *
     * @return string 
     */
    public function getPeriodoFinId()
    {
        return $this->periodoFinId;
    }

    /**
     * Set gestionFinId
     *
     * @param string $gestionFinId
     * @return UnivDatHabAcademico
     */
    public function setGestionFinId($gestionFinId)
    {
        $this->gestionFinId = $gestionFinId;
    
        return $this;
    }

    /**
     * Get gestionFinId
     *
     * @return string 
     */
    public function getGestionFinId()
    {
        return $this->gestionFinId;
    }

    /**
     * Set nResolucionCarrera
     *
     * @param string $nResolucionCarrera
     * @return UnivDatHabAcademico
     */
    public function setNResolucionCarrera($nResolucionCarrera)
    {
        $this->nResolucionCarrera = $nResolucionCarrera;
    
        return $this;
    }

    /**
     * Get nResolucionCarrera
     *
     * @return string 
     */
    public function getNResolucionCarrera()
    {
        return $this->nResolucionCarrera;
    }

    /**
     * Set fechaResolucionCarrera
     *
     * @param string $fechaResolucionCarrera
     * @return UnivDatHabAcademico
     */
    public function setFechaResolucionCarrera($fechaResolucionCarrera)
    {
        $this->fechaResolucionCarrera = $fechaResolucionCarrera;
    
        return $this;
    }

    /**
     * Get fechaResolucionCarrera
     *
     * @return string 
     */
    public function getFechaResolucionCarrera()
    {
        return $this->fechaResolucionCarrera;
    }

    /**
     * Set serieCertificadoCarrera
     *
     * @param string $serieCertificadoCarrera
     * @return UnivDatHabAcademico
     */
    public function setSerieCertificadoCarrera($serieCertificadoCarrera)
    {
        $this->serieCertificadoCarrera = $serieCertificadoCarrera;
    
        return $this;
    }

    /**
     * Get serieCertificadoCarrera
     *
     * @return string 
     */
    public function getSerieCertificadoCarrera()
    {
        return $this->serieCertificadoCarrera;
    }

    /**
     * Set serieCertificadoVerano
     *
     * @param string $serieCertificadoVerano
     * @return UnivDatHabAcademico
     */
    public function setSerieCertificadoVerano($serieCertificadoVerano)
    {
        $this->serieCertificadoVerano = $serieCertificadoVerano;
    
        return $this;
    }

    /**
     * Get serieCertificadoVerano
     *
     * @return string 
     */
    public function getSerieCertificadoVerano()
    {
        return $this->serieCertificadoVerano;
    }

    /**
     * Set serieHistorial
     *
     * @param string $serieHistorial
     * @return UnivDatHabAcademico
     */
    public function setSerieHistorial($serieHistorial)
    {
        $this->serieHistorial = $serieHistorial;
    
        return $this;
    }

    /**
     * Get serieHistorial
     *
     * @return string 
     */
    public function getSerieHistorial()
    {
        return $this->serieHistorial;
    }

    /**
     * Set fechaHistorial
     *
     * @param string $fechaHistorial
     * @return UnivDatHabAcademico
     */
    public function setFechaHistorial($fechaHistorial)
    {
        $this->fechaHistorial = $fechaHistorial;
    
        return $this;
    }

    /**
     * Get fechaHistorial
     *
     * @return string 
     */
    public function getFechaHistorial()
    {
        return $this->fechaHistorial;
    }

    /**
     * Set habilitacionId
     *
     * @param string $habilitacionId
     * @return UnivDatHabAcademico
     */
    public function setHabilitacionId($habilitacionId)
    {
        $this->habilitacionId = $habilitacionId;
    
        return $this;
    }

    /**
     * Get habilitacionId
     *
     * @return string 
     */
    public function getHabilitacionId()
    {
        return $this->habilitacionId;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return UnivDatHabAcademico
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set updatedAt
     *
     * @param string $updatedAt
     * @return UnivDatHabAcademico
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return string 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
