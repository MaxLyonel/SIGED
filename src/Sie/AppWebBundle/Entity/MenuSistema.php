<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuSistema
 */
class MenuSistema
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $detalleMenu;

    /**
     * @var string
     */
    private $icono;

    /**
     * @var integer
     */
    private $orden;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var \DateTime
     */
    private $fechaCreacion;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\MenuTipo
     */
    private $menuTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\SistemaTipo
     */
    private $sistemaTipo;


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
     * Set detalleMenu
     *
     * @param string $detalleMenu
     * @return MenuSistema
     */
    public function setDetalleMenu($detalleMenu)
    {
        $this->detalleMenu = $detalleMenu;
    
        return $this;
    }

    /**
     * Get detalleMenu
     *
     * @return string 
     */
    public function getDetalleMenu()
    {
        return $this->detalleMenu;
    }

    /**
     * Set icono
     *
     * @param string $icono
     * @return MenuSistema
     */
    public function setIcono($icono)
    {
        $this->icono = $icono;
    
        return $this;
    }

    /**
     * Get icono
     *
     * @return string 
     */
    public function getIcono()
    {
        return $this->icono;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     * @return MenuSistema
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;
    
        return $this;
    }

    /**
     * Get orden
     *
     * @return integer 
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return MenuSistema
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    
        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime 
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     * @return MenuSistema
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    
        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime 
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     * @return MenuSistema
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    
        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime 
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return MenuSistema
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
     * Set menuTipo
     *
     * @param \Sie\AppWebBundle\Entity\MenuTipo $menuTipo
     * @return MenuSistema
     */
    public function setMenuTipo(\Sie\AppWebBundle\Entity\MenuTipo $menuTipo = null)
    {
        $this->menuTipo = $menuTipo;
    
        return $this;
    }

    /**
     * Get menuTipo
     *
     * @return \Sie\AppWebBundle\Entity\MenuTipo 
     */
    public function getMenuTipo()
    {
        return $this->menuTipo;
    }

    /**
     * Set sistemaTipo
     *
     * @param \Sie\AppWebBundle\Entity\SistemaTipo $sistemaTipo
     * @return MenuSistema
     */
    public function setSistemaTipo(\Sie\AppWebBundle\Entity\SistemaTipo $sistemaTipo = null)
    {
        $this->sistemaTipo = $sistemaTipo;
    
        return $this;
    }

    /**
     * Get sistemaTipo
     *
     * @return \Sie\AppWebBundle\Entity\SistemaTipo 
     */
    public function getSistemaTipo()
    {
        return $this->sistemaTipo;
    }
}
