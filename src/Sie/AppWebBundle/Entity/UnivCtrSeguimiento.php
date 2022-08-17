<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivCtrSeguimiento
 */
class UnivCtrSeguimiento
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $ci;

    /**
     * @var string
     */
    private $complemento;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var string
     */
    private $paterno;

    /**
     * @var string
     */
    private $materno;

    /**
     * @var string
     */
    private $universidadId;

    /**
     * @var string
     */
    private $universidad;

    /**
     * @var string
     */
    private $sede;

    /**
     * @var string
     */
    private $carrera;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var string
     */
    private $estado;

    /**
     * @var string
     */
    private $usuario;

    /**
     * @var string
     */
    private $fechaRegistro;

    /**
     * @var string
     */
    private $habilitacionId;


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
     * Set ci
     *
     * @param string $ci
     * @return UnivCtrSeguimiento
     */
    public function setCi($ci)
    {
        $this->ci = $ci;
    
        return $this;
    }

    /**
     * Get ci
     *
     * @return string 
     */
    public function getCi()
    {
        return $this->ci;
    }

    /**
     * Set complemento
     *
     * @param string $complemento
     * @return UnivCtrSeguimiento
     */
    public function setComplemento($complemento)
    {
        $this->complemento = $complemento;
    
        return $this;
    }

    /**
     * Get complemento
     *
     * @return string 
     */
    public function getComplemento()
    {
        return $this->complemento;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return UnivCtrSeguimiento
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
     * Set paterno
     *
     * @param string $paterno
     * @return UnivCtrSeguimiento
     */
    public function setPaterno($paterno)
    {
        $this->paterno = $paterno;
    
        return $this;
    }

    /**
     * Get paterno
     *
     * @return string 
     */
    public function getPaterno()
    {
        return $this->paterno;
    }

    /**
     * Set materno
     *
     * @param string $materno
     * @return UnivCtrSeguimiento
     */
    public function setMaterno($materno)
    {
        $this->materno = $materno;
    
        return $this;
    }

    /**
     * Get materno
     *
     * @return string 
     */
    public function getMaterno()
    {
        return $this->materno;
    }

    /**
     * Set universidadId
     *
     * @param string $universidadId
     * @return UnivCtrSeguimiento
     */
    public function setUniversidadId($universidadId)
    {
        $this->universidadId = $universidadId;
    
        return $this;
    }

    /**
     * Get universidadId
     *
     * @return string 
     */
    public function getUniversidadId()
    {
        return $this->universidadId;
    }

    /**
     * Set universidad
     *
     * @param string $universidad
     * @return UnivCtrSeguimiento
     */
    public function setUniversidad($universidad)
    {
        $this->universidad = $universidad;
    
        return $this;
    }

    /**
     * Get universidad
     *
     * @return string 
     */
    public function getUniversidad()
    {
        return $this->universidad;
    }

    /**
     * Set sede
     *
     * @param string $sede
     * @return UnivCtrSeguimiento
     */
    public function setSede($sede)
    {
        $this->sede = $sede;
    
        return $this;
    }

    /**
     * Get sede
     *
     * @return string 
     */
    public function getSede()
    {
        return $this->sede;
    }

    /**
     * Set carrera
     *
     * @param string $carrera
     * @return UnivCtrSeguimiento
     */
    public function setCarrera($carrera)
    {
        $this->carrera = $carrera;
    
        return $this;
    }

    /**
     * Get carrera
     *
     * @return string 
     */
    public function getCarrera()
    {
        return $this->carrera;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return UnivCtrSeguimiento
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
     * Set estado
     *
     * @param string $estado
     * @return UnivCtrSeguimiento
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
     * Set usuario
     *
     * @param string $usuario
     * @return UnivCtrSeguimiento
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return string 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set fechaRegistro
     *
     * @param string $fechaRegistro
     * @return UnivCtrSeguimiento
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return string 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set habilitacionId
     *
     * @param string $habilitacionId
     * @return UnivCtrSeguimiento
     */
    public function setHabilitacionId($habilitacionId)
    {
        $this->habilitacionId = $habilitacionId;
    
        return $this;
    }

    /**
     * Get habilitacionId
     *
     * @return string 
     */
    public function getHabilitacionId()
    {
        return $this->habilitacionId;
    }
}
