<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObservacionInscripcionTipo
 */
class ObservacionInscripcionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $observacion;


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
     * Set observacion
     *
     * @param string $observacion
     * @return ObservacionInscripcionTipo
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }
    /**
     * @var integer
     */
    private $peso;


    /**
     * Set peso
     *
     * @param integer $peso
     * @return ObservacionInscripcionTipo
     */
    public function setPeso($peso)
    {
        $this->peso = $peso;
    
        return $this;
    }

    /**
     * Get peso
     *
     * @return integer 
     */
    public function getPeso()
    {
        return $this->peso;
    }
}
