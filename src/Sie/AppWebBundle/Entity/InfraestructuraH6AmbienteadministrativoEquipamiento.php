<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH6AmbienteadministrativoEquipamiento
 */
class InfraestructuraH6AmbienteadministrativoEquipamiento
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n63Cantidad;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente
     */
    private $infraestructuraH6AmbienteadministrativoAmbiente;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo
     */
    private $n63EstadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoEquipamientoTipo
     */
    private $n63EquipamientoTipo;


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
     * Set n63Cantidad
     *
     * @param integer $n63Cantidad
     * @return InfraestructuraH6AmbienteadministrativoEquipamiento
     */
    public function setN63Cantidad($n63Cantidad)
    {
        $this->n63Cantidad = $n63Cantidad;
    
        return $this;
    }

    /**
     * Get n63Cantidad
     *
     * @return integer 
     */
    public function getN63Cantidad()
    {
        return $this->n63Cantidad;
    }

    /**
     * Set infraestructuraH6AmbienteadministrativoAmbiente
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente $infraestructuraH6AmbienteadministrativoAmbiente
     * @return InfraestructuraH6AmbienteadministrativoEquipamiento
     */
    public function setInfraestructuraH6AmbienteadministrativoAmbiente(\Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente $infraestructuraH6AmbienteadministrativoAmbiente = null)
    {
        $this->infraestructuraH6AmbienteadministrativoAmbiente = $infraestructuraH6AmbienteadministrativoAmbiente;
    
        return $this;
    }

    /**
     * Get infraestructuraH6AmbienteadministrativoAmbiente
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente 
     */
    public function getInfraestructuraH6AmbienteadministrativoAmbiente()
    {
        return $this->infraestructuraH6AmbienteadministrativoAmbiente;
    }

    /**
     * Set n63EstadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n63EstadoTipo
     * @return InfraestructuraH6AmbienteadministrativoEquipamiento
     */
    public function setN63EstadoTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n63EstadoTipo = null)
    {
        $this->n63EstadoTipo = $n63EstadoTipo;
    
        return $this;
    }

    /**
     * Get n63EstadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo 
     */
    public function getN63EstadoTipo()
    {
        return $this->n63EstadoTipo;
    }

    /**
     * Set n63EquipamientoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoEquipamientoTipo $n63EquipamientoTipo
     * @return InfraestructuraH6AmbienteadministrativoEquipamiento
     */
    public function setN63EquipamientoTipo(\Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoEquipamientoTipo $n63EquipamientoTipo = null)
    {
        $this->n63EquipamientoTipo = $n63EquipamientoTipo;
    
        return $this;
    }

    /**
     * Get n63EquipamientoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoEquipamientoTipo 
     */
    public function getN63EquipamientoTipo()
    {
        return $this->n63EquipamientoTipo;
    }
}
