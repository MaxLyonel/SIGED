<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PersonaCarnetControl
 */
class PersonaCarnetControl
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $carnet;

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
     * Set carnet
     *
     * @param string $carnet
     * @return PersonaCarnetControl
     */
    public function setCarnet($carnet)
    {
        $this->carnet = $carnet;
    
        return $this;
    }

    /**
     * Get carnet
     *
     * @return string 
     */
    public function getCarnet()
    {
        return $this->carnet;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return PersonaCarnetControl
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
