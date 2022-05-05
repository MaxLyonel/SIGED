<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatUniCarreraResolucion
 */
class UnivDatUniCarreraResolucion
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $carreraId;

    /**
     * @var string
     */
    private $resolucion;

    /**
     * @var string
     */
    private $estado;

    /**
     * @var string
     */
    private $fecha;


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
     * Set carreraId
     *
     * @param string $carreraId
     * @return UnivDatUniCarreraResolucion
     */
    public function setCarreraId($carreraId)
    {
        $this->carreraId = $carreraId;
    
        return $this;
    }

    /**
     * Get carreraId
     *
     * @return string 
     */
    public function getCarreraId()
    {
        return $this->carreraId;
    }

    /**
     * Set resolucion
     *
     * @param string $resolucion
     * @return UnivDatUniCarreraResolucion
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
     * Set estado
     *
     * @param string $estado
     * @return UnivDatUniCarreraResolucion
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
     * Set fecha
     *
     * @param string $fecha
     * @return UnivDatUniCarreraResolucion
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
}
