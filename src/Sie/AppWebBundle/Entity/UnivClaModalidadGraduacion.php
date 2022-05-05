<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivClaModalidadGraduacion
 */
class UnivClaModalidadGraduacion
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var string
     */
    private $abreviacion;

    /**
     * @var string
     */
    private $estado;

    /**
     * @var string
     */
    private $gradoAcademicoId;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return UnivClaModalidadGraduacion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set abreviacion
     *
     * @param string $abreviacion
     * @return UnivClaModalidadGraduacion
     */
    public function setAbreviacion($abreviacion)
    {
        $this->abreviacion = $abreviacion;
    
        return $this;
    }

    /**
     * Get abreviacion
     *
     * @return string 
     */
    public function getAbreviacion()
    {
        return $this->abreviacion;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return UnivClaModalidadGraduacion
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return string 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set gradoAcademicoId
     *
     * @param string $gradoAcademicoId
     * @return UnivClaModalidadGraduacion
     */
    public function setGradoAcademicoId($gradoAcademicoId)
    {
        $this->gradoAcademicoId = $gradoAcademicoId;
    
        return $this;
    }

    /**
     * Get gradoAcademicoId
     *
     * @return string 
     */
    public function getGradoAcademicoId()
    {
        return $this->gradoAcademicoId;
    }
}
