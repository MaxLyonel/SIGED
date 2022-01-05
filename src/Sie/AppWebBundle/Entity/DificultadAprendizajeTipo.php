<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DificultadAprendizajeTipo
 */
class DificultadAprendizajeTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $dificultadaprendizaje;

    /**
     * @var string
     */
    private $obs;


    /**
     * Set id
     *
     * @param integer $id
     * @return DificultadAprendizajeTipo
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }

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
     * Set dificultadaprendizaje
     *
     * @param string $dificultadaprendizaje
     * @return DificultadAprendizajeTipo
     */
    public function setDificultadaprendizaje($dificultadaprendizaje)
    {
        $this->dificultadaprendizaje = $dificultadaprendizaje;
    
        return $this;
    }

    /**
     * Get dificultadaprendizaje
     *
     * @return string 
     */
    public function getDificultadaprendizaje()
    {
        return $this->dificultadaprendizaje;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return DificultadAprendizajeTipo
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }
}
