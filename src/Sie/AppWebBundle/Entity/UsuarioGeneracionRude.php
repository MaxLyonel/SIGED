<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UsuarioGeneracionRude
 */
class UsuarioGeneracionRude
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
    private $datosCreacion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;


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
     * @return UsuarioGeneracionRude
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
     * Set datosCreacion
     *
     * @param string $datosCreacion
     * @return UsuarioGeneracionRude
     */
    public function setDatosCreacion($datosCreacion)
    {
        $this->datosCreacion = $datosCreacion;
    
        return $this;
    }

    /**
     * Get datosCreacion
     *
     * @return string 
     */
    public function getDatosCreacion()
    {
        return $this->datosCreacion;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return UsuarioGeneracionRude
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
}
