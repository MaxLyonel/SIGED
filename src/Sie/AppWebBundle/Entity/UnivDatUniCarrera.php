<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatUniCarrera
 */
class UnivDatUniCarrera
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $sedeId;

    /**
     * @var string
     */
    private $carrera;

    /**
     * @var string
     */
    private $resolucion;

    /**
     * @var string
     */
    private $fecha;

    /**
     * @var string
     */
    private $rmApertura;

    /**
     * @var string
     */
    private $fechaApertura;

    /**
     * @var string
     */
    private $gradoAcademicoId;

    /**
     * @var string
     */
    private $duracion;

    /**
     * @var string
     */
    private $colegioId;

    /**
     * @var string
     */
    private $estado;

    /**
     * @var string
     */
    private $createdAt;


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
     * Set sedeId
     *
     * @param string $sedeId
     * @return UnivDatUniCarrera
     */
    public function setSedeId($sedeId)
    {
        $this->sedeId = $sedeId;
    
        return $this;
    }

    /**
     * Get sedeId
     *
     * @return string 
     */
    public function getSedeId()
    {
        return $this->sedeId;
    }

    /**
     * Set carrera
     *
     * @param string $carrera
     * @return UnivDatUniCarrera
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
     * Set resolucion
     *
     * @param string $resolucion
     * @return UnivDatUniCarrera
     */
    public function setResolucion($resolucion)
    {
        $this->resolucion = $resolucion;
    
        return $this;
    }

    /**
     * Get resolucion
     *
     * @return string 
     */
    public function getResolucion()
    {
        return $this->resolucion;
    }

    /**
     * Set fecha
     *
     * @param string $fecha
     * @return UnivDatUniCarrera
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
     * Set rmApertura
     *
     * @param string $rmApertura
     * @return UnivDatUniCarrera
     */
    public function setRmApertura($rmApertura)
    {
        $this->rmApertura = $rmApertura;
    
        return $this;
    }

    /**
     * Get rmApertura
     *
     * @return string 
     */
    public function getRmApertura()
    {
        return $this->rmApertura;
    }

    /**
     * Set fechaApertura
     *
     * @param string $fechaApertura
     * @return UnivDatUniCarrera
     */
    public function setFechaApertura($fechaApertura)
    {
        $this->fechaApertura = $fechaApertura;
    
        return $this;
    }

    /**
     * Get fechaApertura
     *
     * @return string 
     */
    public function getFechaApertura()
    {
        return $this->fechaApertura;
    }

    /**
     * Set gradoAcademicoId
     *
     * @param string $gradoAcademicoId
     * @return UnivDatUniCarrera
     */
    public function setGradoAcademicoId($gradoAcademicoId)
    {
        $this->gradoAcademicoId = $gradoAcademicoId;
    
        return $this;
    }

    /**
     * Get gradoAcademicoId
     *
     * @return string 
     */
    public function getGradoAcademicoId()
    {
        return $this->gradoAcademicoId;
    }

    /**
     * Set duracion
     *
     * @param string $duracion
     * @return UnivDatUniCarrera
     */
    public function setDuracion($duracion)
    {
        $this->duracion = $duracion;
    
        return $this;
    }

    /**
     * Get duracion
     *
     * @return string 
     */
    public function getDuracion()
    {
        return $this->duracion;
    }

    /**
     * Set colegioId
     *
     * @param string $colegioId
     * @return UnivDatUniCarrera
     */
    public function setColegioId($colegioId)
    {
        $this->colegioId = $colegioId;
    
        return $this;
    }

    /**
     * Get colegioId
     *
     * @return string 
     */
    public function getColegioId()
    {
        return $this->colegioId;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return UnivDatUniCarrera
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
     * Set createdAt
     *
     * @param string $createdAt
     * @return UnivDatUniCarrera
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return string 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
