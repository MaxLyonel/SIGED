<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteHomonimo
 */
class EstudianteHomonimo
{
    /**
     * @var string
     */
    private $justificacion;

    /**
     * @var string
     */
    private $archivo;

    /**
     * @var integer
     */
    private $usuarioId;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudianteHomonimo;


    /**
     * Set justificacion
     *
     * @param string $justificacion
     * @return EstudianteHomonimo
     */
    public function setJustificacion($justificacion)
    {
        $this->justificacion = $justificacion;
    
        return $this;
    }

    /**
     * Get justificacion
     *
     * @return string 
     */
    public function getJustificacion()
    {
        return $this->justificacion;
    }

    /**
     * Set archivo
     *
     * @param string $archivo
     * @return EstudianteHomonimo
     */
    public function setArchivo($archivo)
    {
        $this->archivo = $archivo;
    
        return $this;
    }

    /**
     * Get archivo
     *
     * @return string 
     */
    public function getArchivo()
    {
        return $this->archivo;
    }

    /**
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return EstudianteHomonimo
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteHomonimo
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
     * @return EstudianteHomonimo
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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return EstudianteHomonimo
     */
    public function setEstudiante(\Sie\AppWebBundle\Entity\Estudiante $estudiante = null)
    {
        $this->estudiante = $estudiante;
    
        return $this;
    }

    /**
     * Get estudiante
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getEstudiante()
    {
        return $this->estudiante;
    }

    /**
     * Set estudianteHomonimo
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudianteHomonimo
     * @return EstudianteHomonimo
     */
    public function setEstudianteHomonimo(\Sie\AppWebBundle\Entity\Estudiante $estudianteHomonimo = null)
    {
        $this->estudianteHomonimo = $estudianteHomonimo;
    
        return $this;
    }

    /**
     * Get estudianteHomonimo
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getEstudianteHomonimo()
    {
        return $this->estudianteHomonimo;
    }
}
