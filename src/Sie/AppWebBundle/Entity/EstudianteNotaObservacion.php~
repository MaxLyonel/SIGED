<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteNotaObservacion
 */
class EstudianteNotaObservacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $estudianteNotaObservacion;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteNota
     */
    private $estudianteNota;


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
     * Set estudianteNotaObservacion
     *
     * @param string $estudianteNotaObservacion
     * @return EstudianteNotaObservacion
     */
    public function setEstudianteNotaObservacion($estudianteNotaObservacion)
    {
        $this->estudianteNotaObservacion = $estudianteNotaObservacion;
    
        return $this;
    }

    /**
     * Get estudianteNotaObservacion
     *
     * @return string 
     */
    public function getEstudianteNotaObservacion()
    {
        return $this->estudianteNotaObservacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteNotaObservacion
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

    /**
     * Set estudianteNota
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteNota $estudianteNota
     * @return EstudianteNotaObservacion
     */
    public function setEstudianteNota(\Sie\AppWebBundle\Entity\EstudianteNota $estudianteNota = null)
    {
        $this->estudianteNota = $estudianteNota;
    
        return $this;
    }

    /**
     * Get estudianteNota
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteNota 
     */
    public function getEstudianteNota()
    {
        return $this->estudianteNota;
    }
}
