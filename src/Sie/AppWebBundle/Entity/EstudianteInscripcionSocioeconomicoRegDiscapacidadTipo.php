<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo
 */
class EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $discapacitadTipo;

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
     * Set discapacitadTipo
     *
     * @param string $discapacitadTipo
     * @return EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo
     */
    public function setDiscapacitadTipo($discapacitadTipo)
    {
        $this->discapacitadTipo = $discapacitadTipo;
    
        return $this;
    }

    /**
     * Get discapacitadTipo
     *
     * @return string 
     */
    public function getDiscapacitadTipo()
    {
        return $this->discapacitadTipo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo
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
