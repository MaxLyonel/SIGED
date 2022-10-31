<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BioInstitucioneducativaBioseguridadPreguntas
 */
class BioInstitucioneducativaBioseguridadPreguntas
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $respSiNo;

    /**
     * @var integer
     */
    private $respVarios;

    /**
     * @var string
     */
    private $pregTexto;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\BioInstitucioneducativaBioseguridad
     */
    private $bioInstitucioneducativaBioseguridad;

    /**
     * @var \Sie\AppWebBundle\Entity\BioPorqueNoRecepcionTipo
     */
    private $bioPorqueNoRecepcionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\BioDesinfeccionRealizadaTipo
     */
    private $bioDesinfeccionRealizadaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\BioDesinfeccionProveeTipo
     */
    private $bioDesinfeccionProveeTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\BioTiempoDesinfeccionTipo
     */
    private $bioTiempoDesinfeccionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\BioCuestionarioTipo
     */
    private $bioCuestionarioTipo;


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
     * Set respSiNo
     *
     * @param boolean $respSiNo
     * @return BioInstitucioneducativaBioseguridadPreguntas
     */
    public function setRespSiNo($respSiNo)
    {
        $this->respSiNo = $respSiNo;
    
        return $this;
    }

    /**
     * Get respSiNo
     *
     * @return boolean 
     */
    public function getRespSiNo()
    {
        return $this->respSiNo;
    }

    /**
     * Set respVarios
     *
     * @param integer $respVarios
     * @return BioInstitucioneducativaBioseguridadPreguntas
     */
    public function setRespVarios($respVarios)
    {
        $this->respVarios = $respVarios;
    
        return $this;
    }

    /**
     * Get respVarios
     *
     * @return integer 
     */
    public function getRespVarios()
    {
        return $this->respVarios;
    }

    /**
     * Set pregTexto
     *
     * @param string $pregTexto
     * @return BioInstitucioneducativaBioseguridadPreguntas
     */
    public function setPregTexto($pregTexto)
    {
        $this->pregTexto = $pregTexto;
    
        return $this;
    }

    /**
     * Get pregTexto
     *
     * @return string 
     */
    public function getPregTexto()
    {
        return $this->pregTexto;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return BioInstitucioneducativaBioseguridadPreguntas
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
     * @return BioInstitucioneducativaBioseguridadPreguntas
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
     * Set bioInstitucioneducativaBioseguridad
     *
     * @param \Sie\AppWebBundle\Entity\BioInstitucioneducativaBioseguridad $bioInstitucioneducativaBioseguridad
     * @return BioInstitucioneducativaBioseguridadPreguntas
     */
    public function setBioInstitucioneducativaBioseguridad(\Sie\AppWebBundle\Entity\BioInstitucioneducativaBioseguridad $bioInstitucioneducativaBioseguridad = null)
    {
        $this->bioInstitucioneducativaBioseguridad = $bioInstitucioneducativaBioseguridad;
    
        return $this;
    }

    /**
     * Get bioInstitucioneducativaBioseguridad
     *
     * @return \Sie\AppWebBundle\Entity\BioInstitucioneducativaBioseguridad 
     */
    public function getBioInstitucioneducativaBioseguridad()
    {
        return $this->bioInstitucioneducativaBioseguridad;
    }

    /**
     * Set bioPorqueNoRecepcionTipo
     *
     * @param \Sie\AppWebBundle\Entity\BioPorqueNoRecepcionTipo $bioPorqueNoRecepcionTipo
     * @return BioInstitucioneducativaBioseguridadPreguntas
     */
    public function setBioPorqueNoRecepcionTipo(\Sie\AppWebBundle\Entity\BioPorqueNoRecepcionTipo $bioPorqueNoRecepcionTipo = null)
    {
        $this->bioPorqueNoRecepcionTipo = $bioPorqueNoRecepcionTipo;
    
        return $this;
    }

    /**
     * Get bioPorqueNoRecepcionTipo
     *
     * @return \Sie\AppWebBundle\Entity\BioPorqueNoRecepcionTipo 
     */
    public function getBioPorqueNoRecepcionTipo()
    {
        return $this->bioPorqueNoRecepcionTipo;
    }

    /**
     * Set bioDesinfeccionRealizadaTipo
     *
     * @param \Sie\AppWebBundle\Entity\BioDesinfeccionRealizadaTipo $bioDesinfeccionRealizadaTipo
     * @return BioInstitucioneducativaBioseguridadPreguntas
     */
    public function setBioDesinfeccionRealizadaTipo(\Sie\AppWebBundle\Entity\BioDesinfeccionRealizadaTipo $bioDesinfeccionRealizadaTipo = null)
    {
        $this->bioDesinfeccionRealizadaTipo = $bioDesinfeccionRealizadaTipo;
    
        return $this;
    }

    /**
     * Get bioDesinfeccionRealizadaTipo
     *
     * @return \Sie\AppWebBundle\Entity\BioDesinfeccionRealizadaTipo 
     */
    public function getBioDesinfeccionRealizadaTipo()
    {
        return $this->bioDesinfeccionRealizadaTipo;
    }

    /**
     * Set bioDesinfeccionProveeTipo
     *
     * @param \Sie\AppWebBundle\Entity\BioDesinfeccionProveeTipo $bioDesinfeccionProveeTipo
     * @return BioInstitucioneducativaBioseguridadPreguntas
     */
    public function setBioDesinfeccionProveeTipo(\Sie\AppWebBundle\Entity\BioDesinfeccionProveeTipo $bioDesinfeccionProveeTipo = null)
    {
        $this->bioDesinfeccionProveeTipo = $bioDesinfeccionProveeTipo;
    
        return $this;
    }

    /**
     * Get bioDesinfeccionProveeTipo
     *
     * @return \Sie\AppWebBundle\Entity\BioDesinfeccionProveeTipo 
     */
    public function getBioDesinfeccionProveeTipo()
    {
        return $this->bioDesinfeccionProveeTipo;
    }

    /**
     * Set bioTiempoDesinfeccionTipo
     *
     * @param \Sie\AppWebBundle\Entity\BioTiempoDesinfeccionTipo $bioTiempoDesinfeccionTipo
     * @return BioInstitucioneducativaBioseguridadPreguntas
     */
    public function setBioTiempoDesinfeccionTipo(\Sie\AppWebBundle\Entity\BioTiempoDesinfeccionTipo $bioTiempoDesinfeccionTipo = null)
    {
        $this->bioTiempoDesinfeccionTipo = $bioTiempoDesinfeccionTipo;
    
        return $this;
    }

    /**
     * Get bioTiempoDesinfeccionTipo
     *
     * @return \Sie\AppWebBundle\Entity\BioTiempoDesinfeccionTipo 
     */
    public function getBioTiempoDesinfeccionTipo()
    {
        return $this->bioTiempoDesinfeccionTipo;
    }

    /**
     * Set bioCuestionarioTipo
     *
     * @param \Sie\AppWebBundle\Entity\BioCuestionarioTipo $bioCuestionarioTipo
     * @return BioInstitucioneducativaBioseguridadPreguntas
     */
    public function setBioCuestionarioTipo(\Sie\AppWebBundle\Entity\BioCuestionarioTipo $bioCuestionarioTipo = null)
    {
        $this->bioCuestionarioTipo = $bioCuestionarioTipo;
    
        return $this;
    }

    /**
     * Get bioCuestionarioTipo
     *
     * @return \Sie\AppWebBundle\Entity\BioCuestionarioTipo 
     */
    public function getBioCuestionarioTipo()
    {
        return $this->bioCuestionarioTipo;
    }
}
