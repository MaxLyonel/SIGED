<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpFaseRegla
 */
class JdpFaseRegla
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $activo;

    /**
     * @var \DateTime
     */
    private $fechaIni;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var \Sie\AppWebBundle\Entity\NivelTipo
     */
    private $nivelTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpFaseTipo
     */
    private $faseTipo;


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
     * Set activo
     *
     * @param boolean $activo
     * @return JdpFaseRegla
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    
        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean 
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set fechaIni
     *
     * @param \DateTime $fechaIni
     * @return JdpFaseRegla
     */
    public function setFechaIni($fechaIni)
    {
        $this->fechaIni = $fechaIni;
    
        return $this;
    }

    /**
     * Get fechaIni
     *
     * @return \DateTime 
     */
    public function getFechaIni()
    {
        return $this->fechaIni;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     * @return JdpFaseRegla
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
     * Set nivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\NivelTipo $nivelTipo
     * @return JdpFaseRegla
     */
    public function setNivelTipo(\Sie\AppWebBundle\Entity\NivelTipo $nivelTipo = null)
    {
        $this->nivelTipo = $nivelTipo;
    
        return $this;
    }

    /**
     * Get nivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\NivelTipo 
     */
    public function getNivelTipo()
    {
        return $this->nivelTipo;
    }

    /**
     * Set faseTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpFaseTipo $faseTipo
     * @return JdpFaseRegla
     */
    public function setFaseTipo(\Sie\AppWebBundle\Entity\JdpFaseTipo $faseTipo = null)
    {
        $this->faseTipo = $faseTipo;
    
        return $this;
    }

    /**
     * Get faseTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpFaseTipo 
     */
    public function getFaseTipo()
    {
        return $this->faseTipo;
    }
}
