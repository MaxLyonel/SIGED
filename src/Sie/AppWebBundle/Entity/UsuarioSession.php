<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioSession
 */
class UsuarioSession
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $usuarioId;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var string
     */
    private $fecharegistro;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var integer
     */
    private $logeoEstado;

    /**
     * @var string
     */
    private $observaciones;


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
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return UsuarioSession
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return UsuarioSession
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set fecharegistro
     *
     * @param string $fecharegistro
     * @return UsuarioSession
     */
    public function setFecharegistro($fecharegistro)
    {
        $this->fecharegistro = $fecharegistro;
    
        return $this;
    }

    /**
     * Get fecharegistro
     *
     * @return string 
     */
    public function getFecharegistro()
    {
        return $this->fecharegistro;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return UsuarioSession
     */
    public function setUsername($username)
    {
        $this->username = $username;
    
        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return UsuarioSession
     */
    public function setIp($ip)
    {
        $this->ip = $ip;
    
        return $this;
    }

    /**
     * Get ip
     *
     * @return string 
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set logeoEstado
     *
     * @param integer $logeoEstado
     * @return UsuarioSession
     */
    public function setLogeoEstado($logeoEstado)
    {
        $this->logeoEstado = $logeoEstado;
    
        return $this;
    }

    /**
     * Get logeoEstado
     *
     * @return integer 
     */
    public function getLogeoEstado()
    {
        return $this->logeoEstado;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return UsuarioSession
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    
        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }
    /**
     * @var integer
     */
    private $rolTipoId;


    /**
     * Set rolTipoId
     *
     * @param integer $rolTipoId
     * @return UsuarioSession
     */
    public function setRolTipoId($rolTipoId)
    {
        $this->rolTipoId = $rolTipoId;
    
        return $this;
    }

    /**
     * Get rolTipoId
     *
     * @return integer 
     */
    public function getRolTipoId()
    {
        return $this->rolTipoId;
    }
}
