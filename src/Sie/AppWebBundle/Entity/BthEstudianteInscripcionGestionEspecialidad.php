<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BthEstudianteInscripcionGestionEspecialidad
 */
class BthEstudianteInscripcionGestionEspecialidad
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $data;

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
    private $usuarioId;

    /**
     * @var string
     */
    private $rutaArchivo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\OperativoGestionEspecialidadTipo
     */
    private $operativoGestionEspecialidadTipo;


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
     * Set data
     *
     * @param string $data
     * @return BthEstudianteInscripcionGestionEspecialidad
     */
    public function setData($data)
    {
        $this->data = $data;
    
        return $this;
    }

    /**
     * Get data
     *
     * @return string 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return BthEstudianteInscripcionGestionEspecialidad
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
     * @return BthEstudianteInscripcionGestionEspecialidad
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
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return BthEstudianteInscripcionGestionEspecialidad
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
     * Set rutaArchivo
     *
     * @param string $rutaArchivo
     * @return BthEstudianteInscripcionGestionEspecialidad
     */
    public function setRutaArchivo($rutaArchivo)
    {
        $this->rutaArchivo = $rutaArchivo;
    
        return $this;
    }

    /**
     * Get rutaArchivo
     *
     * @return string 
     */
    public function getRutaArchivo()
    {
        return $this->rutaArchivo;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return BthEstudianteInscripcionGestionEspecialidad
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
     * Set operativoGestionEspecialidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\OperativoGestionEspecialidadTipo $operativoGestionEspecialidadTipo
     * @return BthEstudianteInscripcionGestionEspecialidad
     */
    public function setOperativoGestionEspecialidadTipo(\Sie\AppWebBundle\Entity\OperativoGestionEspecialidadTipo $operativoGestionEspecialidadTipo = null)
    {
        $this->operativoGestionEspecialidadTipo = $operativoGestionEspecialidadTipo;
    
        return $this;
    }

    /**
     * Get operativoGestionEspecialidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\OperativoGestionEspecialidadTipo 
     */
    public function getOperativoGestionEspecialidadTipo()
    {
        return $this->operativoGestionEspecialidadTipo;
    }
    /**
     * @var string
     */
    private $justificativo;


    /**
     * Set justificativo
     *
     * @param string $justificativo
     * @return BthEstudianteInscripcionGestionEspecialidad
     */
    public function setJustificativo($justificativo)
    {
        $this->justificativo = $justificativo;
    
        return $this;
    }

    /**
     * Get justificativo
     *
     * @return string 
     */
    public function getJustificativo()
    {
        return $this->justificativo;
    }
}
