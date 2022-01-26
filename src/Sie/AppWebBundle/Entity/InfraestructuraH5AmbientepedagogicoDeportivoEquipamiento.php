<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento
 */
class InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n531Cantidad;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo
     */
    private $n531EstadoEquipamientoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4AmbientepedagogicoDeportivoEquimientoTipo
     */
    private $n531EquipamientoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivo
     */
    private $infraestructuraH5AmbientepedagogicoDeportivo;


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
     * Set n531Cantidad
     *
     * @param integer $n531Cantidad
     * @return InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento
     */
    public function setN531Cantidad($n531Cantidad)
    {
        $this->n531Cantidad = $n531Cantidad;
    
        return $this;
    }

    /**
     * Get n531Cantidad
     *
     * @return integer 
     */
    public function getN531Cantidad()
    {
        return $this->n531Cantidad;
    }

    /**
     * Set n531EstadoEquipamientoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n531EstadoEquipamientoTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento
     */
    public function setN531EstadoEquipamientoTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n531EstadoEquipamientoTipo = null)
    {
        $this->n531EstadoEquipamientoTipo = $n531EstadoEquipamientoTipo;
    
        return $this;
    }

    /**
     * Get n531EstadoEquipamientoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo 
     */
    public function getN531EstadoEquipamientoTipo()
    {
        return $this->n531EstadoEquipamientoTipo;
    }

    /**
     * Set n531EquipamientoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4AmbientepedagogicoDeportivoEquimientoTipo $n531EquipamientoTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento
     */
    public function setN531EquipamientoTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4AmbientepedagogicoDeportivoEquimientoTipo $n531EquipamientoTipo = null)
    {
        $this->n531EquipamientoTipo = $n531EquipamientoTipo;
    
        return $this;
    }

    /**
     * Get n531EquipamientoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4AmbientepedagogicoDeportivoEquimientoTipo 
     */
    public function getN531EquipamientoTipo()
    {
        return $this->n531EquipamientoTipo;
    }

    /**
     * Set infraestructuraH5AmbientepedagogicoDeportivo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivo $infraestructuraH5AmbientepedagogicoDeportivo
     * @return InfraestructuraH5AmbientepedagogicoDeportivoEquipamiento
     */
    public function setInfraestructuraH5AmbientepedagogicoDeportivo(\Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivo $infraestructuraH5AmbientepedagogicoDeportivo = null)
    {
        $this->infraestructuraH5AmbientepedagogicoDeportivo = $infraestructuraH5AmbientepedagogicoDeportivo;
    
        return $this;
    }

    /**
     * Get infraestructuraH5AmbientepedagogicoDeportivo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivo 
     */
    public function getInfraestructuraH5AmbientepedagogicoDeportivo()
    {
        return $this->infraestructuraH5AmbientepedagogicoDeportivo;
    }
}
