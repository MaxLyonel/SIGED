<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH6AmbienteadministrativoMobiliario
 */
class InfraestructuraH6AmbienteadministrativoMobiliario
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n21Cantidad;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoMobiliarioTipo
     */
    private $n21MobiliarioTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo
     */
    private $n21EstadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente
     */
    private $infraestructuraH6AmbienteadministrativoAmbiente;


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
     * Set n21Cantidad
     *
     * @param integer $n21Cantidad
     * @return InfraestructuraH6AmbienteadministrativoMobiliario
     */
    public function setN21Cantidad($n21Cantidad)
    {
        $this->n21Cantidad = $n21Cantidad;
    
        return $this;
    }

    /**
     * Get n21Cantidad
     *
     * @return integer 
     */
    public function getN21Cantidad()
    {
        return $this->n21Cantidad;
    }

    /**
     * Set n21MobiliarioTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoMobiliarioTipo $n21MobiliarioTipo
     * @return InfraestructuraH6AmbienteadministrativoMobiliario
     */
    public function setN21MobiliarioTipo(\Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoMobiliarioTipo $n21MobiliarioTipo = null)
    {
        $this->n21MobiliarioTipo = $n21MobiliarioTipo;
    
        return $this;
    }

    /**
     * Get n21MobiliarioTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoMobiliarioTipo 
     */
    public function getN21MobiliarioTipo()
    {
        return $this->n21MobiliarioTipo;
    }

    /**
     * Set n21EstadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n21EstadoTipo
     * @return InfraestructuraH6AmbienteadministrativoMobiliario
     */
    public function setN21EstadoTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo $n21EstadoTipo = null)
    {
        $this->n21EstadoTipo = $n21EstadoTipo;
    
        return $this;
    }

    /**
     * Get n21EstadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoMobEquipTipo 
     */
    public function getN21EstadoTipo()
    {
        return $this->n21EstadoTipo;
    }

    /**
     * Set infraestructuraH6AmbienteadministrativoAmbiente
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoAmbiente $infraestructuraH6AmbienteadministrativoAmbiente
     * @return InfraestructuraH6AmbienteadministrativoMobiliario
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
}
