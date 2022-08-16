<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BioDesinfeccionRealizadaTipo
 */
class BioDesinfeccionRealizadaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $desinfeccionRealizada;

    /**
     * @var string
     */
    private $obs;


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
     * Set desinfeccionRealizada
     *
     * @param string $desinfeccionRealizada
     * @return BioDesinfeccionRealizadaTipo
     */
    public function setDesinfeccionRealizada($desinfeccionRealizada)
    {
        $this->desinfeccionRealizada = $desinfeccionRealizada;
    
        return $this;
    }

    /**
     * Get desinfeccionRealizada
     *
     * @return string 
     */
    public function getDesinfeccionRealizada()
    {
        return $this->desinfeccionRealizada;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BioDesinfeccionRealizadaTipo
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
