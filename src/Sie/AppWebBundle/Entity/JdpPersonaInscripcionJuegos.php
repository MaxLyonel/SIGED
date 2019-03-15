<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpPersonaInscripcionJuegos
 */
class JdpPersonaInscripcionJuegos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos
     */
    private $estudianteInscripcionJuegos;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpComisionTipo
     */
    private $comisionTipo;


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
     * Set obs
     *
     * @param string $obs
     * @return JdpPersonaInscripcionJuegos
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
     * Set estudianteInscripcionJuegos
     *
     * @param \Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos $estudianteInscripcionJuegos
     * @return JdpPersonaInscripcionJuegos
     */
    public function setEstudianteInscripcionJuegos(\Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos $estudianteInscripcionJuegos = null)
    {
        $this->estudianteInscripcionJuegos = $estudianteInscripcionJuegos;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionJuegos
     *
     * @return \Sie\AppWebBundle\Entity\JdpEstudianteInscripcionJuegos 
     */
    public function getEstudianteInscripcionJuegos()
    {
        return $this->estudianteInscripcionJuegos;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return JdpPersonaInscripcionJuegos
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
     * Set comisionTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpComisionTipo $comisionTipo
     * @return JdpPersonaInscripcionJuegos
     */
    public function setComisionTipo(\Sie\AppWebBundle\Entity\JdpComisionTipo $comisionTipo = null)
    {
        $this->comisionTipo = $comisionTipo;
    
        return $this;
    }

    /**
     * Get comisionTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpComisionTipo 
     */
    public function getComisionTipo()
    {
        return $this->comisionTipo;
    }
}
