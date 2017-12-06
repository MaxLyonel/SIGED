<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApoderadoTipo
 */
class ApoderadoTipo {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $apoderado;

    public function __toString() {
        return $this->apoderado;
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
     * Set apoderado
     *
     * @param string $apoderado
     * @return ApoderadoTipo
     */
    public function setApoderado($apoderado) {
        $this->apoderado = $apoderado;

        return $this;
    }

    /**
     * Get apoderado
     *
     * @return string 
     */
    public function getApoderado() {
        return $this->apoderado;
    }

}
