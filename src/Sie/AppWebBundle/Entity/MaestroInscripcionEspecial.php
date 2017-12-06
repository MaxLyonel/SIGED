<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MaestroInscripcionEspecial
 */
class MaestroInscripcionEspecial
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $discapacidad;

    /**
     * @var string
     */
    private $tipoDiscapacidad;

    /**
     * @var \Sie\AppWebBundle\Entity\GradoDiscapacidadTipo
     */
    private $gradoDiscapacidadTipo;

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
     * Set discapacidad
     *
     * @param string $discapacidad
     * @return MaestroInscripcionEspecial
     */
    public function setDiscapacidad($discapacidad)
    {
        $this->discapacidad = $discapacidad;
    
        return $this;
    }

    /**
     * Get discapacidad
     *
     * @return string 
     */
    public function getDiscapacidad()
    {
        return $this->discapacidad;
    }

    /**
     * Set tipoDiscapacidad
     *
     * @param string $tipoDiscapacidad
     * @return MaestroInscripcionEspecial
     */
    public function setTipoDiscapacidad($tipoDiscapacidad)
    {
        $this->tipoDiscapacidad = $tipoDiscapacidad;
    
        return $this;
    }

    /**
     * Get tipoDiscapacidad
     *
     * @return string 
     */
    public function getTipoDiscapacidad()
    {
        return $this->tipoDiscapacidad;
    }

    /**
     * Set gradoDiscapacidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\GradoDiscapacidadTipo $gradoDiscapacidadTipo
     * @return MaestroInscripcionEspecial
     */
    public function setGradoDiscapacidadTipo(\Sie\AppWebBundle\Entity\GradoDiscapacidadTipo $gradoDiscapacidadTipo = null)
    {
        $this->gradoDiscapacidadTipo = $gradoDiscapacidadTipo;
    
        return $this;
    }

    /**
     * Get gradoDiscapacidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\GradoDiscapacidadTipo 
     */
    public function getGradoDiscapacidadTipo()
    {
        return $this->gradoDiscapacidadTipo;
    }

    /**
     * Set maestroInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcion
     * @return MaestroInscripcionEspecial
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
