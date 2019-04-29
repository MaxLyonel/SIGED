<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CdlEventos
 */
class CdlEventos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombreEvento;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\CdlClubLectura
     */
    private $cdlClubLectura;


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
     * Set nombreEvento
     *
     * @param string $nombreEvento
     * @return CdlEventos
     */
    public function setNombreEvento($nombreEvento)
    {
        $this->nombreEvento = $nombreEvento;
    
        return $this;
    }

    /**
     * Get nombreEvento
     *
     * @return string 
     */
    public function getNombreEvento()
    {
        return $this->nombreEvento;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return CdlEventos
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    
        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime 
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     * @return CdlEventos
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    
        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime 
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return CdlEventos
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

    /**
     * Set cdlClubLectura
     *
     * @param \Sie\AppWebBundle\Entity\CdlClubLectura $cdlClubLectura
     * @return CdlEventos
     */
    public function setCdlClubLectura(\Sie\AppWebBundle\Entity\CdlClubLectura $cdlClubLectura = null)
    {
        $this->cdlClubLectura = $cdlClubLectura;
    
        return $this;
    }

    /**
     * Get cdlClubLectura
     *
     * @return \Sie\AppWebBundle\Entity\CdlClubLectura 
     */
    public function getCdlClubLectura()
    {
        return $this->cdlClubLectura;
    }
}
