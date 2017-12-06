<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteNotaModificacion
 */
class EstudianteNotaModificacion
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
    private $remitente;

    /**
     * @var string
     */
    private $receptor;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var \DateTime
     */
    private $hora;

    /**
     * @var integer
     */
    private $estudianteInscripcionId;

    /**
     * @var string
     */
    private $detalle;

    /**
     * @var string
     */
    private $motivo;

    /**
     * @var integer
     */
    private $estado;


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
     * @return EstudianteNotaModificacion
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
     * Set remitente
     *
     * @param string $remitente
     * @return EstudianteNotaModificacion
     */
    public function setRemitente($remitente)
    {
        $this->remitente = $remitente;
    
        return $this;
    }

    /**
     * Get remitente
     *
     * @return string 
     */
    public function getRemitente()
    {
        return $this->remitente;
    }

    /**
     * Set receptor
     *
     * @param string $receptor
     * @return EstudianteNotaModificacion
     */
    public function setReceptor($receptor)
    {
        $this->receptor = $receptor;
    
        return $this;
    }

    /**
     * Get receptor
     *
     * @return string 
     */
    public function getReceptor()
    {
        return $this->receptor;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return EstudianteNotaModificacion
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
     * Set hora
     *
     * @param \DateTime $hora
     * @return EstudianteNotaModificacion
     */
    public function setHora($hora)
    {
        $this->hora = $hora;
    
        return $this;
    }

    /**
     * Get hora
     *
     * @return \DateTime 
     */
    public function getHora()
    {
        return $this->hora;
    }

    /**
     * Set estudianteInscripcionId
     *
     * @param integer $estudianteInscripcionId
     * @return EstudianteNotaModificacion
     */
    public function setEstudianteInscripcionId($estudianteInscripcionId)
    {
        $this->estudianteInscripcionId = $estudianteInscripcionId;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionId
     *
     * @return integer 
     */
    public function getEstudianteInscripcionId()
    {
        return $this->estudianteInscripcionId;
    }

    /**
     * Set detalle
     *
     * @param string $detalle
     * @return EstudianteNotaModificacion
     */
    public function setDetalle($detalle)
    {
        $this->detalle = $detalle;
    
        return $this;
    }

    /**
     * Get detalle
     *
     * @return string 
     */
    public function getDetalle()
    {
        return $this->detalle;
    }

    /**
     * Set motivo
     *
     * @param string $motivo
     * @return EstudianteNotaModificacion
     */
    public function setMotivo($motivo)
    {
        $this->motivo = $motivo;
    
        return $this;
    }

    /**
     * Get motivo
     *
     * @return string 
     */
    public function getMotivo()
    {
        return $this->motivo;
    }

    /**
     * Set estado
     *
     * @param integer $estado
     * @return EstudianteNotaModificacion
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return integer 
     */
    public function getEstado()
    {
        return $this->estado;
    }
}
