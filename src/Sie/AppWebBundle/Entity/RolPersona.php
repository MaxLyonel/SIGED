<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RolPersona
 */
class RolPersona
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
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\RolTipo
     */
    private $rolTipo;


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
     * @return RolPersona
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
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return RolPersona
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
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return RolPersona
     */
    public function setRolTipo(\Sie\AppWebBundle\Entity\RolTipo $rolTipo = null)
    {
        $this->rolTipo = $rolTipo;

        return $this;
    }

    /**
     * Get rolTipo
     *
     * @return \Sie\AppWebBundle\Entity\RolTipo 
     */
    public function getRolTipo()
    {
        return $this->rolTipo;
    }
}
