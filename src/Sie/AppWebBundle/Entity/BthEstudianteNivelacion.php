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
     * @var integer
     */
    private $notaCuantitativa;

    /**
     * @var string
     */
    private $docRespaldo;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\NivelTipo
     */
    private $nivelTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GradoTipo
     */
    private $gradoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\NotaTipo
     */
    private $notaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\AsignaturaTipo
     */
    private $asignatura;

    /**
     * @var \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo
     */
    private $especialidadTecnicoHumanistico;


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
     * Set notaCuantitativa
     *
     * @param integer $notaCuantitativa
     * @return BthEstudianteNivelacion
     */
    public function setNotaCuantitativa($notaCuantitativa)
    {
        $this->notaCuantitativa = $notaCuantitativa;
    
        return $this;
    }

    /**
     * Get notaCuantitativa
     *
     * @return integer 
     */
    public function getNotaCuantitativa()
    {
        return $this->notaCuantitativa;
    }

    /**
     * Set docRespaldo
     *
     * @param string $docRespaldo
     * @return BthEstudianteNivelacion
     */
    public function setDocRespaldo($docRespaldo)
    {
        $this->docRespaldo = $docRespaldo;
    
        return $this;
    }

    /**
     * Get docRespaldo
     *
     * @return string 
     */
    public function getDocRespaldo()
    {
        return $this->docRespaldo;
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

    /**
     * Set nivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\NivelTipo $nivelTipo
     * @return BthEstudianteNivelacion
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
     * Set notaTipo
     *
     * @param \Sie\AppWebBundle\Entity\NotaTipo $notaTipo
     * @return BthEstudianteNivelacion
     */
    public function setNotaTipo(\Sie\AppWebBundle\Entity\NotaTipo $notaTipo = null)
    {
        $this->notaTipo = $notaTipo;
    
        return $this;
    }

    /**
     * Get notaTipo
     *
     * @return \Sie\AppWebBundle\Entity\NotaTipo 
     */
    public function getNotaTipo()
    {
        return $this->notaTipo;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return BthEstudianteNivelacion
     */
    public function setUsuario(\Sie\AppWebBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set asignatura
     *
     * @param \Sie\AppWebBundle\Entity\AsignaturaTipo $asignatura
     * @return BthEstudianteNivelacion
     */
    public function setAsignatura(\Sie\AppWebBundle\Entity\AsignaturaTipo $asignatura = null)
    {
        $this->asignatura = $asignatura;
    
        return $this;
    }

    /**
     * Get asignatura
     *
     * @return \Sie\AppWebBundle\Entity\AsignaturaTipo 
     */
    public function getAsignatura()
    {
        return $this->asignatura;
    }

    /**
     * Set especialidadTecnicoHumanistico
     *
     * @param \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo $especialidadTecnicoHumanistico
     * @return BthEstudianteNivelacion
     */
    public function setEspecialidadTecnicoHumanistico(\Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo $especialidadTecnicoHumanistico = null)
    {
        $this->especialidadTecnicoHumanistico = $especialidadTecnicoHumanistico;
    
        return $this;
    }

    /**
     * Get especialidadTecnicoHumanistico
     *
     * @return \Sie\AppWebBundle\Entity\EspecialidadTecnicoHumanisticoTipo 
     */
    public function getEspecialidadTecnicoHumanistico()
    {
        return $this->especialidadTecnicoHumanistico;
    }
}
