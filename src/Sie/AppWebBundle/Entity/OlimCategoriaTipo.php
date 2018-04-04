<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimCategoriaTipo
 */
class OlimCategoriaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $categoria;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    public function __toString(){
        return $this->categoria;
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
     * Set categoria
     *
     * @param string $categoria
     * @return OlimCategoriaTipo
     */
    public function setCategoria($categoria)
    {
        $this->categoria = $categoria;
    
        return $this;
    }

    /**
     * Get categoria
     *
     * @return string 
     */
    public function getCategoria()
    {
        return $this->categoria;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return OlimCategoriaTipo
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
     * @var string
     */
    private $descripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimMateriaTipo
     */
    private $olimMateriaTipo;


    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return OlimCategoriaTipo
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set olimMateriaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimMateriaTipo $olimMateriaTipo
     * @return OlimCategoriaTipo
     */
    public function setOlimMateriaTipo(\Sie\AppWebBundle\Entity\OlimMateriaTipo $olimMateriaTipo = null)
    {
        $this->olimMateriaTipo = $olimMateriaTipo;
    
        return $this;
    }

    /**
     * Get olimMateriaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimMateriaTipo 
     */
    public function getOlimMateriaTipo()
    {
        return $this->olimMateriaTipo;
    }
}
