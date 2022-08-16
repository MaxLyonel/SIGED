<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstadooperativoTipo
 */
class EstadooperativoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $estadooperativo;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;


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
     * Set estadooperativo
     *
     * @param string $estadooperativo
     * @return EstadooperativoTipo
     */
    public function setEstadooperativo($estadooperativo)
    {
        $this->estadooperativo = $estadooperativo;
    
        return $this;
    }

    /**
     * Get estadooperativo
     *
     * @return string 
     */
    public function getEstadooperativo()
    {
        return $this->estadooperativo;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return EstadooperativoTipo
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstadooperativoTipo
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
     * @return EstadooperativoTipo
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
}
