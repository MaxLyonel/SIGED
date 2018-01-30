<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnidadMilitarTipo
 */
class UnidadMilitarTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $unidadMilitarTipo;


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
     * Set unidadMilitarTipo
     *
     * @param string $unidadMilitarTipo
     * @return UnidadMilitarTipo
     */
    public function setUnidadMilitarTipo($unidadMilitarTipo)
    {
        $this->unidadMilitarTipo = $unidadMilitarTipo;
    
        return $this;
    }

    /**
     * Get unidadMilitarTipo
     *
     * @return string 
     */
    public function getUnidadMilitarTipo()
    {
        return $this->unidadMilitarTipo;
    }
    /**
     * @var string
     */
    private $unidadMilitar;

    /**
     * @var \Sie\AppWebBundle\Entity\FuerzaMilitarTipo
     */
    private $fuerzaMilitarTipo;


    /**
     * Set unidadMilitar
     *
     * @param string $unidadMilitar
     * @return UnidadMilitarTipo
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
     * Set fuerzaMilitarTipo
     *
     * @param \Sie\AppWebBundle\Entity\FuerzaMilitarTipo $fuerzaMilitarTipo
     * @return UnidadMilitarTipo
     */
    public function setFuerzaMilitarTipo(\Sie\AppWebBundle\Entity\FuerzaMilitarTipo $fuerzaMilitarTipo = null)
    {
        $this->fuerzaMilitarTipo = $fuerzaMilitarTipo;
    
        return $this;
    }

    /**
     * Get fuerzaMilitarTipo
     *
     * @return \Sie\AppWebBundle\Entity\FuerzaMilitarTipo 
     */
    public function getFuerzaMilitarTipo()
    {
        return $this->fuerzaMilitarTipo;
    }
}
