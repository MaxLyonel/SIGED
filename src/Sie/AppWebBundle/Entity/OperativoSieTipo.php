<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OperativoSieTipo
 */
class OperativoSieTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $operativo;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var integer
     */
    private $orden;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    public function __toString(){
        return $this->operativo;
    }

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
     * Set operativo
     *
     * @param string $operativo
     * @return OperativoSieTipo
     */
    public function setOperativo($operativo)
    {
        $this->operativo = $operativo;
    
        return $this;
    }

    /**
     * Get operativo
     *
     * @return string 
     */
    public function getOperativo()
    {
        return $this->operativo;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return OperativoSieTipo
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
     * Set orden
     *
     * @param integer $orden
     * @return OperativoSieTipo
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;
    
        return $this;
    }

    /**
     * Get orden
     *
     * @return integer 
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return OperativoSieTipo
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
     * @return OperativoSieTipo
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
     * @var \Sie\AppWebBundle\Entity\SistemaTipo
     */
    private $sistemaTipo;


    /**
     * Set sistemaTipo
     *
     * @param \Sie\AppWebBundle\Entity\SistemaTipo $sistemaTipo
     * @return OperativoSieTipo
     */
    public function setSistemaTipo(\Sie\AppWebBundle\Entity\SistemaTipo $sistemaTipo = null)
    {
        $this->sistemaTipo = $sistemaTipo;
    
        return $this;
    }

    /**
     * Get sistemaTipo
     *
     * @return \Sie\AppWebBundle\Entity\SistemaTipo 
     */
    public function getSistemaTipo()
    {
        return $this->sistemaTipo;
    }
}
