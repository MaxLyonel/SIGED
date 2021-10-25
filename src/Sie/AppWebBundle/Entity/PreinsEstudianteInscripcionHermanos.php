<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PreinsEstudianteInscripcionHermanos
 */
class PreinsEstudianteInscripcionHermanos
{
    /**
     * @var integer
     */
    private $id;

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
    private $observacion;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion
     */
    private $preinsEstudianteInscripcion;


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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return PreinsEstudianteInscripcionHermanos
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
     * @return PreinsEstudianteInscripcionHermanos
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
     * Set observacion
     *
     * @param string $observacion
     * @return PreinsEstudianteInscripcionHermanos
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return PreinsEstudianteInscripcionHermanos
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
     * Set preinsEstudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion $preinsEstudianteInscripcion
     * @return PreinsEstudianteInscripcionHermanos
     */
    public function setPreinsEstudianteInscripcion(\Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion $preinsEstudianteInscripcion = null)
    {
        $this->preinsEstudianteInscripcion = $preinsEstudianteInscripcion;
    
        return $this;
    }

    /**
     * Get preinsEstudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion 
     */
    public function getPreinsEstudianteInscripcion()
    {
        return $this->preinsEstudianteInscripcion;
    }
}
