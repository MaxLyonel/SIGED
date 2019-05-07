<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CdlProductoimagen
 */
class CdlProductoimagen
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $urlImagen;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\CdlProductos
     */
    private $cdlProductos;


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
     * Set urlImagen
     *
     * @param string $urlImagen
     * @return CdlProductoimagen
     */
    public function setUrlImagen($urlImagen)
    {
        $this->urlImagen = $urlImagen;
    
        return $this;
    }

    /**
     * Get urlImagen
     *
     * @return string 
     */
    public function getUrlImagen()
    {
        return $this->urlImagen;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return CdlProductoimagen
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
     * Set cdlProductos
     *
     * @param \Sie\AppWebBundle\Entity\CdlProductos $cdlProductos
     * @return CdlProductoimagen
     */
    public function setCdlProductos(\Sie\AppWebBundle\Entity\CdlProductos $cdlProductos = null)
    {
        $this->cdlProductos = $cdlProductos;
    
        return $this;
    }

    /**
     * Get cdlProductos
     *
     * @return \Sie\AppWebBundle\Entity\CdlProductos 
     */
    public function getCdlProductos()
    {
        return $this->cdlProductos;
    }
}
