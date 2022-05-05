<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatUniUniversidad
 */
class UnivDatUniUniversidad
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var string
     */
    private $abreviacion;

    /**
     * @var string
     */
    private $logo;

    /**
     * @var string
     */
    private $createdAt;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return UnivDatUniUniversidad
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set abreviacion
     *
     * @param string $abreviacion
     * @return UnivDatUniUniversidad
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
     * @return UnivDatUniUniversidad
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
     * Set createdAt
     *
     * @param string $createdAt
     * @return UnivDatUniUniversidad
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return string 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
