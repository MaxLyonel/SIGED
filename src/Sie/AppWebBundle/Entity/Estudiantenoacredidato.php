<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Estudiantenoacredidato
 */
class Estudiantenoacredidato
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $carnetIdentidad;

    /**
     * @var string
     */
    private $paterno;

    /**
     * @var string
     */
    private $materno;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var integer
     */
    private $generoTipoId;

    /**
     * @var \DateTime
     */
    private $fechaNacimiento;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $correo;

    /**
     * @var integer
     */
    private $gestion;

    /**
     * @var integer
     */
    private $nivelId;

    /**
     * @var integer
     */
    private $gradoId;

    /**
     * @var string
     */
    private $paralelo;

    /**
     * @var integer
     */
    private $turnoId;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativanoacreditado
     */
    private $institucioneducativanoacreditada;


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
     * Set carnetIdentidad
     *
     * @param string $carnetIdentidad
     * @return Estudiantenoacredidato
     */
    public function setCarnetIdentidad($carnetIdentidad)
    {
        $this->carnetIdentidad = $carnetIdentidad;
    
        return $this;
    }

    /**
     * Get carnetIdentidad
     *
     * @return string 
     */
    public function getCarnetIdentidad()
    {
        return $this->carnetIdentidad;
    }

    /**
     * Set paterno
     *
     * @param string $paterno
     * @return Estudiantenoacredidato
     */
    public function setPaterno($paterno)
    {
        $this->paterno = $paterno;
    
        return $this;
    }

    /**
     * Get paterno
     *
     * @return string 
     */
    public function getPaterno()
    {
        return $this->paterno;
    }

    /**
     * Set materno
     *
     * @param string $materno
     * @return Estudiantenoacredidato
     */
    public function setMaterno($materno)
    {
        $this->materno = $materno;
    
        return $this;
    }

    /**
     * Get materno
     *
     * @return string 
     */
    public function getMaterno()
    {
        return $this->materno;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Estudiantenoacredidato
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set generoTipoId
     *
     * @param integer $generoTipoId
     * @return Estudiantenoacredidato
     */
    public function setGeneroTipoId($generoTipoId)
    {
        $this->generoTipoId = $generoTipoId;
    
        return $this;
    }

    /**
     * Get generoTipoId
     *
     * @return integer 
     */
    public function getGeneroTipoId()
    {
        return $this->generoTipoId;
    }

    /**
     * Set fechaNacimiento
     *
     * @param \DateTime $fechaNacimiento
     * @return Estudiantenoacredidato
     */
    public function setFechaNacimiento($fechaNacimiento)
    {
        $this->fechaNacimiento = $fechaNacimiento;
    
        return $this;
    }

    /**
     * Get fechaNacimiento
     *
     * @return \DateTime 
     */
    public function getFechaNacimiento()
    {
        return $this->fechaNacimiento;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return Estudiantenoacredidato
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
     * Set correo
     *
     * @param string $correo
     * @return Estudiantenoacredidato
     */
    public function setCorreo($correo)
    {
        $this->correo = $correo;
    
        return $this;
    }

    /**
     * Get correo
     *
     * @return string 
     */
    public function getCorreo()
    {
        return $this->correo;
    }

    /**
     * Set gestion
     *
     * @param integer $gestion
     * @return Estudiantenoacredidato
     */
    public function setGestion($gestion)
    {
        $this->gestion = $gestion;
    
        return $this;
    }

    /**
     * Get gestion
     *
     * @return integer 
     */
    public function getGestion()
    {
        return $this->gestion;
    }

    /**
     * Set nivelId
     *
     * @param integer $nivelId
     * @return Estudiantenoacredidato
     */
    public function setNivelId($nivelId)
    {
        $this->nivelId = $nivelId;
    
        return $this;
    }

    /**
     * Get nivelId
     *
     * @return integer 
     */
    public function getNivelId()
    {
        return $this->nivelId;
    }

    /**
     * Set gradoId
     *
     * @param integer $gradoId
     * @return Estudiantenoacredidato
     */
    public function setGradoId($gradoId)
    {
        $this->gradoId = $gradoId;
    
        return $this;
    }

    /**
     * Get gradoId
     *
     * @return integer 
     */
    public function getGradoId()
    {
        return $this->gradoId;
    }

    /**
     * Set paralelo
     *
     * @param string $paralelo
     * @return Estudiantenoacredidato
     */
    public function setParalelo($paralelo)
    {
        $this->paralelo = $paralelo;
    
        return $this;
    }

    /**
     * Get paralelo
     *
     * @return string 
     */
    public function getParalelo()
    {
        return $this->paralelo;
    }

    /**
     * Set turnoId
     *
     * @param integer $turnoId
     * @return Estudiantenoacredidato
     */
    public function setTurnoId($turnoId)
    {
        $this->turnoId = $turnoId;
    
        return $this;
    }

    /**
     * Get turnoId
     *
     * @return integer 
     */
    public function getTurnoId()
    {
        return $this->turnoId;
    }

    /**
     * Set institucioneducativanoacreditada
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativanoacreditado $institucioneducativanoacreditada
     * @return Estudiantenoacredidato
     */
    public function setInstitucioneducativanoacreditada(\Sie\AppWebBundle\Entity\Institucioneducativanoacreditado $institucioneducativanoacreditada = null)
    {
        $this->institucioneducativanoacreditada = $institucioneducativanoacreditada;
    
        return $this;
    }

    /**
     * Get institucioneducativanoacreditada
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativanoacreditado 
     */
    public function getInstitucioneducativanoacreditada()
    {
        return $this->institucioneducativanoacreditada;
    }
}
