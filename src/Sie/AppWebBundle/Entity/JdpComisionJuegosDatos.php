<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpComisionJuegosDatos
 */
class JdpComisionJuegosDatos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $carnetIdentidad;

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
     * @var integer
     */
    private $celular;

    /**
     * @var string
     */
    private $correo;

    /**
     * @var integer
     */
    private $comisionTipoId;

    /**
     * @var string
     */
    private $foto;

    /**
     * @var integer
     */
    private $generoTipo;

    /**
     * @var integer
     */
    private $disciplinaTipoId;

    /**
     * @var integer
     */
    private $departamentoTipo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var boolean
     */
    private $esentrenador;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var integer
     */
    private $posicion;

    /**
     * @var string
     */
    private $avc;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpFaseTipo
     */
    private $faseTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpPruebaTipo
     */
    private $pruebaTipo;


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
     * Set carnetIdentidad
     *
     * @param string $carnetIdentidad
     * @return JdpComisionJuegosDatos
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
     * Set nombre
     *
     * @param string $nombre
     * @return JdpComisionJuegosDatos
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
     * @return JdpComisionJuegosDatos
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
     * @return JdpComisionJuegosDatos
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
     * Set celular
     *
     * @param integer $celular
     * @return JdpComisionJuegosDatos
     */
    public function setCelular($celular)
    {
        $this->celular = $celular;
    
        return $this;
    }

    /**
     * Get celular
     *
     * @return integer 
     */
    public function getCelular()
    {
        return $this->celular;
    }

    /**
     * Set correo
     *
     * @param string $correo
     * @return JdpComisionJuegosDatos
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
     * Set comisionTipoId
     *
     * @param integer $comisionTipoId
     * @return JdpComisionJuegosDatos
     */
    public function setComisionTipoId($comisionTipoId)
    {
        $this->comisionTipoId = $comisionTipoId;
    
        return $this;
    }

    /**
     * Get comisionTipoId
     *
     * @return integer 
     */
    public function getComisionTipoId()
    {
        return $this->comisionTipoId;
    }

    /**
     * Set foto
     *
     * @param string $foto
     * @return JdpComisionJuegosDatos
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
     * Set generoTipo
     *
     * @param integer $generoTipo
     * @return JdpComisionJuegosDatos
     */
    public function setGeneroTipo($generoTipo)
    {
        $this->generoTipo = $generoTipo;
    
        return $this;
    }

    /**
     * Get generoTipo
     *
     * @return integer 
     */
    public function getGeneroTipo()
    {
        return $this->generoTipo;
    }

    /**
     * Set disciplinaTipoId
     *
     * @param integer $disciplinaTipoId
     * @return JdpComisionJuegosDatos
     */
    public function setDisciplinaTipoId($disciplinaTipoId)
    {
        $this->disciplinaTipoId = $disciplinaTipoId;
    
        return $this;
    }

    /**
     * Get disciplinaTipoId
     *
     * @return integer 
     */
    public function getDisciplinaTipoId()
    {
        return $this->disciplinaTipoId;
    }

    /**
     * Set departamentoTipo
     *
     * @param integer $departamentoTipo
     * @return JdpComisionJuegosDatos
     */
    public function setDepartamentoTipo($departamentoTipo)
    {
        $this->departamentoTipo = $departamentoTipo;
    
        return $this;
    }

    /**
     * Get departamentoTipo
     *
     * @return integer 
     */
    public function getDepartamentoTipo()
    {
        return $this->departamentoTipo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return JdpComisionJuegosDatos
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
     * Set esentrenador
     *
     * @param boolean $esentrenador
     * @return JdpComisionJuegosDatos
     */
    public function setEsentrenador($esentrenador)
    {
        $this->esentrenador = $esentrenador;
    
        return $this;
    }

    /**
     * Get esentrenador
     *
     * @return boolean 
     */
    public function getEsentrenador()
    {
        return $this->esentrenador;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return JdpComisionJuegosDatos
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
     * Set posicion
     *
     * @param integer $posicion
     * @return JdpComisionJuegosDatos
     */
    public function setPosicion($posicion)
    {
        $this->posicion = $posicion;
    
        return $this;
    }

    /**
     * Get posicion
     *
     * @return integer 
     */
    public function getPosicion()
    {
        return $this->posicion;
    }

    /**
     * Set avc
     *
     * @param string $avc
     * @return JdpComisionJuegosDatos
     */
    public function setAvc($avc)
    {
        $this->avc = $avc;
    
        return $this;
    }

    /**
     * Get avc
     *
     * @return string 
     */
    public function getAvc()
    {
        return $this->avc;
    }

    /**
     * Set faseTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpFaseTipo $faseTipo
     * @return JdpComisionJuegosDatos
     */
    public function setFaseTipo(\Sie\AppWebBundle\Entity\JdpFaseTipo $faseTipo = null)
    {
        $this->faseTipo = $faseTipo;
    
        return $this;
    }

    /**
     * Get faseTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpFaseTipo 
     */
    public function getFaseTipo()
    {
        return $this->faseTipo;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return JdpComisionJuegosDatos
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set pruebaTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpPruebaTipo $pruebaTipo
     * @return JdpComisionJuegosDatos
     */
    public function setPruebaTipo(\Sie\AppWebBundle\Entity\JdpPruebaTipo $pruebaTipo = null)
    {
        $this->pruebaTipo = $pruebaTipo;
    
        return $this;
    }

    /**
     * Get pruebaTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpPruebaTipo 
     */
    public function getPruebaTipo()
    {
        return $this->pruebaTipo;
    }
}
