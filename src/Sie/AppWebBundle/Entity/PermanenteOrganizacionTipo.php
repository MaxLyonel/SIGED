<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PermanenteOrganizacionTipo
 */
class PermanenteOrganizacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $organizacion;

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
     * Set organizacion
     *
     * @param string $organizacion
     * @return PermanenteOrganizacionTipo
     */
    public function setOrganizacion($organizacion)
    {
        $this->organizacion = $organizacion;
    
        return $this;
    }

    /**
     * Get organizacion
     *
     * @return string 
     */
    public function getOrganizacion()
    {
        return $this->organizacion;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return PermanenteOrganizacionTipo
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
