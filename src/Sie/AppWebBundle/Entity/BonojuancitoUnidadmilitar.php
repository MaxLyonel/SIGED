<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonojuancitoUnidadmilitar
 */
class BonojuancitoUnidadmilitar
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $unidadmilitar;

    /**
     * @var string
     */
    private $comandanteUnidad;

    /**
     * @var string
     */
    private $responsable;

    /**
     * @var string
     */
    private $telefonoR;

    /**
     * @var string
     */
    private $celular;

    /**
     * @var string
     */
    private $departamento;

    /**
     * @var string
     */
    private $provincia;

    /**
     * @var string
     */
    private $municipio;

    /**
     * @var string
     */
    private $canton;

    /**
     * @var string
     */
    private $localidad;

    /**
     * @var string
     */
    private $zona;

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
    private $correoElectronico;

    /**
     * @var string
     */
    private $referenciaUbicacion;

    /**
     * @var string
     */
    private $montoasignado;

    /**
     * @var string
     */
    private $montoentregado;

    /**
     * @var string
     */
    private $montorevertido;

    /**
     * @var string
     */
    private $montotransferido;

    /**
     * @var integer
     */
    private $codGranum;

    /**
     * @var string
     */
    private $responsablerp;

    /**
     * @var string
     */
    private $telefonoRp;

    /**
     * @var string
     */
    private $correoRp;

    /**
     * @var string
     */
    private $responsableRf;

    /**
     * @var string
     */
    private $telefonoRf;

    /**
     * @var string
     */
    private $correoRf;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\BonojuancitoGranUnidadmilitar
     */
    private $bonojuancitoGranUnidadmilitar;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


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
     * Set unidadmilitar
     *
     * @param string $unidadmilitar
     * @return BonojuancitoUnidadmilitar
     */
    public function setUnidadmilitar($unidadmilitar)
    {
        $this->unidadmilitar = $unidadmilitar;
    
        return $this;
    }

    /**
     * Get unidadmilitar
     *
     * @return string 
     */
    public function getUnidadmilitar()
    {
        return $this->unidadmilitar;
    }

    /**
     * Set comandanteUnidad
     *
     * @param string $comandanteUnidad
     * @return BonojuancitoUnidadmilitar
     */
    public function setComandanteUnidad($comandanteUnidad)
    {
        $this->comandanteUnidad = $comandanteUnidad;
    
        return $this;
    }

    /**
     * Get comandanteUnidad
     *
     * @return string 
     */
    public function getComandanteUnidad()
    {
        return $this->comandanteUnidad;
    }

    /**
     * Set responsable
     *
     * @param string $responsable
     * @return BonojuancitoUnidadmilitar
     */
    public function setResponsable($responsable)
    {
        $this->responsable = $responsable;
    
        return $this;
    }

    /**
     * Get responsable
     *
     * @return string 
     */
    public function getResponsable()
    {
        return $this->responsable;
    }

    /**
     * Set telefonoR
     *
     * @param string $telefonoR
     * @return BonojuancitoUnidadmilitar
     */
    public function setTelefonoR($telefonoR)
    {
        $this->telefonoR = $telefonoR;
    
        return $this;
    }

    /**
     * Get telefonoR
     *
     * @return string 
     */
    public function getTelefonoR()
    {
        return $this->telefonoR;
    }

    /**
     * Set celular
     *
     * @param string $celular
     * @return BonojuancitoUnidadmilitar
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
     * Set departamento
     *
     * @param string $departamento
     * @return BonojuancitoUnidadmilitar
     */
    public function setDepartamento($departamento)
    {
        $this->departamento = $departamento;
    
        return $this;
    }

    /**
     * Get departamento
     *
     * @return string 
     */
    public function getDepartamento()
    {
        return $this->departamento;
    }

    /**
     * Set provincia
     *
     * @param string $provincia
     * @return BonojuancitoUnidadmilitar
     */
    public function setProvincia($provincia)
    {
        $this->provincia = $provincia;
    
        return $this;
    }

    /**
     * Get provincia
     *
     * @return string 
     */
    public function getProvincia()
    {
        return $this->provincia;
    }

    /**
     * Set municipio
     *
     * @param string $municipio
     * @return BonojuancitoUnidadmilitar
     */
    public function setMunicipio($municipio)
    {
        $this->municipio = $municipio;
    
        return $this;
    }

    /**
     * Get municipio
     *
     * @return string 
     */
    public function getMunicipio()
    {
        return $this->municipio;
    }

    /**
     * Set canton
     *
     * @param string $canton
     * @return BonojuancitoUnidadmilitar
     */
    public function setCanton($canton)
    {
        $this->canton = $canton;
    
        return $this;
    }

    /**
     * Get canton
     *
     * @return string 
     */
    public function getCanton()
    {
        return $this->canton;
    }

    /**
     * Set localidad
     *
     * @param string $localidad
     * @return BonojuancitoUnidadmilitar
     */
    public function setLocalidad($localidad)
    {
        $this->localidad = $localidad;
    
        return $this;
    }

    /**
     * Get localidad
     *
     * @return string 
     */
    public function getLocalidad()
    {
        return $this->localidad;
    }

    /**
     * Set zona
     *
     * @param string $zona
     * @return BonojuancitoUnidadmilitar
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
     * Set direccion
     *
     * @param string $direccion
     * @return BonojuancitoUnidadmilitar
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
     * @return BonojuancitoUnidadmilitar
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
     * Set correoElectronico
     *
     * @param string $correoElectronico
     * @return BonojuancitoUnidadmilitar
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
     * Set referenciaUbicacion
     *
     * @param string $referenciaUbicacion
     * @return BonojuancitoUnidadmilitar
     */
    public function setReferenciaUbicacion($referenciaUbicacion)
    {
        $this->referenciaUbicacion = $referenciaUbicacion;
    
        return $this;
    }

    /**
     * Get referenciaUbicacion
     *
     * @return string 
     */
    public function getReferenciaUbicacion()
    {
        return $this->referenciaUbicacion;
    }

    /**
     * Set montoasignado
     *
     * @param string $montoasignado
     * @return BonojuancitoUnidadmilitar
     */
    public function setMontoasignado($montoasignado)
    {
        $this->montoasignado = $montoasignado;
    
        return $this;
    }

    /**
     * Get montoasignado
     *
     * @return string 
     */
    public function getMontoasignado()
    {
        return $this->montoasignado;
    }

    /**
     * Set montoentregado
     *
     * @param string $montoentregado
     * @return BonojuancitoUnidadmilitar
     */
    public function setMontoentregado($montoentregado)
    {
        $this->montoentregado = $montoentregado;
    
        return $this;
    }

    /**
     * Get montoentregado
     *
     * @return string 
     */
    public function getMontoentregado()
    {
        return $this->montoentregado;
    }

    /**
     * Set montorevertido
     *
     * @param string $montorevertido
     * @return BonojuancitoUnidadmilitar
     */
    public function setMontorevertido($montorevertido)
    {
        $this->montorevertido = $montorevertido;
    
        return $this;
    }

    /**
     * Get montorevertido
     *
     * @return string 
     */
    public function getMontorevertido()
    {
        return $this->montorevertido;
    }

    /**
     * Set montotransferido
     *
     * @param string $montotransferido
     * @return BonojuancitoUnidadmilitar
     */
    public function setMontotransferido($montotransferido)
    {
        $this->montotransferido = $montotransferido;
    
        return $this;
    }

    /**
     * Get montotransferido
     *
     * @return string 
     */
    public function getMontotransferido()
    {
        return $this->montotransferido;
    }

    /**
     * Set codGranum
     *
     * @param integer $codGranum
     * @return BonojuancitoUnidadmilitar
     */
    public function setCodGranum($codGranum)
    {
        $this->codGranum = $codGranum;
    
        return $this;
    }

    /**
     * Get codGranum
     *
     * @return integer 
     */
    public function getCodGranum()
    {
        return $this->codGranum;
    }

    /**
     * Set responsablerp
     *
     * @param string $responsablerp
     * @return BonojuancitoUnidadmilitar
     */
    public function setResponsablerp($responsablerp)
    {
        $this->responsablerp = $responsablerp;
    
        return $this;
    }

    /**
     * Get responsablerp
     *
     * @return string 
     */
    public function getResponsablerp()
    {
        return $this->responsablerp;
    }

    /**
     * Set telefonoRp
     *
     * @param string $telefonoRp
     * @return BonojuancitoUnidadmilitar
     */
    public function setTelefonoRp($telefonoRp)
    {
        $this->telefonoRp = $telefonoRp;
    
        return $this;
    }

    /**
     * Get telefonoRp
     *
     * @return string 
     */
    public function getTelefonoRp()
    {
        return $this->telefonoRp;
    }

    /**
     * Set correoRp
     *
     * @param string $correoRp
     * @return BonojuancitoUnidadmilitar
     */
    public function setCorreoRp($correoRp)
    {
        $this->correoRp = $correoRp;
    
        return $this;
    }

    /**
     * Get correoRp
     *
     * @return string 
     */
    public function getCorreoRp()
    {
        return $this->correoRp;
    }

    /**
     * Set responsableRf
     *
     * @param string $responsableRf
     * @return BonojuancitoUnidadmilitar
     */
    public function setResponsableRf($responsableRf)
    {
        $this->responsableRf = $responsableRf;
    
        return $this;
    }

    /**
     * Get responsableRf
     *
     * @return string 
     */
    public function getResponsableRf()
    {
        return $this->responsableRf;
    }

    /**
     * Set telefonoRf
     *
     * @param string $telefonoRf
     * @return BonojuancitoUnidadmilitar
     */
    public function setTelefonoRf($telefonoRf)
    {
        $this->telefonoRf = $telefonoRf;
    
        return $this;
    }

    /**
     * Get telefonoRf
     *
     * @return string 
     */
    public function getTelefonoRf()
    {
        return $this->telefonoRf;
    }

    /**
     * Set correoRf
     *
     * @param string $correoRf
     * @return BonojuancitoUnidadmilitar
     */
    public function setCorreoRf($correoRf)
    {
        $this->correoRf = $correoRf;
    
        return $this;
    }

    /**
     * Get correoRf
     *
     * @return string 
     */
    public function getCorreoRf()
    {
        return $this->correoRf;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BonojuancitoUnidadmilitar
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
     * Set bonojuancitoGranUnidadmilitar
     *
     * @param \Sie\AppWebBundle\Entity\BonojuancitoGranUnidadmilitar $bonojuancitoGranUnidadmilitar
     * @return BonojuancitoUnidadmilitar
     */
    public function setBonojuancitoGranUnidadmilitar(\Sie\AppWebBundle\Entity\BonojuancitoGranUnidadmilitar $bonojuancitoGranUnidadmilitar = null)
    {
        $this->bonojuancitoGranUnidadmilitar = $bonojuancitoGranUnidadmilitar;
    
        return $this;
    }

    /**
     * Get bonojuancitoGranUnidadmilitar
     *
     * @return \Sie\AppWebBundle\Entity\BonojuancitoGranUnidadmilitar 
     */
    public function getBonojuancitoGranUnidadmilitar()
    {
        return $this->bonojuancitoGranUnidadmilitar;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return BonojuancitoUnidadmilitar
     */
    public function setUsuario(\Sie\AppWebBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return BonojuancitoUnidadmilitar
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
}
