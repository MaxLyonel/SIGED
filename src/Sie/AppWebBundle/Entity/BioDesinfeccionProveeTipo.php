<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BioDesinfeccionProveeTipo
 */
class BioDesinfeccionProveeTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $desinfeccionProvee;

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
     * Set desinfeccionProvee
     *
     * @param string $desinfeccionProvee
     * @return BioDesinfeccionProveeTipo
     */
    public function setDesinfeccionProvee($desinfeccionProvee)
    {
        $this->desinfeccionProvee = $desinfeccionProvee;
    
        return $this;
    }

    /**
     * Get desinfeccionProvee
     *
     * @return string 
     */
    public function getDesinfeccionProvee()
    {
        return $this->desinfeccionProvee;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BioDesinfeccionProveeTipo
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
