<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IdiomaMaterno
 */
class IdiomaMaterno {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $idiomaMaterno;

    public function __toString() {
        return $this->idiomaMaterno;
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
     * Set idiomaMaterno
     *
     * @param string $idiomaMaterno
     * @return IdiomaMaterno
     */
    public function setIdiomaMaterno($idiomaMaterno) {
        $this->idiomaMaterno = $idiomaMaterno;

        return $this;
    }

    /**
     * Get idiomaMaterno
     *
     * @return string 
     */
    public function getIdiomaMaterno() {
        return $this->idiomaMaterno;
    }

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaorigenTipo
     */
    private $idiomaorigenTipo;


    /**
     * Set idiomaorigenTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaorigenTipo $idiomaorigenTipo
     * @return IdiomaMaterno
     */
    public function setIdiomaorigenTipo(\Sie\AppWebBundle\Entity\IdiomaorigenTipo $idiomaorigenTipo = null)
    {
        $this->idiomaorigenTipo = $idiomaorigenTipo;
    
        return $this;
    }

    /**
     * Get idiomaorigenTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaorigenTipo 
     */
    public function getIdiomaorigenTipo()
    {
        return $this->idiomaorigenTipo;
    }
}
