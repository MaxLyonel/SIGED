<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegTiempotransTipo
 */
class EstudianteInscripcionSocioeconomicoRegTiempotransTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tiempoTransporte;

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
     * Set tiempoTransporte
     *
     * @param string $tiempoTransporte
     * @return EstudianteInscripcionSocioeconomicoRegTiempotransTipo
     */
    public function setTiempoTransporte($tiempoTransporte)
    {
        $this->tiempoTransporte = $tiempoTransporte;
    
        return $this;
    }

    /**
     * Get tiempoTransporte
     *
     * @return string 
     */
    public function getTiempoTransporte()
    {
        return $this->tiempoTransporte;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoRegTiempotransTipo
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
