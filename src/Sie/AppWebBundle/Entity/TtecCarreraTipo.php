<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecCarreraTipo
 */
class TtecCarreraTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var integer
     */
    private $tiempoEstudio;

    /**
     * @var string
     */
    private $codigo;

    /**
     * @var integer
     */
    private $cargaHoraria;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecAreaFormacionTipo
     */
    private $ttecAreaFormacionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo
     */
    private $ttecEstadoCarreraTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecRegimenEstudioTipo
     */
    private $ttecRegimenEstudioTipo;

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
     * Set nombre
     *
     * @param string $nombre
     * @return TtecCarreraTipo
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
     * Set tiempoEstudio
     *
     * @param integer $tiempoEstudio
     * @return TtecCarreraTipo
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
     * Set codigo
     *
     * @param string $codigo
     * @return TtecCarreraTipo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set cargaHoraria
     *
     * @param integer $cargaHoraria
     * @return TtecCarreraTipo
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecCarreraTipo
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
     * @return TtecCarreraTipo
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
     * Set ttecAreaFormacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecAreaFormacionTipo $ttecAreaFormacionTipo
     * @return TtecCarreraTipo
     */
    public function setTtecAreaFormacionTipo(\Sie\AppWebBundle\Entity\TtecAreaFormacionTipo $ttecAreaFormacionTipo = null)
    {
        $this->ttecAreaFormacionTipo = $ttecAreaFormacionTipo;
    
        return $this;
    }

    /**
     * Get ttecAreaFormacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecAreaFormacionTipo 
     */
    public function getTtecAreaFormacionTipo()
    {
        return $this->ttecAreaFormacionTipo;
    }

    /**
     * Set ttecEstadoCarreraTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo $ttecEstadoCarreraTipo
     * @return TtecCarreraTipo
     */
    public function setTtecEstadoCarreraTipo(\Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo $ttecEstadoCarreraTipo = null)
    {
        $this->ttecEstadoCarreraTipo = $ttecEstadoCarreraTipo;
    
        return $this;
    }

    /**
     * Get ttecEstadoCarreraTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo 
     */
    public function getTtecEstadoCarreraTipo()
    {
        return $this->ttecEstadoCarreraTipo;
    }

    /**
     * Set ttecRegimenEstudioTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecRegimenEstudioTipo $ttecRegimenEstudioTipo
     * @return TtecCarreraTipo
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
     * Set nivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\NivelTipo $nivelTipo
     * @return TtecCarreraTipo
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
