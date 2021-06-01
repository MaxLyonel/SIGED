<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MaestroInscripcionEstadosalud
 */
class MaestroInscripcionEstadosalud
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadosaludTipo
     */
    private $estadosaludTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\MaestroInscripcion
     */
    private $maestroInscripcion;


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
     * Set estadosaludTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstadosaludTipo $estadosaludTipo
     * @return MaestroInscripcionEstadosalud
     */
    public function setEstadosaludTipo(\Sie\AppWebBundle\Entity\EstadosaludTipo $estadosaludTipo = null)
    {
        $this->estadosaludTipo = $estadosaludTipo;
    
        return $this;
    }

    /**
     * Get estadosaludTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstadosaludTipo 
     */
    public function getEstadosaludTipo()
    {
        return $this->estadosaludTipo;
    }

    /**
     * Set maestroInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcion
     * @return MaestroInscripcionEstadosalud
     */
    public function setMaestroInscripcion(\Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcion = null)
    {
        $this->maestroInscripcion = $maestroInscripcion;
    
        return $this;
    }

    /**
     * Get maestroInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\MaestroInscripcion 
     */
    public function getMaestroInscripcion()
    {
        return $this->maestroInscripcion;
    }
}
