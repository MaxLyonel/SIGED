<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HabextrFaseTipo
 */
class HabextrFaseTipo
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
     * Set fase
     *
     * @param string $fase
     * @return HabextrFaseTipo
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
     * Set observacion
     *
     * @param string $observacion
     * @return HabextrFaseTipo
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
}
