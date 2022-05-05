<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatMsgPregunta
 */
class UnivDatMsgPregunta
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $mensaje;

    /**
     * @var string
     */
    private $usuarioId;

    /**
     * @var string
     */
    private $fecha;

    /**
     * @var string
     */
    private $hora;

    /**
     * @var string
     */
    private $estado;

    /**
     * @var string
     */
    private $preguntaId;


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
     * Set mensaje
     *
     * @param string $mensaje
     * @return UnivDatMsgPregunta
     */
    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;
    
        return $this;
    }

    /**
     * Get mensaje
     *
     * @return string 
     */
    public function getMensaje()
    {
        return $this->mensaje;
    }

    /**
     * Set usuarioId
     *
     * @param string $usuarioId
     * @return UnivDatMsgPregunta
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
     * Set fecha
     *
     * @param string $fecha
     * @return UnivDatMsgPregunta
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
     * Set hora
     *
     * @param string $hora
     * @return UnivDatMsgPregunta
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

    /**
     * Set estado
     *
     * @param string $estado
     * @return UnivDatMsgPregunta
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
     * Set preguntaId
     *
     * @param string $preguntaId
     * @return UnivDatMsgPregunta
     */
    public function setPreguntaId($preguntaId)
    {
        $this->preguntaId = $preguntaId;
    
        return $this;
    }

    /**
     * Get preguntaId
     *
     * @return string 
     */
    public function getPreguntaId()
    {
        return $this->preguntaId;
    }
}
