<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativasucursalOperativo
 */
class InstitucioneducativasucursalOperativo
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
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioAct;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioReg;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadooperativoTipo
     */
    private $estadooperativoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OperativoSieTipo
     */
    private $operativoSieTipo;

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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return InstitucioneducativasucursalOperativo
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
     * @return InstitucioneducativasucursalOperativo
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
     * @return InstitucioneducativasucursalOperativo
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
     * @return InstitucioneducativasucursalOperativo
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
     * Set usuarioAct
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioAct
     * @return InstitucioneducativasucursalOperativo
     */
    public function setUsuarioAct(\Sie\AppWebBundle\Entity\Usuario $usuarioAct = null)
    {
        $this->usuarioAct = $usuarioAct;
    
        return $this;
    }

    /**
     * Get usuarioAct
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioAct()
    {
        return $this->usuarioAct;
    }

    /**
     * Set usuarioReg
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioReg
     * @return InstitucioneducativasucursalOperativo
     */
    public function setUsuarioReg(\Sie\AppWebBundle\Entity\Usuario $usuarioReg = null)
    {
        $this->usuarioReg = $usuarioReg;
    
        return $this;
    }

    /**
     * Get usuarioReg
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioReg()
    {
        return $this->usuarioReg;
    }

    /**
     * Set estadooperativoTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstadooperativoTipo $estadooperativoTipo
     * @return InstitucioneducativasucursalOperativo
     */
    public function setEstadooperativoTipo(\Sie\AppWebBundle\Entity\EstadooperativoTipo $estadooperativoTipo = null)
    {
        $this->estadooperativoTipo = $estadooperativoTipo;
    
        return $this;
    }

    /**
     * Get estadooperativoTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstadooperativoTipo 
     */
    public function getEstadooperativoTipo()
    {
        return $this->estadooperativoTipo;
    }

    /**
     * Set operativoSieTipo
     *
     * @param \Sie\AppWebBundle\Entity\OperativoSieTipo $operativoSieTipo
     * @return InstitucioneducativasucursalOperativo
     */
    public function setOperativoSieTipo(\Sie\AppWebBundle\Entity\OperativoSieTipo $operativoSieTipo = null)
    {
        $this->operativoSieTipo = $operativoSieTipo;
    
        return $this;
    }

    /**
     * Get operativoSieTipo
     *
     * @return \Sie\AppWebBundle\Entity\OperativoSieTipo 
     */
    public function getOperativoSieTipo()
    {
        return $this->operativoSieTipo;
    }

    /**
     * Set institucioneducativaSucursal
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal
     * @return InstitucioneducativasucursalOperativo
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
