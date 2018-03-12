<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimMateriaTipo
 */
class OlimMateriaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $materia;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    public function __toString(){
        return $this->materia;
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
     * Set materia
     *
     * @param string $materia
     * @return OlimMateriaTipo
     */
    public function setMateria($materia)
    {
        $this->materia = $materia;
    
        return $this;
    }

    /**
     * Get materia
     *
     * @return string 
     */
    public function getMateria()
    {
        return $this->materia;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return OlimMateriaTipo
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
}
