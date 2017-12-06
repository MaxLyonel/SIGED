<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialidadTipoHumnisticoTecnico
 */
class EspecialidadTipoHumnisticoTecnico {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $especialidad;

    public function __toString() {
        return $this->especialidad;
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
     * Set especialidad
     *
     * @param string $especialidad
     * @return EspecialidadTipoHumnisticoTecnico
     */
    public function setEspecialidad($especialidad) {
        $this->especialidad = $especialidad;

        return $this;
    }

    /**
     * Get especialidad
     *
     * @return string 
     */
    public function getEspecialidad() {
        return $this->especialidad;
    }

}
