<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstadomatriculaTipo
 */
class EstadomatriculaTipo {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $estadomatricula;

    /**
     * @var boolean
     */
    private $operativo;

    public function __toString() {
        return $this->estadomatricula;
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
     * Set estadomatricula
     *
     * @param string $estadomatricula
     * @return EstadomatriculaTipo
     */
    public function setEstadomatricula($estadomatricula) {
        $this->estadomatricula = $estadomatricula;

        return $this;
    }

    /**
     * Get estadomatricula
     *
     * @return string 
     */
    public function getEstadomatricula() {
        return $this->estadomatricula;
    }

    /**
     * Set operativo
     *
     * @param boolean $operativo
     * @return EstadomatriculaTipo
     */
    public function setOperativo($operativo) {
        $this->operativo = $operativo;

        return $this;
    }

    /**
     * Get operativo
     *
     * @return boolean 
     */
    public function getOperativo() {
        return $this->operativo;
    }

}
