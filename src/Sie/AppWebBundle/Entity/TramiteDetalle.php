<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TramiteDetalle
 */
class TramiteDetalle
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tramteDetalle;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\TramiteDetalle
     */
    private $tramiteDetalle;

    /**
     * @var \Sie\AppWebBundle\Entity\Tramite
     */
    private $tramite;

    /**
     * @var \Sie\AppWebBundle\Entity\TramiteEstado
     */
    private $tramiteEstado;

    /**
     * @var \Sie\AppWebBundle\Entity\FlujoProceso
     */
    private $flujoProceso;
    
    /**
     * @var integer
     */
    private $usuarioDestinatarioId;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaEnvio;

    /**
     * @var \DateTime
     */
    private $fechaRecepcion;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioRemitente;

    /**
     * @var \Sie\AppWebBundle\Entity\TramiteDetalle
     */
    private $tramiteDetalle2;
    
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
     * Set tramteDetalle
     *
     * @param string $tramteDetalle
     * @return TramiteDetalle
     */
    public function setTramteDetalle($tramteDetalle)
    {
        $this->tramteDetalle = $tramteDetalle;
    
        return $this;
    }

    /**
     * Get tramteDetalle
     *
     * @return string 
     */
    public function getTramteDetalle()
    {
        return $this->tramteDetalle;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return TramiteDetalle
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
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return TramiteDetalle
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
     * Set tramiteDetalle
     *
     * @param \Sie\AppWebBundle\Entity\TramiteDetalle $tramiteDetalle
     * @return TramiteDetalle
     */
    public function setTramiteDetalle(\Sie\AppWebBundle\Entity\TramiteDetalle $tramiteDetalle = null)
    {
        $this->tramiteDetalle = $tramiteDetalle;
    
        return $this;
    }

    /**
     * Get tramiteDetalle
     *
     * @return \Sie\AppWebBundle\Entity\TramiteDetalle 
     */
    public function getTramiteDetalle()
    {
        return $this->tramiteDetalle;
    }

    /**
     * Set tramite
     *
     * @param \Sie\AppWebBundle\Entity\Tramite $tramite
     * @return TramiteDetalle
     */
    public function setTramite(\Sie\AppWebBundle\Entity\Tramite $tramite = null)
    {
        $this->tramite = $tramite;
    
        return $this;
    }

    /**
     * Get tramite
     *
     * @return \Sie\AppWebBundle\Entity\Tramite 
     */
    public function getTramite()
    {
        return $this->tramite;
    }

    /**
     * Set tramiteEstado
     *
     * @param \Sie\AppWebBundle\Entity\TramiteEstado $tramiteEstado
     * @return TramiteDetalle
     */
    public function setTramiteEstado(\Sie\AppWebBundle\Entity\TramiteEstado $tramiteEstado = null)
    {
        $this->tramiteEstado = $tramiteEstado;
    
        return $this;
    }

    /**
     * Get tramiteEstado
     *
     * @return \Sie\AppWebBundle\Entity\TramiteEstado 
     */
    public function getTramiteEstado()
    {
        return $this->tramiteEstado;
    }
    

    /**
     * Set usuarioDestinatarioId
     *
     * @param integer $usuarioDestinatarioId
     * @return TramiteDetalle
     */
    public function setUsuarioDestinatarioId($usuarioDestinatarioId)
    {
        $this->usuarioDestinatarioId = $usuarioDestinatarioId;
    
        return $this;
    }

    /**
     * Get usuarioDestinatarioId
     *
     * @return integer 
     */
    public function getUsuarioDestinatarioId()
    {
        return $this->usuarioDestinatarioId;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TramiteDetalle
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
     * Set fechaEnvio
     *
     * @param \DateTime $fechaEnvio
     * @return TramiteDetalle
     */
    public function setFechaEnvio($fechaEnvio)
    {
        $this->fechaEnvio = $fechaEnvio;
    
        return $this;
    }

    /**
     * Get fechaEnvio
     *
     * @return \DateTime 
     */
    public function getFechaEnvio()
    {
        return $this->fechaEnvio;
    }

    /**
     * Set fechaRecepcion
     *
     * @param \DateTime $fechaRecepcion
     * @return TramiteDetalle
     */
    public function setFechaRecepcion($fechaRecepcion)
    {
        $this->fechaRecepcion = $fechaRecepcion;
    
        return $this;
    }

    /**
     * Get fechaRecepcion
     *
     * @return \DateTime 
     */
    public function getFechaRecepcion()
    {
        return $this->fechaRecepcion;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return TramiteDetalle
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set usuarioRemitente
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioRemitente
     * @return TramiteDetalle
     */
    public function setUsuarioRemitente(\Sie\AppWebBundle\Entity\Usuario $usuarioRemitente = null)
    {
        $this->usuarioRemitente = $usuarioRemitente;
    
        return $this;
    }

    /**
     * Get usuarioRemitente
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioRemitente()
    {
        return $this->usuarioRemitente;
    }

    /**
     * Set tramiteDetalle2
     *
     * @param \Sie\AppWebBundle\Entity\TramiteDetalle $tramiteDetalle2
     * @return TramiteDetalle
     */
    public function setTramiteDetalle2(\Sie\AppWebBundle\Entity\TramiteDetalle $tramiteDetalle2 = null)
    {
        $this->tramiteDetalle2 = $tramiteDetalle2;
    
        return $this;
    }

    /**
     * Get tramiteDetalle2
     *
     * @return \Sie\AppWebBundle\Entity\TramiteDetalle 
     */
    public function getTramiteDetalle2()
    {
        return $this->tramiteDetalle2;
    }
    

    /**
     * Set flujoProceso
     *
     * @param \Sie\AppWebBundle\Entity\FlujoProceso $flujoProceso
     * @return TramiteDetalle
     */
    public function setFlujoProceso(\Sie\AppWebBundle\Entity\FlujoProceso $flujoProceso = null)
    {
        $this->flujoProceso = $flujoProceso;
    
        return $this;
    }

    /**
     * Get flujoProceso
     *
     * @return \Sie\AppWebBundle\Entity\FlujoProceso 
     */
    public function getFlujoProceso()
    {
        return $this->flujoProceso;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioDestinatario;


    /**
     * Set usuarioDestinatario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioDestinatario
     * @return TramiteDetalle
     */
    public function setUsuarioDestinatario(\Sie\AppWebBundle\Entity\Usuario $usuarioDestinatario = null)
    {
        $this->usuarioDestinatario = $usuarioDestinatario;
    
        return $this;
    }

    /**
     * Get usuarioDestinatario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioDestinatario()
    {
        return $this->usuarioDestinatario;
    }
}
