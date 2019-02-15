<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionEspecialEtapa
 */
class EstudianteInscripcionEspecialEtapa
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var integer
     */
    private $usuarioRegistroId;

    /**
     * @var integer
     */
    private $usuarioModificacionId;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionEspecial
     */
    private $estudianteInscripcionEspecial;

    /**
     * @var \Sie\AppWebBundle\Entity\NotaTipo
     */
    private $notaTipo;


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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return EstudianteInscripcionEspecialEtapa
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    
        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime 
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     * @return EstudianteInscripcionEspecialEtapa
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    
        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime 
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set usuarioRegistroId
     *
     * @param integer $usuarioRegistroId
     * @return EstudianteInscripcionEspecialEtapa
     */
    public function setUsuarioRegistroId($usuarioRegistroId)
    {
        $this->usuarioRegistroId = $usuarioRegistroId;
    
        return $this;
    }

    /**
     * Get usuarioRegistroId
     *
     * @return integer 
     */
    public function getUsuarioRegistroId()
    {
        return $this->usuarioRegistroId;
    }

    /**
     * Set usuarioModificacionId
     *
     * @param integer $usuarioModificacionId
     * @return EstudianteInscripcionEspecialEtapa
     */
    public function setUsuarioModificacionId($usuarioModificacionId)
    {
        $this->usuarioModificacionId = $usuarioModificacionId;
    
        return $this;
    }

    /**
     * Get usuarioModificacionId
     *
     * @return integer 
     */
    public function getUsuarioModificacionId()
    {
        return $this->usuarioModificacionId;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteInscripcionEspecialEtapa
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
     * @return EstudianteInscripcionEspecialEtapa
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
     * Set estudianteInscripcionEspecial
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionEspecial $estudianteInscripcionEspecial
     * @return EstudianteInscripcionEspecialEtapa
     */
    public function setEstudianteInscripcionEspecial(\Sie\AppWebBundle\Entity\EstudianteInscripcionEspecial $estudianteInscripcionEspecial = null)
    {
        $this->estudianteInscripcionEspecial = $estudianteInscripcionEspecial;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionEspecial
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionEspecial 
     */
    public function getEstudianteInscripcionEspecial()
    {
        return $this->estudianteInscripcionEspecial;
    }

    /**
     * Set notaTipo
     *
     * @param \Sie\AppWebBundle\Entity\NotaTipo $notaTipo
     * @return EstudianteInscripcionEspecialEtapa
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
}
