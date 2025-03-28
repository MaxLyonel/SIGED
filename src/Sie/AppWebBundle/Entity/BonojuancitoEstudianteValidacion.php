<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonojuancitoEstudianteValidacion
 */
class BonojuancitoEstudianteValidacion
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
     * @var integer
     */
    private $institucioneducativaId;

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
     * @var integer
     */
    private $nivelTipoId;

    /**
     * @var string
     */
    private $nivel;

    /**
     * @var integer
     */
    private $cicloTipoId;

    /**
     * @var string
     */
    private $ciclo;

    /**
     * @var integer
     */
    private $gradoTipoId;

    /**
     * @var string
     */
    private $grado;

    /**
     * @var string
     */
    private $paralelo;

    /**
     * @var integer
     */
    private $turnoTipoId;

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
    private $estadomatricula;

    /**
     * @var integer
     */
    private $estadomatriculaInicioTipoId;

    /**
     * @var integer
     */
    private $id2;

    /**
     * @var integer
     */
    private $nota1;

    /**
     * @var integer
     */
    private $nota2;

    /**
     * @var float
     */
    private $edad;

    /**
     * @var boolean
     */
    private $esNuevo;

    /**
     * @var boolean
     */
    private $esPagado;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var integer
     */
    private $gestionTipoId;


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
     * @return BonojuancitoEstudianteValidacion
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
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return BonojuancitoEstudianteValidacion
     */
    public function setInstitucioneducativaId($institucioneducativaId)
    {
        $this->institucioneducativaId = $institucioneducativaId;
    
        return $this;
    }

    /**
     * Get institucioneducativaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaId()
    {
        return $this->institucioneducativaId;
    }

    /**
     * Set carnetIdentidad
     *
     * @param string $carnetIdentidad
     * @return BonojuancitoEstudianteValidacion
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
     * @return BonojuancitoEstudianteValidacion
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
     * @return BonojuancitoEstudianteValidacion
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
     * @return BonojuancitoEstudianteValidacion
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
     * @return BonojuancitoEstudianteValidacion
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
     * @return BonojuancitoEstudianteValidacion
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
     * Set nivelTipoId
     *
     * @param integer $nivelTipoId
     * @return BonojuancitoEstudianteValidacion
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
     * Set nivel
     *
     * @param string $nivel
     * @return BonojuancitoEstudianteValidacion
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
     * Set cicloTipoId
     *
     * @param integer $cicloTipoId
     * @return BonojuancitoEstudianteValidacion
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
     * Set ciclo
     *
     * @param string $ciclo
     * @return BonojuancitoEstudianteValidacion
     */
    public function setCiclo($ciclo)
    {
        $this->ciclo = $ciclo;
    
        return $this;
    }

    /**
     * Get ciclo
     *
     * @return string 
     */
    public function getCiclo()
    {
        return $this->ciclo;
    }

    /**
     * Set gradoTipoId
     *
     * @param integer $gradoTipoId
     * @return BonojuancitoEstudianteValidacion
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
     * Set grado
     *
     * @param string $grado
     * @return BonojuancitoEstudianteValidacion
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
     * @return BonojuancitoEstudianteValidacion
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
     * Set turnoTipoId
     *
     * @param integer $turnoTipoId
     * @return BonojuancitoEstudianteValidacion
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
     * Set turno
     *
     * @param string $turno
     * @return BonojuancitoEstudianteValidacion
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
     * @return BonojuancitoEstudianteValidacion
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
     * Set estadomatricula
     *
     * @param string $estadomatricula
     * @return BonojuancitoEstudianteValidacion
     */
    public function setEstadomatricula($estadomatricula)
    {
        $this->estadomatricula = $estadomatricula;
    
        return $this;
    }

    /**
     * Get estadomatricula
     *
     * @return string 
     */
    public function getEstadomatricula()
    {
        return $this->estadomatricula;
    }

    /**
     * Set estadomatriculaInicioTipoId
     *
     * @param integer $estadomatriculaInicioTipoId
     * @return BonojuancitoEstudianteValidacion
     */
    public function setEstadomatriculaInicioTipoId($estadomatriculaInicioTipoId)
    {
        $this->estadomatriculaInicioTipoId = $estadomatriculaInicioTipoId;
    
        return $this;
    }

    /**
     * Get estadomatriculaInicioTipoId
     *
     * @return integer 
     */
    public function getEstadomatriculaInicioTipoId()
    {
        return $this->estadomatriculaInicioTipoId;
    }

    /**
     * Set id2
     *
     * @param integer $id2
     * @return BonojuancitoEstudianteValidacion
     */
    public function setId2($id2)
    {
        $this->id2 = $id2;
    
        return $this;
    }

    /**
     * Get id2
     *
     * @return integer 
     */
    public function getId2()
    {
        return $this->id2;
    }

    /**
     * Set nota1
     *
     * @param integer $nota1
     * @return BonojuancitoEstudianteValidacion
     */
    public function setNota1($nota1)
    {
        $this->nota1 = $nota1;
    
        return $this;
    }

    /**
     * Get nota1
     *
     * @return integer 
     */
    public function getNota1()
    {
        return $this->nota1;
    }

    /**
     * Set nota2
     *
     * @param integer $nota2
     * @return BonojuancitoEstudianteValidacion
     */
    public function setNota2($nota2)
    {
        $this->nota2 = $nota2;
    
        return $this;
    }

    /**
     * Get nota2
     *
     * @return integer 
     */
    public function getNota2()
    {
        return $this->nota2;
    }

    /**
     * Set edad
     *
     * @param float $edad
     * @return BonojuancitoEstudianteValidacion
     */
    public function setEdad($edad)
    {
        $this->edad = $edad;
    
        return $this;
    }

    /**
     * Get edad
     *
     * @return float 
     */
    public function getEdad()
    {
        return $this->edad;
    }

    /**
     * Set esNuevo
     *
     * @param boolean $esNuevo
     * @return BonojuancitoEstudianteValidacion
     */
    public function setEsNuevo($esNuevo)
    {
        $this->esNuevo = $esNuevo;
    
        return $this;
    }

    /**
     * Get esNuevo
     *
     * @return boolean 
     */
    public function getEsNuevo()
    {
        return $this->esNuevo;
    }

    /**
     * Set esPagado
     *
     * @param boolean $esPagado
     * @return BonojuancitoEstudianteValidacion
     */
    public function setEsPagado($esPagado)
    {
        $this->esPagado = $esPagado;
    
        return $this;
    }

    /**
     * Get esPagado
     *
     * @return boolean 
     */
    public function getEsPagado()
    {
        return $this->esPagado;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BonojuancitoEstudianteValidacion
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
     * @return BonojuancitoEstudianteValidacion
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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return BonojuancitoEstudianteValidacion
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }
    /**
     * @var integer
     */
    private $pagoTipoId;

    /**
     * @var integer
     */
    private $estudianteInscripcionId;


    /**
     * Set pagoTipoId
     *
     * @param integer $pagoTipoId
     * @return BonojuancitoEstudianteValidacion
     */
    public function setPagoTipoId($pagoTipoId)
    {
        $this->pagoTipoId = $pagoTipoId;
    
        return $this;
    }

    /**
     * Get pagoTipoId
     *
     * @return integer 
     */
    public function getPagoTipoId()
    {
        return $this->pagoTipoId;
    }

    /**
     * Set estudianteInscripcionId
     *
     * @param integer $estudianteInscripcionId
     * @return BonojuancitoEstudianteValidacion
     */
    public function setEstudianteInscripcionId($estudianteInscripcionId)
    {
        $this->estudianteInscripcionId = $estudianteInscripcionId;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionId
     *
     * @return integer 
     */
    public function getEstudianteInscripcionId()
    {
        return $this->estudianteInscripcionId;
    }
}
