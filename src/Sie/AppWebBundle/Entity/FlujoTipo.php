<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FlujoTipo
 */
class FlujoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $flujo;

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
     * Set flujo
     *
     * @param string $flujo
     * @return FlujoTipo
     */
    public function setFlujo($flujo)
    {
        $this->flujo = $flujo;
    
        return $this;
    }

    /**
     * Get flujo
     *
     * @return string 
     */
    public function getFlujo()
    {
        return $this->flujo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return FlujoTipo
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
