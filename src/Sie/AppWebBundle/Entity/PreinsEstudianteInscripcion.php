<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PreinsEstudianteInscripcion
 */
class PreinsEstudianteInscripcion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $municipioVive;

    /**
     * @var string
     */
    private $zonaVive;

    /**
     * @var string
     */
    private $avenidaVive;

    /**
     * @var string
     */
    private $calleVive;

    /**
     * @var string
     */
    private $numeroVive;

    /**
     * @var string
     */
    private $celular;

    /**
     * @var string
     */
    private $telefono;

    /**
     * @var \DateTime
     */
    private $fechaInscripcion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\PreinsInstitucioneducativaCursoCupo
     */
    private $preinsInstitucioneducativaCursoCupo;

    /**
     * @var \Sie\AppWebBundle\Entity\PreinsEstudiante
     */
    private $preinsEstudiante;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadomatriculaTipo
     */
    private $estadomatriculaInicioTipo;


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
     * Set municipioVive
     *
     * @param string $municipioVive
     * @return PreinsEstudianteInscripcion
     */
    public function setMunicipioVive($municipioVive)
    {
        $this->municipioVive = $municipioVive;
    
        return $this;
    }

    /**
     * Get municipioVive
     *
     * @return string 
     */
    public function getMunicipioVive()
    {
        return $this->municipioVive;
    }

    /**
     * Set zonaVive
     *
     * @param string $zonaVive
     * @return PreinsEstudianteInscripcion
     */
    public function setZonaVive($zonaVive)
    {
        $this->zonaVive = $zonaVive;
    
        return $this;
    }

    /**
     * Get zonaVive
     *
     * @return string 
     */
    public function getZonaVive()
    {
        return $this->zonaVive;
    }

    /**
     * Set avenidaVive
     *
     * @param string $avenidaVive
     * @return PreinsEstudianteInscripcion
     */
    public function setAvenidaVive($avenidaVive)
    {
        $this->avenidaVive = $avenidaVive;
    
        return $this;
    }

    /**
     * Get avenidaVive
     *
     * @return string 
     */
    public function getAvenidaVive()
    {
        return $this->avenidaVive;
    }

    /**
     * Set calleVive
     *
     * @param string $calleVive
     * @return PreinsEstudianteInscripcion
     */
    public function setCalleVive($calleVive)
    {
        $this->calleVive = $calleVive;
    
        return $this;
    }

    /**
     * Get calleVive
     *
     * @return string 
     */
    public function getCalleVive()
    {
        return $this->calleVive;
    }

    /**
     * Set numeroVive
     *
     * @param string $numeroVive
     * @return PreinsEstudianteInscripcion
     */
    public function setNumeroVive($numeroVive)
    {
        $this->numeroVive = $numeroVive;
    
        return $this;
    }

    /**
     * Get numeroVive
     *
     * @return string 
     */
    public function getNumeroVive()
    {
        return $this->numeroVive;
    }

    /**
     * Set celular
     *
     * @param string $celular
     * @return PreinsEstudianteInscripcion
     */
    public function setCelular($celular)
    {
        $this->celular = $celular;
    
        return $this;
    }

    /**
     * Get celular
     *
     * @return string 
     */
    public function getCelular()
    {
        return $this->celular;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return PreinsEstudianteInscripcion
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    
        return $this;
    }

    /**
     * Get telefono
     *
     * @return string 
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set fechaInscripcion
     *
     * @param \DateTime $fechaInscripcion
     * @return PreinsEstudianteInscripcion
     */
    public function setFechaInscripcion($fechaInscripcion)
    {
        $this->fechaInscripcion = $fechaInscripcion;
    
        return $this;
    }

    /**
     * Get fechaInscripcion
     *
     * @return \DateTime 
     */
    public function getFechaInscripcion()
    {
        return $this->fechaInscripcion;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return PreinsEstudianteInscripcion
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
     * Set preinsInstitucioneducativaCursoCupo
     *
     * @param \Sie\AppWebBundle\Entity\PreinsInstitucioneducativaCursoCupo $preinsInstitucioneducativaCursoCupo
     * @return PreinsEstudianteInscripcion
     */
    public function setPreinsInstitucioneducativaCursoCupo(\Sie\AppWebBundle\Entity\PreinsInstitucioneducativaCursoCupo $preinsInstitucioneducativaCursoCupo = null)
    {
        $this->preinsInstitucioneducativaCursoCupo = $preinsInstitucioneducativaCursoCupo;
    
        return $this;
    }

    /**
     * Get preinsInstitucioneducativaCursoCupo
     *
     * @return \Sie\AppWebBundle\Entity\PreinsInstitucioneducativaCursoCupo 
     */
    public function getPreinsInstitucioneducativaCursoCupo()
    {
        return $this->preinsInstitucioneducativaCursoCupo;
    }

    /**
     * Set preinsEstudiante
     *
     * @param \Sie\AppWebBundle\Entity\PreinsEstudiante $preinsEstudiante
     * @return PreinsEstudianteInscripcion
     */
    public function setPreinsEstudiante(\Sie\AppWebBundle\Entity\PreinsEstudiante $preinsEstudiante = null)
    {
        $this->preinsEstudiante = $preinsEstudiante;
    
        return $this;
    }

    /**
     * Get preinsEstudiante
     *
     * @return \Sie\AppWebBundle\Entity\PreinsEstudiante 
     */
    public function getPreinsEstudiante()
    {
        return $this->preinsEstudiante;
    }

    /**
     * Set estadomatriculaInicioTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaInicioTipo
     * @return PreinsEstudianteInscripcion
     */
    public function setEstadomatriculaInicioTipo(\Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaInicioTipo = null)
    {
        $this->estadomatriculaInicioTipo = $estadomatriculaInicioTipo;
    
        return $this;
    }

    /**
     * Get estadomatriculaInicioTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstadomatriculaTipo 
     */
    public function getEstadomatriculaInicioTipo()
    {
        return $this->estadomatriculaInicioTipo;
    }
}
