<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BfObservadosBono
 */
class BfObservadosBono
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var integer
     */
    private $ide;

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
     * @var integer
     */
    private $generoTipoId;

    /**
     * @var \DateTime
     */
    private $fechaNacimiento;

    /**
     * @var integer
     */
    private $segipId;

    /**
     * @var integer
     */
    private $idins;

    /**
     * @var string
     */
    private $obs;


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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return BfObservadosBono
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
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return BfObservadosBono
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
     * Set ide
     *
     * @param integer $ide
     * @return BfObservadosBono
     */
    public function setIde($ide)
    {
        $this->ide = $ide;
    
        return $this;
    }

    /**
     * Get ide
     *
     * @return integer 
     */
    public function getIde()
    {
        return $this->ide;
    }

    /**
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return BfObservadosBono
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
     * @return BfObservadosBono
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
     * @return BfObservadosBono
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
     * @return BfObservadosBono
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
     * @return BfObservadosBono
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
     * @return BfObservadosBono
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
     * @return BfObservadosBono
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
     * @return BfObservadosBono
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
     * @return BfObservadosBono
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
     * Set idins
     *
     * @param integer $idins
     * @return BfObservadosBono
     */
    public function setIdins($idins)
    {
        $this->idins = $idins;
    
        return $this;
    }

    /**
     * Get idins
     *
     * @return integer 
     */
    public function getIdins()
    {
        return $this->idins;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BfObservadosBono
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
}
