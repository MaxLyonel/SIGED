<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SangreTipo
 */
class SangreTipo {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $grupoSanguineo;

    public function __toString() {
        return $this->grupoSanguineo;
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
     * Set grupoSanguineo
     *
     * @param string $grupoSanguineo
     * @return SangreTipo
     */
    public function setGrupoSanguineo($grupoSanguineo) {
        $this->grupoSanguineo = $grupoSanguineo;

        return $this;
    }

    /**
     * Get grupoSanguineo
     *
     * @return string 
     */
    public function getGrupoSanguineo() {
        return $this->grupoSanguineo;
    }

}
