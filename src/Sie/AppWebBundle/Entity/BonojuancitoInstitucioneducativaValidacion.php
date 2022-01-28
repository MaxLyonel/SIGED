<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonojuancitoInstitucioneducativaValidacion
 */
class BonojuancitoInstitucioneducativaValidacion
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
     * @return BonojuancitoInstitucioneducativaValidacion
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
     * @return BonojuancitoInstitucioneducativaValidacion
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
     * @return BonojuancitoInstitucioneducativaValidacion
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
     * @return BonojuancitoInstitucioneducativaValidacion
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
     * @return BonojuancitoInstitucioneducativaValidacion
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
     * @return BonojuancitoInstitucioneducativaValidacion
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
     * @return BonojuancitoInstitucioneducativaValidacion
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
     * @return BonojuancitoInstitucioneducativaValidacion
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
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var integer
     */
    private $institucioneducativaIdNueva;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var \DateTime
     */
    private $fechaFinVal;

    /**
     * @var \DateTime
     */
    private $fechaFinEdit;

    /**
     * @var string
     */
    private $obs;


    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return BonojuancitoInstitucioneducativaValidacion
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
     * Set institucioneducativaIdNueva
     *
     * @param integer $institucioneducativaIdNueva
     * @return BonojuancitoInstitucioneducativaValidacion
     */
    public function setInstitucioneducativaIdNueva($institucioneducativaIdNueva)
    {
        $this->institucioneducativaIdNueva = $institucioneducativaIdNueva;
    
        return $this;
    }

    /**
     * Get institucioneducativaIdNueva
     *
     * @return integer 
     */
    public function getInstitucioneducativaIdNueva()
    {
        return $this->institucioneducativaIdNueva;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return BonojuancitoInstitucioneducativaValidacion
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }

    /**
     * Set fechaFinVal
     *
     * @param \DateTime $fechaFinVal
     * @return BonojuancitoInstitucioneducativaValidacion
     */
    public function setFechaFinVal($fechaFinVal)
    {
        $this->fechaFinVal = $fechaFinVal;
    
        return $this;
    }

    /**
     * Get fechaFinVal
     *
     * @return \DateTime 
     */
    public function getFechaFinVal()
    {
        return $this->fechaFinVal;
    }

    /**
     * Set fechaFinEdit
     *
     * @param \DateTime $fechaFinEdit
     * @return BonojuancitoInstitucioneducativaValidacion
     */
    public function setFechaFinEdit($fechaFinEdit)
    {
        $this->fechaFinEdit = $fechaFinEdit;
    
        return $this;
    }

    /**
     * Get fechaFinEdit
     *
     * @return \DateTime 
     */
    public function getFechaFinEdit()
    {
        return $this->fechaFinEdit;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BonojuancitoInstitucioneducativaValidacion
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
}
