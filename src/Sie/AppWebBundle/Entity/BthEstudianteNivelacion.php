<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BthEstudianteNivelacion
 */
class BthEstudianteNivelacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteNotaCualitativa
     */
    private $estudianteNotaCualitativa;

    /**
     * @var \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo
     */
    private $especialidadTecnicoHumanisticoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GradoTipo
     */
    private $gradoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\AsignaturaTipo
     */
    private $asignaturaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;


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
     * Set obs
     *
     * @param string $obs
     * @return BthEstudianteNivelacion
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return BthEstudianteNivelacion
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return BthEstudianteNivelacion
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
     * Set estudianteNotaCualitativa
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteNotaCualitativa $estudianteNotaCualitativa
     * @return BthEstudianteNivelacion
     */
    public function setEstudianteNotaCualitativa(\Sie\AppWebBundle\Entity\EstudianteNotaCualitativa $estudianteNotaCualitativa = null)
    {
        $this->estudianteNotaCualitativa = $estudianteNotaCualitativa;
    
        return $this;
    }

    /**
     * Get estudianteNotaCualitativa
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteNotaCualitativa 
     */
    public function getEstudianteNotaCualitativa()
    {
        return $this->estudianteNotaCualitativa;
    }

    /**
     * Set especialidadTecnicoHumanisticoTipo
     *
     * @param \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo $especialidadTecnicoHumanisticoTipo
     * @return BthEstudianteNivelacion
     */
    public function setEspecialidadTecnicoHumanisticoTipo(\Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo $especialidadTecnicoHumanisticoTipo = null)
    {
        $this->especialidadTecnicoHumanisticoTipo = $especialidadTecnicoHumanisticoTipo;
    
        return $this;
    }

    /**
     * Get especialidadTecnicoHumanisticoTipo
     *
     * @return \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo 
     */
    public function getEspecialidadTecnicoHumanisticoTipo()
    {
        return $this->especialidadTecnicoHumanisticoTipo;
    }

    /**
     * Set gradoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GradoTipo $gradoTipo
     * @return BthEstudianteNivelacion
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
     * @return BthEstudianteNivelacion
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

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return BthEstudianteNivelacion
     */
    public function setEstudianteInscripcion(\Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion = null)
    {
        $this->estudianteInscripcion = $estudianteInscripcion;
    
        return $this;
    }

    /**
     * Get estudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcion 
     */
    public function getEstudianteInscripcion()
    {
        return $this->estudianteInscripcion;
    }
}
