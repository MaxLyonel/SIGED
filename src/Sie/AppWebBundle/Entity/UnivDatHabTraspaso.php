<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatHabTraspaso
 */
class UnivDatHabTraspaso
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $planOrigen;

    /**
     * @var string
     */
    private $planDestino;

    /**
     * @var string
     */
    private $nResolucionPlan;

    /**
     * @var string
     */
    private $nInformePlan;

    /**
     * @var string
     */
    private $universidadOrigen;

    /**
     * @var string
     */
    private $serieCertificados;

    /**
     * @var string
     */
    private $nConvalidacion;

    /**
     * @var string
     */
    private $fechaConvalidacion;

    /**
     * @var string
     */
    private $habilitacionId;

    /**
     * @var string
     */
    private $nInformeTecnico;

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
     * Set planOrigen
     *
     * @param string $planOrigen
     * @return UnivDatHabTraspaso
     */
    public function setPlanOrigen($planOrigen)
    {
        $this->planOrigen = $planOrigen;
    
        return $this;
    }

    /**
     * Get planOrigen
     *
     * @return string 
     */
    public function getPlanOrigen()
    {
        return $this->planOrigen;
    }

    /**
     * Set planDestino
     *
     * @param string $planDestino
     * @return UnivDatHabTraspaso
     */
    public function setPlanDestino($planDestino)
    {
        $this->planDestino = $planDestino;
    
        return $this;
    }

    /**
     * Get planDestino
     *
     * @return string 
     */
    public function getPlanDestino()
    {
        return $this->planDestino;
    }

    /**
     * Set nResolucionPlan
     *
     * @param string $nResolucionPlan
     * @return UnivDatHabTraspaso
     */
    public function setNResolucionPlan($nResolucionPlan)
    {
        $this->nResolucionPlan = $nResolucionPlan;
    
        return $this;
    }

    /**
     * Get nResolucionPlan
     *
     * @return string 
     */
    public function getNResolucionPlan()
    {
        return $this->nResolucionPlan;
    }

    /**
     * Set nInformePlan
     *
     * @param string $nInformePlan
     * @return UnivDatHabTraspaso
     */
    public function setNInformePlan($nInformePlan)
    {
        $this->nInformePlan = $nInformePlan;
    
        return $this;
    }

    /**
     * Get nInformePlan
     *
     * @return string 
     */
    public function getNInformePlan()
    {
        return $this->nInformePlan;
    }

    /**
     * Set universidadOrigen
     *
     * @param string $universidadOrigen
     * @return UnivDatHabTraspaso
     */
    public function setUniversidadOrigen($universidadOrigen)
    {
        $this->universidadOrigen = $universidadOrigen;
    
        return $this;
    }

    /**
     * Get universidadOrigen
     *
     * @return string 
     */
    public function getUniversidadOrigen()
    {
        return $this->universidadOrigen;
    }

    /**
     * Set serieCertificados
     *
     * @param string $serieCertificados
     * @return UnivDatHabTraspaso
     */
    public function setSerieCertificados($serieCertificados)
    {
        $this->serieCertificados = $serieCertificados;
    
        return $this;
    }

    /**
     * Get serieCertificados
     *
     * @return string 
     */
    public function getSerieCertificados()
    {
        return $this->serieCertificados;
    }

    /**
     * Set nConvalidacion
     *
     * @param string $nConvalidacion
     * @return UnivDatHabTraspaso
     */
    public function setNConvalidacion($nConvalidacion)
    {
        $this->nConvalidacion = $nConvalidacion;
    
        return $this;
    }

    /**
     * Get nConvalidacion
     *
     * @return string 
     */
    public function getNConvalidacion()
    {
        return $this->nConvalidacion;
    }

    /**
     * Set fechaConvalidacion
     *
     * @param string $fechaConvalidacion
     * @return UnivDatHabTraspaso
     */
    public function setFechaConvalidacion($fechaConvalidacion)
    {
        $this->fechaConvalidacion = $fechaConvalidacion;
    
        return $this;
    }

    /**
     * Get fechaConvalidacion
     *
     * @return string 
     */
    public function getFechaConvalidacion()
    {
        return $this->fechaConvalidacion;
    }

    /**
     * Set habilitacionId
     *
     * @param string $habilitacionId
     * @return UnivDatHabTraspaso
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
     * Set nInformeTecnico
     *
     * @param string $nInformeTecnico
     * @return UnivDatHabTraspaso
     */
    public function setNInformeTecnico($nInformeTecnico)
    {
        $this->nInformeTecnico = $nInformeTecnico;
    
        return $this;
    }

    /**
     * Get nInformeTecnico
     *
     * @return string 
     */
    public function getNInformeTecnico()
    {
        return $this->nInformeTecnico;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return UnivDatHabTraspaso
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
     * @return UnivDatHabTraspaso
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
