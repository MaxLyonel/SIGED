<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LogError
 */
class LogError
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $error;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var integer
     */
    private $usuario;

    /**
     * @var string
     */
    private $rude;

    /**
     * @var string
     */
    private $ie;

    /**
     * @var integer
     */
    private $inscripcionId;


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
     * Set error
     *
     * @param string $error
     * @return LogError
     */
    public function setError($error)
    {
        $this->error = $error;
    
        return $this;
    }

    /**
     * Get error
     *
     * @return string 
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return LogError
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
     * Set usuario
     *
     * @param integer $usuario
     * @return LogError
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return integer 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set rude
     *
     * @param string $rude
     * @return LogError
     */
    public function setRude($rude)
    {
        $this->rude = $rude;
    
        return $this;
    }

    /**
     * Get rude
     *
     * @return string 
     */
    public function getRude()
    {
        return $this->rude;
    }

    /**
     * Set ie
     *
     * @param string $ie
     * @return LogError
     */
    public function setIe($ie)
    {
        $this->ie = $ie;
    
        return $this;
    }

    /**
     * Get ie
     *
     * @return string 
     */
    public function getIe()
    {
        return $this->ie;
    }

    /**
     * Set inscripcionId
     *
     * @param integer $inscripcionId
     * @return LogError
     */
    public function setInscripcionId($inscripcionId)
    {
        $this->inscripcionId = $inscripcionId;
    
        return $this;
    }

    /**
     * Get inscripcionId
     *
     * @return integer 
     */
    public function getInscripcionId()
    {
        return $this->inscripcionId;
    }
}
