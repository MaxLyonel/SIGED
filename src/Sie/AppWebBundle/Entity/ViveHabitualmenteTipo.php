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
    private $descripcionViveHabitualmente;

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
        return $this->descripcionViveHabitualmente;
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
     * Set descripcionViveHabitualmente
     *
     * @param string $descripcionViveHabitualmente
     * @return ViveHabitualmenteTipo
     */
    public function setDescripcionViveHabitualmente($descripcionViveHabitualmente)
    {
        $this->descripcionViveHabitualmente = $descripcionViveHabitualmente;
    
        return $this;
    }

    /**
     * Get descripcionViveHabitualmente
     *
     * @return string 
     */
    public function getDescripcionViveHabitualmente()
    {
        return $this->descripcionViveHabitualmente;
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
