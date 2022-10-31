<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaSucursalModalidadAtencion
 */
class InstitucioneducativaSucursalModalidadAtencion
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
     * @var string
     */
    private $observacion;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal
     */
    private $institucioneducativaSucursal;

    /**
     * @var \Sie\AppWebBundle\Entity\ModalidadAtencionTipo
     */
    private $modalidadAtencionTipo;


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
     * @return InstitucioneducativaSucursalModalidadAtencion
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
     * @return InstitucioneducativaSucursalModalidadAtencion
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
     * Set observacion
     *
     * @param string $observacion
     * @return InstitucioneducativaSucursalModalidadAtencion
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
     * Set institucioneducativaSucursal
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal
     * @return InstitucioneducativaSucursalModalidadAtencion
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
     * Set modalidadAtencionTipo
     *
     * @param \Sie\AppWebBundle\Entity\ModalidadAtencionTipo $modalidadAtencionTipo
     * @return InstitucioneducativaSucursalModalidadAtencion
     */
    public function setModalidadAtencionTipo(\Sie\AppWebBundle\Entity\ModalidadAtencionTipo $modalidadAtencionTipo = null)
    {
        $this->modalidadAtencionTipo = $modalidadAtencionTipo;
    
        return $this;
    }

    /**
     * Get modalidadAtencionTipo
     *
     * @return \Sie\AppWebBundle\Entity\ModalidadAtencionTipo 
     */
    public function getModalidadAtencionTipo()
    {
        return $this->modalidadAtencionTipo;
    }
}
