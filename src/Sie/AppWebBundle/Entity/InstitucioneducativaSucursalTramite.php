<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaSucursalTramite
 */
class InstitucioneducativaSucursalTramite
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $distritoCod;

    /**
     * @var \DateTime
     */
    private $fechainicio;

    /**
     * @var integer
     */
    private $usuarioIdInicio;

    /**
     * @var \DateTime
     */
    private $fechamodificacion;

    /**
     * @var integer
     */
    private $usuarioIdModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal
     */
    private $institucioneducativaSucursal;

    /**
     * @var \Sie\AppWebBundle\Entity\PeriodoEstadoTipo
     */
    private $periodoEstado;

    /**
     * @var \Sie\AppWebBundle\Entity\TramiteEstado
     */
    private $tramiteEstado;

    /**
     * @var \Sie\AppWebBundle\Entity\TramiteTipo
     */
    private $tramiteTipo;


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
     * Set distritoCod
     *
     * @param integer $distritoCod
     * @return InstitucioneducativaSucursalTramite
     */
    public function setDistritoCod($distritoCod)
    {
        $this->distritoCod = $distritoCod;
    
        return $this;
    }

    /**
     * Get distritoCod
     *
     * @return integer 
     */
    public function getDistritoCod()
    {
        return $this->distritoCod;
    }

    /**
     * Set fechainicio
     *
     * @param \DateTime $fechainicio
     * @return InstitucioneducativaSucursalTramite
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
     * Set usuarioIdInicio
     *
     * @param integer $usuarioIdInicio
     * @return InstitucioneducativaSucursalTramite
     */
    public function setUsuarioIdInicio($usuarioIdInicio)
    {
        $this->usuarioIdInicio = $usuarioIdInicio;
    
        return $this;
    }

    /**
     * Get usuarioIdInicio
     *
     * @return integer 
     */
    public function getUsuarioIdInicio()
    {
        return $this->usuarioIdInicio;
    }

    /**
     * Set fechamodificacion
     *
     * @param \DateTime $fechamodificacion
     * @return InstitucioneducativaSucursalTramite
     */
    public function setFechamodificacion($fechamodificacion)
    {
        $this->fechamodificacion = $fechamodificacion;
    
        return $this;
    }

    /**
     * Get fechamodificacion
     *
     * @return \DateTime 
     */
    public function getFechamodificacion()
    {
        return $this->fechamodificacion;
    }

    /**
     * Set usuarioIdModificacion
     *
     * @param integer $usuarioIdModificacion
     * @return InstitucioneducativaSucursalTramite
     */
    public function setUsuarioIdModificacion($usuarioIdModificacion)
    {
        $this->usuarioIdModificacion = $usuarioIdModificacion;
    
        return $this;
    }

    /**
     * Get usuarioIdModificacion
     *
     * @return integer 
     */
    public function getUsuarioIdModificacion()
    {
        return $this->usuarioIdModificacion;
    }

    /**
     * Set institucioneducativaSucursal
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal
     * @return InstitucioneducativaSucursalTramite
     */
    public function setInstitucioneducativaSucursal(\Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal = null)
    {
        $this->institucioneducativaSucursal = $institucioneducativaSucursal;
    
        return $this;
    }

    /**
     * Get institucioneducativaSucursal
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal 
     */
    public function getInstitucioneducativaSucursal()
    {
        return $this->institucioneducativaSucursal;
    }

    /**
     * Set periodoEstado
     *
     * @param \Sie\AppWebBundle\Entity\PeriodoEstadoTipo $periodoEstado
     * @return InstitucioneducativaSucursalTramite
     */
    public function setPeriodoEstado(\Sie\AppWebBundle\Entity\PeriodoEstadoTipo $periodoEstado = null)
    {
        $this->periodoEstado = $periodoEstado;
    
        return $this;
    }

    /**
     * Get periodoEstado
     *
     * @return \Sie\AppWebBundle\Entity\PeriodoEstadoTipo 
     */
    public function getPeriodoEstado()
    {
        return $this->periodoEstado;
    }

    /**
     * Set tramiteEstado
     *
     * @param \Sie\AppWebBundle\Entity\TramiteEstado $tramiteEstado
     * @return InstitucioneducativaSucursalTramite
     */
    public function setTramiteEstado(\Sie\AppWebBundle\Entity\TramiteEstado $tramiteEstado = null)
    {
        $this->tramiteEstado = $tramiteEstado;
    
        return $this;
    }

    /**
     * Get tramiteEstado
     *
     * @return \Sie\AppWebBundle\Entity\TramiteEstado 
     */
    public function getTramiteEstado()
    {
        return $this->tramiteEstado;
    }

    /**
     * Set tramiteTipo
     *
     * @param \Sie\AppWebBundle\Entity\TramiteTipo $tramiteTipo
     * @return InstitucioneducativaSucursalTramite
     */
    public function setTramiteTipo(\Sie\AppWebBundle\Entity\TramiteTipo $tramiteTipo = null)
    {
        $this->tramiteTipo = $tramiteTipo;
    
        return $this;
    }

    /**
     * Get tramiteTipo
     *
     * @return \Sie\AppWebBundle\Entity\TramiteTipo 
     */
    public function getTramiteTipo()
    {
        return $this->tramiteTipo;
    }
}
