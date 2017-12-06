<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnidadMilitar
 */
class UnidadMilitar
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $unidadMilitar;

    /**
     * @var integer
     */
    private $unidadMilitarTipoId;

    /**
     * @var integer
     */
    private $distritoId;


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
     * Set unidadMilitar
     *
     * @param string $unidadMilitar
     * @return UnidadMilitar
     */
    public function setUnidadMilitar($unidadMilitar)
    {
        $this->unidadMilitar = $unidadMilitar;
    
        return $this;
    }

    /**
     * Get unidadMilitar
     *
     * @return string 
     */
    public function getUnidadMilitar()
    {
        return $this->unidadMilitar;
    }

    /**
     * Set unidadMilitarTipoId
     *
     * @param integer $unidadMilitarTipoId
     * @return UnidadMilitar
     */
    public function setUnidadMilitarTipoId($unidadMilitarTipoId)
    {
        $this->unidadMilitarTipoId = $unidadMilitarTipoId;
    
        return $this;
    }

    /**
     * Get unidadMilitarTipoId
     *
     * @return integer 
     */
    public function getUnidadMilitarTipoId()
    {
        return $this->unidadMilitarTipoId;
    }

    /**
     * Set distritoId
     *
     * @param integer $distritoId
     * @return UnidadMilitar
     */
    public function setDistritoId($distritoId)
    {
        $this->distritoId = $distritoId;
    
        return $this;
    }

    /**
     * Get distritoId
     *
     * @return integer 
     */
    public function getDistritoId()
    {
        return $this->distritoId;
    }
    /**
     * @var string
     */
    private $localidad;

    /**
     * @var \Sie\AppWebBundle\Entity\UnidadMilitarTipo
     */
    private $unidadMilitarTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $provincia;


    /**
     * Set localidad
     *
     * @param string $localidad
     * @return UnidadMilitar
     */
    public function setLocalidad($localidad)
    {
        $this->localidad = $localidad;
    
        return $this;
    }

    /**
     * Get localidad
     *
     * @return string 
     */
    public function getLocalidad()
    {
        return $this->localidad;
    }

    /**
     * Set unidadMilitarTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnidadMilitarTipo $unidadMilitarTipo
     * @return UnidadMilitar
     */
    public function setUnidadMilitarTipo(\Sie\AppWebBundle\Entity\UnidadMilitarTipo $unidadMilitarTipo = null)
    {
        $this->unidadMilitarTipo = $unidadMilitarTipo;
    
        return $this;
    }

    /**
     * Get unidadMilitarTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnidadMilitarTipo 
     */
    public function getUnidadMilitarTipo()
    {
        return $this->unidadMilitarTipo;
    }

    /**
     * Set provincia
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $provincia
     * @return UnidadMilitar
     */
    public function setProvincia(\Sie\AppWebBundle\Entity\LugarTipo $provincia = null)
    {
        $this->provincia = $provincia;
    
        return $this;
    }

    /**
     * Get provincia
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getProvincia()
    {
        return $this->provincia;
    }
}
