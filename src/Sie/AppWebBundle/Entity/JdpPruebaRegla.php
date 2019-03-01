<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpPruebaRegla
 */
class JdpPruebaRegla
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $iniGestionTipoId;

    /**
     * @var integer
     */
    private $finGestionTipoId;

    /**
     * @var integer
     */
    private $cupoInscripcion;

    /**
     * @var integer
     */
    private $cupoPresentacion;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpFaseTipo
     */
    private $faseTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpClasificacionTipo
     */
    private $clasificacionTipo;


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
     * Set iniGestionTipoId
     *
     * @param integer $iniGestionTipoId
     * @return JdpPruebaRegla
     */
    public function setIniGestionTipoId($iniGestionTipoId)
    {
        $this->iniGestionTipoId = $iniGestionTipoId;
    
        return $this;
    }

    /**
     * Get iniGestionTipoId
     *
     * @return integer 
     */
    public function getIniGestionTipoId()
    {
        return $this->iniGestionTipoId;
    }

    /**
     * Set finGestionTipoId
     *
     * @param integer $finGestionTipoId
     * @return JdpPruebaRegla
     */
    public function setFinGestionTipoId($finGestionTipoId)
    {
        $this->finGestionTipoId = $finGestionTipoId;
    
        return $this;
    }

    /**
     * Get finGestionTipoId
     *
     * @return integer 
     */
    public function getFinGestionTipoId()
    {
        return $this->finGestionTipoId;
    }

    /**
     * Set cupoInscripcion
     *
     * @param integer $cupoInscripcion
     * @return JdpPruebaRegla
     */
    public function setCupoInscripcion($cupoInscripcion)
    {
        $this->cupoInscripcion = $cupoInscripcion;
    
        return $this;
    }

    /**
     * Get cupoInscripcion
     *
     * @return integer 
     */
    public function getCupoInscripcion()
    {
        return $this->cupoInscripcion;
    }

    /**
     * Set cupoPresentacion
     *
     * @param integer $cupoPresentacion
     * @return JdpPruebaRegla
     */
    public function setCupoPresentacion($cupoPresentacion)
    {
        $this->cupoPresentacion = $cupoPresentacion;
    
        return $this;
    }

    /**
     * Get cupoPresentacion
     *
     * @return integer 
     */
    public function getCupoPresentacion()
    {
        return $this->cupoPresentacion;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return JdpPruebaRegla
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }

    /**
     * Set faseTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpFaseTipo $faseTipo
     * @return JdpPruebaRegla
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

    /**
     * Set clasificacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpClasificacionTipo $clasificacionTipo
     * @return JdpPruebaRegla
     */
    public function setClasificacionTipo(\Sie\AppWebBundle\Entity\JdpClasificacionTipo $clasificacionTipo = null)
    {
        $this->clasificacionTipo = $clasificacionTipo;
    
        return $this;
    }

    /**
     * Get clasificacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpClasificacionTipo 
     */
    public function getClasificacionTipo()
    {
        return $this->clasificacionTipo;
    }
}
