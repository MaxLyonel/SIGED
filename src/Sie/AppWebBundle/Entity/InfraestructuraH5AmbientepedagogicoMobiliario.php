<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5AmbientepedagogicoMobiliario
 */
class InfraestructuraH5AmbientepedagogicoMobiliario
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n52Cantidad;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo
     */
    private $n52EstadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoMobiliarioTipo
     */
    private $n52MobiliarioTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5Ambientepedagogico
     */
    private $infraestructuraH5Ambientepedagogico;


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
     * Set n52Cantidad
     *
     * @param integer $n52Cantidad
     * @return InfraestructuraH5AmbientepedagogicoMobiliario
     */
    public function setN52Cantidad($n52Cantidad)
    {
        $this->n52Cantidad = $n52Cantidad;
    
        return $this;
    }

    /**
     * Get n52Cantidad
     *
     * @return integer 
     */
    public function getN52Cantidad()
    {
        return $this->n52Cantidad;
    }

    /**
     * Set n52EstadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n52EstadoTipo
     * @return InfraestructuraH5AmbientepedagogicoMobiliario
     */
    public function setN52EstadoTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n52EstadoTipo = null)
    {
        $this->n52EstadoTipo = $n52EstadoTipo;
    
        return $this;
    }

    /**
     * Get n52EstadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo 
     */
    public function getN52EstadoTipo()
    {
        return $this->n52EstadoTipo;
    }

    /**
     * Set n52MobiliarioTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoMobiliarioTipo $n52MobiliarioTipo
     * @return InfraestructuraH5AmbientepedagogicoMobiliario
     */
    public function setN52MobiliarioTipo(\Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoMobiliarioTipo $n52MobiliarioTipo = null)
    {
        $this->n52MobiliarioTipo = $n52MobiliarioTipo;
    
        return $this;
    }

    /**
     * Get n52MobiliarioTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoMobiliarioTipo 
     */
    public function getN52MobiliarioTipo()
    {
        return $this->n52MobiliarioTipo;
    }

    /**
     * Set infraestructuraH5Ambientepedagogico
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5Ambientepedagogico $infraestructuraH5Ambientepedagogico
     * @return InfraestructuraH5AmbientepedagogicoMobiliario
     */
    public function setInfraestructuraH5Ambientepedagogico(\Sie\AppWebBundle\Entity\InfraestructuraH5Ambientepedagogico $infraestructuraH5Ambientepedagogico = null)
    {
        $this->infraestructuraH5Ambientepedagogico = $infraestructuraH5Ambientepedagogico;
    
        return $this;
    }

    /**
     * Get infraestructuraH5Ambientepedagogico
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5Ambientepedagogico 
     */
    public function getInfraestructuraH5Ambientepedagogico()
    {
        return $this->infraestructuraH5Ambientepedagogico;
    }
}
