<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificacionSistema
 */
class NotificacionSistema
{
    /**
     * @var integer
     */
    private $id;

    
    /**
     * @var \Sie\AppWebBundle\Entity\Notificacion
     */
    private $notif;

    /**
     * @var \Sie\AppWebBundle\Entity\SistemaTipo
     */
    private $sistemaTipo;


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
     * Set notif
     *
     * @param \Sie\AppWebBundle\Entity\Notificacion $notif
     * @return NotificacionSistema
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
     * Set sistemaTipo
     *
     * @param \Sie\AppWebBundle\Entity\SistemaTipo $sistemaTipo
     * @return NotificacionSistema
     */
    public function setSistemaTipo(\Sie\AppWebBundle\Entity\SistemaTipo $sistemaTipo = null)
    {
        $this->sistemaTipo = $sistemaTipo;
    
        return $this;
    }

    /**
     * Get sistemaTipo
     *
     * @return \Sie\AppWebBundle\Entity\SistemaTipo 
     */
    public function getSistemaTipo()
    {
        return $this->sistemaTipo;
    }
}
