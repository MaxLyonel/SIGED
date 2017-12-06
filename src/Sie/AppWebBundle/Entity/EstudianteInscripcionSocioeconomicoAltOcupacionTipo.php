<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoAltOcupacionTipo
 */
class EstudianteInscripcionSocioeconomicoAltOcupacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $ocupacion;

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
     * Set ocupacion
     *
     * @param string $ocupacion
     * @return EstudianteInscripcionSocioeconomicoAltOcupacionTipo
     */
    public function setOcupacion($ocupacion)
    {
        $this->ocupacion = $ocupacion;
    
        return $this;
    }

    /**
     * Get ocupacion
     *
     * @return string 
     */
    public function getOcupacion()
    {
        return $this->ocupacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoAltOcupacionTipo
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
