<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionEspecialTalento
 */
class EstudianteInscripcionEspecialTalento
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaSolicitud;

    /**
     * @var string
     */
    private $nroInforme;

    /**
     * @var \DateTime
     */
    private $fechaInforme;

    /**
     * @var boolean
     */
    private $esTalento;

    /**
     * @var string
     */
    private $talentoTipo;

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
    private $informe;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioModificacion;


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
     * Set fechaSolicitud
     *
     * @param \DateTime $fechaSolicitud
     * @return EstudianteInscripcionEspecialTalento
     */
    public function setFechaSolicitud($fechaSolicitud)
    {
        $this->fechaSolicitud = $fechaSolicitud;
    
        return $this;
    }

    /**
     * Get fechaSolicitud
     *
     * @return \DateTime 
     */
    public function getFechaSolicitud()
    {
        return $this->fechaSolicitud;
    }

    /**
     * Set nroInforme
     *
     * @param string $nroInforme
     * @return EstudianteInscripcionEspecialTalento
     */
    public function setNroInforme($nroInforme)
    {
        $this->nroInforme = $nroInforme;
    
        return $this;
    }

    /**
     * Get nroInforme
     *
     * @return string 
     */
    public function getNroInforme()
    {
        return $this->nroInforme;
    }

    /**
     * Set fechaInforme
     *
     * @param \DateTime $fechaInforme
     * @return EstudianteInscripcionEspecialTalento
     */
    public function setFechaInforme($fechaInforme)
    {
        $this->fechaInforme = $fechaInforme;
    
        return $this;
    }

    /**
     * Get fechaInforme
     *
     * @return \DateTime 
     */
    public function getFechaInforme()
    {
        return $this->fechaInforme;
    }

    /**
     * Set esTalento
     *
     * @param boolean $esTalento
     * @return EstudianteInscripcionEspecialTalento
     */
    public function setEsTalento($esTalento)
    {
        $this->esTalento = $esTalento;
    
        return $this;
    }

    /**
     * Get esTalento
     *
     * @return boolean 
     */
    public function getEsTalento()
    {
        return $this->esTalento;
    }

    /**
     * Set talentoTipo
     *
     * @param string $talentoTipo
     * @return EstudianteInscripcionEspecialTalento
     */
    public function setTalentoTipo($talentoTipo)
    {
        $this->talentoTipo = $talentoTipo;
    
        return $this;
    }

    /**
     * Get talentoTipo
     *
     * @return string 
     */
    public function getTalentoTipo()
    {
        return $this->talentoTipo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteInscripcionEspecialTalento
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
     * @return EstudianteInscripcionEspecialTalento
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
     * Set informe
     *
     * @param string $informe
     * @return EstudianteInscripcionEspecialTalento
     */
    public function setInforme($informe)
    {
        $this->informe = $informe;
    
        return $this;
    }

    /**
     * Get informe
     *
     * @return string 
     */
    public function getInforme()
    {
        return $this->informe;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EstudianteInscripcionEspecialTalento
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
     * Set usuarioRegistro
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioRegistro
     * @return EstudianteInscripcionEspecialTalento
     */
    public function setUsuarioRegistro(\Sie\AppWebBundle\Entity\Usuario $usuarioRegistro = null)
    {
        $this->usuarioRegistro = $usuarioRegistro;
    
        return $this;
    }

    /**
     * Get usuarioRegistro
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioRegistro()
    {
        return $this->usuarioRegistro;
    }

    /**
     * Set usuarioModificacion
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioModificacion
     * @return EstudianteInscripcionEspecialTalento
     */
    public function setUsuarioModificacion(\Sie\AppWebBundle\Entity\Usuario $usuarioModificacion = null)
    {
        $this->usuarioModificacion = $usuarioModificacion;
    
        return $this;
    }

    /**
     * Get usuarioModificacion
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioModificacion()
    {
        return $this->usuarioModificacion;
    }
}
