<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ComisionJuegosDatos
 */
class ComisionJuegosDatos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
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
     * @param integer $carnetIdentidad
     * @return ComisionJuegosDatos
     */
    public function setCarnetIdentidad($carnetIdentidad)
    {
        $this->carnetIdentidad = $carnetIdentidad;
    
        return $this;
    }

    /**
     * Get carnetIdentidad
     *
     * @return integer 
     */
    public function getCarnetIdentidad()
    {
        return $this->carnetIdentidad;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * @var \Sie\AppWebBundle\Entity\PruebaTipo
     */
    private $pruebaTipo;


    /**
     * Set foto
     *
     * @param string $foto
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * Set pruebaTipo
     *
     * @param \Sie\AppWebBundle\Entity\PruebaTipo $pruebaTipo
     * @return ComisionJuegosDatos
     */
    public function setPruebaTipo(\Sie\AppWebBundle\Entity\PruebaTipo $pruebaTipo = null)
    {
        $this->pruebaTipo = $pruebaTipo;
    
        return $this;
    }

    /**
     * Get pruebaTipo
     *
     * @return \Sie\AppWebBundle\Entity\PruebaTipo 
     */
    public function getPruebaTipo()
    {
        return $this->pruebaTipo;
    }
    /**
     * @var integer
     */
    private $posicion;

    /**
     * @var string
     */
    private $avc;


    /**
     * Set posicion
     *
     * @param integer $posicion
     * @return ComisionJuegosDatos
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
     * @return ComisionJuegosDatos
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
     * @var \Sie\AppWebBundle\Entity\FaseTipo
     */
    private $faseTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;


    /**
     * Set faseTipo
     *
     * @param \Sie\AppWebBundle\Entity\FaseTipo $faseTipo
     * @return ComisionJuegosDatos
     */
    public function setFaseTipo(\Sie\AppWebBundle\Entity\FaseTipo $faseTipo = null)
    {
        $this->faseTipo = $faseTipo;
    
        return $this;
    }

    /**
     * Get faseTipo
     *
     * @return \Sie\AppWebBundle\Entity\FaseTipo 
     */
    public function getFaseTipo()
    {
        return $this->faseTipo;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return ComisionJuegosDatos
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
}
