<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WfSolicitudTramite
 */
class WfSolicitudTramite
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $datos;

    /**
     * @var boolean
     */
    private $esValido;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var integer
     */
    private $lugarTipoId;

    /**
     * @var \Sie\AppWebBundle\Entity\Tramite
     */
    private $tramite;


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
     * Set datos
     *
     * @param string $datos
     * @return WfSolicitudTramite
     */
    public function setDatos($datos)
    {
        $this->datos = $datos;
    
        return $this;
    }

    /**
     * Get datos
     *
     * @return string 
     */
    public function getDatos()
    {
        return $this->datos;
    }

    /**
     * Set esValido
     *
     * @param boolean $esValido
     * @return WfSolicitudTramite
     */
    public function setEsValido($esValido)
    {
        $this->esValido = $esValido;
    
        return $this;
    }

    /**
     * Get esValido
     *
     * @return boolean 
     */
    public function getEsValido()
    {
        return $this->esValido;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return WfSolicitudTramite
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return WfSolicitudTramite
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set lugarTipoId
     *
     * @param integer $lugarTipoId
     * @return WfSolicitudTramite
     */
    public function setLugarTipoId($lugarTipoId)
    {
        $this->lugarTipoId = $lugarTipoId;
    
        return $this;
    }

    /**
     * Get lugarTipoId
     *
     * @return integer 
     */
    public function getLugarTipoId()
    {
        return $this->lugarTipoId;
    }

    /**
     * Set tramite
     *
     * @param \Sie\AppWebBundle\Entity\Tramite $tramite
     * @return WfSolicitudTramite
     */
    public function setTramite(\Sie\AppWebBundle\Entity\Tramite $tramite = null)
    {
        $this->tramite = $tramite;
    
        return $this;
    }

    /**
     * Get tramite
     *
     * @return \Sie\AppWebBundle\Entity\Tramite 
     */
    public function getTramite()
    {
        return $this->tramite;
    }
}
