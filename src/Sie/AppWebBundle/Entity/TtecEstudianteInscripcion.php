<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecEstudianteInscripcion
 */
class TtecEstudianteInscripcion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $estadomatriculaTipoInicioId;

    /**
     * @var integer
     */
    private $estadomatriculaTipoFinId;

    /**
     * @var \DateTime
     */
    private $fechaInscripcion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecParaleloMateria
     */
    private $ttecParaleloMateria;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;


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
     * Set estadomatriculaTipoInicioId
     *
     * @param integer $estadomatriculaTipoInicioId
     * @return TtecEstudianteInscripcion
     */
    public function setEstadomatriculaTipoInicioId($estadomatriculaTipoInicioId)
    {
        $this->estadomatriculaTipoInicioId = $estadomatriculaTipoInicioId;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipoInicioId
     *
     * @return integer 
     */
    public function getEstadomatriculaTipoInicioId()
    {
        return $this->estadomatriculaTipoInicioId;
    }

    /**
     * Set estadomatriculaTipoFinId
     *
     * @param integer $estadomatriculaTipoFinId
     * @return TtecEstudianteInscripcion
     */
    public function setEstadomatriculaTipoFinId($estadomatriculaTipoFinId)
    {
        $this->estadomatriculaTipoFinId = $estadomatriculaTipoFinId;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipoFinId
     *
     * @return integer 
     */
    public function getEstadomatriculaTipoFinId()
    {
        return $this->estadomatriculaTipoFinId;
    }

    /**
     * Set fechaInscripcion
     *
     * @param \DateTime $fechaInscripcion
     * @return TtecEstudianteInscripcion
     */
    public function setFechaInscripcion($fechaInscripcion)
    {
        $this->fechaInscripcion = $fechaInscripcion;
    
        return $this;
    }

    /**
     * Get fechaInscripcion
     *
     * @return \DateTime 
     */
    public function getFechaInscripcion()
    {
        return $this->fechaInscripcion;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecEstudianteInscripcion
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set ttecParaleloMateria
     *
     * @param \Sie\AppWebBundle\Entity\TtecParaleloMateria $ttecParaleloMateria
     * @return TtecEstudianteInscripcion
     */
    public function setTtecParaleloMateria(\Sie\AppWebBundle\Entity\TtecParaleloMateria $ttecParaleloMateria = null)
    {
        $this->ttecParaleloMateria = $ttecParaleloMateria;
    
        return $this;
    }

    /**
     * Get ttecParaleloMateria
     *
     * @return \Sie\AppWebBundle\Entity\TtecParaleloMateria 
     */
    public function getTtecParaleloMateria()
    {
        return $this->ttecParaleloMateria;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return TtecEstudianteInscripcion
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
    /**
     * @var \Sie\AppWebBundle\Entity\EstadomatriculaTipo
     */
    private $estadomatriculaTipoFin;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadomatriculaTipo
     */
    private $estadomatriculaTipoInicio;


    /**
     * Set estadomatriculaTipoFin
     *
     * @param \Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipoFin
     * @return TtecEstudianteInscripcion
     */
    public function setEstadomatriculaTipoFin(\Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipoFin = null)
    {
        $this->estadomatriculaTipoFin = $estadomatriculaTipoFin;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipoFin
     *
     * @return \Sie\AppWebBundle\Entity\EstadomatriculaTipo 
     */
    public function getEstadomatriculaTipoFin()
    {
        return $this->estadomatriculaTipoFin;
    }

    /**
     * Set estadomatriculaTipoInicio
     *
     * @param \Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipoInicio
     * @return TtecEstudianteInscripcion
     */
    public function setEstadomatriculaTipoInicio(\Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipoInicio = null)
    {
        $this->estadomatriculaTipoInicio = $estadomatriculaTipoInicio;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipoInicio
     *
     * @return \Sie\AppWebBundle\Entity\EstadomatriculaTipo 
     */
    public function getEstadomatriculaTipoInicio()
    {
        return $this->estadomatriculaTipoInicio;
    }
}
