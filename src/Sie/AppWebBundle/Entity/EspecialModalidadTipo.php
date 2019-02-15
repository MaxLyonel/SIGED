<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialModalidadTipo
 */
class EspecialModalidadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $modalidad;

    /**
     * @var boolean
     */
    private $esValido;


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
     * Set modalidad
     *
     * @param string $modalidad
     * @return EspecialModalidadTipo
     */
    public function setModalidad($modalidad)
    {
        $this->modalidad = $modalidad;
    
        return $this;
    }

    /**
     * Get modalidad
     *
     * @return string 
     */
    public function getModalidad()
    {
        return $this->modalidad;
    }

    /**
     * Set esValido
     *
     * @param boolean $esValido
     * @return EspecialModalidadTipo
     */
    public function setEsValido($esValido)
    {
        $this->esValido = $esValido;
    
        return $this;
    }

    /**
     * Get esValido
     *
     * @return boolean 
     */
    public function getEsValido()
    {
        return $this->esValido;
    }
}
