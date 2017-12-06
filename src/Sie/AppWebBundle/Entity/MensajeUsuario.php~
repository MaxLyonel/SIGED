<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MensajeUsuario
 */
class MensajeUsuario
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $leido;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $emisor;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $receptor;

    /**
     * @var \Sie\AppWebBundle\Entity\Mensaje
     */
    private $mensaje;


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
     * Set leido
     *
     * @param boolean $leido
     * @return MensajeUsuario
     */
    public function setLeido($leido)
    {
        $this->leido = $leido;
    
        return $this;
    }

    /**
     * Get leido
     *
     * @return boolean 
     */
    public function getLeido()
    {
        return $this->leido;
    }

    /**
     * Set emisor
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $emisor
     * @return MensajeUsuario
     */
    public function setEmisor(\Sie\AppWebBundle\Entity\Usuario $emisor = null)
    {
        $this->emisor = $emisor;
    
        return $this;
    }

    /**
     * Get emisor
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getEmisor()
    {
        return $this->emisor;
    }

    /**
     * Set receptor
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $receptor
     * @return MensajeUsuario
     */
    public function setReceptor(\Sie\AppWebBundle\Entity\Usuario $receptor = null)
    {
        $this->receptor = $receptor;
    
        return $this;
    }

    /**
     * Get receptor
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getReceptor()
    {
        return $this->receptor;
    }

    /**
     * Set mensaje
     *
     * @param \Sie\AppWebBundle\Entity\Mensaje $mensaje
     * @return MensajeUsuario
     */
    public function setMensaje(\Sie\AppWebBundle\Entity\Mensaje $mensaje = null)
    {
        $this->mensaje = $mensaje;
    
        return $this;
    }

    /**
     * Get mensaje
     *
     * @return \Sie\AppWebBundle\Entity\Mensaje 
     */
    public function getMensaje()
    {
        return $this->mensaje;
    }
    /**
     * @var integer
     */
    private $mensajeId;


    /**
     * Set mensajeId
     *
     * @param integer $mensajeId
     * @return MensajeUsuario
     */
    public function setMensajeId($mensajeId)
    {
        $this->mensajeId = $mensajeId;
    
        return $this;
    }

    /**
     * Get mensajeId
     *
     * @return integer 
     */
    public function getMensajeId()
    {
        return $this->mensajeId;
    }
}
