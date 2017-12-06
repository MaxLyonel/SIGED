<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PeriodoTipo
 */
class PeriodoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $periodo;

    /**
     * @var \DateTime
     */
    private $fechainicio;

    /**
     * @var \DateTime
     */
    private $fechafin;

    public function __toString() {
        return $this->periodo;
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
     * Set periodo
     *
     * @param string $periodo
     * @return PeriodoTipo
     */
    public function setPeriodo($periodo)
    {
        $this->periodo = $periodo;

        return $this;
    }

    /**
     * Get periodo
     *
     * @return string 
     */
    public function getPeriodo()
    {
        return $this->periodo;
    }

    /**
     * Set fechainicio
     *
     * @param \DateTime $fechainicio
     * @return PeriodoTipo
     */
    public function setFechainicio($fechainicio)
    {
        $this->fechainicio = $fechainicio;

        return $this;
    }

    /**
     * Get fechainicio
     *
     * @return \DateTime 
     */
    public function getFechainicio()
    {
        return $this->fechainicio;
    }

    /**
     * Set fechafin
     *
     * @param \DateTime $fechafin
     * @return PeriodoTipo
     */
    public function setFechafin($fechafin)
    {
        $this->fechafin = $fechafin;

        return $this;
    }

    /**
     * Get fechafin
     *
     * @return \DateTime 
     */
    public function getFechafin()
    {
        return $this->fechafin;
    }
}
