<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpFaseTipo
 */
class JdpFaseTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fase;

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
     * Set fase
     *
     * @param string $fase
     * @return JdpFaseTipo
     */
    public function setFase($fase)
    {
        $this->fase = $fase;
    
        return $this;
    }

    /**
     * Get fase
     *
     * @return string 
     */
    public function getFase()
    {
        return $this->fase;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return JdpFaseTipo
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
