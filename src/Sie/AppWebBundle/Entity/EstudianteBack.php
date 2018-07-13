<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteBack
 */
class EstudianteBack
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
     * @var integer
     */
    private $generoTipoId;

    /**
     * @var integer
     */
    private $estadoCivilId;

    /**
     * @var integer
     */
    private $lugarNacTipoId;

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
    private $sangreTipoId;

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
     * @var integer
     */
    private $paisTipoId;

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
     * @var integer
     */
    private $lugarProvNacTipoId;

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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * Set estadoCivilId
     *
     * @param integer $estadoCivilId
     * @return EstudianteBack
     */
    public function setEstadoCivilId($estadoCivilId)
    {
        $this->estadoCivilId = $estadoCivilId;
    
        return $this;
    }

    /**
     * Get estadoCivilId
     *
     * @return integer 
     */
    public function getEstadoCivilId()
    {
        return $this->estadoCivilId;
    }

    /**
     * Set lugarNacTipoId
     *
     * @param integer $lugarNacTipoId
     * @return EstudianteBack
     */
    public function setLugarNacTipoId($lugarNacTipoId)
    {
        $this->lugarNacTipoId = $lugarNacTipoId;
    
        return $this;
    }

    /**
     * Get lugarNacTipoId
     *
     * @return integer 
     */
    public function getLugarNacTipoId()
    {
        return $this->lugarNacTipoId;
    }

    /**
     * Set oficialia
     *
     * @param string $oficialia
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * Set sangreTipoId
     *
     * @param integer $sangreTipoId
     * @return EstudianteBack
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
     * Set idiomaMaternoId
     *
     * @param integer $idiomaMaternoId
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * Set paisTipoId
     *
     * @param integer $paisTipoId
     * @return EstudianteBack
     */
    public function setPaisTipoId($paisTipoId)
    {
        $this->paisTipoId = $paisTipoId;
    
        return $this;
    }

    /**
     * Get paisTipoId
     *
     * @return integer 
     */
    public function getPaisTipoId()
    {
        return $this->paisTipoId;
    }

    /**
     * Set localidadNac
     *
     * @param string $localidadNac
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * Set lugarProvNacTipoId
     *
     * @param integer $lugarProvNacTipoId
     * @return EstudianteBack
     */
    public function setLugarProvNacTipoId($lugarProvNacTipoId)
    {
        $this->lugarProvNacTipoId = $lugarProvNacTipoId;
    
        return $this;
    }

    /**
     * Get lugarProvNacTipoId
     *
     * @return integer 
     */
    public function getLugarProvNacTipoId()
    {
        return $this->lugarProvNacTipoId;
    }

    /**
     * Set libretaMilitar
     *
     * @param string $libretaMilitar
     * @return EstudianteBack
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
     * @return EstudianteBack
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
     * @return EstudianteBack
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
}
