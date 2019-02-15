<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Homologacion
 */
class Homologacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombreInstitucioneducativa;

    /**
     * @var integer
     */
    private $gestionId;

    /**
     * @var string
     */
    private $rudeal;

    /**
     * @var integer
     */
    private $nivelId;

    /**
     * @var integer
     */
    private $cicloId;

    /**
     * @var integer
     */
    private $gradoId;

    /**
     * @var integer
     */
    private $cargaHoraria;

    /**
     * @var string
     */
    private $nroCertificado;

    /**
     * @var integer
     */
    private $usuarioId;

    /**
     * @var string
     */
    private $fechaReg;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;


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
     * Set nombreInstitucioneducativa
     *
     * @param string $nombreInstitucioneducativa
     * @return Homologacion
     */
    public function setNombreInstitucioneducativa($nombreInstitucioneducativa)
    {
        $this->nombreInstitucioneducativa = $nombreInstitucioneducativa;
    
        return $this;
    }

    /**
     * Get nombreInstitucioneducativa
     *
     * @return string 
     */
    public function getNombreInstitucioneducativa()
    {
        return $this->nombreInstitucioneducativa;
    }

    /**
     * Set gestionId
     *
     * @param integer $gestionId
     * @return Homologacion
     */
    public function setGestionId($gestionId)
    {
        $this->gestionId = $gestionId;
    
        return $this;
    }

    /**
     * Get gestionId
     *
     * @return integer 
     */
    public function getGestionId()
    {
        return $this->gestionId;
    }

    /**
     * Set rudeal
     *
     * @param string $rudeal
     * @return Homologacion
     */
    public function setRudeal($rudeal)
    {
        $this->rudeal = $rudeal;
    
        return $this;
    }

    /**
     * Get rudeal
     *
     * @return string 
     */
    public function getRudeal()
    {
        return $this->rudeal;
    }

    /**
     * Set nivelId
     *
     * @param integer $nivelId
     * @return Homologacion
     */
    public function setNivelId($nivelId)
    {
        $this->nivelId = $nivelId;
    
        return $this;
    }

    /**
     * Get nivelId
     *
     * @return integer 
     */
    public function getNivelId()
    {
        return $this->nivelId;
    }

    /**
     * Set cicloId
     *
     * @param integer $cicloId
     * @return Homologacion
     */
    public function setCicloId($cicloId)
    {
        $this->cicloId = $cicloId;
    
        return $this;
    }

    /**
     * Get cicloId
     *
     * @return integer 
     */
    public function getCicloId()
    {
        return $this->cicloId;
    }

    /**
     * Set gradoId
     *
     * @param integer $gradoId
     * @return Homologacion
     */
    public function setGradoId($gradoId)
    {
        $this->gradoId = $gradoId;
    
        return $this;
    }

    /**
     * Get gradoId
     *
     * @return integer 
     */
    public function getGradoId()
    {
        return $this->gradoId;
    }

    /**
     * Set cargaHoraria
     *
     * @param integer $cargaHoraria
     * @return Homologacion
     */
    public function setCargaHoraria($cargaHoraria)
    {
        $this->cargaHoraria = $cargaHoraria;
    
        return $this;
    }

    /**
     * Get cargaHoraria
     *
     * @return integer 
     */
    public function getCargaHoraria()
    {
        return $this->cargaHoraria;
    }

    /**
     * Set nroCertificado
     *
     * @param string $nroCertificado
     * @return Homologacion
     */
    public function setNroCertificado($nroCertificado)
    {
        $this->nroCertificado = $nroCertificado;
    
        return $this;
    }

    /**
     * Get nroCertificado
     *
     * @return string 
     */
    public function getNroCertificado()
    {
        return $this->nroCertificado;
    }

    /**
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return Homologacion
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set fechaReg
     *
     * @param string $fechaReg
     * @return Homologacion
     */
    public function setFechaReg($fechaReg)
    {
        $this->fechaReg = $fechaReg;
    
        return $this;
    }

    /**
     * Get fechaReg
     *
     * @return string 
     */
    public function getFechaReg()
    {
        return $this->fechaReg;
    }

    /**
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return Homologacion
     */
    public function setEstudiante(\Sie\AppWebBundle\Entity\Estudiante $estudiante = null)
    {
        $this->estudiante = $estudiante;
    
        return $this;
    }

    /**
     * Get estudiante
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getEstudiante()
    {
        return $this->estudiante;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return Homologacion
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
