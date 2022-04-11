<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecInstitucioneducativaRatificacion
 */
class TtecInstitucioneducativaRatificacion
{
    /**
     * @var integer
     */
    private $id;

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
    private $observacion;

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
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistorico
     */
    private $ttecInstitucioneducativaHistorico;


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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return TtecInstitucioneducativaRatificacion
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
     * @return TtecInstitucioneducativaRatificacion
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
     * Set observacion
     *
     * @param string $observacion
     * @return TtecInstitucioneducativaRatificacion
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
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return TtecInstitucioneducativaRatificacion
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
     * @return TtecInstitucioneducativaRatificacion
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
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return TtecInstitucioneducativaRatificacion
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set ttecInstitucioneducativaHistorico
     *
     * @param \Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistorico $ttecInstitucioneducativaHistorico
     * @return TtecInstitucioneducativaRatificacion
     */
    public function setTtecInstitucioneducativaHistorico(\Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistorico $ttecInstitucioneducativaHistorico = null)
    {
        $this->ttecInstitucioneducativaHistorico = $ttecInstitucioneducativaHistorico;
    
        return $this;
    }

    /**
     * Get ttecInstitucioneducativaHistorico
     *
     * @return \Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistorico 
     */
    public function getTtecInstitucioneducativaHistorico()
    {
        return $this->ttecInstitucioneducativaHistorico;
    }
}
