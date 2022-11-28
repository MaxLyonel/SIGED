<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecInstitucioneducativaHistoricoDetalle
 */
class TtecInstitucioneducativaHistoricoDetalle
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $valorAnterior;

    /**
     * @var integer
     */
    private $valorAnteriorId;

    /**
     * @var string
     */
    private $valorNuevo;

    /**
     * @var integer
     */
    private $valorNuevoId;

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
    private $usuarioId;

    /**
     * @var boolean
     */
    private $aprobado;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecResolucionTipo
     */
    private $resolucionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistorico
     */
    private $institucioneducativaHistorico;


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
     * Set valorAnterior
     *
     * @param string $valorAnterior
     * @return TtecInstitucioneducativaHistoricoDetalle
     */
    public function setValorAnterior($valorAnterior)
    {
        $this->valorAnterior = $valorAnterior;
    
        return $this;
    }

    /**
     * Get valorAnterior
     *
     * @return string 
     */
    public function getValorAnterior()
    {
        return $this->valorAnterior;
    }

    /**
     * Set valorAnteriorId
     *
     * @param integer $valorAnteriorId
     * @return TtecInstitucioneducativaHistoricoDetalle
     */
    public function setValorAnteriorId($valorAnteriorId)
    {
        $this->valorAnteriorId = $valorAnteriorId;
    
        return $this;
    }

    /**
     * Get valorAnteriorId
     *
     * @return integer 
     */
    public function getValorAnteriorId()
    {
        return $this->valorAnteriorId;
    }

    /**
     * Set valorNuevo
     *
     * @param string $valorNuevo
     * @return TtecInstitucioneducativaHistoricoDetalle
     */
    public function setValorNuevo($valorNuevo)
    {
        $this->valorNuevo = $valorNuevo;
    
        return $this;
    }

    /**
     * Get valorNuevo
     *
     * @return string 
     */
    public function getValorNuevo()
    {
        return $this->valorNuevo;
    }

    /**
     * Set valorNuevoId
     *
     * @param integer $valorNuevoId
     * @return TtecInstitucioneducativaHistoricoDetalle
     */
    public function setValorNuevoId($valorNuevoId)
    {
        $this->valorNuevoId = $valorNuevoId;
    
        return $this;
    }

    /**
     * Get valorNuevoId
     *
     * @return integer 
     */
    public function getValorNuevoId()
    {
        return $this->valorNuevoId;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecInstitucioneducativaHistoricoDetalle
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
     * @return TtecInstitucioneducativaHistoricoDetalle
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
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return TtecInstitucioneducativaHistoricoDetalle
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set aprobado
     *
     * @param boolean $aprobado
     * @return TtecInstitucioneducativaHistoricoDetalle
     */
    public function setAprobado($aprobado)
    {
        $this->aprobado = $aprobado;
    
        return $this;
    }

    /**
     * Get aprobado
     *
     * @return boolean 
     */
    public function getAprobado()
    {
        return $this->aprobado;
    }

    /**
     * Set resolucionTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecResolucionTipo $resolucionTipo
     * @return TtecInstitucioneducativaHistoricoDetalle
     */
    public function setResolucionTipo(\Sie\AppWebBundle\Entity\TtecResolucionTipo $resolucionTipo = null)
    {
        $this->resolucionTipo = $resolucionTipo;
    
        return $this;
    }

    /**
     * Get resolucionTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecResolucionTipo 
     */
    public function getResolucionTipo()
    {
        return $this->resolucionTipo;
    }

    /**
     * Set institucioneducativaHistorico
     *
     * @param \Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistorico $institucioneducativaHistorico
     * @return TtecInstitucioneducativaHistoricoDetalle
     */
    public function setInstitucioneducativaHistorico(\Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistorico $institucioneducativaHistorico = null)
    {
        $this->institucioneducativaHistorico = $institucioneducativaHistorico;
    
        return $this;
    }

    /**
     * Get institucioneducativaHistorico
     *
     * @return \Sie\AppWebBundle\Entity\TtecInstitucioneducativaHistorico 
     */
    public function getInstitucioneducativaHistorico()
    {
        return $this->institucioneducativaHistorico;
    }
}
