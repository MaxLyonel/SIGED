<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificacionUsuario
 */
class NotificacionUsuario
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $visto;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var \Sie\AppWebBundle\Entity\Notificacion
     */
    private $notif;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

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
     * Set visto
     *
     * @param boolean $visto
     * @return NotificacionUsuario
     */
    public function setVisto($visto)
    {
        $this->visto = $visto;
    
        return $this;
    }

    /**
     * Get visto
     *
     * @return boolean 
     */
    public function getVisto()
    {
        return $this->visto;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return NotificacionUsuario
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set notif
     *
     * @param \Sie\AppWebBundle\Entity\Notificacion $notif
     * @return NotificacionUsuario
     */
    public function setNotif(\Sie\AppWebBundle\Entity\Notificacion $notif = null)
    {
        $this->notif = $notif;
    
        return $this;
    }

    /**
     * Get notif
     *
     * @return \Sie\AppWebBundle\Entity\Notificacion 
     */
    public function getNotif()
    {
        return $this->notif;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return NotificacionUsuario
     */
    public function setUsuario(\Sie\AppWebBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return NotificacionUsuario
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
