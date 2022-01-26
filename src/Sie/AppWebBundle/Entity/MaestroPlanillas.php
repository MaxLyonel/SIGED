<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MaestroPlanillas
 */
class MaestroPlanillas
{
    /**
     * @var integer
     */
    private $rda;

    /**
     * @var integer
     */
    private $ci;

    /**
     * @var string
     */
    private $paterno;

    /**
     * @var string
     */
    private $materno;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var string
     */
    private $fechaNacimiento;

    /**
     * @var string
     */
    private $genero;


    /**
     * Get rda
     *
     * @return integer 
     */
    public function getRda()
    {
        return $this->rda;
    }

    /**
     * Set ci
     *
     * @param integer $ci
     * @return MaestroPlanillas
     */
    public function setCi($ci)
    {
        $this->ci = $ci;

        return $this;
    }

    /**
     * Get ci
     *
     * @return integer 
     */
    public function getCi()
    {
        return $this->ci;
    }

    /**
     * Set paterno
     *
     * @param string $paterno
     * @return MaestroPlanillas
     */
    public function setPaterno($paterno)
    {
        $this->paterno = $paterno;

        return $this;
    }

    /**
     * Get paterno
     *
     * @return string 
     */
    public function getPaterno()
    {
        return $this->paterno;
    }

    /**
     * Set materno
     *
     * @param string $materno
     * @return MaestroPlanillas
     */
    public function setMaterno($materno)
    {
        $this->materno = $materno;

        return $this;
    }

    /**
     * Get materno
     *
     * @return string 
     */
    public function getMaterno()
    {
        return $this->materno;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return MaestroPlanillas
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set fechaNacimiento
     *
     * @param string $fechaNacimiento
     * @return MaestroPlanillas
     */
    public function setFechaNacimiento($fechaNacimiento)
    {
        $this->fechaNacimiento = $fechaNacimiento;

        return $this;
    }

    /**
     * Get fechaNacimiento
     *
     * @return string 
     */
    public function getFechaNacimiento()
    {
        return $this->fechaNacimiento;
    }

    /**
     * Set genero
     *
     * @param string $genero
     * @return MaestroPlanillas
     */
    public function setGenero($genero)
    {
        $this->genero = $genero;

        return $this;
    }

    /**
     * Get genero
     *
     * @return string 
     */
    public function getGenero()
    {
        return $this->genero;
    }
}
