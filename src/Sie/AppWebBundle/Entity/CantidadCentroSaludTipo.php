<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CantidadCentroSaludTipo
 */
class CantidadCentroSaludTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcionCantidad;

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
    private $fechaModificaion;

    public function __toString(){
        return $this->descripcionCantidad;
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
     * Set descripcionCantidad
     *
     * @param string $descripcionCantidad
     * @return CantidadCentroSaludTipo
     */
    public function setDescripcionCantidad($descripcionCantidad)
    {
        $this->descripcionCantidad = $descripcionCantidad;
    
        return $this;
    }

    /**
     * Get descripcionCantidad
     *
     * @return string 
     */
    public function getDescripcionCantidad()
    {
        return $this->descripcionCantidad;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return CantidadCentroSaludTipo
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
     * @return CantidadCentroSaludTipo
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
     * Set fechaModificaion
     *
     * @param \DateTime $fechaModificaion
     * @return CantidadCentroSaludTipo
     */
    public function setFechaModificaion($fechaModificaion)
    {
        $this->fechaModificaion = $fechaModificaion;
    
        return $this;
    }

    /**
     * Get fechaModificaion
     *
     * @return \DateTime 
     */
    public function getFechaModificaion()
    {
        return $this->fechaModificaion;
    }
}
