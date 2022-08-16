<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaModalidadAtencion
 */
class InstitucioneducativaModalidadAtencion
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
     * @var integer
     */
    private $orgcurricularTipoId;

    /**
     * @var \Sie\AppWebBundle\Entity\ModalidadAtencionTipo
     */
    private $modalidadAtencionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;


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
     * @return InstitucioneducativaModalidadAtencion
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
     * @return InstitucioneducativaModalidadAtencion
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
     * Set orgcurricularTipoId
     *
     * @param integer $orgcurricularTipoId
     * @return InstitucioneducativaModalidadAtencion
     */
    public function setOrgcurricularTipoId($orgcurricularTipoId)
    {
        $this->orgcurricularTipoId = $orgcurricularTipoId;
    
        return $this;
    }

    /**
     * Get orgcurricularTipoId
     *
     * @return integer 
     */
    public function getOrgcurricularTipoId()
    {
        return $this->orgcurricularTipoId;
    }

    /**
     * Set modalidadAtencionTipo
     *
     * @param \Sie\AppWebBundle\Entity\ModalidadAtencionTipo $modalidadAtencionTipo
     * @return InstitucioneducativaModalidadAtencion
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaModalidadAtencion
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }
}
