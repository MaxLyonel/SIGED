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
    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\CargoTipo
     */
    private $cargoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;


    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return MaestroInscripcionEstadosalud
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set cargoTipo
     *
     * @param \Sie\AppWebBundle\Entity\CargoTipo $cargoTipo
     * @return MaestroInscripcionEstadosalud
     */
    public function setCargoTipo(\Sie\AppWebBundle\Entity\CargoTipo $cargoTipo = null)
    {
        $this->cargoTipo = $cargoTipo;
    
        return $this;
    }

    /**
     * Get cargoTipo
     *
     * @return \Sie\AppWebBundle\Entity\CargoTipo 
     */
    public function getCargoTipo()
    {
        return $this->cargoTipo;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return MaestroInscripcionEstadosalud
     */
    public function setPersona(\Sie\AppWebBundle\Entity\Persona $persona = null)
    {
        $this->persona = $persona;
    
        return $this;
    }

    /**
     * Get persona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getPersona()
    {
        return $this->persona;
    }
}
