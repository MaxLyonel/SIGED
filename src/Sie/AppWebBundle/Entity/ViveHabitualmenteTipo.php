<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ViveHabitualmenteTipo
 */
class ViveHabitualmenteTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcionViveHabitualmenet;

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
     * Set descripcionViveHabitualmenet
     *
     * @param string $descripcionViveHabitualmenet
     * @return ViveHabitualmenteTipo
     */
    public function setDescripcionViveHabitualmenet($descripcionViveHabitualmenet)
    {
        $this->descripcionViveHabitualmenet = $descripcionViveHabitualmenet;
    
        return $this;
    }

    /**
     * Get descripcionViveHabitualmenet
     *
     * @return string 
     */
    public function getDescripcionViveHabitualmenet()
    {
        return $this->descripcionViveHabitualmenet;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return ViveHabitualmenteTipo
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
     * @return ViveHabitualmenteTipo
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
     * @return ViveHabitualmenteTipo
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
