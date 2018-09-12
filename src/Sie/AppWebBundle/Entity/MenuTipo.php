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
    private $menuTipoId;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var string
     */
    private $control;


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
}
