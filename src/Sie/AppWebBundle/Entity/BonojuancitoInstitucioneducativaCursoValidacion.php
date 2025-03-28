<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonojuancitoInstitucioneducativaCursoValidacion
 */
class BonojuancitoInstitucioneducativaCursoValidacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var integer
     */
    private $nivelTipoId;

    /**
     * @var string
     */
    private $nivel;

    /**
     * @var integer
     */
    private $gradoTipoId;

    /**
     * @var string
     */
    private $grado;

    /**
     * @var string
     */
    private $paralelo;

    /**
     * @var integer
     */
    private $turnoTipoId;

    /**
     * @var string
     */
    private $turno;


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
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return BonojuancitoInstitucioneducativaCursoValidacion
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
     * Set nivelTipoId
     *
     * @param integer $nivelTipoId
     * @return BonojuancitoInstitucioneducativaCursoValidacion
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
     * Set nivel
     *
     * @param string $nivel
     * @return BonojuancitoInstitucioneducativaCursoValidacion
     */
    public function setNivel($nivel)
    {
        $this->nivel = $nivel;
    
        return $this;
    }

    /**
     * Get nivel
     *
     * @return string 
     */
    public function getNivel()
    {
        return $this->nivel;
    }

    /**
     * Set gradoTipoId
     *
     * @param integer $gradoTipoId
     * @return BonojuancitoInstitucioneducativaCursoValidacion
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
     * Set grado
     *
     * @param string $grado
     * @return BonojuancitoInstitucioneducativaCursoValidacion
     */
    public function setGrado($grado)
    {
        $this->grado = $grado;
    
        return $this;
    }

    /**
     * Get grado
     *
     * @return string 
     */
    public function getGrado()
    {
        return $this->grado;
    }

    /**
     * Set paralelo
     *
     * @param string $paralelo
     * @return BonojuancitoInstitucioneducativaCursoValidacion
     */
    public function setParalelo($paralelo)
    {
        $this->paralelo = $paralelo;
    
        return $this;
    }

    /**
     * Get paralelo
     *
     * @return string 
     */
    public function getParalelo()
    {
        return $this->paralelo;
    }

    /**
     * Set turnoTipoId
     *
     * @param integer $turnoTipoId
     * @return BonojuancitoInstitucioneducativaCursoValidacion
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
     * Set turno
     *
     * @param string $turno
     * @return BonojuancitoInstitucioneducativaCursoValidacion
     */
    public function setTurno($turno)
    {
        $this->turno = $turno;
    
        return $this;
    }

    /**
     * Get turno
     *
     * @return string 
     */
    public function getTurno()
    {
        return $this->turno;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return BonojuancitoInstitucioneducativaCursoValidacion
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
