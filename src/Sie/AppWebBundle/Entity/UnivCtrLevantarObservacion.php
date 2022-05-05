<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivCtrLevantarObservacion
 */
class UnivCtrLevantarObservacion
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $habilitacionId;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var string
     */
    private $fecha;

    /**
     * @var string
     */
    private $usuarioId;


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
     * Set habilitacionId
     *
     * @param string $habilitacionId
     * @return UnivCtrLevantarObservacion
     */
    public function setHabilitacionId($habilitacionId)
    {
        $this->habilitacionId = $habilitacionId;
    
        return $this;
    }

    /**
     * Get habilitacionId
     *
     * @return string 
     */
    public function getHabilitacionId()
    {
        return $this->habilitacionId;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return UnivCtrLevantarObservacion
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set fecha
     *
     * @param string $fecha
     * @return UnivCtrLevantarObservacion
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
     * Set usuarioId
     *
     * @param string $usuarioId
     * @return UnivCtrLevantarObservacion
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return string 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }
}
