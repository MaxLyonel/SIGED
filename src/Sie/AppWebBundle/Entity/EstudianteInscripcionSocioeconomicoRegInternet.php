<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegInternet
 */
class EstudianteInscripcionSocioeconomicoRegInternet
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
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegInternetTipo
     */
    private $estudianteInscripcionSocioeconomicoRegInternetTipo;


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
     * @return EstudianteInscripcionSocioeconomicoRegInternet
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
     * @return EstudianteInscripcionSocioeconomicoRegInternet
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
     * @return EstudianteInscripcionSocioeconomicoRegInternet
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
     * @return EstudianteInscripcionSocioeconomicoRegInternet
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
     * Set estudianteInscripcionSocioeconomicoRegInternetTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegInternetTipo $estudianteInscripcionSocioeconomicoRegInternetTipo
     * @return EstudianteInscripcionSocioeconomicoRegInternet
     */
    public function setEstudianteInscripcionSocioeconomicoRegInternetTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegInternetTipo $estudianteInscripcionSocioeconomicoRegInternetTipo = null)
    {
        $this->estudianteInscripcionSocioeconomicoRegInternetTipo = $estudianteInscripcionSocioeconomicoRegInternetTipo;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionSocioeconomicoRegInternetTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegInternetTipo 
     */
    public function getEstudianteInscripcionSocioeconomicoRegInternetTipo()
    {
        return $this->estudianteInscripcionSocioeconomicoRegInternetTipo;
    }
}
