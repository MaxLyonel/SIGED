<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo
 */
class EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $cantCentrosalud;

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
     * Set cantCentrosalud
     *
     * @param string $cantCentrosalud
     * @return EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo
     */
    public function setCantCentrosalud($cantCentrosalud)
    {
        $this->cantCentrosalud = $cantCentrosalud;
    
        return $this;
    }

    /**
     * Get cantCentrosalud
     *
     * @return string 
     */
    public function getCantCentrosalud()
    {
        return $this->cantCentrosalud;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo
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
