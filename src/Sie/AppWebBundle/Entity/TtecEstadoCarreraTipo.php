<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecEstadoCarreraTipo
 */
class TtecEstadoCarreraTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $estadoCarrera;


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
     * Set estadoCarrera
     *
     * @param string $estadoCarrera
     * @return TtecEstadoCarreraTipo
     */
    public function setEstadoCarrera($estadoCarrera)
    {
        $this->estadoCarrera = $estadoCarrera;
    
        return $this;
    }

    /**
     * Get estadoCarrera
     *
     * @return string 
     */
    public function getEstadoCarrera()
    {
        return $this->estadoCarrera;
    }
}
