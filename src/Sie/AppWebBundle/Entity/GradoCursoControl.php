<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GradoCursoControl
 */
class GradoCursoControl
{
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
    private $idGrado;

    /**
     * @var string
     */
    private $descGrado;

    /**
     * @var string
     */
    private $edadMinima;

    /**
     * @var string
     */
    private $edadMaxima;


    /**
     * Set nivelId
     *
     * @param integer $nivelId
     * @return GradoCursoControl
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
     * @return GradoCursoControl
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
     * Set idGrado
     *
     * @param integer $idGrado
     * @return GradoCursoControl
     */
    public function setIdGrado($idGrado)
    {
        $this->idGrado = $idGrado;
    
        return $this;
    }

    /**
     * Get idGrado
     *
     * @return integer 
     */
    public function getIdGrado()
    {
        return $this->idGrado;
    }

    /**
     * Set descGrado
     *
     * @param string $descGrado
     * @return GradoCursoControl
     */
    public function setDescGrado($descGrado)
    {
        $this->descGrado = $descGrado;
    
        return $this;
    }

    /**
     * Get descGrado
     *
     * @return string 
     */
    public function getDescGrado()
    {
        return $this->descGrado;
    }

    /**
     * Set edadMinima
     *
     * @param string $edadMinima
     * @return GradoCursoControl
     */
    public function setEdadMinima($edadMinima)
    {
        $this->edadMinima = $edadMinima;
    
        return $this;
    }

    /**
     * Get edadMinima
     *
     * @return string 
     */
    public function getEdadMinima()
    {
        return $this->edadMinima;
    }

    /**
     * Set edadMaxima
     *
     * @param string $edadMaxima
     * @return GradoCursoControl
     */
    public function setEdadMaxima($edadMaxima)
    {
        $this->edadMaxima = $edadMaxima;
    
        return $this;
    }

    /**
     * Get edadMaxima
     *
     * @return string 
     */
    public function getEdadMaxima()
    {
        return $this->edadMaxima;
    }
}
