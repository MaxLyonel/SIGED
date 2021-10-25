<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PreinsEstudiante
 */
class PreinsEstudiante
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
    private $localidadNac;

    /**
     * @var integer
     */
    private $segipId;

    /**
     * @var \DateTime
     */
    private $fechaNacimiento;

    /**
     * @var boolean
     */
    private $esDobleNacionalidad;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \Sie\AppWebBundle\Entity\DepartamentoTipo
     */
    private $expedido;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;


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
     * @return PreinsEstudiante
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
     * @return PreinsEstudiante
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
     * @return PreinsEstudiante
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
     * @return PreinsEstudiante
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
     * @return PreinsEstudiante
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
     * @return PreinsEstudiante
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
     * Set localidadNac
     *
     * @param string $localidadNac
     * @return PreinsEstudiante
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
     * Set segipId
     *
     * @param integer $segipId
     * @return PreinsEstudiante
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
     * Set fechaNacimiento
     *
     * @param \DateTime $fechaNacimiento
     * @return PreinsEstudiante
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
     * Set esDobleNacionalidad
     *
     * @param boolean $esDobleNacionalidad
     * @return PreinsEstudiante
     */
    public function setEsDobleNacionalidad($esDobleNacionalidad)
    {
        $this->esDobleNacionalidad = $esDobleNacionalidad;
    
        return $this;
    }

    /**
     * Get esDobleNacionalidad
     *
     * @return boolean 
     */
    public function getEsDobleNacionalidad()
    {
        return $this->esDobleNacionalidad;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return PreinsEstudiante
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set expedido
     *
     * @param \Sie\AppWebBundle\Entity\DepartamentoTipo $expedido
     * @return PreinsEstudiante
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

    /**
     * Set generoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GeneroTipo $generoTipo
     * @return PreinsEstudiante
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
}
