<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TiempoMaximoTrayectoTipo
 */
class TiempoMaximoTrayectoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcionTiempoMaxTipo;

    /**
     * @var boolean
     */
    private $esVigente;

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
     * Set descripcionTiempoMaxTipo
     *
     * @param string $descripcionTiempoMaxTipo
     * @return TiempoMaximoTrayectoTipo
     */
    public function setDescripcionTiempoMaxTipo($descripcionTiempoMaxTipo)
    {
        $this->descripcionTiempoMaxTipo = $descripcionTiempoMaxTipo;
    
        return $this;
    }

    /**
     * Get descripcionTiempoMaxTipo
     *
     * @return string 
     */
    public function getDescripcionTiempoMaxTipo()
    {
        return $this->descripcionTiempoMaxTipo;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return TiempoMaximoTrayectoTipo
     */
    public function setEsVigente($esVigente)
    {
        $this->esVigente = $esVigente;
    
        return $this;
    }

    /**
     * Get esVigente
     *
     * @return boolean 
     */
    public function getEsVigente()
    {
        return $this->esVigente;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TiempoMaximoTrayectoTipo
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
     * @return TiempoMaximoTrayectoTipo
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
