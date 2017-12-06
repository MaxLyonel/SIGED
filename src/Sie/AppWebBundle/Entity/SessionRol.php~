<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SessionRol
 */
class SessionRol
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\RolTipo
     */
    private $rolTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UsuarioSession
     */
    private $session;


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
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return SessionRol
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

    /**
     * Set session
     *
     * @param \Sie\AppWebBundle\Entity\UsuarioSession $session
     * @return SessionRol
     */
    public function setSession(\Sie\AppWebBundle\Entity\UsuarioSession $session = null)
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Get session
     *
     * @return \Sie\AppWebBundle\Entity\UsuarioSession 
     */
    public function getSession()
    {
        return $this->session;
    }
}
