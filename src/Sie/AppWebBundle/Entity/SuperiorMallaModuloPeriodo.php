<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperiorMallaModuloPeriodo
 */
class SuperiorMallaModuloPeriodo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorModuloPeriodo
     */
    private $superiorModuloPeriodo;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorPeriodoTipo
     */
    private $superiorPeriodoTipo;


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
     * Set observacion
     *
     * @param string $observacion
     * @return SuperiorMallaModuloPeriodo
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return SuperiorMallaModuloPeriodo
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
     * @return SuperiorMallaModuloPeriodo
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
     * Set superiorModuloPeriodo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorModuloPeriodo $superiorModuloPeriodo
     * @return SuperiorMallaModuloPeriodo
     */
    public function setSuperiorModuloPeriodo(\Sie\AppWebBundle\Entity\SuperiorModuloPeriodo $superiorModuloPeriodo = null)
    {
        $this->superiorModuloPeriodo = $superiorModuloPeriodo;
    
        return $this;
    }

    /**
     * Get superiorModuloPeriodo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorModuloPeriodo 
     */
    public function getSuperiorModuloPeriodo()
    {
        return $this->superiorModuloPeriodo;
    }

    /**
     * Set superiorPeriodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorPeriodoTipo $superiorPeriodoTipo
     * @return SuperiorMallaModuloPeriodo
     */
    public function setSuperiorPeriodoTipo(\Sie\AppWebBundle\Entity\SuperiorPeriodoTipo $superiorPeriodoTipo = null)
    {
        $this->superiorPeriodoTipo = $superiorPeriodoTipo;
    
        return $this;
    }

    /**
     * Get superiorPeriodoTipo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorPeriodoTipo 
     */
    public function getSuperiorPeriodoTipo()
    {
        return $this->superiorPeriodoTipo;
    }
}
