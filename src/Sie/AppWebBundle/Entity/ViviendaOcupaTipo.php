<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ViviendaOcupaTipo
 */
class ViviendaOcupaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcionViviendaOcupa;

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

    public function __toString(){
        return $this->descripcionViviendaOcupa;
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
     * Set descripcionViviendaOcupa
     *
     * @param string $descripcionViviendaOcupa
     * @return ViviendaOcupaTipo
     */
    public function setDescripcionViviendaOcupa($descripcionViviendaOcupa)
    {
        $this->descripcionViviendaOcupa = $descripcionViviendaOcupa;
    
        return $this;
    }

    /**
     * Get descripcionViviendaOcupa
     *
     * @return string 
     */
    public function getDescripcionViviendaOcupa()
    {
        return $this->descripcionViviendaOcupa;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return ViviendaOcupaTipo
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
     * @return ViviendaOcupaTipo
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
     * @return ViviendaOcupaTipo
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
