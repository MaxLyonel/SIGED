<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RolRolesAsignacion
 */
class RolRolesAsignacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $roles;

    /**
     * @var \Sie\AppWebBundle\Entity\RolTipo
     */
    private $rol;


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
     * Set roles
     *
     * @param integer $roles
     * @return RolRolesAsignacion
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    
        return $this;
    }

    /**
     * Get roles
     *
     * @return integer 
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Set rol
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rol
     * @return RolRolesAsignacion
     */
    public function setRol(\Sie\AppWebBundle\Entity\RolTipo $rol = null)
    {
        $this->rol = $rol;
    
        return $this;
    }

    /**
     * Get rol
     *
     * @return \Sie\AppWebBundle\Entity\RolTipo 
     */
    public function getRol()
    {
        return $this->rol;
    }
}
