<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RehabilitacionBth
 */
class RehabilitacionBth
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var string
     */
    private $adjunto;

    /**
     * @var \Sie\AppWebBundle\Entity\Tramite
     */
    private $tramite;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico
     */
    private $institucioneducativaHumanisticoTecnico;


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
     * Set obs
     *
     * @param string $obs
     * @return RehabilitacionBth
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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return RehabilitacionBth
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
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     * @return RehabilitacionBth
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    
        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime 
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set adjunto
     *
     * @param string $adjunto
     * @return RehabilitacionBth
     */
    public function setAdjunto($adjunto)
    {
        $this->adjunto = $adjunto;
    
        return $this;
    }

    /**
     * Get adjunto
     *
     * @return string 
     */
    public function getAdjunto()
    {
        return $this->adjunto;
    }

    /**
     * Set tramite
     *
     * @param \Sie\AppWebBundle\Entity\Tramite $tramite
     * @return RehabilitacionBth
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
     * Set institucioneducativaHumanisticoTecnico
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico $institucioneducativaHumanisticoTecnico
     * @return RehabilitacionBth
     */
    public function setInstitucioneducativaHumanisticoTecnico(\Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico $institucioneducativaHumanisticoTecnico = null)
    {
        $this->institucioneducativaHumanisticoTecnico = $institucioneducativaHumanisticoTecnico;
    
        return $this;
    }

    /**
     * Get institucioneducativaHumanisticoTecnico
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnico 
     */
    public function getInstitucioneducativaHumanisticoTecnico()
    {
        return $this->institucioneducativaHumanisticoTecnico;
    }
    /**
     * @var integer
     */
    private $usuarioRegistroId;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var integer
     */
    private $usuarioModificaId;


    /**
     * Set usuarioRegistroId
     *
     * @param integer $usuarioRegistroId
     * @return RehabilitacionBth
     */
    public function setUsuarioRegistroId($usuarioRegistroId)
    {
        $this->usuarioRegistroId = $usuarioRegistroId;
    
        return $this;
    }

    /**
     * Get usuarioRegistroId
     *
     * @return integer 
     */
    public function getUsuarioRegistroId()
    {
        return $this->usuarioRegistroId;
    }

    /**
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return RehabilitacionBth
     */
    public function setInstitucioneducativaId($institucioneducativaId)
    {
        $this->institucioneducativaId = $institucioneducativaId;
    
        return $this;
    }

    /**
     * Get institucioneducativaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaId()
    {
        return $this->institucioneducativaId;
    }

    /**
     * Set usuarioModificaId
     *
     * @param integer $usuarioModificaId
     * @return RehabilitacionBth
     */
    public function setUsuarioModificaId($usuarioModificaId)
    {
        $this->usuarioModificaId = $usuarioModificaId;
    
        return $this;
    }

    /**
     * Get usuarioModificaId
     *
     * @return integer 
     */
    public function getUsuarioModificaId()
    {
        return $this->usuarioModificaId;
    }
}
