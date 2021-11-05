<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BjpEstudianteApoderadoPagados
 */
class BjpEstudianteApoderadoPagados
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $gestion;

    /**
     * @var integer
     */
    private $estudianteId;

    /**
     * @var string
     */
    private $codigoRude;

    /**
     * @var integer
     */
    private $personaId;

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
    private $fechaNacimiento;

    /**
     * @var string
     */
    private $controlCod;

    /**
     * @var integer
     */
    private $pagoDepartamento;

    /**
     * @var integer
     */
    private $pagoLocalidad;

    /**
     * @var string
     */
    private $entidadId;

    /**
     * @var string
     */
    private $agenciaId;

    /**
     * @var string
     */
    private $cajero;

    /**
     * @var \DateTime
     */
    private $fechaPago;

    /**
     * @var \DateTime
     */
    private $horaPago;

    /**
     * @var string
     */
    private $transaccion;

    /**
     * @var \DateTime
     */
    private $fechaInformacion;

    /**
     * @var integer
     */
    private $tipoServicioId;

    /**
     * @var integer
     */
    private $institucioneducativaCursoId;


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
     * Set gestion
     *
     * @param integer $gestion
     * @return BjpEstudianteApoderadoPagados
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
     * Set estudianteId
     *
     * @param integer $estudianteId
     * @return BjpEstudianteApoderadoPagados
     */
    public function setEstudianteId($estudianteId)
    {
        $this->estudianteId = $estudianteId;
    
        return $this;
    }

    /**
     * Get estudianteId
     *
     * @return integer 
     */
    public function getEstudianteId()
    {
        return $this->estudianteId;
    }

    /**
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return BjpEstudianteApoderadoPagados
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
     * Set personaId
     *
     * @param integer $personaId
     * @return BjpEstudianteApoderadoPagados
     */
    public function setPersonaId($personaId)
    {
        $this->personaId = $personaId;
    
        return $this;
    }

    /**
     * Get personaId
     *
     * @return integer 
     */
    public function getPersonaId()
    {
        return $this->personaId;
    }

    /**
     * Set carnet
     *
     * @param string $carnet
     * @return BjpEstudianteApoderadoPagados
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
     * @return BjpEstudianteApoderadoPagados
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
     * Set nombre
     *
     * @param string $nombre
     * @return BjpEstudianteApoderadoPagados
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
     * @return BjpEstudianteApoderadoPagados
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
     * @return BjpEstudianteApoderadoPagados
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
     * Set fechaNacimiento
     *
     * @param string $fechaNacimiento
     * @return BjpEstudianteApoderadoPagados
     */
    public function setFechaNacimiento($fechaNacimiento)
    {
        $this->fechaNacimiento = $fechaNacimiento;
    
        return $this;
    }

    /**
     * Get fechaNacimiento
     *
     * @return string 
     */
    public function getFechaNacimiento()
    {
        return $this->fechaNacimiento;
    }

    /**
     * Set controlCod
     *
     * @param string $controlCod
     * @return BjpEstudianteApoderadoPagados
     */
    public function setControlCod($controlCod)
    {
        $this->controlCod = $controlCod;
    
        return $this;
    }

    /**
     * Get controlCod
     *
     * @return string 
     */
    public function getControlCod()
    {
        return $this->controlCod;
    }

    /**
     * Set pagoDepartamento
     *
     * @param integer $pagoDepartamento
     * @return BjpEstudianteApoderadoPagados
     */
    public function setPagoDepartamento($pagoDepartamento)
    {
        $this->pagoDepartamento = $pagoDepartamento;
    
        return $this;
    }

    /**
     * Get pagoDepartamento
     *
     * @return integer 
     */
    public function getPagoDepartamento()
    {
        return $this->pagoDepartamento;
    }

    /**
     * Set pagoLocalidad
     *
     * @param integer $pagoLocalidad
     * @return BjpEstudianteApoderadoPagados
     */
    public function setPagoLocalidad($pagoLocalidad)
    {
        $this->pagoLocalidad = $pagoLocalidad;
    
        return $this;
    }

    /**
     * Get pagoLocalidad
     *
     * @return integer 
     */
    public function getPagoLocalidad()
    {
        return $this->pagoLocalidad;
    }

    /**
     * Set entidadId
     *
     * @param string $entidadId
     * @return BjpEstudianteApoderadoPagados
     */
    public function setEntidadId($entidadId)
    {
        $this->entidadId = $entidadId;
    
        return $this;
    }

    /**
     * Get entidadId
     *
     * @return string 
     */
    public function getEntidadId()
    {
        return $this->entidadId;
    }

    /**
     * Set agenciaId
     *
     * @param string $agenciaId
     * @return BjpEstudianteApoderadoPagados
     */
    public function setAgenciaId($agenciaId)
    {
        $this->agenciaId = $agenciaId;
    
        return $this;
    }

    /**
     * Get agenciaId
     *
     * @return string 
     */
    public function getAgenciaId()
    {
        return $this->agenciaId;
    }

    /**
     * Set cajero
     *
     * @param string $cajero
     * @return BjpEstudianteApoderadoPagados
     */
    public function setCajero($cajero)
    {
        $this->cajero = $cajero;
    
        return $this;
    }

    /**
     * Get cajero
     *
     * @return string 
     */
    public function getCajero()
    {
        return $this->cajero;
    }

    /**
     * Set fechaPago
     *
     * @param \DateTime $fechaPago
     * @return BjpEstudianteApoderadoPagados
     */
    public function setFechaPago($fechaPago)
    {
        $this->fechaPago = $fechaPago;
    
        return $this;
    }

    /**
     * Get fechaPago
     *
     * @return \DateTime 
     */
    public function getFechaPago()
    {
        return $this->fechaPago;
    }

    /**
     * Set horaPago
     *
     * @param \DateTime $horaPago
     * @return BjpEstudianteApoderadoPagados
     */
    public function setHoraPago($horaPago)
    {
        $this->horaPago = $horaPago;
    
        return $this;
    }

    /**
     * Get horaPago
     *
     * @return \DateTime 
     */
    public function getHoraPago()
    {
        return $this->horaPago;
    }

    /**
     * Set transaccion
     *
     * @param string $transaccion
     * @return BjpEstudianteApoderadoPagados
     */
    public function setTransaccion($transaccion)
    {
        $this->transaccion = $transaccion;
    
        return $this;
    }

    /**
     * Get transaccion
     *
     * @return string 
     */
    public function getTransaccion()
    {
        return $this->transaccion;
    }

    /**
     * Set fechaInformacion
     *
     * @param \DateTime $fechaInformacion
     * @return BjpEstudianteApoderadoPagados
     */
    public function setFechaInformacion($fechaInformacion)
    {
        $this->fechaInformacion = $fechaInformacion;
    
        return $this;
    }

    /**
     * Get fechaInformacion
     *
     * @return \DateTime 
     */
    public function getFechaInformacion()
    {
        return $this->fechaInformacion;
    }

    /**
     * Set tipoServicioId
     *
     * @param integer $tipoServicioId
     * @return BjpEstudianteApoderadoPagados
     */
    public function setTipoServicioId($tipoServicioId)
    {
        $this->tipoServicioId = $tipoServicioId;
    
        return $this;
    }

    /**
     * Get tipoServicioId
     *
     * @return integer 
     */
    public function getTipoServicioId()
    {
        return $this->tipoServicioId;
    }

    /**
     * Set institucioneducativaCursoId
     *
     * @param integer $institucioneducativaCursoId
     * @return BjpEstudianteApoderadoPagados
     */
    public function setInstitucioneducativaCursoId($institucioneducativaCursoId)
    {
        $this->institucioneducativaCursoId = $institucioneducativaCursoId;
    
        return $this;
    }

    /**
     * Get institucioneducativaCursoId
     *
     * @return integer 
     */
    public function getInstitucioneducativaCursoId()
    {
        return $this->institucioneducativaCursoId;
    }
}
