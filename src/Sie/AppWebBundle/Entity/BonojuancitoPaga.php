<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonojuancitoPaga
 */
class BonojuancitoPaga
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $codigoRude;

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
     * @var \DateTime
     */
    private $fechaNacimiento;

    /**
     * @var string
     */
    private $genero;

    /**
     * @var string
     */
    private $nivel;

    /**
     * @var string
     */
    private $grado;

    /**
     * @var string
     */
    private $paralelo;

    /**
     * @var string
     */
    private $turno;

    /**
     * @var integer
     */
    private $estadomatriculaTipoId;

    /**
     * @var string
     */
    private $estadomatriculaTipo;

    /**
     * @var boolean
     */
    private $esnuevoingreso;

    /**
     * @var boolean
     */
    private $espagado;

    /**
     * @var boolean
     */
    private $eshabilitado;

    /**
     * @var string
     */
    private $lugar;

    /**
     * @var boolean
     */
    private $esnuevorude;

    /**
     * @var string
     */
    private $observaciones;

    /**
     * @var string
     */
    private $estadopagoEstudiante;

    /**
     * @var \Sie\AppWebBundle\Entity\BonojuancitoUnidadmilitar
     */
    private $bonojuancitoUnidadmilitar;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\NivelTipo
     */
    private $nivelTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GradoTipo
     */
    private $gradoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;

    /**
     * @var \Sie\AppWebBundle\Entity\TurnoTipo
     */
    private $idTurno;

    /**
     * @var \Sie\AppWebBundle\Entity\ParaleloTipo
     */
    private $paraleloTipo;


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
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return BonojuancitoPaga
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
     * Set carnetIdentidad
     *
     * @param string $carnetIdentidad
     * @return BonojuancitoPaga
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
     * @return BonojuancitoPaga
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
     * @return BonojuancitoPaga
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
     * @return BonojuancitoPaga
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
     * Set fechaNacimiento
     *
     * @param \DateTime $fechaNacimiento
     * @return BonojuancitoPaga
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
     * Set genero
     *
     * @param string $genero
     * @return BonojuancitoPaga
     */
    public function setGenero($genero)
    {
        $this->genero = $genero;
    
        return $this;
    }

    /**
     * Get genero
     *
     * @return string 
     */
    public function getGenero()
    {
        return $this->genero;
    }

    /**
     * Set nivel
     *
     * @param string $nivel
     * @return BonojuancitoPaga
     */
    public function setNivel($nivel)
    {
        $this->nivel = $nivel;
    
        return $this;
    }

    /**
     * Get nivel
     *
     * @return string 
     */
    public function getNivel()
    {
        return $this->nivel;
    }

    /**
     * Set grado
     *
     * @param string $grado
     * @return BonojuancitoPaga
     */
    public function setGrado($grado)
    {
        $this->grado = $grado;
    
        return $this;
    }

    /**
     * Get grado
     *
     * @return string 
     */
    public function getGrado()
    {
        return $this->grado;
    }

    /**
     * Set paralelo
     *
     * @param string $paralelo
     * @return BonojuancitoPaga
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
     * Set turno
     *
     * @param string $turno
     * @return BonojuancitoPaga
     */
    public function setTurno($turno)
    {
        $this->turno = $turno;
    
        return $this;
    }

    /**
     * Get turno
     *
     * @return string 
     */
    public function getTurno()
    {
        return $this->turno;
    }

    /**
     * Set estadomatriculaTipoId
     *
     * @param integer $estadomatriculaTipoId
     * @return BonojuancitoPaga
     */
    public function setEstadomatriculaTipoId($estadomatriculaTipoId)
    {
        $this->estadomatriculaTipoId = $estadomatriculaTipoId;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipoId
     *
     * @return integer 
     */
    public function getEstadomatriculaTipoId()
    {
        return $this->estadomatriculaTipoId;
    }

    /**
     * Set estadomatriculaTipo
     *
     * @param string $estadomatriculaTipo
     * @return BonojuancitoPaga
     */
    public function setEstadomatriculaTipo($estadomatriculaTipo)
    {
        $this->estadomatriculaTipo = $estadomatriculaTipo;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipo
     *
     * @return string 
     */
    public function getEstadomatriculaTipo()
    {
        return $this->estadomatriculaTipo;
    }

    /**
     * Set esnuevoingreso
     *
     * @param boolean $esnuevoingreso
     * @return BonojuancitoPaga
     */
    public function setEsnuevoingreso($esnuevoingreso)
    {
        $this->esnuevoingreso = $esnuevoingreso;
    
        return $this;
    }

    /**
     * Get esnuevoingreso
     *
     * @return boolean 
     */
    public function getEsnuevoingreso()
    {
        return $this->esnuevoingreso;
    }

    /**
     * Set espagado
     *
     * @param boolean $espagado
     * @return BonojuancitoPaga
     */
    public function setEspagado($espagado)
    {
        $this->espagado = $espagado;
    
        return $this;
    }

    /**
     * Get espagado
     *
     * @return boolean 
     */
    public function getEspagado()
    {
        return $this->espagado;
    }

    /**
     * Set eshabilitado
     *
     * @param boolean $eshabilitado
     * @return BonojuancitoPaga
     */
    public function setEshabilitado($eshabilitado)
    {
        $this->eshabilitado = $eshabilitado;
    
        return $this;
    }

    /**
     * Get eshabilitado
     *
     * @return boolean 
     */
    public function getEshabilitado()
    {
        return $this->eshabilitado;
    }

    /**
     * Set lugar
     *
     * @param string $lugar
     * @return BonojuancitoPaga
     */
    public function setLugar($lugar)
    {
        $this->lugar = $lugar;
    
        return $this;
    }

    /**
     * Get lugar
     *
     * @return string 
     */
    public function getLugar()
    {
        return $this->lugar;
    }

    /**
     * Set esnuevorude
     *
     * @param boolean $esnuevorude
     * @return BonojuancitoPaga
     */
    public function setEsnuevorude($esnuevorude)
    {
        $this->esnuevorude = $esnuevorude;
    
        return $this;
    }

    /**
     * Get esnuevorude
     *
     * @return boolean 
     */
    public function getEsnuevorude()
    {
        return $this->esnuevorude;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return BonojuancitoPaga
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    
        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set estadopagoEstudiante
     *
     * @param string $estadopagoEstudiante
     * @return BonojuancitoPaga
     */
    public function setEstadopagoEstudiante($estadopagoEstudiante)
    {
        $this->estadopagoEstudiante = $estadopagoEstudiante;
    
        return $this;
    }

    /**
     * Get estadopagoEstudiante
     *
     * @return string 
     */
    public function getEstadopagoEstudiante()
    {
        return $this->estadopagoEstudiante;
    }

    /**
     * Set bonojuancitoUnidadmilitar
     *
     * @param \Sie\AppWebBundle\Entity\BonojuancitoUnidadmilitar $bonojuancitoUnidadmilitar
     * @return BonojuancitoPaga
     */
    public function setBonojuancitoUnidadmilitar(\Sie\AppWebBundle\Entity\BonojuancitoUnidadmilitar $bonojuancitoUnidadmilitar = null)
    {
        $this->bonojuancitoUnidadmilitar = $bonojuancitoUnidadmilitar;
    
        return $this;
    }

    /**
     * Get bonojuancitoUnidadmilitar
     *
     * @return \Sie\AppWebBundle\Entity\BonojuancitoUnidadmilitar 
     */
    public function getBonojuancitoUnidadmilitar()
    {
        return $this->bonojuancitoUnidadmilitar;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return BonojuancitoPaga
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return BonojuancitoPaga
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return BonojuancitoPaga
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
     * Set nivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\NivelTipo $nivelTipo
     * @return BonojuancitoPaga
     */
    public function setNivelTipo(\Sie\AppWebBundle\Entity\NivelTipo $nivelTipo = null)
    {
        $this->nivelTipo = $nivelTipo;
    
        return $this;
    }

    /**
     * Get nivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\NivelTipo 
     */
    public function getNivelTipo()
    {
        return $this->nivelTipo;
    }

    /**
     * Set gradoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GradoTipo $gradoTipo
     * @return BonojuancitoPaga
     */
    public function setGradoTipo(\Sie\AppWebBundle\Entity\GradoTipo $gradoTipo = null)
    {
        $this->gradoTipo = $gradoTipo;
    
        return $this;
    }

    /**
     * Get gradoTipo
     *
     * @return \Sie\AppWebBundle\Entity\GradoTipo 
     */
    public function getGradoTipo()
    {
        return $this->gradoTipo;
    }

    /**
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return BonojuancitoPaga
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
     * Set idTurno
     *
     * @param \Sie\AppWebBundle\Entity\TurnoTipo $idTurno
     * @return BonojuancitoPaga
     */
    public function setIdTurno(\Sie\AppWebBundle\Entity\TurnoTipo $idTurno = null)
    {
        $this->idTurno = $idTurno;
    
        return $this;
    }

    /**
     * Get idTurno
     *
     * @return \Sie\AppWebBundle\Entity\TurnoTipo 
     */
    public function getIdTurno()
    {
        return $this->idTurno;
    }

    /**
     * Set paraleloTipo
     *
     * @param \Sie\AppWebBundle\Entity\ParaleloTipo $paraleloTipo
     * @return BonojuancitoPaga
     */
    public function setParaleloTipo(\Sie\AppWebBundle\Entity\ParaleloTipo $paraleloTipo = null)
    {
        $this->paraleloTipo = $paraleloTipo;
    
        return $this;
    }

    /**
     * Get paraleloTipo
     *
     * @return \Sie\AppWebBundle\Entity\ParaleloTipo 
     */
    public function getParaleloTipo()
    {
        return $this->paraleloTipo;
    }
}
