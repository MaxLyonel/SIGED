<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PersonaAdministrativoInscripcion
 */
class PersonaAdministrativoInscripcion
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
     * @var boolean
     */
    private $esactivo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\PersonaAdministrativoInscripcionTipo
     */
    private $personaAdministrativoInscripcionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\DistritoTipo
     */
    private $distritoTipo;


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
     * @return PersonaAdministrativoInscripcion
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
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return PersonaAdministrativoInscripcion
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return PersonaAdministrativoInscripcion
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }

    /**
     * Set personaAdministrativoInscripcionTipo
     *
     * @param \Sie\AppWebBundle\Entity\PersonaAdministrativoInscripcionTipo $personaAdministrativoInscripcionTipo
     * @return PersonaAdministrativoInscripcion
     */
    public function setPersonaAdministrativoInscripcionTipo(\Sie\AppWebBundle\Entity\PersonaAdministrativoInscripcionTipo $personaAdministrativoInscripcionTipo = null)
    {
        $this->personaAdministrativoInscripcionTipo = $personaAdministrativoInscripcionTipo;
    
        return $this;
    }

    /**
     * Get personaAdministrativoInscripcionTipo
     *
     * @return \Sie\AppWebBundle\Entity\PersonaAdministrativoInscripcionTipo 
     */
    public function getPersonaAdministrativoInscripcionTipo()
    {
        return $this->personaAdministrativoInscripcionTipo;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return PersonaAdministrativoInscripcion
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
     * Set distritoTipo
     *
     * @param \Sie\AppWebBundle\Entity\DistritoTipo $distritoTipo
     * @return PersonaAdministrativoInscripcion
     */
    public function setDistritoTipo(\Sie\AppWebBundle\Entity\DistritoTipo $distritoTipo = null)
    {
        $this->distritoTipo = $distritoTipo;
    
        return $this;
    }

    /**
     * Get distritoTipo
     *
     * @return \Sie\AppWebBundle\Entity\DistritoTipo 
     */
    public function getDistritoTipo()
    {
        return $this->distritoTipo;
    }
    /**
     * @var boolean
     */
    private $finalizado;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadosaludTipo
     */
    private $estadosaludTipo;


    /**
     * Set finalizado
     *
     * @param boolean $finalizado
     * @return PersonaAdministrativoInscripcion
     */
    public function setFinalizado($finalizado)
    {
        $this->finalizado = $finalizado;
    
        return $this;
    }

    /**
     * Get finalizado
     *
     * @return boolean 
     */
    public function getFinalizado()
    {
        return $this->finalizado;
    }

    /**
     * Set estadosaludTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstadosaludTipo $estadosaludTipo
     * @return PersonaAdministrativoInscripcion
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
     * @var boolean
     */
    private $esvacuna;


    /**
     * Set esvacuna
     *
     * @param boolean $esvacuna
     * @return PersonaAdministrativoInscripcion
     */
    public function setEsvacuna($esvacuna)
    {
        $this->esvacuna = $esvacuna;
    
        return $this;
    }

    /**
     * Get esvacuna
     *
     * @return boolean 
     */
    public function getEsvacuna()
    {
        return $this->esvacuna;
    }
}
