<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucionalizacionDgesttla
 */
class InstitucionalizacionDgesttla
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $carnet;

    /**
     * @var string
     */
    private $complemento;

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
     * @var string
     */
    private $apellidoEsposo;

    /**
     * @var string
     */
    private $nacionalidad;

    /**
     * @var integer
     */
    private $genero;

    /**
     * @var \DateTime
     */
    private $fechaNacimiento;

    /**
     * @var string
     */
    private $direccion;

    /**
     * @var string
     */
    private $telefono;

    /**
     * @var string
     */
    private $celular;

    /**
     * @var string
     */
    private $correoElectronico;

    /**
     * @var string
     */
    private $licenciatura;

    /**
     * @var string
     */
    private $tecnicoSuperior;

    /**
     * @var integer
     */
    private $tipoPostgrado;

    /**
     * @var string
     */
    private $postgrado;

    /**
     * @var integer
     */
    private $departamento;

    /**
     * @var integer
     */
    private $instituto;

    /**
     * @var integer
     */
    private $cargo;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;


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
     * Set carnet
     *
     * @param string $carnet
     * @return InstitucionalizacionDgesttla
     */
    public function setCarnet($carnet)
    {
        $this->carnet = $carnet;
    
        return $this;
    }

    /**
     * Get carnet
     *
     * @return string 
     */
    public function getCarnet()
    {
        return $this->carnet;
    }

    /**
     * Set complemento
     *
     * @param string $complemento
     * @return InstitucionalizacionDgesttla
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
     * Set paterno
     *
     * @param string $paterno
     * @return InstitucionalizacionDgesttla
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
     * @return InstitucionalizacionDgesttla
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
     * @return InstitucionalizacionDgesttla
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
     * Set apellidoEsposo
     *
     * @param string $apellidoEsposo
     * @return InstitucionalizacionDgesttla
     */
    public function setApellidoEsposo($apellidoEsposo)
    {
        $this->apellidoEsposo = $apellidoEsposo;
    
        return $this;
    }

    /**
     * Get apellidoEsposo
     *
     * @return string 
     */
    public function getApellidoEsposo()
    {
        return $this->apellidoEsposo;
    }

    /**
     * Set nacionalidad
     *
     * @param string $nacionalidad
     * @return InstitucionalizacionDgesttla
     */
    public function setNacionalidad($nacionalidad)
    {
        $this->nacionalidad = $nacionalidad;
    
        return $this;
    }

    /**
     * Get nacionalidad
     *
     * @return string 
     */
    public function getNacionalidad()
    {
        return $this->nacionalidad;
    }

    /**
     * Set genero
     *
     * @param integer $genero
     * @return InstitucionalizacionDgesttla
     */
    public function setGenero($genero)
    {
        $this->genero = $genero;
    
        return $this;
    }

    /**
     * Get genero
     *
     * @return integer 
     */
    public function getGenero()
    {
        return $this->genero;
    }

    /**
     * Set fechaNacimiento
     *
     * @param \DateTime $fechaNacimiento
     * @return InstitucionalizacionDgesttla
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
     * Set direccion
     *
     * @param string $direccion
     * @return InstitucionalizacionDgesttla
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    
        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return InstitucionalizacionDgesttla
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
     * Set celular
     *
     * @param string $celular
     * @return InstitucionalizacionDgesttla
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
     * Set correoElectronico
     *
     * @param string $correoElectronico
     * @return InstitucionalizacionDgesttla
     */
    public function setCorreoElectronico($correoElectronico)
    {
        $this->correoElectronico = $correoElectronico;
    
        return $this;
    }

    /**
     * Get correoElectronico
     *
     * @return string 
     */
    public function getCorreoElectronico()
    {
        return $this->correoElectronico;
    }

    /**
     * Set licenciatura
     *
     * @param string $licenciatura
     * @return InstitucionalizacionDgesttla
     */
    public function setLicenciatura($licenciatura)
    {
        $this->licenciatura = $licenciatura;
    
        return $this;
    }

    /**
     * Get licenciatura
     *
     * @return string 
     */
    public function getLicenciatura()
    {
        return $this->licenciatura;
    }

    /**
     * Set tecnicoSuperior
     *
     * @param string $tecnicoSuperior
     * @return InstitucionalizacionDgesttla
     */
    public function setTecnicoSuperior($tecnicoSuperior)
    {
        $this->tecnicoSuperior = $tecnicoSuperior;
    
        return $this;
    }

    /**
     * Get tecnicoSuperior
     *
     * @return string 
     */
    public function getTecnicoSuperior()
    {
        return $this->tecnicoSuperior;
    }

    /**
     * Set tipoPostgrado
     *
     * @param integer $tipoPostgrado
     * @return InstitucionalizacionDgesttla
     */
    public function setTipoPostgrado($tipoPostgrado)
    {
        $this->tipoPostgrado = $tipoPostgrado;
    
        return $this;
    }

    /**
     * Get tipoPostgrado
     *
     * @return integer 
     */
    public function getTipoPostgrado()
    {
        return $this->tipoPostgrado;
    }

    /**
     * Set postgrado
     *
     * @param string $postgrado
     * @return InstitucionalizacionDgesttla
     */
    public function setPostgrado($postgrado)
    {
        $this->postgrado = $postgrado;
    
        return $this;
    }

    /**
     * Get postgrado
     *
     * @return string 
     */
    public function getPostgrado()
    {
        return $this->postgrado;
    }

    /**
     * Set departamento
     *
     * @param integer $departamento
     * @return InstitucionalizacionDgesttla
     */
    public function setDepartamento($departamento)
    {
        $this->departamento = $departamento;
    
        return $this;
    }

    /**
     * Get departamento
     *
     * @return integer 
     */
    public function getDepartamento()
    {
        return $this->departamento;
    }

    /**
     * Set instituto
     *
     * @param integer $instituto
     * @return InstitucionalizacionDgesttla
     */
    public function setInstituto($instituto)
    {
        $this->instituto = $instituto;
    
        return $this;
    }

    /**
     * Get instituto
     *
     * @return integer 
     */
    public function getInstituto()
    {
        return $this->instituto;
    }

    /**
     * Set cargo
     *
     * @param integer $cargo
     * @return InstitucionalizacionDgesttla
     */
    public function setCargo($cargo)
    {
        $this->cargo = $cargo;
    
        return $this;
    }

    /**
     * Get cargo
     *
     * @return integer 
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucionalizacionDgesttla
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
     * @return InstitucionalizacionDgesttla
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
     * @var boolean
     */
    private $esOficial;


    /**
     * Set esOficial
     *
     * @param boolean $esOficial
     * @return InstitucionalizacionDgesttla
     */
    public function setEsOficial($esOficial)
    {
        $this->esOficial = $esOficial;
    
        return $this;
    }

    /**
     * Get esOficial
     *
     * @return boolean 
     */
    public function getEsOficial()
    {
        return $this->esOficial;
    }
    /**
     * @var string
     */
    private $diplomado;

    /**
     * @var string
     */
    private $especialidad;

    /**
     * @var string
     */
    private $maestria;

    /**
     * @var string
     */
    private $doctorado;


    /**
     * Set diplomado
     *
     * @param string $diplomado
     * @return InstitucionalizacionDgesttla
     */
    public function setDiplomado($diplomado)
    {
        $this->diplomado = $diplomado;
    
        return $this;
    }

    /**
     * Get diplomado
     *
     * @return string 
     */
    public function getDiplomado()
    {
        return $this->diplomado;
    }

    /**
     * Set especialidad
     *
     * @param string $especialidad
     * @return InstitucionalizacionDgesttla
     */
    public function setEspecialidad($especialidad)
    {
        $this->especialidad = $especialidad;
    
        return $this;
    }

    /**
     * Get especialidad
     *
     * @return string 
     */
    public function getEspecialidad()
    {
        return $this->especialidad;
    }

    /**
     * Set maestria
     *
     * @param string $maestria
     * @return InstitucionalizacionDgesttla
     */
    public function setMaestria($maestria)
    {
        $this->maestria = $maestria;
    
        return $this;
    }

    /**
     * Get maestria
     *
     * @return string 
     */
    public function getMaestria()
    {
        return $this->maestria;
    }

    /**
     * Set doctorado
     *
     * @param string $doctorado
     * @return InstitucionalizacionDgesttla
     */
    public function setDoctorado($doctorado)
    {
        $this->doctorado = $doctorado;
    
        return $this;
    }

    /**
     * Get doctorado
     *
     * @return string 
     */
    public function getDoctorado()
    {
        return $this->doctorado;
    }
    /**
     * @var string
     */
    private $nroDeposito;


    /**
     * Set nroDeposito
     *
     * @param string $nroDeposito
     * @return InstitucionalizacionDgesttla
     */
    public function setNroDeposito($nroDeposito)
    {
        $this->nroDeposito = $nroDeposito;
    
        return $this;
    }

    /**
     * Get nroDeposito
     *
     * @return string 
     */
    public function getNroDeposito()
    {
        return $this->nroDeposito;
    }
}
