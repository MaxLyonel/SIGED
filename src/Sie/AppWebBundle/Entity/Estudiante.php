<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Estudiante
 */
class Estudiante
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
     * @var string
     */
    private $oficialia;

    /**
     * @var string
     */
    private $libro;

    /**
     * @var string
     */
    private $partida;

    /**
     * @var string
     */
    private $folio;

    /**
     * @var integer
     */
    private $idiomaMaternoId;

    /**
     * @var integer
     */
    private $segipId;

    /**
     * @var string
     */
    private $complemento;

    /**
     * @var integer
     */
    private $bolean;

    /**
     * @var \DateTime
     */
    private $fechaNacimiento;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $correo;

    /**
     * @var string
     */
    private $localidadNac;

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
    private $resolucionaprovatoria;

    /**
     * @var string
     */
    private $carnetCodepedis;

    /**
     * @var string
     */
    private $observacionadicional;

    /**
     * @var string
     */
    private $carnetIbc;

    /**
     * @var string
     */
    private $libretaMilitar;

    /**
     * @var boolean
     */
    private $esDobleNacionalidad;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \Sie\AppWebBundle\Entity\GeneroTipo
     */
    private $generoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $lugarNacTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadoCivilTipo
     */
    private $estadoCivil;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $lugarProvNacTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\PaisTipo
     */
    private $paisTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\SangreTipo
     */
    private $sangreTipo;

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
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return Estudiante
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
     * @return Estudiante
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
     * Set pasaporte
     *
     * @param string $pasaporte
     * @return Estudiante
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
     * @return Estudiante
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
     * @return Estudiante
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
     * @return Estudiante
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
     * Set oficialia
     *
     * @param string $oficialia
     * @return Estudiante
     */
    public function setOficialia($oficialia)
    {
        $this->oficialia = $oficialia;
    
        return $this;
    }

    /**
     * Get oficialia
     *
     * @return string 
     */
    public function getOficialia()
    {
        return $this->oficialia;
    }

    /**
     * Set libro
     *
     * @param string $libro
     * @return Estudiante
     */
    public function setLibro($libro)
    {
        $this->libro = $libro;
    
        return $this;
    }

    /**
     * Get libro
     *
     * @return string 
     */
    public function getLibro()
    {
        return $this->libro;
    }

    /**
     * Set partida
     *
     * @param string $partida
     * @return Estudiante
     */
    public function setPartida($partida)
    {
        $this->partida = $partida;
    
        return $this;
    }

    /**
     * Get partida
     *
     * @return string 
     */
    public function getPartida()
    {
        return $this->partida;
    }

    /**
     * Set folio
     *
     * @param string $folio
     * @return Estudiante
     */
    public function setFolio($folio)
    {
        $this->folio = $folio;
    
        return $this;
    }

    /**
     * Get folio
     *
     * @return string 
     */
    public function getFolio()
    {
        return $this->folio;
    }

    /**
     * Set idiomaMaternoId
     *
     * @param integer $idiomaMaternoId
     * @return Estudiante
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
     * Set segipId
     *
     * @param integer $segipId
     * @return Estudiante
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
     * @return Estudiante
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
     * Set bolean
     *
     * @param integer $bolean
     * @return Estudiante
     */
    public function setBolean($bolean)
    {
        $this->bolean = $bolean;
    
        return $this;
    }

    /**
     * Get bolean
     *
     * @return integer 
     */
    public function getBolean()
    {
        return $this->bolean;
    }

    /**
     * Set fechaNacimiento
     *
     * @param \DateTime $fechaNacimiento
     * @return Estudiante
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
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return Estudiante
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
     * Set correo
     *
     * @param string $correo
     * @return Estudiante
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
     * Set localidadNac
     *
     * @param string $localidadNac
     * @return Estudiante
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
     * Set foto
     *
     * @param string $foto
     * @return Estudiante
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
     * @return Estudiante
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
     * Set resolucionaprovatoria
     *
     * @param string $resolucionaprovatoria
     * @return Estudiante
     */
    public function setResolucionaprovatoria($resolucionaprovatoria)
    {
        $this->resolucionaprovatoria = $resolucionaprovatoria;
    
        return $this;
    }

    /**
     * Get resolucionaprovatoria
     *
     * @return string 
     */
    public function getResolucionaprovatoria()
    {
        return $this->resolucionaprovatoria;
    }

    /**
     * Set carnetCodepedis
     *
     * @param string $carnetCodepedis
     * @return Estudiante
     */
    public function setCarnetCodepedis($carnetCodepedis)
    {
        $this->carnetCodepedis = $carnetCodepedis;
    
        return $this;
    }

    /**
     * Get carnetCodepedis
     *
     * @return string 
     */
    public function getCarnetCodepedis()
    {
        return $this->carnetCodepedis;
    }

    /**
     * Set observacionadicional
     *
     * @param string $observacionadicional
     * @return Estudiante
     */
    public function setObservacionadicional($observacionadicional)
    {
        $this->observacionadicional = $observacionadicional;
    
        return $this;
    }

    /**
     * Get observacionadicional
     *
     * @return string 
     */
    public function getObservacionadicional()
    {
        return $this->observacionadicional;
    }

    /**
     * Set carnetIbc
     *
     * @param string $carnetIbc
     * @return Estudiante
     */
    public function setCarnetIbc($carnetIbc)
    {
        $this->carnetIbc = $carnetIbc;
    
        return $this;
    }

    /**
     * Get carnetIbc
     *
     * @return string 
     */
    public function getCarnetIbc()
    {
        return $this->carnetIbc;
    }

    /**
     * Set libretaMilitar
     *
     * @param string $libretaMilitar
     * @return Estudiante
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
     * Set esDobleNacionalidad
     *
     * @param boolean $esDobleNacionalidad
     * @return Estudiante
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
     * @return Estudiante
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
     * Set generoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GeneroTipo $generoTipo
     * @return Estudiante
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
     * Set lugarNacTipo
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $lugarNacTipo
     * @return Estudiante
     */
    public function setLugarNacTipo(\Sie\AppWebBundle\Entity\LugarTipo $lugarNacTipo = null)
    {
        $this->lugarNacTipo = $lugarNacTipo;
    
        return $this;
    }

    /**
     * Get lugarNacTipo
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getLugarNacTipo()
    {
        return $this->lugarNacTipo;
    }

    /**
     * Set estadoCivil
     *
     * @param \Sie\AppWebBundle\Entity\EstadoCivilTipo $estadoCivil
     * @return Estudiante
     */
    public function setEstadoCivil(\Sie\AppWebBundle\Entity\EstadoCivilTipo $estadoCivil = null)
    {
        $this->estadoCivil = $estadoCivil;
    
        return $this;
    }

    /**
     * Get estadoCivil
     *
     * @return \Sie\AppWebBundle\Entity\EstadoCivilTipo 
     */
    public function getEstadoCivil()
    {
        return $this->estadoCivil;
    }

    /**
     * Set lugarProvNacTipo
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $lugarProvNacTipo
     * @return Estudiante
     */
    public function setLugarProvNacTipo(\Sie\AppWebBundle\Entity\LugarTipo $lugarProvNacTipo = null)
    {
        $this->lugarProvNacTipo = $lugarProvNacTipo;
    
        return $this;
    }

    /**
     * Get lugarProvNacTipo
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getLugarProvNacTipo()
    {
        return $this->lugarProvNacTipo;
    }

    /**
     * Set paisTipo
     *
     * @param \Sie\AppWebBundle\Entity\PaisTipo $paisTipo
     * @return Estudiante
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
     * Set sangreTipo
     *
     * @param \Sie\AppWebBundle\Entity\SangreTipo $sangreTipo
     * @return Estudiante
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
     * Set expedido
     *
     * @param \Sie\AppWebBundle\Entity\DepartamentoTipo $expedido
     * @return Estudiante
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
