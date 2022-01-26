<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PreinsPersona
 */
class PreinsPersona
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
     * @var \DateTime
     */
    private $fechaNacimiento;

    /**
     * @var integer
     */
    private $segipId;

    /**
     * @var boolean
     */
    private $esExtranjero;

    /**
     * @var string
     */
    private $localidadNac;

    /**
     * @var string
     */
    private $apellidoEsposo;

    /**
     * @var string
     */
    private $ocupacionLaboral;

    /**
     * @var string
     */
    private $zona;

    /**
     * @var string
     */
    private $avenida;

    /**
     * @var string
     */
    private $calle;

    /**
     * @var string
     */
    private $numero;

    /**
     * @var string
     */
    private $celular;

    /**
     * @var string
     */
    private $nomLugTrab;

    /**
     * @var string
     */
    private $munLugTrab;

    /**
     * @var string
     */
    private $zonaLugTrab;

    /**
     * @var string
     */
    private $avenidaLugTrab;

    /**
     * @var string
     */
    private $calleLugTrab;

    /**
     * @var string
     */
    private $numeroLugTrab;

    /**
     * @var string
     */
    private $celularLugTrab;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\PaisTipo
     */
    private $paisTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\DepartamentoTipo
     */
    private $expedido;


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
     * @return PreinsPersona
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
     * @return PreinsPersona
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
     * @return PreinsPersona
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
     * @return PreinsPersona
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
     * @return PreinsPersona
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
     * @return PreinsPersona
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
     * Set segipId
     *
     * @param integer $segipId
     * @return PreinsPersona
     */
    public function setSegipId($segipId)
    {
        $this->segipId = $segipId;
    
        return $this;
    }

    /**
     * Get segipId
     *
     * @return integer 
     */
    public function getSegipId()
    {
        return $this->segipId;
    }

    /**
     * Set esExtranjero
     *
     * @param boolean $esExtranjero
     * @return PreinsPersona
     */
    public function setEsExtranjero($esExtranjero)
    {
        $this->esExtranjero = $esExtranjero;
    
        return $this;
    }

    /**
     * Get esExtranjero
     *
     * @return boolean 
     */
    public function getEsExtranjero()
    {
        return $this->esExtranjero;
    }

    /**
     * Set localidadNac
     *
     * @param string $localidadNac
     * @return PreinsPersona
     */
    public function setLocalidadNac($localidadNac)
    {
        $this->localidadNac = $localidadNac;
    
        return $this;
    }

    /**
     * Get localidadNac
     *
     * @return string 
     */
    public function getLocalidadNac()
    {
        return $this->localidadNac;
    }

    /**
     * Set apellidoEsposo
     *
     * @param string $apellidoEsposo
     * @return PreinsPersona
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
     * Set ocupacionLaboral
     *
     * @param string $ocupacionLaboral
     * @return PreinsPersona
     */
    public function setOcupacionLaboral($ocupacionLaboral)
    {
        $this->ocupacionLaboral = $ocupacionLaboral;
    
        return $this;
    }

    /**
     * Get ocupacionLaboral
     *
     * @return string 
     */
    public function getOcupacionLaboral()
    {
        return $this->ocupacionLaboral;
    }

    /**
     * Set zona
     *
     * @param string $zona
     * @return PreinsPersona
     */
    public function setZona($zona)
    {
        $this->zona = $zona;
    
        return $this;
    }

    /**
     * Get zona
     *
     * @return string 
     */
    public function getZona()
    {
        return $this->zona;
    }

    /**
     * Set avenida
     *
     * @param string $avenida
     * @return PreinsPersona
     */
    public function setAvenida($avenida)
    {
        $this->avenida = $avenida;
    
        return $this;
    }

    /**
     * Get avenida
     *
     * @return string 
     */
    public function getAvenida()
    {
        return $this->avenida;
    }

    /**
     * Set calle
     *
     * @param string $calle
     * @return PreinsPersona
     */
    public function setCalle($calle)
    {
        $this->calle = $calle;
    
        return $this;
    }

    /**
     * Get calle
     *
     * @return string 
     */
    public function getCalle()
    {
        return $this->calle;
    }

    /**
     * Set numero
     *
     * @param string $numero
     * @return PreinsPersona
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    
        return $this;
    }

    /**
     * Get numero
     *
     * @return string 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set celular
     *
     * @param string $celular
     * @return PreinsPersona
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
     * Set nomLugTrab
     *
     * @param string $nomLugTrab
     * @return PreinsPersona
     */
    public function setNomLugTrab($nomLugTrab)
    {
        $this->nomLugTrab = $nomLugTrab;
    
        return $this;
    }

    /**
     * Get nomLugTrab
     *
     * @return string 
     */
    public function getNomLugTrab()
    {
        return $this->nomLugTrab;
    }

    /**
     * Set munLugTrab
     *
     * @param string $munLugTrab
     * @return PreinsPersona
     */
    public function setMunLugTrab($munLugTrab)
    {
        $this->munLugTrab = $munLugTrab;
    
        return $this;
    }

    /**
     * Get munLugTrab
     *
     * @return string 
     */
    public function getMunLugTrab()
    {
        return $this->munLugTrab;
    }

    /**
     * Set zonaLugTrab
     *
     * @param string $zonaLugTrab
     * @return PreinsPersona
     */
    public function setZonaLugTrab($zonaLugTrab)
    {
        $this->zonaLugTrab = $zonaLugTrab;
    
        return $this;
    }

    /**
     * Get zonaLugTrab
     *
     * @return string 
     */
    public function getZonaLugTrab()
    {
        return $this->zonaLugTrab;
    }

    /**
     * Set avenidaLugTrab
     *
     * @param string $avenidaLugTrab
     * @return PreinsPersona
     */
    public function setAvenidaLugTrab($avenidaLugTrab)
    {
        $this->avenidaLugTrab = $avenidaLugTrab;
    
        return $this;
    }

    /**
     * Get avenidaLugTrab
     *
     * @return string 
     */
    public function getAvenidaLugTrab()
    {
        return $this->avenidaLugTrab;
    }

    /**
     * Set calleLugTrab
     *
     * @param string $calleLugTrab
     * @return PreinsPersona
     */
    public function setCalleLugTrab($calleLugTrab)
    {
        $this->calleLugTrab = $calleLugTrab;
    
        return $this;
    }

    /**
     * Get calleLugTrab
     *
     * @return string 
     */
    public function getCalleLugTrab()
    {
        return $this->calleLugTrab;
    }

    /**
     * Set numeroLugTrab
     *
     * @param string $numeroLugTrab
     * @return PreinsPersona
     */
    public function setNumeroLugTrab($numeroLugTrab)
    {
        $this->numeroLugTrab = $numeroLugTrab;
    
        return $this;
    }

    /**
     * Get numeroLugTrab
     *
     * @return string 
     */
    public function getNumeroLugTrab()
    {
        return $this->numeroLugTrab;
    }

    /**
     * Set celularLugTrab
     *
     * @param string $celularLugTrab
     * @return PreinsPersona
     */
    public function setCelularLugTrab($celularLugTrab)
    {
        $this->celularLugTrab = $celularLugTrab;
    
        return $this;
    }

    /**
     * Get celularLugTrab
     *
     * @return string 
     */
    public function getCelularLugTrab()
    {
        return $this->celularLugTrab;
    }

    /**
     * Set generoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GeneroTipo $generoTipo
     * @return PreinsPersona
     */
    public function setGeneroTipo(\Sie\AppWebBundle\Entity\GeneroTipo $generoTipo = null)
    {
        $this->generoTipo = $generoTipo;
    
        return $this;
    }

    /**
     * Get generoTipo
     *
     * @return \Sie\AppWebBundle\Entity\GeneroTipo 
     */
    public function getGeneroTipo()
    {
        return $this->generoTipo;
    }

    /**
     * Set paisTipo
     *
     * @param \Sie\AppWebBundle\Entity\PaisTipo $paisTipo
     * @return PreinsPersona
     */
    public function setPaisTipo(\Sie\AppWebBundle\Entity\PaisTipo $paisTipo = null)
    {
        $this->paisTipo = $paisTipo;
    
        return $this;
    }

    /**
     * Get paisTipo
     *
     * @return \Sie\AppWebBundle\Entity\PaisTipo 
     */
    public function getPaisTipo()
    {
        return $this->paisTipo;
    }

    /**
     * Set expedido
     *
     * @param \Sie\AppWebBundle\Entity\DepartamentoTipo $expedido
     * @return PreinsPersona
     */
    public function setExpedido(\Sie\AppWebBundle\Entity\DepartamentoTipo $expedido = null)
    {
        $this->expedido = $expedido;
    
        return $this;
    }

    /**
     * Get expedido
     *
     * @return \Sie\AppWebBundle\Entity\DepartamentoTipo 
     */
    public function getExpedido()
    {
        return $this->expedido;
    }
}
