<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteNotaSolicitud
 */
class EstudianteNotaSolicitud
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
     * @var string
     */
    private $respuesta;

    /**
     * @var integer
     */
    private $tipo;


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
     * @return EstudianteNotaSolicitud
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
     * @return EstudianteNotaSolicitud
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
     * @return EstudianteNotaSolicitud
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
     * @return EstudianteNotaSolicitud
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
     * @return EstudianteNotaSolicitud
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
     * @return EstudianteNotaSolicitud
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
     * @return EstudianteNotaSolicitud
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
     * @return EstudianteNotaSolicitud
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
     * @return EstudianteNotaSolicitud
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

    /**
     * Set respuesta
     *
     * @param string $respuesta
     * @return EstudianteNotaSolicitud
     */
    public function setRespuesta($respuesta)
    {
        $this->respuesta = $respuesta;
    
        return $this;
    }

    /**
     * Get respuesta
     *
     * @return string 
     */
    public function getRespuesta()
    {
        return $this->respuesta;
    }

    /**
     * Set tipo
     *
     * @param integer $tipo
     * @return EstudianteNotaSolicitud
     */
    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    
        return $this;
    }

    /**
     * Get tipo
     *
     * @return integer 
     */
    public function getTipo()
    {
        return $this->tipo;
    }
    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var integer
     */
    private $gestionTipoId;


    /**
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return EstudianteNotaSolicitud
     */
    public function setInstitucioneducativaId($institucioneducativaId)
    {
        $this->institucioneducativaId = $institucioneducativaId;
    
        return $this;
    }

    /**
     * Get institucioneducativaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaId()
    {
        return $this->institucioneducativaId;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return EstudianteNotaSolicitud
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }
    /**
     * @var integer
     */
    private $usuarioIdResp;

    /**
     * @var \DateTime
     */
    private $fechaResp;

    /**
     * @var integer
     */
    private $departamentoTipoId;


    /**
     * Set usuarioIdResp
     *
     * @param integer $usuarioIdResp
     * @return EstudianteNotaSolicitud
     */
    public function setUsuarioIdResp($usuarioIdResp)
    {
        $this->usuarioIdResp = $usuarioIdResp;
    
        return $this;
    }

    /**
     * Get usuarioIdResp
     *
     * @return integer 
     */
    public function getUsuarioIdResp()
    {
        return $this->usuarioIdResp;
    }

    /**
     * Set fechaResp
     *
     * @param \DateTime $fechaResp
     * @return EstudianteNotaSolicitud
     */
    public function setFechaResp($fechaResp)
    {
        $this->fechaResp = $fechaResp;
    
        return $this;
    }

    /**
     * Get fechaResp
     *
     * @return \DateTime 
     */
    public function getFechaResp()
    {
        return $this->fechaResp;
    }

    /**
     * Set departamentoTipoId
     *
     * @param integer $departamentoTipoId
     * @return EstudianteNotaSolicitud
     */
    public function setDepartamentoTipoId($departamentoTipoId)
    {
        $this->departamentoTipoId = $departamentoTipoId;
    
        return $this;
    }

    /**
     * Get departamentoTipoId
     *
     * @return integer 
     */
    public function getDepartamentoTipoId()
    {
        return $this->departamentoTipoId;
    }
}
