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
    private $localidadNac;

    /**
     * @var string
     */
    private $apellidoEsposo;

    /**
     * @var \Sie\AppWebBundle\Entity\SangreTipo
     */
    private $sangreTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaMaterno;

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
     * @var \Sie\AppWebBundle\Entity\EstadoCivilTipo
     */
    private $estadocivilTipo;


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
     * Set localidadNac
     *
     * @param string $localidadNac
     * @return Persona
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
     * @return Persona
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
     * Set sangreTipo
     *
     * @param \Sie\AppWebBundle\Entity\SangreTipo $sangreTipo
     * @return Persona
     */
    public function setSangreTipo(\Sie\AppWebBundle\Entity\SangreTipo $sangreTipo = null)
    {
        $this->sangreTipo = $sangreTipo;
    
        return $this;
    }

    /**
     * Get sangreTipo
     *
     * @return \Sie\AppWebBundle\Entity\SangreTipo 
     */
    public function getSangreTipo()
    {
        return $this->sangreTipo;
    }

    /**
     * Set idiomaMaterno
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaMaterno
     * @return Persona
     */
    public function setIdiomaMaterno(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaMaterno = null)
    {
        $this->idiomaMaterno = $idiomaMaterno;
    
        return $this;
    }

    /**
     * Get idiomaMaterno
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaMaterno()
    {
        return $this->idiomaMaterno;
    }

    /**
     * Set generoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GeneroTipo $generoTipo
     * @return Persona
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
     * @return Persona
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
     * @return Persona
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
     * Set estadocivilTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstadoCivilTipo $estadocivilTipo
     * @return Persona
     */
    public function setEstadocivilTipo(\Sie\AppWebBundle\Entity\EstadoCivilTipo $estadocivilTipo = null)
    {
        $this->estadocivilTipo = $estadocivilTipo;
    
        return $this;
    }

    /**
     * Get estadocivilTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstadoCivilTipo 
     */
    public function getEstadocivilTipo()
    {
        return $this->estadocivilTipo;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\CedulaTipo
     */
    private $cedulaTipo;


    /**
     * Set cedulaTipo
     *
     * @param \Sie\AppWebBundle\Entity\CedulaTipo $cedulaTipo
     * @return Persona
     */
    public function setCedulaTipo(\Sie\AppWebBundle\Entity\CedulaTipo $cedulaTipo = null)
    {
        $this->cedulaTipo = $cedulaTipo;
    
        return $this;
    }

    /**
     * Get cedulaTipo
     *
     * @return \Sie\AppWebBundle\Entity\CedulaTipo 
     */
    public function getCedulaTipo()
    {
        return $this->cedulaTipo;
    }
}
