<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClaseTablaTipo
 */
class ClaseTablaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tipoTabla;

    /**
     * @var string
     */
    private $observaciones;

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
     * Set tipoTabla
     *
     * @param string $tipoTabla
     * @return ClaseTablaTipo
     */
    public function setTipoTabla($tipoTabla)
    {
        $this->tipoTabla = $tipoTabla;
    
        return $this;
    }

    /**
     * Get tipoTabla
     *
     * @return string 
     */
    public function getTipoTabla()
    {
        return $this->tipoTabla;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return ClaseTablaTipo
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    
        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return ClaseTablaTipo
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
     * @return ClaseTablaTipo
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
