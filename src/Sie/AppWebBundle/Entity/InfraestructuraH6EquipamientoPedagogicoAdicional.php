<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH6EquipamientoPedagogicoAdicional
 */
class InfraestructuraH6EquipamientoPedagogicoAdicional
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n5Cantidad;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6Equipamiento
     */
    private $infraestructuraH6Equipamiento;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n5EquipamientoAdicionaEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6MobiliarioAdicionalTipo
     */
    private $n5MobiliarioAdicionalTipo;


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
     * Set n5Cantidad
     *
     * @param integer $n5Cantidad
     * @return InfraestructuraH6EquipamientoPedagogicoAdicional
     */
    public function setN5Cantidad($n5Cantidad)
    {
        $this->n5Cantidad = $n5Cantidad;
    
        return $this;
    }

    /**
     * Get n5Cantidad
     *
     * @return integer 
     */
    public function getN5Cantidad()
    {
        return $this->n5Cantidad;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH6EquipamientoPedagogicoAdicional
     */
    public function setFecharegistro($fecharegistro)
    {
        $this->fecharegistro = $fecharegistro;
    
        return $this;
    }

    /**
     * Get fecharegistro
     *
     * @return \DateTime 
     */
    public function getFecharegistro()
    {
        return $this->fecharegistro;
    }

    /**
     * Set infraestructuraH6Equipamiento
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6Equipamiento $infraestructuraH6Equipamiento
     * @return InfraestructuraH6EquipamientoPedagogicoAdicional
     */
    public function setInfraestructuraH6Equipamiento(\Sie\AppWebBundle\Entity\InfraestructuraH6Equipamiento $infraestructuraH6Equipamiento = null)
    {
        $this->infraestructuraH6Equipamiento = $infraestructuraH6Equipamiento;
    
        return $this;
    }

    /**
     * Get infraestructuraH6Equipamiento
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6Equipamiento 
     */
    public function getInfraestructuraH6Equipamiento()
    {
        return $this->infraestructuraH6Equipamiento;
    }

    /**
     * Set n5EquipamientoAdicionaEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n5EquipamientoAdicionaEstadogeneralTipo
     * @return InfraestructuraH6EquipamientoPedagogicoAdicional
     */
    public function setN5EquipamientoAdicionaEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n5EquipamientoAdicionaEstadogeneralTipo = null)
    {
        $this->n5EquipamientoAdicionaEstadogeneralTipo = $n5EquipamientoAdicionaEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n5EquipamientoAdicionaEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN5EquipamientoAdicionaEstadogeneralTipo()
    {
        return $this->n5EquipamientoAdicionaEstadogeneralTipo;
    }

    /**
     * Set n5MobiliarioAdicionalTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6MobiliarioAdicionalTipo $n5MobiliarioAdicionalTipo
     * @return InfraestructuraH6EquipamientoPedagogicoAdicional
     */
    public function setN5MobiliarioAdicionalTipo(\Sie\AppWebBundle\Entity\InfraestructuraH6MobiliarioAdicionalTipo $n5MobiliarioAdicionalTipo = null)
    {
        $this->n5MobiliarioAdicionalTipo = $n5MobiliarioAdicionalTipo;
    
        return $this;
    }

    /**
     * Get n5MobiliarioAdicionalTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6MobiliarioAdicionalTipo 
     */
    public function getN5MobiliarioAdicionalTipo()
    {
        return $this->n5MobiliarioAdicionalTipo;
    }
}
