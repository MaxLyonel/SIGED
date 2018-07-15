<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimModalidadTipo
 */
class OlimModalidadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $modalidad;

    /**
     * @var string
     */
    private $observacion;


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
     * Set modalidad
     *
     * @param string $modalidad
     * @return OlimModalidadTipo
     */
    public function setModalidad($modalidad)
    {
        $this->modalidad = $modalidad;
    
        return $this;
    }

    /**
     * Get modalidad
     *
     * @return string 
     */
    public function getModalidad()
    {
        return $this->modalidad;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return OlimModalidadTipo
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }
}
