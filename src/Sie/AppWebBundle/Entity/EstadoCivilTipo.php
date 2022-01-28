<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstadoCivilTipo
 */
class EstadoCivilTipo {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $estadoCivil;

    /**
     * @var boolean
     */
    private $esactivo;

    public function __toString() {
        return $this->estadoCivil;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set estadoCivil
     *
     * @param string $estadoCivil
     * @return EstadoCivilTipo
     */
    public function setEstadoCivil($estadoCivil) {
        $this->estadoCivil = $estadoCivil;

        return $this;
    }

    /**
     * Get estadoCivil
     *
     * @return string 
     */
    public function getEstadoCivil() {
        return $this->estadoCivil;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return EstadoCivilTipo
     */
    public function setEsactivo($esactivo) {
        $this->esactivo = $esactivo;

        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo() {
        return $this->esactivo;
    }

}
