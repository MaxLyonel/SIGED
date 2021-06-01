<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstadosaludTipo
 */
class EstadosaludTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $estadosalud;

    /**
     * @var boolean
     */
    private $esactivo;


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
     * Set estadosalud
     *
     * @param string $estadosalud
     * @return EstadosaludTipo
     */
    public function setEstadosalud($estadosalud)
    {
        $this->estadosalud = $estadosalud;
    
        return $this;
    }

    /**
     * Get estadosalud
     *
     * @return string 
     */
    public function getEstadosalud()
    {
        return $this->estadosalud;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return EstadosaludTipo
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }
}
