<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegHablaFrec
 */
class EstudianteInscripcionSocioeconomicoRegHablaFrec
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
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $estudianteInscripcionSocioeconomicoRegHablaFrecTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegular
     */
    private $estudianteInscripcionSocioeconomicoRegular;


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
     * @return EstudianteInscripcionSocioeconomicoRegHablaFrec
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
     * @return EstudianteInscripcionSocioeconomicoRegHablaFrec
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
     * @return EstudianteInscripcionSocioeconomicoRegHablaFrec
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
     * Set estudianteInscripcionSocioeconomicoRegHablaFrecTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $estudianteInscripcionSocioeconomicoRegHablaFrecTipo
     * @return EstudianteInscripcionSocioeconomicoRegHablaFrec
     */
    public function setEstudianteInscripcionSocioeconomicoRegHablaFrecTipo(\Sie\AppWebBundle\Entity\IdiomaTipo $estudianteInscripcionSocioeconomicoRegHablaFrecTipo = null)
    {
        $this->estudianteInscripcionSocioeconomicoRegHablaFrecTipo = $estudianteInscripcionSocioeconomicoRegHablaFrecTipo;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionSocioeconomicoRegHablaFrecTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getEstudianteInscripcionSocioeconomicoRegHablaFrecTipo()
    {
        return $this->estudianteInscripcionSocioeconomicoRegHablaFrecTipo;
    }

    /**
     * Set estudianteInscripcionSocioeconomicoRegular
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegular $estudianteInscripcionSocioeconomicoRegular
     * @return EstudianteInscripcionSocioeconomicoRegHablaFrec
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
}
