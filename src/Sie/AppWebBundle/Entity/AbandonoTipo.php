<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AbandonoTipo
 */
class AbandonoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $descripcionAbandono;

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
     * Set descripcionAbandono
     *
     * @param integer $descripcionAbandono
     * @return AbandonoTipo
     */
    public function setDescripcionAbandono($descripcionAbandono)
    {
        $this->descripcionAbandono = $descripcionAbandono;
    
        return $this;
    }

    /**
     * Get descripcionAbandono
     *
     * @return integer 
     */
    public function getDescripcionAbandono()
    {
        return $this->descripcionAbandono;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return AbandonoTipo
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
     * @return AbandonoTipo
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
     * @return AbandonoTipo
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
