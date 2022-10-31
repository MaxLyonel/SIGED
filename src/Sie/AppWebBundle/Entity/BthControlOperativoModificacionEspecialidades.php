<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BthControlOperativoModificacionEspecialidades
 */
class BthControlOperativoModificacionEspecialidades
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var boolean
     */
    private $estadoOperativo;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var \DateTime
     */
    private $fechaCierre;

    /**
     * @var \DateTime
     */
    private $fechaHabilitacion;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo
     */
    private $institucioneducativaOperativoLogTipo;


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
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return BthControlOperativoModificacionEspecialidades
     */
    public function setInstitucioneducativaId($institucioneducativaId)
    {
        $this->institucioneducativaId = $institucioneducativaId;
    
        return $this;
    }

    /**
     * Get institucioneducativaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaId()
    {
        return $this->institucioneducativaId;
    }

    /**
     * Set estadoOperativo
     *
     * @param boolean $estadoOperativo
     * @return BthControlOperativoModificacionEspecialidades
     */
    public function setEstadoOperativo($estadoOperativo)
    {
        $this->estadoOperativo = $estadoOperativo;
    
        return $this;
    }

    /**
     * Get estadoOperativo
     *
     * @return boolean 
     */
    public function getEstadoOperativo()
    {
        return $this->estadoOperativo;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return BthControlOperativoModificacionEspecialidades
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

    /**
     * Set fechaCierre
     *
     * @param \DateTime $fechaCierre
     * @return BthControlOperativoModificacionEspecialidades
     */
    public function setFechaCierre($fechaCierre)
    {
        $this->fechaCierre = $fechaCierre;
    
        return $this;
    }

    /**
     * Get fechaCierre
     *
     * @return \DateTime 
     */
    public function getFechaCierre()
    {
        return $this->fechaCierre;
    }

    /**
     * Set fechaHabilitacion
     *
     * @param \DateTime $fechaHabilitacion
     * @return BthControlOperativoModificacionEspecialidades
     */
    public function setFechaHabilitacion($fechaHabilitacion)
    {
        $this->fechaHabilitacion = $fechaHabilitacion;
    
        return $this;
    }

    /**
     * Get fechaHabilitacion
     *
     * @return \DateTime 
     */
    public function getFechaHabilitacion()
    {
        return $this->fechaHabilitacion;
    }

    /**
     * Set institucioneducativaOperativoLogTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo $institucioneducativaOperativoLogTipo
     * @return BthControlOperativoModificacionEspecialidades
     */
    public function setInstitucioneducativaOperativoLogTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo $institucioneducativaOperativoLogTipo = null)
    {
        $this->institucioneducativaOperativoLogTipo = $institucioneducativaOperativoLogTipo;
    
        return $this;
    }

    /**
     * Get institucioneducativaOperativoLogTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaOperativoLogTipo 
     */
    public function getInstitucioneducativaOperativoLogTipo()
    {
        return $this->institucioneducativaOperativoLogTipo;
    }
}
