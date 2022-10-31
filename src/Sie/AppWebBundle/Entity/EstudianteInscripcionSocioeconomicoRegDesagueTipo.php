<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegDesagueTipo
 */
class EstudianteInscripcionSocioeconomicoRegDesagueTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $desague;

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
     * Set desague
     *
     * @param string $desague
     * @return EstudianteInscripcionSocioeconomicoRegDesagueTipo
     */
    public function setDesague($desague)
    {
        $this->desague = $desague;
    
        return $this;
    }

    /**
     * Get desague
     *
     * @return string 
     */
    public function getDesague()
    {
        return $this->desague;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoRegDesagueTipo
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
