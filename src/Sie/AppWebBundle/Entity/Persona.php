<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Persona
 */
class Persona
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $idiomaMaternoId;

    /**
     * @var integer
     */
    private $generoTipoId;

    /**
     * @var integer
     */
    private $sangreTipoId;

    /**
     * @var integer
     */
    private $estadocivilTipoId;

    /**
     * @var string
     */
    private $carnet;

    /**
     * @var integer
     */
    private $rda;

    /**
     * @var string
     */
    private $libretaMilitar;

    /**
     * @var string
     */
    private $pasaporte;

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
     * @var string
     */
    private $complemento;

    /**
     * @var boolean
     */
    private $activo;

    /**
     * @var string
     */
    private $correo;

    /**
     * @var string
     */
    private $foto;

    /**
     * @var string
     */
    private $celular;

    /**
     * @var string
     */
    private $direccion;

    /**
     * @var boolean
     */
    private $esvigente;

    /**
     * @var integer
     */
    private $esvigenteApoderado;

    /**
     * @var integer
     */
    private $countEdit;

    /**
     * @var string
     */
    private $obsSegip;

    /**
     * @var boolean
     */
    private $esExtranjero;

    /**
     * @var string
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
     * Set idiomaMaternoId
     *
     * @param integer $idiomaMaternoId
     * @return Persona
     */
    public function setIdiomaMaternoId($idiomaMaternoId)
    {
        $this->idiomaMaternoId = $idiomaMaternoId;
    
        return $this;
    }

    /**
     * Get idiomaMaternoId
     *
     * @return integer 
     */
    public function getIdiomaMaternoId()
    {
        return $this->idiomaMaternoId;
    }

    /**
     * Set generoTipoId
     *
     * @param integer $generoTipoId
     * @return Persona
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
     * Set sangreTipoId
     *
     * @param integer $sangreTipoId
     * @return Persona
     */
    public function setSangreTipoId($sangreTipoId)
    {
        $this->sangreTipoId = $sangreTipoId;
    
        return $this;
    }

    /**
     * Get sangreTipoId
     *
     * @return integer 
     */
    public function getSangreTipoId()
    {
        return $this->sangreTipoId;
    }

    /**
     * Set estadocivilTipoId
     *
     * @param integer $estadocivilTipoId
     * @return Persona
     */
    public function setEstadocivilTipoId($estadocivilTipoId)
    {
        $this->estadocivilTipoId = $estadocivilTipoId;
    
        return $this;
    }

    /**
     * Get estadocivilTipoId
     *
     * @return integer 
     */
    public function getEstadocivilTipoId()
    {
        return $this->estadocivilTipoId;
    }

    /**
     * Set carnet
     *
     * @param string $carnet
     * @return Persona
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
     * Set rda
     *
     * @param integer $rda
     * @return Persona
     */
    public function setRda($rda)
    {
        $this->rda = $rda;
    
        return $this;
    }

    /**
     * Get rda
     *
     * @return integer 
     */
    public function getRda()
    {
        return $this->rda;
    }

    /**
     * Set libretaMilitar
     *
     * @param string $libretaMilitar
     * @return Persona
     */
    public function setLibretaMilitar($libretaMilitar)
    {
        $this->libretaMilitar = $libretaMilitar;
    
        return $this;
    }

    /**
     * Get libretaMilitar
     *
     * @return string 
     */
    public function getLibretaMilitar()
    {
        return $this->libretaMilitar;
    }

    /**
     * Set pasaporte
     *
     * @param string $pasaporte
     * @return Persona
     */
    public function setPasaporte($pasaporte)
    {
        $this->pasaporte = $pasaporte;
    
        return $this;
    }

    /**
     * Get pasaporte
     *
     * @return string 
     */
    public function getPasaporte()
    {
        return $this->pasaporte;
    }

    /**
     * Set paterno
     *
     * @param string $paterno
     * @return Persona
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
     * @return Persona
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
     * @return Persona
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
     * @return Persona
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
     * @return Persona
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
     * Set complemento
     *
     * @param string $complemento
     * @return Persona
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
     * Set activo
     *
     * @param boolean $activo
     * @return Persona
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    
        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean 
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set correo
     *
     * @param string $correo
     * @return Persona
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
     * Set foto
     *
     * @param string $foto
     * @return Persona
     */
    public function setFoto($foto)
    {
        $this->foto = $foto;
    
        return $this;
    }

    /**
     * Get foto
     *
     * @return string 
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * Set celular
     *
     * @param string $celular
     * @return Persona
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
     * Set direccion
     *
     * @param string $direccion
     * @return Persona
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
     * Set esvigente
     *
     * @param boolean $esvigente
     * @return Persona
     */
    public function setEsvigente($esvigente)
    {
        $this->esvigente = $esvigente;
    
        return $this;
    }

    /**
     * Get esvigente
     *
     * @return boolean 
     */
    public function getEsvigente()
    {
        return $this->esvigente;
    }

    /**
     * Set esvigenteApoderado
     *
     * @param integer $esvigenteApoderado
     * @return Persona
     */
    public function setEsvigenteApoderado($esvigenteApoderado)
    {
        $this->esvigenteApoderado = $esvigenteApoderado;
    
        return $this;
    }

    /**
     * Get esvigenteApoderado
     *
     * @return integer 
     */
    public function getEsvigenteApoderado()
    {
        return $this->esvigenteApoderado;
    }

    /**
     * Set countEdit
     *
     * @param integer $countEdit
     * @return Persona
     */
    public function setCountEdit($countEdit)
    {
        $this->countEdit = $countEdit;
    
        return $this;
    }

    /**
     * Get countEdit
     *
     * @return integer 
     */
    public function getCountEdit()
    {
        return $this->countEdit;
    }

    /**
     * Set obsSegip
     *
     * @param string $obsSegip
     * @return Persona
     */
    public function setObsSegip($obsSegip)
    {
        $this->obsSegip = $obsSegip;
    
        return $this;
    }

    /**
     * Get obsSegip
     *
     * @return string 
     */
    public function getObsSegip()
    {
        return $this->obsSegip;
    }

    /**
     * Set esExtranjero
     *
     * @param boolean $esExtranjero
     * @return Persona
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
     * Set expedido
     *
     * @param string $expedido
     * @return Persona
     */
    public function setExpedido($expedido)
    {
        $this->expedido = $expedido;
    
        return $this;
    }

    /**
     * Get expedido
     *
     * @return string 
     */
    public function getExpedido()
    {
        return $this->expedido;
    }
}
