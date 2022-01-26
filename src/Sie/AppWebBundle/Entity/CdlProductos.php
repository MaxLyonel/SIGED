<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CdlProductos
 */
class CdlProductos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombreProducto;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\CdlEventos
     */
    private $cdlEventos;


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
     * Set nombreProducto
     *
     * @param string $nombreProducto
     * @return CdlProductos
     */
    public function setNombreProducto($nombreProducto)
    {
        $this->nombreProducto = $nombreProducto;
    
        return $this;
    }

    /**
     * Get nombreProducto
     *
     * @return string 
     */
    public function getNombreProducto()
    {
        return $this->nombreProducto;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return CdlProductos
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
     * Set cdlEventos
     *
     * @param \Sie\AppWebBundle\Entity\CdlEventos $cdlEventos
     * @return CdlProductos
     */
    public function setCdlEventos(\Sie\AppWebBundle\Entity\CdlEventos $cdlEventos = null)
    {
        $this->cdlEventos = $cdlEventos;
    
        return $this;
    }

    /**
     * Get cdlEventos
     *
     * @return \Sie\AppWebBundle\Entity\CdlEventos 
     */
    public function getCdlEventos()
    {
        return $this->cdlEventos;
    }
}
