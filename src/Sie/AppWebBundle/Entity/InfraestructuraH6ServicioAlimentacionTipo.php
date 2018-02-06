<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH6ServicioAlimentacionTipo
 */
class InfraestructuraH6ServicioAlimentacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraServicioAlimentacion;

    /**
     * @var string
     */
    private $obs;


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
     * Set infraestructuraServicioAlimentacion
     *
     * @param string $infraestructuraServicioAlimentacion
     * @return InfraestructuraH6ServicioAlimentacionTipo
     */
    public function setInfraestructuraServicioAlimentacion($infraestructuraServicioAlimentacion)
    {
        $this->infraestructuraServicioAlimentacion = $infraestructuraServicioAlimentacion;
    
        return $this;
    }

    /**
     * Get infraestructuraServicioAlimentacion
     *
     * @return string 
     */
    public function getInfraestructuraServicioAlimentacion()
    {
        return $this->infraestructuraServicioAlimentacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH6ServicioAlimentacionTipo
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }
    /**
     * @var integer
     */
    private $gestionTipoId;


    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return InfraestructuraH6ServicioAlimentacionTipo
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }
}
