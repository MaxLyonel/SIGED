<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5AmbientepedagogicoEquipamiento
 */
class InfraestructuraH5AmbientepedagogicoEquipamiento
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n53Cantidad;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5Ambientepedagogico
     */
    private $infraestructuraH5Ambientepedagogico;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo
     */
    private $n53EstadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoEquiposTipo
     */
    private $n53EquiposTipo;


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
     * Set n53Cantidad
     *
     * @param integer $n53Cantidad
     * @return InfraestructuraH5AmbientepedagogicoEquipamiento
     */
    public function setN53Cantidad($n53Cantidad)
    {
        $this->n53Cantidad = $n53Cantidad;
    
        return $this;
    }

    /**
     * Get n53Cantidad
     *
     * @return integer 
     */
    public function getN53Cantidad()
    {
        return $this->n53Cantidad;
    }

    /**
     * Set infraestructuraH5Ambientepedagogico
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5Ambientepedagogico $infraestructuraH5Ambientepedagogico
     * @return InfraestructuraH5AmbientepedagogicoEquipamiento
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

    /**
     * Set n53EstadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n53EstadoTipo
     * @return InfraestructuraH5AmbientepedagogicoEquipamiento
     */
    public function setN53EstadoTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n53EstadoTipo = null)
    {
        $this->n53EstadoTipo = $n53EstadoTipo;
    
        return $this;
    }

    /**
     * Get n53EstadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo 
     */
    public function getN53EstadoTipo()
    {
        return $this->n53EstadoTipo;
    }

    /**
     * Set n53EquiposTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoEquiposTipo $n53EquiposTipo
     * @return InfraestructuraH5AmbientepedagogicoEquipamiento
     */
    public function setN53EquiposTipo(\Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoEquiposTipo $n53EquiposTipo = null)
    {
        $this->n53EquiposTipo = $n53EquiposTipo;
    
        return $this;
    }

    /**
     * Get n53EquiposTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoEquiposTipo 
     */
    public function getN53EquiposTipo()
    {
        return $this->n53EquiposTipo;
    }
}
