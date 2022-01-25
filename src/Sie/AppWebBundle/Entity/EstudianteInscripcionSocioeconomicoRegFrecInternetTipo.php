<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegFrecInternetTipo
 */
class EstudianteInscripcionSocioeconomicoRegFrecInternetTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $internet;

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
     * Set internet
     *
     * @param string $internet
     * @return EstudianteInscripcionSocioeconomicoRegFrecInternetTipo
     */
    public function setInternet($internet)
    {
        $this->internet = $internet;
    
        return $this;
    }

    /**
     * Get internet
     *
     * @return string 
     */
    public function getInternet()
    {
        return $this->internet;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoRegFrecInternetTipo
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
