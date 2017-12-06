<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GradoTipo
 */
class GradoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $grado;

    /**
     * @var integer
     */
    private $edadMinima;

    /**
     * @var integer
     */
    private $edadMaxima;

    /**
     * @var integer
     */
    private $idSeguimiento;

    public function __toString() {
        return $this->grado;
    }
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
     * Set grado
     *
     * @param string $grado
     * @return GradoTipo
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
     * Set edadMinima
     *
     * @param integer $edadMinima
     * @return GradoTipo
     */
    public function setEdadMinima($edadMinima)
    {
        $this->edadMinima = $edadMinima;

        return $this;
    }

    /**
     * Get edadMinima
     *
     * @return integer 
     */
    public function getEdadMinima()
    {
        return $this->edadMinima;
    }

    /**
     * Set edadMaxima
     *
     * @param integer $edadMaxima
     * @return GradoTipo
     */
    public function setEdadMaxima($edadMaxima)
    {
        $this->edadMaxima = $edadMaxima;

        return $this;
    }

    /**
     * Get edadMaxima
     *
     * @return integer 
     */
    public function getEdadMaxima()
    {
        return $this->edadMaxima;
    }

    /**
     * Set idSeguimiento
     *
     * @param integer $idSeguimiento
     * @return GradoTipo
     */
    public function setIdSeguimiento($idSeguimiento)
    {
        $this->idSeguimiento = $idSeguimiento;

        return $this;
    }

    /**
     * Get idSeguimiento
     *
     * @return integer 
     */
    public function getIdSeguimiento()
    {
        return $this->idSeguimiento;
    }
}
