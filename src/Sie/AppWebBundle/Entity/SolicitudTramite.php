<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SolicitudTramite
 */
class SolicitudTramite
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $datos;

    /**
     * @var integer
     */
    private $codigo;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var boolean
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
     * Set datos
     *
     * @param string $datos
     * @return SolicitudTramite
     */
    public function setDatos($datos)
    {
        $this->datos = $datos;
    
        return $this;
    }

    /**
     * Get datos
     *
     * @return string 
     */
    public function getDatos()
    {
        return $this->datos;
    }

    /**
     * Set codigo
     *
     * @param integer $codigo
     * @return SolicitudTramite
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return integer 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return SolicitudTramite
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
     * Set estado
     *
     * @param boolean $estado
     * @return SolicitudTramite
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return boolean 
     */
    public function getEstado()
    {
        return $this->estado;
    }
}
