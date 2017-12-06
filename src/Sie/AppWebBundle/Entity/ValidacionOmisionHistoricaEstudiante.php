<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ValidacionOmisionHistoricaEstudiante
 */
class ValidacionOmisionHistoricaEstudiante
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaProceso;

    /**
     * @var integer
     */
    private $gestionTipoId;

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
    private $codigoRude;


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
     * Set fechaProceso
     *
     * @param \DateTime $fechaProceso
     * @return ValidacionOmisionHistoricaEstudiante
     */
    public function setFechaProceso($fechaProceso)
    {
        $this->fechaProceso = $fechaProceso;
    
        return $this;
    }

    /**
     * Get fechaProceso
     *
     * @return \DateTime 
     */
    public function getFechaProceso()
    {
        return $this->fechaProceso;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return ValidacionOmisionHistoricaEstudiante
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
     * Set nivelTipoId
     *
     * @param integer $nivelTipoId
     * @return ValidacionOmisionHistoricaEstudiante
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
     * @return ValidacionOmisionHistoricaEstudiante
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
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return ValidacionOmisionHistoricaEstudiante
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
}
