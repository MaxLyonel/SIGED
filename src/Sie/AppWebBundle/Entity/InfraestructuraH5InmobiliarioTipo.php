<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5InmobiliarioTipo
 */
class InfraestructuraH5InmobiliarioTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraInmobiliario;

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
     * Set infraestructuraInmobiliario
     *
     * @param string $infraestructuraInmobiliario
     * @return InfraestructuraH5InmobiliarioTipo
     */
    public function setInfraestructuraInmobiliario($infraestructuraInmobiliario)
    {
        $this->infraestructuraInmobiliario = $infraestructuraInmobiliario;
    
        return $this;
    }

    /**
     * Get infraestructuraInmobiliario
     *
     * @return string 
     */
    public function getInfraestructuraInmobiliario()
    {
        return $this->infraestructuraInmobiliario;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH5InmobiliarioTipo
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
     * @return InfraestructuraH5InmobiliarioTipo
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
