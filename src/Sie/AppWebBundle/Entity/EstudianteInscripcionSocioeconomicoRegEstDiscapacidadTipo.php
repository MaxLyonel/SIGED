<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegEstDiscapacidadTipo
 */
class EstudianteInscripcionSocioeconomicoRegEstDiscapacidadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $discapacidadEstudianteTipo;

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
     * Set discapacidadEstudianteTipo
     *
     * @param string $discapacidadEstudianteTipo
     * @return EstudianteInscripcionSocioeconomicoRegEstDiscapacidadTipo
     */
    public function setDiscapacidadEstudianteTipo($discapacidadEstudianteTipo)
    {
        $this->discapacidadEstudianteTipo = $discapacidadEstudianteTipo;
    
        return $this;
    }

    /**
     * Get discapacidadEstudianteTipo
     *
     * @return string 
     */
    public function getDiscapacidadEstudianteTipo()
    {
        return $this->discapacidadEstudianteTipo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoRegEstDiscapacidadTipo
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
