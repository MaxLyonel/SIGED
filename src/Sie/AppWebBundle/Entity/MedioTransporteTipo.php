<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MedioTransporteTipo
 */
class MedioTransporteTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcionMedioTrasnporte;

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
     * Set descripcionMedioTrasnporte
     *
     * @param string $descripcionMedioTrasnporte
     * @return MedioTransporteTipo
     */
    public function setDescripcionMedioTrasnporte($descripcionMedioTrasnporte)
    {
        $this->descripcionMedioTrasnporte = $descripcionMedioTrasnporte;
    
        return $this;
    }

    /**
     * Get descripcionMedioTrasnporte
     *
     * @return string 
     */
    public function getDescripcionMedioTrasnporte()
    {
        return $this->descripcionMedioTrasnporte;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return MedioTransporteTipo
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
     * @return MedioTransporteTipo
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
     * @return MedioTransporteTipo
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
