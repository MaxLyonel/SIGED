<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GeneroTipo
 */
class GeneroTipo {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $genero;

    /**
     * @var string
     */
    private $obs;

    public function __toString() {
        return $this->genero;
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
     * Set genero
     *
     * @param string $genero
     * @return GeneroTipo
     */
    public function setGenero($genero) {
        $this->genero = $genero;

        return $this;
    }

    /**
     * Get genero
     *
     * @return string 
     */
    public function getGenero() {
        return $this->genero;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return GeneroTipo
     */
    public function setObs($obs) {
        $this->obs = $obs;

        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs() {
        return $this->obs;
    }

}
