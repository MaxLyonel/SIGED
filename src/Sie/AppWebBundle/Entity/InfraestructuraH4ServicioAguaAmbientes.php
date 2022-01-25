<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4ServicioAguaAmbientes
 */
class InfraestructuraH4ServicioAguaAmbientes
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4ServicioAguaAmbientesTipo
     */
    private $n29AmbientesAguaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4Servicio
     */
    private $infraestructuraH4Servicio;


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
     * Set n29AmbientesAguaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4ServicioAguaAmbientesTipo $n29AmbientesAguaTipo
     * @return InfraestructuraH4ServicioAguaAmbientes
     */
    public function setN29AmbientesAguaTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4ServicioAguaAmbientesTipo $n29AmbientesAguaTipo = null)
    {
        $this->n29AmbientesAguaTipo = $n29AmbientesAguaTipo;
    
        return $this;
    }

    /**
     * Get n29AmbientesAguaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4ServicioAguaAmbientesTipo 
     */
    public function getN29AmbientesAguaTipo()
    {
        return $this->n29AmbientesAguaTipo;
    }

    /**
     * Set infraestructuraH4Servicio
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4Servicio $infraestructuraH4Servicio
     * @return InfraestructuraH4ServicioAguaAmbientes
     */
    public function setInfraestructuraH4Servicio(\Sie\AppWebBundle\Entity\InfraestructuraH4Servicio $infraestructuraH4Servicio = null)
    {
        $this->infraestructuraH4Servicio = $infraestructuraH4Servicio;
    
        return $this;
    }

    /**
     * Get infraestructuraH4Servicio
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4Servicio 
     */
    public function getInfraestructuraH4Servicio()
    {
        return $this->infraestructuraH4Servicio;
    }
}
