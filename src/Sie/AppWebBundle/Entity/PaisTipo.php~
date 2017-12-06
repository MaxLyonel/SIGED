<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PaisTipo
 */
class PaisTipo {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $pais;

    /**
     * @var string
     */
    private $descAbreviada;

    public function __toString() {
        return $this->pais;
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
     * Set pais
     *
     * @param string $pais
     * @return PaisTipo
     */
    public function setPais($pais) {
        $this->pais = $pais;

        return $this;
    }

    /**
     * Get pais
     *
     * @return string 
     */
    public function getPais() {
        return $this->pais;
    }

    /**
     * Set descAbreviada
     *
     * @param string $descAbreviada
     * @return PaisTipo
     */
    public function setDescAbreviada($descAbreviada) {
        $this->descAbreviada = $descAbreviada;

        return $this;
    }

    /**
     * Get descAbreviada
     *
     * @return string 
     */
    public function getDescAbreviada() {
        return $this->descAbreviada;
    }

}
