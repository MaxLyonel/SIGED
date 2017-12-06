<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DependenciaTipo
 */
class DependenciaTipo {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $dependencia;

    /**
     * @var integer
     */
    private $idTipoAdministracion;

    public function __toString() {
        return $this->dependencia;
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
     * Set dependencia
     *
     * @param string $dependencia
     * @return DependenciaTipo
     */
    public function setDependencia($dependencia) {
        $this->dependencia = $dependencia;

        return $this;
    }

    /**
     * Get dependencia
     *
     * @return string 
     */
    public function getDependencia() {
        return $this->dependencia;
    }

    /**
     * Set idTipoAdministracion
     *
     * @param integer $idTipoAdministracion
     * @return DependenciaTipo
     */
    public function setIdTipoAdministracion($idTipoAdministracion) {
        $this->idTipoAdministracion = $idTipoAdministracion;

        return $this;
    }

    /**
     * Get idTipoAdministracion
     *
     * @return integer 
     */
    public function getIdTipoAdministracion() {
        return $this->idTipoAdministracion;
    }

}
