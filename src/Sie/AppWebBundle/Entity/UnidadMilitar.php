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
}
