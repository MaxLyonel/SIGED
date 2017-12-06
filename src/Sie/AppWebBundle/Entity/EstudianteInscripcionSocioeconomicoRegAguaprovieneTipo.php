<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo
 */
class EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $guaproviene;

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
     * Set guaproviene
     *
     * @param string $guaproviene
     * @return EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo
     */
    public function setGuaproviene($guaproviene)
    {
        $this->guaproviene = $guaproviene;
    
        return $this;
    }

    /**
     * Get guaproviene
     *
     * @return string 
     */
    public function getGuaproviene()
    {
        return $this->guaproviene;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo
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
