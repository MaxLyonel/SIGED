<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RsInscripcion
 */
class RsInscripcion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $formularioSolicitud;

    /**
     * @var boolean
     */
    private $fotocopiaCi;

    /**
     * @var integer
     */
    private $rsDocumentoTipoId;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificación;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaAcreditacion
     */
    private $superiorInstitucioneducativaAcreditacion;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal
     */
    private $institucioneducativaSucursal;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;


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
     * Set formularioSolicitud
     *
     * @param boolean $formularioSolicitud
     * @return RsInscripcion
     */
    public function setFormularioSolicitud($formularioSolicitud)
    {
        $this->formularioSolicitud = $formularioSolicitud;
    
        return $this;
    }

    /**
     * Get formularioSolicitud
     *
     * @return boolean 
     */
    public function getFormularioSolicitud()
    {
        return $this->formularioSolicitud;
    }

    /**
     * Set fotocopiaCi
     *
     * @param boolean $fotocopiaCi
     * @return RsInscripcion
     */
    public function setFotocopiaCi($fotocopiaCi)
    {
        $this->fotocopiaCi = $fotocopiaCi;
    
        return $this;
    }

    /**
     * Get fotocopiaCi
     *
     * @return boolean 
     */
    public function getFotocopiaCi()
    {
        return $this->fotocopiaCi;
    }

    /**
     * Set rsDocumentoTipoId
     *
     * @param integer $rsDocumentoTipoId
     * @return RsInscripcion
     */
    public function setRsDocumentoTipoId($rsDocumentoTipoId)
    {
        $this->rsDocumentoTipoId = $rsDocumentoTipoId;
    
        return $this;
    }

    /**
     * Get rsDocumentoTipoId
     *
     * @return integer 
     */
    public function getRsDocumentoTipoId()
    {
        return $this->rsDocumentoTipoId;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return RsInscripcion
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RsInscripcion
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
     * Set fechaModificación
     *
     * @param \DateTime $fechaModificación
     * @return RsInscripcion
     */
    public function setFechaModificación($fechaModificación)
    {
        $this->fechaModificación = $fechaModificación;
    
        return $this;
    }

    /**
     * Get fechaModificación
     *
     * @return \DateTime 
     */
    public function getFechaModificación()
    {
        return $this->fechaModificación;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return RsInscripcion
     */
    public function setUsuario(\Sie\AppWebBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set superiorInstitucioneducativaAcreditacion
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaAcreditacion $superiorInstitucioneducativaAcreditacion
     * @return RsInscripcion
     */
    public function setSuperiorInstitucioneducativaAcreditacion(\Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaAcreditacion $superiorInstitucioneducativaAcreditacion = null)
    {
        $this->superiorInstitucioneducativaAcreditacion = $superiorInstitucioneducativaAcreditacion;
    
        return $this;
    }

    /**
     * Get superiorInstitucioneducativaAcreditacion
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaAcreditacion 
     */
    public function getSuperiorInstitucioneducativaAcreditacion()
    {
        return $this->superiorInstitucioneducativaAcreditacion;
    }

    /**
     * Set institucioneducativaSucursal
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal
     * @return RsInscripcion
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
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return RsInscripcion
     */
    public function setEstudiante(\Sie\AppWebBundle\Entity\Estudiante $estudiante = null)
    {
        $this->estudiante = $estudiante;
    
        return $this;
    }

    /**
     * Get estudiante
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getEstudiante()
    {
        return $this->estudiante;
    }
}
