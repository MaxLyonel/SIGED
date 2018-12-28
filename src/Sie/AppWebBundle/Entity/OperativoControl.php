<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OperativoControl
 */
class OperativoControl
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
    private $obs;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\OperativoTipo
     */
    private $operativoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\DistritoTipo
     */
    private $distritoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal
     */
    private $institucioneducativaSucursal;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioModificacion;


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
     * @return OperativoControl
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
     * @return OperativoControl
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
     * Set obs
     *
     * @param string $obs
     * @return OperativoControl
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
     * @return OperativoControl
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
     * @return OperativoControl
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
     * Set operativoTipo
     *
     * @param \Sie\AppWebBundle\Entity\OperativoTipo $operativoTipo
     * @return OperativoControl
     */
    public function setOperativoTipo(\Sie\AppWebBundle\Entity\OperativoTipo $operativoTipo = null)
    {
        $this->operativoTipo = $operativoTipo;
    
        return $this;
    }

    /**
     * Get operativoTipo
     *
     * @return \Sie\AppWebBundle\Entity\OperativoTipo 
     */
    public function getOperativoTipo()
    {
        return $this->operativoTipo;
    }

    /**
     * Set distritoTipo
     *
     * @param \Sie\AppWebBundle\Entity\DistritoTipo $distritoTipo
     * @return OperativoControl
     */
    public function setDistritoTipo(\Sie\AppWebBundle\Entity\DistritoTipo $distritoTipo = null)
    {
        $this->distritoTipo = $distritoTipo;
    
        return $this;
    }

    /**
     * Get distritoTipo
     *
     * @return \Sie\AppWebBundle\Entity\DistritoTipo 
     */
    public function getDistritoTipo()
    {
        return $this->distritoTipo;
    }

    /**
     * Set institucioneducativaSucursal
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal
     * @return OperativoControl
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

    /**
     * Set usuarioRegistro
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioRegistro
     * @return OperativoControl
     */
    public function setUsuarioRegistro(\Sie\AppWebBundle\Entity\Usuario $usuarioRegistro = null)
    {
        $this->usuarioRegistro = $usuarioRegistro;
    
        return $this;
    }

    /**
     * Get usuarioRegistro
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioRegistro()
    {
        return $this->usuarioRegistro;
    }

    /**
     * Set usuarioModificacion
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioModificacion
     * @return OperativoControl
     */
    public function setUsuarioModificacion(\Sie\AppWebBundle\Entity\Usuario $usuarioModificacion = null)
    {
        $this->usuarioModificacion = $usuarioModificacion;
    
        return $this;
    }

    /**
     * Get usuarioModificacion
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioModificacion()
    {
        return $this->usuarioModificacion;
    }
}
