<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaSucursalRiesgoMes
 */
class InstitucioneducativaSucursalRiesgoMes
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $mes;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $otros;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \Sie\AppWebBundle\Entity\RiesgoUnidadeducativaTipo
     */
    private $riesgoUnidadeducativaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal
     */
    private $institucioneducativaSucursal;


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
     * Set mes
     *
     * @param \DateTime $mes
     * @return InstitucioneducativaSucursalRiesgoMes
     */
    public function setMes($mes)
    {
        $this->mes = $mes;
    
        return $this;
    }

    /**
     * Get mes
     *
     * @return \DateTime 
     */
    public function getMes()
    {
        return $this->mes;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return InstitucioneducativaSucursalRiesgoMes
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
     * @return InstitucioneducativaSucursalRiesgoMes
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaSucursalRiesgoMes
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
     * @return InstitucioneducativaSucursalRiesgoMes
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
     * Set otros
     *
     * @param string $otros
     * @return InstitucioneducativaSucursalRiesgoMes
     */
    public function setOtros($otros)
    {
        $this->otros = $otros;
    
        return $this;
    }

    /**
     * Get otros
     *
     * @return string 
     */
    public function getOtros()
    {
        return $this->otros;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return InstitucioneducativaSucursalRiesgoMes
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
     * Set riesgoUnidadeducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\RiesgoUnidadeducativaTipo $riesgoUnidadeducativaTipo
     * @return InstitucioneducativaSucursalRiesgoMes
     */
    public function setRiesgoUnidadeducativaTipo(\Sie\AppWebBundle\Entity\RiesgoUnidadeducativaTipo $riesgoUnidadeducativaTipo = null)
    {
        $this->riesgoUnidadeducativaTipo = $riesgoUnidadeducativaTipo;
    
        return $this;
    }

    /**
     * Get riesgoUnidadeducativaTipo
     *
     * @return \Sie\AppWebBundle\Entity\RiesgoUnidadeducativaTipo 
     */
    public function getRiesgoUnidadeducativaTipo()
    {
        return $this->riesgoUnidadeducativaTipo;
    }

    /**
     * Set institucioneducativaSucursal
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal
     * @return InstitucioneducativaSucursalRiesgoMes
     */
    public function setInstitucioneducativaSucursal(\Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal = null)
    {
        $this->institucioneducativaSucursal = $institucioneducativaSucursal;
    
        return $this;
    }

    /**
     * Get institucioneducativaSucursal
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal 
     */
    public function getInstitucioneducativaSucursal()
    {
        return $this->institucioneducativaSucursal;
    }
}
