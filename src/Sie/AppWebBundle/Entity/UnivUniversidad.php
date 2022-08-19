<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivUniversidad
 */
class UnivUniversidad
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $universidad;

    /**
     * @var string
     */
    private $decretoSupremo;

    /**
     * @var string
     */
    private $abreviacion;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivTipoUniversidad
     */
    private $univTipoUniversidad;


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
     * Set universidad
     *
     * @param string $universidad
     * @return UnivUniversidad
     */
    public function setUniversidad($universidad)
    {
        $this->universidad = $universidad;
    
        return $this;
    }

    /**
     * Get universidad
     *
     * @return string 
     */
    public function getUniversidad()
    {
        return $this->universidad;
    }

    /**
     * Set decretoSupremo
     *
     * @param string $decretoSupremo
     * @return UnivUniversidad
     */
    public function setDecretoSupremo($decretoSupremo)
    {
        $this->decretoSupremo = $decretoSupremo;
    
        return $this;
    }

    /**
     * Get decretoSupremo
     *
     * @return string 
     */
    public function getDecretoSupremo()
    {
        return $this->decretoSupremo;
    }

    /**
     * Set abreviacion
     *
     * @param string $abreviacion
     * @return UnivUniversidad
     */
    public function setAbreviacion($abreviacion)
    {
        $this->abreviacion = $abreviacion;
    
        return $this;
    }

    /**
     * Get abreviacion
     *
     * @return string 
     */
    public function getAbreviacion()
    {
        return $this->abreviacion;
    }

    /**
     * Set logo
     *
     * @param string $logo
     * @return UnivUniversidad
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    
        return $this;
    }

    /**
     * Get logo
     *
     * @return string 
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return UnivUniversidad
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
     * @return UnivUniversidad
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
     * Set univTipoUniversidad
     *
     * @param \Sie\AppWebBundle\Entity\UnivTipoUniversidad $univTipoUniversidad
     * @return UnivUniversidad
     */
    public function setUnivTipoUniversidad(\Sie\AppWebBundle\Entity\UnivTipoUniversidad $univTipoUniversidad = null)
    {
        $this->univTipoUniversidad = $univTipoUniversidad;
    
        return $this;
    }

    /**
     * Get univTipoUniversidad
     *
     * @return \Sie\AppWebBundle\Entity\UnivTipoUniversidad 
     */
    public function getUnivTipoUniversidad()
    {
        return $this->univTipoUniversidad;
    }
}
