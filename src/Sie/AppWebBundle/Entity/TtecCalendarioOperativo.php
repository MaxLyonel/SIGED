<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecCalendarioOperativo
 */
class TtecCalendarioOperativo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecPeriodoTipo
     */
    private $ttecPeriodoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecOperativoTipo
     */
    private $ttecOperativoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;


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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecCalendarioOperativo
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
     * @return TtecCalendarioOperativo
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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return TtecCalendarioOperativo
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
     * @return TtecCalendarioOperativo
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
     * Set ttecPeriodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecPeriodoTipo $ttecPeriodoTipo
     * @return TtecCalendarioOperativo
     */
    public function setTtecPeriodoTipo(\Sie\AppWebBundle\Entity\TtecPeriodoTipo $ttecPeriodoTipo = null)
    {
        $this->ttecPeriodoTipo = $ttecPeriodoTipo;
    
        return $this;
    }

    /**
     * Get ttecPeriodoTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecPeriodoTipo 
     */
    public function getTtecPeriodoTipo()
    {
        return $this->ttecPeriodoTipo;
    }

    /**
     * Set ttecOperativoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecOperativoTipo $ttecOperativoTipo
     * @return TtecCalendarioOperativo
     */
    public function setTtecOperativoTipo(\Sie\AppWebBundle\Entity\TtecOperativoTipo $ttecOperativoTipo = null)
    {
        $this->ttecOperativoTipo = $ttecOperativoTipo;
    
        return $this;
    }

    /**
     * Get ttecOperativoTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecOperativoTipo 
     */
    public function getTtecOperativoTipo()
    {
        return $this->ttecOperativoTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return TtecCalendarioOperativo
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return TtecCalendarioOperativo
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }
}
