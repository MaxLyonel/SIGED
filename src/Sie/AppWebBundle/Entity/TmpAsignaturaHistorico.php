<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TmpAsignaturaHistorico
 */
class TmpAsignaturaHistorico
{
    /**
     * @var integer
     */
    private $id;

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
    private $asignatura;

    /**
     * @var integer
     */
    private $frec;

    /**
     * @var integer
     */
    private $orgacurricularTipoId;

    /**
     * @var \Sie\AppWebBundle\Entity\AsignaturaTipo
     */
    private $asignaturaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\AreaTipo
     */
    private $areaTipo;


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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return TmpAsignaturaHistorico
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
     * @return TmpAsignaturaHistorico
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
     * @return TmpAsignaturaHistorico
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
     * Set asignatura
     *
     * @param string $asignatura
     * @return TmpAsignaturaHistorico
     */
    public function setAsignatura($asignatura)
    {
        $this->asignatura = $asignatura;
    
        return $this;
    }

    /**
     * Get asignatura
     *
     * @return string 
     */
    public function getAsignatura()
    {
        return $this->asignatura;
    }

    /**
     * Set frec
     *
     * @param integer $frec
     * @return TmpAsignaturaHistorico
     */
    public function setFrec($frec)
    {
        $this->frec = $frec;
    
        return $this;
    }

    /**
     * Get frec
     *
     * @return integer 
     */
    public function getFrec()
    {
        return $this->frec;
    }

    /**
     * Set orgacurricularTipoId
     *
     * @param integer $orgacurricularTipoId
     * @return TmpAsignaturaHistorico
     */
    public function setOrgacurricularTipoId($orgacurricularTipoId)
    {
        $this->orgacurricularTipoId = $orgacurricularTipoId;
    
        return $this;
    }

    /**
     * Get orgacurricularTipoId
     *
     * @return integer 
     */
    public function getOrgacurricularTipoId()
    {
        return $this->orgacurricularTipoId;
    }

    /**
     * Set asignaturaTipo
     *
     * @param \Sie\AppWebBundle\Entity\AsignaturaTipo $asignaturaTipo
     * @return TmpAsignaturaHistorico
     */
    public function setAsignaturaTipo(\Sie\AppWebBundle\Entity\AsignaturaTipo $asignaturaTipo = null)
    {
        $this->asignaturaTipo = $asignaturaTipo;
    
        return $this;
    }

    /**
     * Get asignaturaTipo
     *
     * @return \Sie\AppWebBundle\Entity\AsignaturaTipo 
     */
    public function getAsignaturaTipo()
    {
        return $this->asignaturaTipo;
    }

    /**
     * Set areaTipo
     *
     * @param \Sie\AppWebBundle\Entity\AreaTipo $areaTipo
     * @return TmpAsignaturaHistorico
     */
    public function setAreaTipo(\Sie\AppWebBundle\Entity\AreaTipo $areaTipo = null)
    {
        $this->areaTipo = $areaTipo;
    
        return $this;
    }

    /**
     * Get areaTipo
     *
     * @return \Sie\AppWebBundle\Entity\AreaTipo 
     */
    public function getAreaTipo()
    {
        return $this->areaTipo;
    }
}
