<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ValidacionReglaTipo
 */
class ValidacionReglaTipo
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
    private $detalle;

    /**
     * @var string
     */
    private $tablas;

    /**
     * @var string
     */
    private $campos;

    /**
     * @var string
     */
    private $llaves;

    /**
     * @var integer
     */
    private $poderacion;

    /**
     * @var \Sie\AppWebBundle\Entity\ValidacionReglaEntidadTipo
     */
    private $validacionReglaEntidadTipo;


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
     * @return ValidacionReglaTipo
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
     * Set detalle
     *
     * @param string $detalle
     * @return ValidacionReglaTipo
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
     * Set tablas
     *
     * @param string $tablas
     * @return ValidacionReglaTipo
     */
    public function setTablas($tablas)
    {
        $this->tablas = $tablas;
    
        return $this;
    }

    /**
     * Get tablas
     *
     * @return string 
     */
    public function getTablas()
    {
        return $this->tablas;
    }

    /**
     * Set campos
     *
     * @param string $campos
     * @return ValidacionReglaTipo
     */
    public function setCampos($campos)
    {
        $this->campos = $campos;
    
        return $this;
    }

    /**
     * Get campos
     *
     * @return string 
     */
    public function getCampos()
    {
        return $this->campos;
    }

    /**
     * Set llaves
     *
     * @param string $llaves
     * @return ValidacionReglaTipo
     */
    public function setLlaves($llaves)
    {
        $this->llaves = $llaves;
    
        return $this;
    }

    /**
     * Get llaves
     *
     * @return string 
     */
    public function getLlaves()
    {
        return $this->llaves;
    }

    /**
     * Set poderacion
     *
     * @param integer $poderacion
     * @return ValidacionReglaTipo
     */
    public function setPoderacion($poderacion)
    {
        $this->poderacion = $poderacion;
    
        return $this;
    }

    /**
     * Get poderacion
     *
     * @return integer 
     */
    public function getPoderacion()
    {
        return $this->poderacion;
    }

    /**
     * Set validacionReglaEntidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\ValidacionReglaEntidadTipo $validacionReglaEntidadTipo
     * @return ValidacionReglaTipo
     */
    public function setValidacionReglaEntidadTipo(\Sie\AppWebBundle\Entity\ValidacionReglaEntidadTipo $validacionReglaEntidadTipo = null)
    {
        $this->validacionReglaEntidadTipo = $validacionReglaEntidadTipo;
    
        return $this;
    }

    /**
     * Get validacionReglaEntidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\ValidacionReglaEntidadTipo 
     */
    public function getValidacionReglaEntidadTipo()
    {
        return $this->validacionReglaEntidadTipo;
    }
}
