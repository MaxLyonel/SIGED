<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuTipo
 */
class MenuTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var string
     */
    private $ruta;

    /**
     * @var string
     */
    private $icono;

    /**
     * @var integer
     */
    private $orden;

    /**
     * @var integer
     */
    private $menuTipoId;


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
     * Set nombre
     *
     * @param string $nombre
     * @return MenuTipo
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set ruta
     *
     * @param string $ruta
     * @return MenuTipo
     */
    public function setRuta($ruta)
    {
        $this->ruta = $ruta;

        return $this;
    }

    /**
     * Get ruta
     *
     * @return string 
     */
    public function getRuta()
    {
        return $this->ruta;
    }

    /**
     * Set icono
     *
     * @param string $icono
     * @return MenuTipo
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
     * @return MenuTipo
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
     * Set menuTipoId
     *
     * @param integer $menuTipoId
     * @return MenuTipo
     */
    public function setMenuTipoId($menuTipoId)
    {
        $this->menuTipoId = $menuTipoId;

        return $this;
    }

    /**
     * Get menuTipoId
     *
     * @return integer 
     */
    public function getMenuTipoId()
    {
        return $this->menuTipoId;
    }
    /**
     * @var string
     */
    private $obs;

    /**
     * @var string
     */
    private $control;


    /**
     * Set obs
     *
     * @param string $obs
     * @return MenuTipo
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
     * Set control
     *
     * @param string $control
     * @return MenuTipo
     */
    public function setControl($control)
    {
        $this->control = $control;
    
        return $this;
    }

    /**
     * Get control
     *
     * @return string 
     */
    public function getControl()
    {
        return $this->control;
    }
    
    public function __toString() {
        return $this->nombre;
    }
    /**
     * @var string
     */
    private $detalleMenu;

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
     * @var \Sie\AppWebBundle\Entity\MenuNivelTipo
     */
    private $menuNivelTipo;


    /**
     * Set detalleMenu
     *
     * @param string $detalleMenu
     * @return MenuTipo
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
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     * @return MenuTipo
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
     * @return MenuTipo
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
     * @return MenuTipo
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
     * Set menuNivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\MenuNivelTipo $menuNivelTipo
     * @return MenuTipo
     */
    public function setMenuNivelTipo(\Sie\AppWebBundle\Entity\MenuNivelTipo $menuNivelTipo = null)
    {
        $this->menuNivelTipo = $menuNivelTipo;
    
        return $this;
    }

    /**
     * Get menuNivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\MenuNivelTipo 
     */
    public function getMenuNivelTipo()
    {
        return $this->menuNivelTipo;
    }
}
