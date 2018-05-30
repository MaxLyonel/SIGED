<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoAltAccesoTipo
 */
class EstudianteInscripcionSocioeconomicoAltAccesoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $acceso;

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
     * Set acceso
     *
     * @param string $acceso
     * @return EstudianteInscripcionSocioeconomicoAltAccesoTipo
     */
    public function setAcceso($acceso)
    {
        $this->acceso = $acceso;
    
        return $this;
    }

    /**
     * Get acceso
     *
     * @return string 
     */
    public function getAcceso()
    {
        return $this->acceso;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoAltAccesoTipo
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
