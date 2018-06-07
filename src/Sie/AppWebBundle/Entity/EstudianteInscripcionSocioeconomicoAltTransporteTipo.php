<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoAltTransporteTipo
 */
class EstudianteInscripcionSocioeconomicoAltTransporteTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $transporte;

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
     * Set transporte
     *
     * @param string $transporte
     * @return EstudianteInscripcionSocioeconomicoAltTransporteTipo
     */
    public function setTransporte($transporte)
    {
        $this->transporte = $transporte;
    
        return $this;
    }

    /**
     * Get transporte
     *
     * @return string 
     */
    public function getTransporte()
    {
        return $this->transporte;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoAltTransporteTipo
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
