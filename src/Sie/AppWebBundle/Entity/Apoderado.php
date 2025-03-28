<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Apoderado
 */
class Apoderado
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $empleo;

    /**
     * @var integer
     */
    private $instruccionId;

    /**
     * @var string
     */
    private $telefono;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var integer
     */
    private $validado;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestion;

    /**
     * @var \Sie\AppWebBundle\Entity\ApoderadoTipo
     */
    private $apoderadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $personaApoderado;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $personaEstudiante;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaMaterno
     */
    private $idiomaMaterno;


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
     * Set empleo
     *
     * @param string $empleo
     * @return Apoderado
     */
    public function setEmpleo($empleo)
    {
        $this->empleo = $empleo;
    
        return $this;
    }

    /**
     * Get empleo
     *
     * @return string 
     */
    public function getEmpleo()
    {
        return $this->empleo;
    }

    /**
     * Set instruccionId
     *
     * @param integer $instruccionId
     * @return Apoderado
     */
    public function setInstruccionId($instruccionId)
    {
        $this->instruccionId = $instruccionId;
    
        return $this;
    }

    /**
     * Get instruccionId
     *
     * @return integer 
     */
    public function getInstruccionId()
    {
        return $this->instruccionId;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return Apoderado
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    
        return $this;
    }

    /**
     * Get telefono
     *
     * @return string 
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return Apoderado
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }

    /**
     * Set validado
     *
     * @param integer $validado
     * @return Apoderado
     */
    public function setValidado($validado)
    {
        $this->validado = $validado;
    
        return $this;
    }

    /**
     * Get validado
     *
     * @return integer 
     */
    public function getValidado()
    {
        return $this->validado;
    }

    /**
     * Set gestion
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestion
     * @return Apoderado
     */
    public function setGestion(\Sie\AppWebBundle\Entity\GestionTipo $gestion = null)
    {
        $this->gestion = $gestion;
    
        return $this;
    }

    /**
     * Get gestion
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestion()
    {
        return $this->gestion;
    }

    /**
     * Set apoderadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\ApoderadoTipo $apoderadoTipo
     * @return Apoderado
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

    /**
     * Set personaApoderado
     *
     * @param \Sie\AppWebBundle\Entity\Persona $personaApoderado
     * @return Apoderado
     */
    public function setPersonaApoderado(\Sie\AppWebBundle\Entity\Persona $personaApoderado = null)
    {
        $this->personaApoderado = $personaApoderado;
    
        return $this;
    }

    /**
     * Get personaApoderado
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getPersonaApoderado()
    {
        return $this->personaApoderado;
    }

    /**
     * Set personaEstudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $personaEstudiante
     * @return Apoderado
     */
    public function setPersonaEstudiante(\Sie\AppWebBundle\Entity\Estudiante $personaEstudiante = null)
    {
        $this->personaEstudiante = $personaEstudiante;
    
        return $this;
    }

    /**
     * Get personaEstudiante
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getPersonaEstudiante()
    {
        return $this->personaEstudiante;
    }

    /**
     * Set idiomaMaterno
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaMaterno $idiomaMaterno
     * @return Apoderado
     */
    public function setIdiomaMaterno(\Sie\AppWebBundle\Entity\IdiomaMaterno $idiomaMaterno = null)
    {
        $this->idiomaMaterno = $idiomaMaterno;
    
        return $this;
    }

    /**
     * Get idiomaMaterno
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaMaterno 
     */
    public function getIdiomaMaterno()
    {
        return $this->idiomaMaterno;
    }
}
