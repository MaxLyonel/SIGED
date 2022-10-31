<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5AmbienteadministrativoAmbienteEquipamiento
 */
class InfraestructuraH5AmbienteadministrativoAmbienteEquipamiento
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n62Cantidad;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente
     */
    private $infraestructuraH5AmbienteadministrativoAmbiente;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo
     */
    private $n62EstadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoEquipamientoTipo
     */
    private $n62EquipamientoTipo;


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
     * Set n62Cantidad
     *
     * @param integer $n62Cantidad
     * @return InfraestructuraH5AmbienteadministrativoAmbienteEquipamiento
     */
    public function setN62Cantidad($n62Cantidad)
    {
        $this->n62Cantidad = $n62Cantidad;
    
        return $this;
    }

    /**
     * Get n62Cantidad
     *
     * @return integer 
     */
    public function getN62Cantidad()
    {
        return $this->n62Cantidad;
    }

    /**
     * Set infraestructuraH5AmbienteadministrativoAmbiente
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente $infraestructuraH5AmbienteadministrativoAmbiente
     * @return InfraestructuraH5AmbienteadministrativoAmbienteEquipamiento
     */
    public function setInfraestructuraH5AmbienteadministrativoAmbiente(\Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente $infraestructuraH5AmbienteadministrativoAmbiente = null)
    {
        $this->infraestructuraH5AmbienteadministrativoAmbiente = $infraestructuraH5AmbienteadministrativoAmbiente;
    
        return $this;
    }

    /**
     * Get infraestructuraH5AmbienteadministrativoAmbiente
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente 
     */
    public function getInfraestructuraH5AmbienteadministrativoAmbiente()
    {
        return $this->infraestructuraH5AmbienteadministrativoAmbiente;
    }

    /**
     * Set n62EstadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n62EstadoTipo
     * @return InfraestructuraH5AmbienteadministrativoAmbienteEquipamiento
     */
    public function setN62EstadoTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n62EstadoTipo = null)
    {
        $this->n62EstadoTipo = $n62EstadoTipo;
    
        return $this;
    }

    /**
     * Get n62EstadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo 
     */
    public function getN62EstadoTipo()
    {
        return $this->n62EstadoTipo;
    }

    /**
     * Set n62EquipamientoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoEquipamientoTipo $n62EquipamientoTipo
     * @return InfraestructuraH5AmbienteadministrativoAmbienteEquipamiento
     */
    public function setN62EquipamientoTipo(\Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoEquipamientoTipo $n62EquipamientoTipo = null)
    {
        $this->n62EquipamientoTipo = $n62EquipamientoTipo;
    
        return $this;
    }

    /**
     * Get n62EquipamientoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteadministrativoEquipamientoTipo 
     */
    public function getN62EquipamientoTipo()
    {
        return $this->n62EquipamientoTipo;
    }
}
