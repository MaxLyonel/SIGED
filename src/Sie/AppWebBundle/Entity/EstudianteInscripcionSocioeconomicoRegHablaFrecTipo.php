<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegHablaFrecTipo
 */
class EstudianteInscripcionSocioeconomicoRegHablaFrecTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $hablaFrec;

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
     * Set hablaFrec
     *
     * @param integer $hablaFrec
     * @return EstudianteInscripcionSocioeconomicoRegHablaFrecTipo
     */
    public function setHablaFrec($hablaFrec)
    {
        $this->hablaFrec = $hablaFrec;
    
        return $this;
    }

    /**
     * Get hablaFrec
     *
     * @return integer 
     */
    public function getHablaFrec()
    {
        return $this->hablaFrec;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionSocioeconomicoRegHablaFrecTipo
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
