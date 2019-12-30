<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteHistorialModificacion
 */
class EstudianteHistorialModificacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $datoAnterior;

    /**
     * @var string
     */
    private $resolucion;

    /**
     * @var \DateTime
     */
    private $fechaResolucion;

    /**
     * @var string
     */
    private $justificacion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;


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
     * Set datoAnterior
     *
     * @param string $datoAnterior
     * @return EstudianteHistorialModificacion
     */
    public function setDatoAnterior($datoAnterior)
    {
        $this->datoAnterior = $datoAnterior;
    
        return $this;
    }

    /**
     * Get datoAnterior
     *
     * @return string 
     */
    public function getDatoAnterior()
    {
        return $this->datoAnterior;
    }

    /**
     * Set resolucion
     *
     * @param string $resolucion
     * @return EstudianteHistorialModificacion
     */
    public function setResolucion($resolucion)
    {
        $this->resolucion = $resolucion;
    
        return $this;
    }

    /**
     * Get resolucion
     *
     * @return string 
     */
    public function getResolucion()
    {
        return $this->resolucion;
    }

    /**
     * Set fechaResolucion
     *
     * @param \DateTime $fechaResolucion
     * @return EstudianteHistorialModificacion
     */
    public function setFechaResolucion($fechaResolucion)
    {
        $this->fechaResolucion = $fechaResolucion;
    
        return $this;
    }

    /**
     * Get fechaResolucion
     *
     * @return \DateTime 
     */
    public function getFechaResolucion()
    {
        return $this->fechaResolucion;
    }

    /**
     * Set justificacion
     *
     * @param string $justificacion
     * @return EstudianteHistorialModificacion
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteHistorialModificacion
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
     * @return EstudianteHistorialModificacion
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
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return EstudianteHistorialModificacion
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
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return EstudianteHistorialModificacion
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
     * @var string
     */
    private $urlDocumento;


    /**
     * Set urlDocumento
     *
     * @param string $urlDocumento
     * @return EstudianteHistorialModificacion
     */
    public function setUrlDocumento($urlDocumento)
    {
        $this->urlDocumento = $urlDocumento;
    
        return $this;
    }

    /**
     * Get urlDocumento
     *
     * @return string 
     */
    public function getUrlDocumento()
    {
        return $this->urlDocumento;
    }
}
