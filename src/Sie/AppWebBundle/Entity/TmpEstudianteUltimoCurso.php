<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TmpEstudianteUltimoCurso
 */
class TmpEstudianteUltimoCurso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $gestionId;

    /**
     * @var string
     */
    private $codigoRudeId;

    /**
     * @var string
     */
    private $nivelId;

    /**
     * @var string
     */
    private $cicloId;

    /**
     * @var string
     */
    private $gradoId;

    /**
     * @var integer
     */
    private $nivMat;

    /**
     * @var string
     */
    private $estadoMatriculaFinId;


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
     * Set gestionId
     *
     * @param integer $gestionId
     * @return TmpEstudianteUltimoCurso
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
     * Set codigoRudeId
     *
     * @param string $codigoRudeId
     * @return TmpEstudianteUltimoCurso
     */
    public function setCodigoRudeId($codigoRudeId)
    {
        $this->codigoRudeId = $codigoRudeId;
    
        return $this;
    }

    /**
     * Get codigoRudeId
     *
     * @return string 
     */
    public function getCodigoRudeId()
    {
        return $this->codigoRudeId;
    }

    /**
     * Set nivelId
     *
     * @param string $nivelId
     * @return TmpEstudianteUltimoCurso
     */
    public function setNivelId($nivelId)
    {
        $this->nivelId = $nivelId;
    
        return $this;
    }

    /**
     * Get nivelId
     *
     * @return string 
     */
    public function getNivelId()
    {
        return $this->nivelId;
    }

    /**
     * Set cicloId
     *
     * @param string $cicloId
     * @return TmpEstudianteUltimoCurso
     */
    public function setCicloId($cicloId)
    {
        $this->cicloId = $cicloId;
    
        return $this;
    }

    /**
     * Get cicloId
     *
     * @return string 
     */
    public function getCicloId()
    {
        return $this->cicloId;
    }

    /**
     * Set gradoId
     *
     * @param string $gradoId
     * @return TmpEstudianteUltimoCurso
     */
    public function setGradoId($gradoId)
    {
        $this->gradoId = $gradoId;
    
        return $this;
    }

    /**
     * Get gradoId
     *
     * @return string 
     */
    public function getGradoId()
    {
        return $this->gradoId;
    }

    /**
     * Set nivMat
     *
     * @param integer $nivMat
     * @return TmpEstudianteUltimoCurso
     */
    public function setNivMat($nivMat)
    {
        $this->nivMat = $nivMat;
    
        return $this;
    }

    /**
     * Get nivMat
     *
     * @return integer 
     */
    public function getNivMat()
    {
        return $this->nivMat;
    }

    /**
     * Set estadoMatriculaFinId
     *
     * @param string $estadoMatriculaFinId
     * @return TmpEstudianteUltimoCurso
     */
    public function setEstadoMatriculaFinId($estadoMatriculaFinId)
    {
        $this->estadoMatriculaFinId = $estadoMatriculaFinId;
    
        return $this;
    }

    /**
     * Get estadoMatriculaFinId
     *
     * @return string 
     */
    public function getEstadoMatriculaFinId()
    {
        return $this->estadoMatriculaFinId;
    }
}
