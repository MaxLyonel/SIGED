<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5AmbienteadministrativoPicina
 */
class InfraestructuraH5AmbienteadministrativoPicina
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $n25EsTechado;

    /**
     * @var integer
     */
    private $n25Capacidad;

    /**
     * @var integer
     */
    private $n25AreaMt2;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n25PisoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5MaterialTipo
     */
    private $n25PredominanteMaterialTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5Ambienteadministrativo
     */
    private $infraestructuraH5Ambienteadministrativo;


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
     * Set n25EsTechado
     *
     * @param boolean $n25EsTechado
     * @return InfraestructuraH5AmbienteadministrativoPicina
     */
    public function setN25EsTechado($n25EsTechado)
    {
        $this->n25EsTechado = $n25EsTechado;
    
        return $this;
    }

    /**
     * Get n25EsTechado
     *
     * @return boolean 
     */
    public function getN25EsTechado()
    {
        return $this->n25EsTechado;
    }

    /**
     * Set n25Capacidad
     *
     * @param integer $n25Capacidad
     * @return InfraestructuraH5AmbienteadministrativoPicina
     */
    public function setN25Capacidad($n25Capacidad)
    {
        $this->n25Capacidad = $n25Capacidad;
    
        return $this;
    }

    /**
     * Get n25Capacidad
     *
     * @return integer 
     */
    public function getN25Capacidad()
    {
        return $this->n25Capacidad;
    }

    /**
     * Set n25AreaMt2
     *
     * @param integer $n25AreaMt2
     * @return InfraestructuraH5AmbienteadministrativoPicina
     */
    public function setN25AreaMt2($n25AreaMt2)
    {
        $this->n25AreaMt2 = $n25AreaMt2;
    
        return $this;
    }

    /**
     * Get n25AreaMt2
     *
     * @return integer 
     */
    public function getN25AreaMt2()
    {
        return $this->n25AreaMt2;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH5AmbienteadministrativoPicina
     */
    public function setFecharegistro($fecharegistro)
    {
        $this->fecharegistro = $fecharegistro;
    
        return $this;
    }

    /**
     * Get fecharegistro
     *
     * @return \DateTime 
     */
    public function getFecharegistro()
    {
        return $this->fecharegistro;
    }

    /**
     * Set n25PisoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n25PisoEstadogeneralTipo
     * @return InfraestructuraH5AmbienteadministrativoPicina
     */
    public function setN25PisoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n25PisoEstadogeneralTipo = null)
    {
        $this->n25PisoEstadogeneralTipo = $n25PisoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n25PisoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN25PisoEstadogeneralTipo()
    {
        return $this->n25PisoEstadogeneralTipo;
    }

    /**
     * Set n25PredominanteMaterialTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5MaterialTipo $n25PredominanteMaterialTipo
     * @return InfraestructuraH5AmbienteadministrativoPicina
     */
    public function setN25PredominanteMaterialTipo(\Sie\AppWebBundle\Entity\InfraestructuraH5MaterialTipo $n25PredominanteMaterialTipo = null)
    {
        $this->n25PredominanteMaterialTipo = $n25PredominanteMaterialTipo;
    
        return $this;
    }

    /**
     * Get n25PredominanteMaterialTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5MaterialTipo 
     */
    public function getN25PredominanteMaterialTipo()
    {
        return $this->n25PredominanteMaterialTipo;
    }

    /**
     * Set infraestructuraH5Ambienteadministrativo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5Ambienteadministrativo $infraestructuraH5Ambienteadministrativo
     * @return InfraestructuraH5AmbienteadministrativoPicina
     */
    public function setInfraestructuraH5Ambienteadministrativo(\Sie\AppWebBundle\Entity\InfraestructuraH5Ambienteadministrativo $infraestructuraH5Ambienteadministrativo = null)
    {
        $this->infraestructuraH5Ambienteadministrativo = $infraestructuraH5Ambienteadministrativo;
    
        return $this;
    }

    /**
     * Get infraestructuraH5Ambienteadministrativo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5Ambienteadministrativo 
     */
    public function getInfraestructuraH5Ambienteadministrativo()
    {
        return $this->infraestructuraH5Ambienteadministrativo;
    }
}
