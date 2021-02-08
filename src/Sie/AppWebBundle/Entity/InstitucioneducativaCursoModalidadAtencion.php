<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaCursoModalidadAtencion
 */
class InstitucioneducativaCursoModalidadAtencion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \Sie\AppWebBundle\Entity\ModalidadAtencionTipo
     */
    private $modalidadAtencionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaCurso
     */
    private $institucioneducativaCurso;


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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaCursoModalidadAtencion
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
     * @return InstitucioneducativaCursoModalidadAtencion
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
     * Set observacion
     *
     * @param string $observacion
     * @return InstitucioneducativaCursoModalidadAtencion
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
     * Set modalidadAtencionTipo
     *
     * @param \Sie\AppWebBundle\Entity\ModalidadAtencionTipo $modalidadAtencionTipo
     * @return InstitucioneducativaCursoModalidadAtencion
     */
    public function setModalidadAtencionTipo(\Sie\AppWebBundle\Entity\ModalidadAtencionTipo $modalidadAtencionTipo = null)
    {
        $this->modalidadAtencionTipo = $modalidadAtencionTipo;
    
        return $this;
    }

    /**
     * Get modalidadAtencionTipo
     *
     * @return \Sie\AppWebBundle\Entity\ModalidadAtencionTipo 
     */
    public function getModalidadAtencionTipo()
    {
        return $this->modalidadAtencionTipo;
    }

    /**
     * Set institucioneducativaCurso
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCurso $institucioneducativaCurso
     * @return InstitucioneducativaCursoModalidadAtencion
     */
    public function setInstitucioneducativaCurso(\Sie\AppWebBundle\Entity\InstitucioneducativaCurso $institucioneducativaCurso = null)
    {
        $this->institucioneducativaCurso = $institucioneducativaCurso;
    
        return $this;
    }

    /**
     * Get institucioneducativaCurso
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCurso 
     */
    public function getInstitucioneducativaCurso()
    {
        return $this->institucioneducativaCurso;
    }
}
