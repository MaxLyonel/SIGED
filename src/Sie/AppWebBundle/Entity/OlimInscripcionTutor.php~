<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimInscripcionTutor
 */
class OlimInscripcionTutor
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimTutor
     */
    private $olimTutor;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimEstudianteInscripcion
     */
    private $olimEstudianteInscripcion;


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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return OlimInscripcionTutor
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
     * Set olimTutor
     *
     * @param \Sie\AppWebBundle\Entity\OlimTutor $olimTutor
     * @return OlimInscripcionTutor
     */
    public function setOlimTutor(\Sie\AppWebBundle\Entity\OlimTutor $olimTutor = null)
    {
        $this->olimTutor = $olimTutor;
    
        return $this;
    }

    /**
     * Get olimTutor
     *
     * @return \Sie\AppWebBundle\Entity\OlimTutor 
     */
    public function getOlimTutor()
    {
        return $this->olimTutor;
    }

    /**
     * Set olimEstudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\OlimEstudianteInscripcion $olimEstudianteInscripcion
     * @return OlimInscripcionTutor
     */
    public function setOlimEstudianteInscripcion(\Sie\AppWebBundle\Entity\OlimEstudianteInscripcion $olimEstudianteInscripcion = null)
    {
        $this->olimEstudianteInscripcion = $olimEstudianteInscripcion;
    
        return $this;
    }

    /**
     * Get olimEstudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\OlimEstudianteInscripcion 
     */
    public function getOlimEstudianteInscripcion()
    {
        return $this->olimEstudianteInscripcion;
    }
}
