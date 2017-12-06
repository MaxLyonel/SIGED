<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificacionNorecordar
 */
class NotificacionNorecordar
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaLectura;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\Notificacion
     */
    private $notif;


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
     * Set fechaLectura
     *
     * @param \DateTime $fechaLectura
     * @return NotificacionNorecordar
     */
    public function setFechaLectura($fechaLectura)
    {
        $this->fechaLectura = $fechaLectura;
    
        return $this;
    }

    /**
     * Get fechaLectura
     *
     * @return \DateTime 
     */
    public function getFechaLectura()
    {
        return $this->fechaLectura;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return NotificacionNorecordar
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
     * Set notif
     *
     * @param \Sie\AppWebBundle\Entity\Notificacion $notif
     * @return NotificacionNorecordar
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
}
