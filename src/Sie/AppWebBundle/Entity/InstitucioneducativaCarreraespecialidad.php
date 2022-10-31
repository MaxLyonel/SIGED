<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaCarreraespecialidad
 */
class InstitucioneducativaCarreraespecialidad
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
     * @var integer
     */
    private $carreraespecialidadTipoId;

    /**
     * @var integer
     */
    private $programapermanenteTipoId;

    /**
     * @var integer
     */
    private $modalidadTipoId;


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
     * @return InstitucioneducativaCarreraespecialidad
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
     * Set carreraespecialidadTipoId
     *
     * @param integer $carreraespecialidadTipoId
     * @return InstitucioneducativaCarreraespecialidad
     */
    public function setCarreraespecialidadTipoId($carreraespecialidadTipoId)
    {
        $this->carreraespecialidadTipoId = $carreraespecialidadTipoId;
    
        return $this;
    }

    /**
     * Get carreraespecialidadTipoId
     *
     * @return integer 
     */
    public function getCarreraespecialidadTipoId()
    {
        return $this->carreraespecialidadTipoId;
    }

    /**
     * Set programapermanenteTipoId
     *
     * @param integer $programapermanenteTipoId
     * @return InstitucioneducativaCarreraespecialidad
     */
    public function setProgramapermanenteTipoId($programapermanenteTipoId)
    {
        $this->programapermanenteTipoId = $programapermanenteTipoId;
    
        return $this;
    }

    /**
     * Get programapermanenteTipoId
     *
     * @return integer 
     */
    public function getProgramapermanenteTipoId()
    {
        return $this->programapermanenteTipoId;
    }

    /**
     * Set modalidadTipoId
     *
     * @param integer $modalidadTipoId
     * @return InstitucioneducativaCarreraespecialidad
     */
    public function setModalidadTipoId($modalidadTipoId)
    {
        $this->modalidadTipoId = $modalidadTipoId;
    
        return $this;
    }

    /**
     * Get modalidadTipoId
     *
     * @return integer 
     */
    public function getModalidadTipoId()
    {
        return $this->modalidadTipoId;
    }
    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\CarreraespecialidadTipo
     */
    private $carreraespecialidadTipo;


    /**
     * Set obs
     *
     * @param string $obs
     * @return InstitucioneducativaCarreraespecialidad
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaCarreraespecialidad
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

    /**
     * Set carreraespecialidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\CarreraespecialidadTipo $carreraespecialidadTipo
     * @return InstitucioneducativaCarreraespecialidad
     */
    public function setCarreraespecialidadTipo(\Sie\AppWebBundle\Entity\CarreraespecialidadTipo $carreraespecialidadTipo = null)
    {
        $this->carreraespecialidadTipo = $carreraespecialidadTipo;
    
        return $this;
    }

    /**
     * Get carreraespecialidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\CarreraespecialidadTipo 
     */
    public function getCarreraespecialidadTipo()
    {
        return $this->carreraespecialidadTipo;
    }
}
