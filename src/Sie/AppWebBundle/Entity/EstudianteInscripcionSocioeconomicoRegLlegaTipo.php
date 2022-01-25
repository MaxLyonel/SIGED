<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegLlegaTipo
 */
class EstudianteInscripcionSocioeconomicoRegLlegaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $llega;

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
     * Set llega
     *
     * @param string $llega
     * @return EstudianteInscripcionSocioeconomicoRegLlegaTipo
     */
    public function setLlega($llega)
    {
        $this->llega = $llega;
    
        return $this;
    }

    /**
     * Get llega
     *
     * @return string 
     */
    public function getLlega()
    {
        return $this->llega;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoRegLlegaTipo
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
