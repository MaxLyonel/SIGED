<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteQuipus
 */
class EstudianteQuipus
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
     * @var string
     */
    private $codigoRude;

    /**
     * @var string
     */
    private $nombre;

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
    private $carnetIdentidad;

    /**
     * @var string
     */
    private $complemento;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var integer
     */
    private $orgcurricularTipoId;

    /**
     * @var \DateTime
     */
    private $fechaNacimiento;

    /**
     * @var boolean
     */
    private $estado;

    /**
     * @var integer
     */
    private $cicloTipoId;

    /**
     * @var integer
     */
    private $gradoTipoId;

    /**
     * @var string
     */
    private $paraleloTipoId;

    /**
     * @var integer
     */
    private $nivelTipoId;

    /**
     * @var integer
     */
    private $turnoTipoId;

    /**
     * @var integer
     */
    private $generoTipoId;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteQuipus
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
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return EstudianteQuipus
     */
    public function setCodigoRude($codigoRude)
    {
        $this->codigoRude = $codigoRude;
    
        return $this;
    }

    /**
     * Get codigoRude
     *
     * @return string 
     */
    public function getCodigoRude()
    {
        return $this->codigoRude;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return EstudianteQuipus
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
     * Set paterno
     *
     * @param string $paterno
     * @return EstudianteQuipus
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
     * @return EstudianteQuipus
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
     * Set carnetIdentidad
     *
     * @param string $carnetIdentidad
     * @return EstudianteQuipus
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
     * Set complemento
     *
     * @param string $complemento
     * @return EstudianteQuipus
     */
    public function setComplemento($complemento)
    {
        $this->complemento = $complemento;
    
        return $this;
    }

    /**
     * Get complemento
     *
     * @return string 
     */
    public function getComplemento()
    {
        return $this->complemento;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return EstudianteQuipus
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
     * Set orgcurricularTipoId
     *
     * @param integer $orgcurricularTipoId
     * @return EstudianteQuipus
     */
    public function setOrgcurricularTipoId($orgcurricularTipoId)
    {
        $this->orgcurricularTipoId = $orgcurricularTipoId;
    
        return $this;
    }

    /**
     * Get orgcurricularTipoId
     *
     * @return integer 
     */
    public function getOrgcurricularTipoId()
    {
        return $this->orgcurricularTipoId;
    }

    /**
     * Set fechaNacimiento
     *
     * @param \DateTime $fechaNacimiento
     * @return EstudianteQuipus
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
     * Set estado
     *
     * @param boolean $estado
     * @return EstudianteQuipus
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return boolean 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set cicloTipoId
     *
     * @param integer $cicloTipoId
     * @return EstudianteQuipus
     */
    public function setCicloTipoId($cicloTipoId)
    {
        $this->cicloTipoId = $cicloTipoId;
    
        return $this;
    }

    /**
     * Get cicloTipoId
     *
     * @return integer 
     */
    public function getCicloTipoId()
    {
        return $this->cicloTipoId;
    }

    /**
     * Set gradoTipoId
     *
     * @param integer $gradoTipoId
     * @return EstudianteQuipus
     */
    public function setGradoTipoId($gradoTipoId)
    {
        $this->gradoTipoId = $gradoTipoId;
    
        return $this;
    }

    /**
     * Get gradoTipoId
     *
     * @return integer 
     */
    public function getGradoTipoId()
    {
        return $this->gradoTipoId;
    }

    /**
     * Set paraleloTipoId
     *
     * @param string $paraleloTipoId
     * @return EstudianteQuipus
     */
    public function setParaleloTipoId($paraleloTipoId)
    {
        $this->paraleloTipoId = $paraleloTipoId;
    
        return $this;
    }

    /**
     * Get paraleloTipoId
     *
     * @return string 
     */
    public function getParaleloTipoId()
    {
        return $this->paraleloTipoId;
    }

    /**
     * Set nivelTipoId
     *
     * @param integer $nivelTipoId
     * @return EstudianteQuipus
     */
    public function setNivelTipoId($nivelTipoId)
    {
        $this->nivelTipoId = $nivelTipoId;
    
        return $this;
    }

    /**
     * Get nivelTipoId
     *
     * @return integer 
     */
    public function getNivelTipoId()
    {
        return $this->nivelTipoId;
    }

    /**
     * Set turnoTipoId
     *
     * @param integer $turnoTipoId
     * @return EstudianteQuipus
     */
    public function setTurnoTipoId($turnoTipoId)
    {
        $this->turnoTipoId = $turnoTipoId;
    
        return $this;
    }

    /**
     * Get turnoTipoId
     *
     * @return integer 
     */
    public function getTurnoTipoId()
    {
        return $this->turnoTipoId;
    }

    /**
     * Set generoTipoId
     *
     * @param integer $generoTipoId
     * @return EstudianteQuipus
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return EstudianteQuipus
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return EstudianteQuipus
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EstudianteQuipus
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
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return EstudianteQuipus
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
}
