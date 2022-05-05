<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatForEnvio
 */
class UnivDatForEnvio
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var string
     */
    private $fecha;

    /**
     * @var string
     */
    private $usuarioId;

    /**
     * @var string
     */
    private $estado;

    /**
     * @var string
     */
    private $hora;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return UnivDatForEnvio
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set fecha
     *
     * @param string $fecha
     * @return UnivDatForEnvio
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return string 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set usuarioId
     *
     * @param string $usuarioId
     * @return UnivDatForEnvio
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return string 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return UnivDatForEnvio
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return string 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set hora
     *
     * @param string $hora
     * @return UnivDatForEnvio
     */
    public function setHora($hora)
    {
        $this->hora = $hora;
    
        return $this;
    }

    /**
     * Get hora
     *
     * @return string 
     */
    public function getHora()
    {
        return $this->hora;
    }
}
