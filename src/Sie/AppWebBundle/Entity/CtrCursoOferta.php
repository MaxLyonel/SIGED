<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CtrCursoOferta
 */
class CtrCursoOferta
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $nivelTipoId;

    /**
     * @var integer
     */
    private $cicloTipoId;

    /**
     * @var integer
     */
    private $gradoTipoId;

    /**
     * @var integer
     */
    private $asignaturaTipoId;

    /**
     * @var integer
     */
    private $gestionTipoId;


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
     * Set nivelTipoId
     *
     * @param integer $nivelTipoId
     * @return CtrCursoOferta
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
     * Set cicloTipoId
     *
     * @param integer $cicloTipoId
     * @return CtrCursoOferta
     */
    public function setCicloTipoId($cicloTipoId)
    {
        $this->cicloTipoId = $cicloTipoId;
    
        return $this;
    }

    /**
     * Get cicloTipoId
     *
     * @return integer 
     */
    public function getCicloTipoId()
    {
        return $this->cicloTipoId;
    }

    /**
     * Set gradoTipoId
     *
     * @param integer $gradoTipoId
     * @return CtrCursoOferta
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
     * Set asignaturaTipoId
     *
     * @param integer $asignaturaTipoId
     * @return CtrCursoOferta
     */
    public function setAsignaturaTipoId($asignaturaTipoId)
    {
        $this->asignaturaTipoId = $asignaturaTipoId;
    
        return $this;
    }

    /**
     * Get asignaturaTipoId
     *
     * @return integer 
     */
    public function getAsignaturaTipoId()
    {
        return $this->asignaturaTipoId;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return CtrCursoOferta
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
}
