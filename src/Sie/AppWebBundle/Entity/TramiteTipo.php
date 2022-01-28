<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TramiteTipo
 */
class TramiteTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tramiteTipo;

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
     * Set tramiteTipo
     *
     * @param string $tramiteTipo
     * @return TramiteTipo
     */
    public function setTramiteTipo($tramiteTipo)
    {
        $this->tramiteTipo = $tramiteTipo;
    
        return $this;
    }

    /**
     * Get tramiteTipo
     *
     * @return string 
     */
    public function getTramiteTipo()
    {
        return $this->tramiteTipo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return TramiteTipo
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
