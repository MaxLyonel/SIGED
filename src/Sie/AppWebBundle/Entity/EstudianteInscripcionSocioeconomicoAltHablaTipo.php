<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoAltHablaTipo
 */
class EstudianteInscripcionSocioeconomicoAltHablaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $habla;

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
     * Set habla
     *
     * @param string $habla
     * @return EstudianteInscripcionSocioeconomicoAltHablaTipo
     */
    public function setHabla($habla)
    {
        $this->habla = $habla;
    
        return $this;
    }

    /**
     * Get habla
     *
     * @return string 
     */
    public function getHabla()
    {
        return $this->habla;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoAltHablaTipo
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
