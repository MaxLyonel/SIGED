<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApoderadoInscripcion
 */
class ApoderadoInscripcion
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
     * @var integer
     */
    private $esValidado;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\ApoderadoTipo
     */
    private $apoderadoTipo;


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
     * @return ApoderadoInscripcion
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
     * Set esValidado
     *
     * @param integer $esValidado
     * @return ApoderadoInscripcion
     */
    public function setEsValidado($esValidado)
    {
        $this->esValidado = $esValidado;
    
        return $this;
    }

    /**
     * Get esValidado
     *
     * @return integer 
     */
    public function getEsValidado()
    {
        return $this->esValidado;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return ApoderadoInscripcion
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
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return ApoderadoInscripcion
     */
    public function setEstudianteInscripcion(\Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion = null)
    {
        $this->estudianteInscripcion = $estudianteInscripcion;
    
        return $this;
    }

    /**
     * Get estudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcion 
     */
    public function getEstudianteInscripcion()
    {
        return $this->estudianteInscripcion;
    }

    /**
     * Set apoderadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\ApoderadoTipo $apoderadoTipo
     * @return ApoderadoInscripcion
     */
    public function setApoderadoTipo(\Sie\AppWebBundle\Entity\ApoderadoTipo $apoderadoTipo = null)
    {
        $this->apoderadoTipo = $apoderadoTipo;
    
        return $this;
    }

    /**
     * Get apoderadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\ApoderadoTipo 
     */
    public function getApoderadoTipo()
    {
        return $this->apoderadoTipo;
    }
}
