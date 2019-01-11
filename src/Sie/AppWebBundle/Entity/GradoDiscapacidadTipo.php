<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GradoDiscapacidadTipo
 */
class GradoDiscapacidadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $gradoDiscapacidad;

    /**
     * @var boolean
     */
    private $esVigente;

    public function __toString(){
        return $this->gradoDiscapacidad;
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
     * Set gradoDiscapacidad
     *
     * @param string $gradoDiscapacidad
     * @return GradoDiscapacidadTipo
     */
    public function setGradoDiscapacidad($gradoDiscapacidad)
    {
        $this->gradoDiscapacidad = $gradoDiscapacidad;
    
        return $this;
    }

    /**
     * Get gradoDiscapacidad
     *
     * @return string 
     */
    public function getGradoDiscapacidad()
    {
        return $this->gradoDiscapacidad;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return GradoDiscapacidadTipo
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
}
