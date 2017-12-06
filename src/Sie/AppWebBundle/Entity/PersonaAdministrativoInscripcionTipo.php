<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PersonaAdministrativoInscripcionTipo
 */
class PersonaAdministrativoInscripcionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $personaAdministrativoInscripcion;

    /**
     * @var string
     */
    private $obs;


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
     * Set personaAdministrativoInscripcion
     *
     * @param string $personaAdministrativoInscripcion
     * @return PersonaAdministrativoInscripcionTipo
     */
    public function setPersonaAdministrativoInscripcion($personaAdministrativoInscripcion)
    {
        $this->personaAdministrativoInscripcion = $personaAdministrativoInscripcion;
    
        return $this;
    }

    /**
     * Get personaAdministrativoInscripcion
     *
     * @return string 
     */
    public function getPersonaAdministrativoInscripcion()
    {
        return $this->personaAdministrativoInscripcion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return PersonaAdministrativoInscripcionTipo
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
}
