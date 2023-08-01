<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmparejaPlanillaAsignaturaTipo
 */
class EmparejaPlanillaAsignaturaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaCreacion;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\EmparejaSiePlanilla
     */
    private $emparejaSiePlanilla;

    /**
     * @var \Sie\AppWebBundle\Entity\TurnoTipo
     */
    private $turnoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ParaleloTipo
     */
    private $paraleloTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\NivelTipo
     */
    private $nivelTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GradoTipo
     */
    private $gradoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\AsignaturaTipo
     */
    private $asignaturaTipo;


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
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     * @return EmparejaPlanillaAsignaturaTipo
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    
        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime 
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return EmparejaPlanillaAsignaturaTipo
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set emparejaSiePlanilla
     *
     * @param \Sie\AppWebBundle\Entity\EmparejaSiePlanilla $emparejaSiePlanilla
     * @return EmparejaPlanillaAsignaturaTipo
     */
    public function setEmparejaSiePlanilla(\Sie\AppWebBundle\Entity\EmparejaSiePlanilla $emparejaSiePlanilla = null)
    {
        $this->emparejaSiePlanilla = $emparejaSiePlanilla;
    
        return $this;
    }

    /**
     * Get emparejaSiePlanilla
     *
     * @return \Sie\AppWebBundle\Entity\EmparejaSiePlanilla 
     */
    public function getEmparejaSiePlanilla()
    {
        return $this->emparejaSiePlanilla;
    }

    /**
     * Set turnoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TurnoTipo $turnoTipo
     * @return EmparejaPlanillaAsignaturaTipo
     */
    public function setTurnoTipo(\Sie\AppWebBundle\Entity\TurnoTipo $turnoTipo = null)
    {
        $this->turnoTipo = $turnoTipo;
    
        return $this;
    }

    /**
     * Get turnoTipo
     *
     * @return \Sie\AppWebBundle\Entity\TurnoTipo 
     */
    public function getTurnoTipo()
    {
        return $this->turnoTipo;
    }

    /**
     * Set paraleloTipo
     *
     * @param \Sie\AppWebBundle\Entity\ParaleloTipo $paraleloTipo
     * @return EmparejaPlanillaAsignaturaTipo
     */
    public function setParaleloTipo(\Sie\AppWebBundle\Entity\ParaleloTipo $paraleloTipo = null)
    {
        $this->paraleloTipo = $paraleloTipo;
    
        return $this;
    }

    /**
     * Get paraleloTipo
     *
     * @return \Sie\AppWebBundle\Entity\ParaleloTipo 
     */
    public function getParaleloTipo()
    {
        return $this->paraleloTipo;
    }

    /**
     * Set nivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\NivelTipo $nivelTipo
     * @return EmparejaPlanillaAsignaturaTipo
     */
    public function setNivelTipo(\Sie\AppWebBundle\Entity\NivelTipo $nivelTipo = null)
    {
        $this->nivelTipo = $nivelTipo;
    
        return $this;
    }

    /**
     * Get nivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\NivelTipo 
     */
    public function getNivelTipo()
    {
        return $this->nivelTipo;
    }

    /**
     * Set gradoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GradoTipo $gradoTipo
     * @return EmparejaPlanillaAsignaturaTipo
     */
    public function setGradoTipo(\Sie\AppWebBundle\Entity\GradoTipo $gradoTipo = null)
    {
        $this->gradoTipo = $gradoTipo;
    
        return $this;
    }

    /**
     * Get gradoTipo
     *
     * @return \Sie\AppWebBundle\Entity\GradoTipo 
     */
    public function getGradoTipo()
    {
        return $this->gradoTipo;
    }

    /**
     * Set asignaturaTipo
     *
     * @param \Sie\AppWebBundle\Entity\AsignaturaTipo $asignaturaTipo
     * @return EmparejaPlanillaAsignaturaTipo
     */
    public function setAsignaturaTipo(\Sie\AppWebBundle\Entity\AsignaturaTipo $asignaturaTipo = null)
    {
        $this->asignaturaTipo = $asignaturaTipo;
    
        return $this;
    }

    /**
     * Get asignaturaTipo
     *
     * @return \Sie\AppWebBundle\Entity\AsignaturaTipo 
     */
    public function getAsignaturaTipo()
    {
        return $this->asignaturaTipo;
    }
}
