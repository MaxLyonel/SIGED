<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecResolucionCarrera
 */
class TtecResolucionCarrera
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var string
     */
    private $numero;

    /**
     * @var string
     */
    private $path;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var string
     */
    private $resuelve;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var integer
     */
    private $tiempoEstudio;

    /**
     * @var integer
     */
    private $cargaHoraria;

    /**
     * @var string
     */
    private $operacion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecResolucionTipo
     */
    private $ttecResolucionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecRegimenEstudioTipo
     */
    private $ttecRegimenEstudioTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecInstitucioneducativaCarreraAutorizada
     */
    private $ttecInstitucioneducativaCarreraAutorizada;

    /**
     * @var \Sie\AppWebBundle\Entity\NivelTipo
     */
    private $nivelTipo;


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
     * Set descripcion
     *
     * @param string $descripcion
     * @return TtecResolucionCarrera
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set numero
     *
     * @param string $numero
     * @return TtecResolucionCarrera
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    
        return $this;
    }

    /**
     * Get numero
     *
     * @return string 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return TtecResolucionCarrera
     */
    public function setPath($path)
    {
        $this->path = $path;
    
        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return TtecResolucionCarrera
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
     * Set resuelve
     *
     * @param string $resuelve
     * @return TtecResolucionCarrera
     */
    public function setResuelve($resuelve)
    {
        $this->resuelve = $resuelve;
    
        return $this;
    }

    /**
     * Get resuelve
     *
     * @return string 
     */
    public function getResuelve()
    {
        return $this->resuelve;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecResolucionCarrera
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
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return TtecResolucionCarrera
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
     * Set tiempoEstudio
     *
     * @param integer $tiempoEstudio
     * @return TtecResolucionCarrera
     */
    public function setTiempoEstudio($tiempoEstudio)
    {
        $this->tiempoEstudio = $tiempoEstudio;
    
        return $this;
    }

    /**
     * Get tiempoEstudio
     *
     * @return integer 
     */
    public function getTiempoEstudio()
    {
        return $this->tiempoEstudio;
    }

    /**
     * Set cargaHoraria
     *
     * @param integer $cargaHoraria
     * @return TtecResolucionCarrera
     */
    public function setCargaHoraria($cargaHoraria)
    {
        $this->cargaHoraria = $cargaHoraria;
    
        return $this;
    }

    /**
     * Get cargaHoraria
     *
     * @return integer 
     */
    public function getCargaHoraria()
    {
        return $this->cargaHoraria;
    }

    /**
     * Set operacion
     *
     * @param string $operacion
     * @return TtecResolucionCarrera
     */
    public function setOperacion($operacion)
    {
        $this->operacion = $operacion;
    
        return $this;
    }

    /**
     * Get operacion
     *
     * @return string 
     */
    public function getOperacion()
    {
        return $this->operacion;
    }

    /**
     * Set ttecResolucionTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecResolucionTipo $ttecResolucionTipo
     * @return TtecResolucionCarrera
     */
    public function setTtecResolucionTipo(\Sie\AppWebBundle\Entity\TtecResolucionTipo $ttecResolucionTipo = null)
    {
        $this->ttecResolucionTipo = $ttecResolucionTipo;
    
        return $this;
    }

    /**
     * Get ttecResolucionTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecResolucionTipo 
     */
    public function getTtecResolucionTipo()
    {
        return $this->ttecResolucionTipo;
    }

    /**
     * Set ttecRegimenEstudioTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecRegimenEstudioTipo $ttecRegimenEstudioTipo
     * @return TtecResolucionCarrera
     */
    public function setTtecRegimenEstudioTipo(\Sie\AppWebBundle\Entity\TtecRegimenEstudioTipo $ttecRegimenEstudioTipo = null)
    {
        $this->ttecRegimenEstudioTipo = $ttecRegimenEstudioTipo;
    
        return $this;
    }

    /**
     * Get ttecRegimenEstudioTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecRegimenEstudioTipo 
     */
    public function getTtecRegimenEstudioTipo()
    {
        return $this->ttecRegimenEstudioTipo;
    }

    /**
     * Set ttecInstitucioneducativaCarreraAutorizada
     *
     * @param \Sie\AppWebBundle\Entity\TtecInstitucioneducativaCarreraAutorizada $ttecInstitucioneducativaCarreraAutorizada
     * @return TtecResolucionCarrera
     */
    public function setTtecInstitucioneducativaCarreraAutorizada(\Sie\AppWebBundle\Entity\TtecInstitucioneducativaCarreraAutorizada $ttecInstitucioneducativaCarreraAutorizada = null)
    {
        $this->ttecInstitucioneducativaCarreraAutorizada = $ttecInstitucioneducativaCarreraAutorizada;
    
        return $this;
    }

    /**
     * Get ttecInstitucioneducativaCarreraAutorizada
     *
     * @return \Sie\AppWebBundle\Entity\TtecInstitucioneducativaCarreraAutorizada 
     */
    public function getTtecInstitucioneducativaCarreraAutorizada()
    {
        return $this->ttecInstitucioneducativaCarreraAutorizada;
    }

    /**
     * Set nivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\NivelTipo $nivelTipo
     * @return TtecResolucionCarrera
     */
    public function setNivelTipo(\Sie\AppWebBundle\Entity\NivelTipo $nivelTipo = null)
    {
        $this->nivelTipo = $nivelTipo;
    
        return $this;
    }

    /**
     * Get nivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\NivelTipo 
     */
    public function getNivelTipo()
    {
        return $this->nivelTipo;
    }
}
