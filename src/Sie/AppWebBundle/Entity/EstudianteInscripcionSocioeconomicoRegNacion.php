<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegNacion
 */
class EstudianteInscripcionSocioeconomicoRegNacion
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
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegular
     */
    private $estudianteInscripcionSocioeconomicoRegular;

    /**
     * @var \Sie\AppWebBundle\Entity\NacionOriginariaTipo
     */
    private $nacionOriginariaTipo;


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
     * @return EstudianteInscripcionSocioeconomicoRegNacion
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
     * @return EstudianteInscripcionSocioeconomicoRegNacion
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
     * @return EstudianteInscripcionSocioeconomicoRegNacion
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
     * Set estudianteInscripcionSocioeconomicoRegular
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegular $estudianteInscripcionSocioeconomicoRegular
     * @return EstudianteInscripcionSocioeconomicoRegNacion
     */
    public function setEstudianteInscripcionSocioeconomicoRegular(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegular $estudianteInscripcionSocioeconomicoRegular = null)
    {
        $this->estudianteInscripcionSocioeconomicoRegular = $estudianteInscripcionSocioeconomicoRegular;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionSocioeconomicoRegular
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegular 
     */
    public function getEstudianteInscripcionSocioeconomicoRegular()
    {
        return $this->estudianteInscripcionSocioeconomicoRegular;
    }

    /**
     * Set nacionOriginariaTipo
     *
     * @param \Sie\AppWebBundle\Entity\NacionOriginariaTipo $nacionOriginariaTipo
     * @return EstudianteInscripcionSocioeconomicoRegNacion
     */
    public function setNacionOriginariaTipo(\Sie\AppWebBundle\Entity\NacionOriginariaTipo $nacionOriginariaTipo = null)
    {
        $this->nacionOriginariaTipo = $nacionOriginariaTipo;
    
        return $this;
    }

    /**
     * Get nacionOriginariaTipo
     *
     * @return \Sie\AppWebBundle\Entity\NacionOriginariaTipo 
     */
    public function getNacionOriginariaTipo()
    {
        return $this->nacionOriginariaTipo;
    }
}
