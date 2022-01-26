<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UesObservadas
 */
class UesObservadas
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $idDepartamento;

    /**
     * @var string
     */
    private $descDepartamento;

    /**
     * @var string
     */
    private $codDistrito;

    /**
     * @var string
     */
    private $distrito;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var string
     */
    private $institucioneducativa;

    /**
     * @var integer
     */
    private $turnoTipoId;

    /**
     * @var integer
     */
    private $nivelTipoId;

    /**
     * @var integer
     */
    private $gradoTipoId;

    /**
     * @var string
     */
    private $paraleloTipoId;

    /**
     * @var string
     */
    private $codigoRude;

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
    private $estadomatriculaTipoId;

    /**
     * @var integer
     */
    private $estadomatriculaInicioTipoId;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var boolean
     */
    private $estado;


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
     * Set idDepartamento
     *
     * @param string $idDepartamento
     * @return UesObservadas
     */
    public function setIdDepartamento($idDepartamento)
    {
        $this->idDepartamento = $idDepartamento;
    
        return $this;
    }

    /**
     * Get idDepartamento
     *
     * @return string 
     */
    public function getIdDepartamento()
    {
        return $this->idDepartamento;
    }

    /**
     * Set descDepartamento
     *
     * @param string $descDepartamento
     * @return UesObservadas
     */
    public function setDescDepartamento($descDepartamento)
    {
        $this->descDepartamento = $descDepartamento;
    
        return $this;
    }

    /**
     * Get descDepartamento
     *
     * @return string 
     */
    public function getDescDepartamento()
    {
        return $this->descDepartamento;
    }

    /**
     * Set codDistrito
     *
     * @param string $codDistrito
     * @return UesObservadas
     */
    public function setCodDistrito($codDistrito)
    {
        $this->codDistrito = $codDistrito;
    
        return $this;
    }

    /**
     * Get codDistrito
     *
     * @return string 
     */
    public function getCodDistrito()
    {
        return $this->codDistrito;
    }

    /**
     * Set distrito
     *
     * @param string $distrito
     * @return UesObservadas
     */
    public function setDistrito($distrito)
    {
        $this->distrito = $distrito;
    
        return $this;
    }

    /**
     * Get distrito
     *
     * @return string 
     */
    public function getDistrito()
    {
        return $this->distrito;
    }

    /**
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return UesObservadas
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
     * Set institucioneducativa
     *
     * @param string $institucioneducativa
     * @return UesObservadas
     */
    public function setInstitucioneducativa($institucioneducativa)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return string 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set turnoTipoId
     *
     * @param integer $turnoTipoId
     * @return UesObservadas
     */
    public function setTurnoTipoId($turnoTipoId)
    {
        $this->turnoTipoId = $turnoTipoId;
    
        return $this;
    }

    /**
     * Get turnoTipoId
     *
     * @return integer 
     */
    public function getTurnoTipoId()
    {
        return $this->turnoTipoId;
    }

    /**
     * Set nivelTipoId
     *
     * @param integer $nivelTipoId
     * @return UesObservadas
     */
    public function setNivelTipoId($nivelTipoId)
    {
        $this->nivelTipoId = $nivelTipoId;
    
        return $this;
    }

    /**
     * Get nivelTipoId
     *
     * @return integer 
     */
    public function getNivelTipoId()
    {
        return $this->nivelTipoId;
    }

    /**
     * Set gradoTipoId
     *
     * @param integer $gradoTipoId
     * @return UesObservadas
     */
    public function setGradoTipoId($gradoTipoId)
    {
        $this->gradoTipoId = $gradoTipoId;
    
        return $this;
    }

    /**
     * Get gradoTipoId
     *
     * @return integer 
     */
    public function getGradoTipoId()
    {
        return $this->gradoTipoId;
    }

    /**
     * Set paraleloTipoId
     *
     * @param string $paraleloTipoId
     * @return UesObservadas
     */
    public function setParaleloTipoId($paraleloTipoId)
    {
        $this->paraleloTipoId = $paraleloTipoId;
    
        return $this;
    }

    /**
     * Get paraleloTipoId
     *
     * @return string 
     */
    public function getParaleloTipoId()
    {
        return $this->paraleloTipoId;
    }

    /**
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return UesObservadas
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
     * Set paterno
     *
     * @param string $paterno
     * @return UesObservadas
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
     * @return UesObservadas
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
     * @return UesObservadas
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
     * Set estadomatriculaTipoId
     *
     * @param integer $estadomatriculaTipoId
     * @return UesObservadas
     */
    public function setEstadomatriculaTipoId($estadomatriculaTipoId)
    {
        $this->estadomatriculaTipoId = $estadomatriculaTipoId;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipoId
     *
     * @return integer 
     */
    public function getEstadomatriculaTipoId()
    {
        return $this->estadomatriculaTipoId;
    }

    /**
     * Set estadomatriculaInicioTipoId
     *
     * @param integer $estadomatriculaInicioTipoId
     * @return UesObservadas
     */
    public function setEstadomatriculaInicioTipoId($estadomatriculaInicioTipoId)
    {
        $this->estadomatriculaInicioTipoId = $estadomatriculaInicioTipoId;
    
        return $this;
    }

    /**
     * Get estadomatriculaInicioTipoId
     *
     * @return integer 
     */
    public function getEstadomatriculaInicioTipoId()
    {
        return $this->estadomatriculaInicioTipoId;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return UesObservadas
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
     * Set estado
     *
     * @param boolean $estado
     * @return UesObservadas
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return boolean 
     */
    public function getEstado()
    {
        return $this->estado;
    }
}
