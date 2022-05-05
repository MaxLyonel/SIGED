<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatUsuMenu
 */
class UnivDatUsuMenu
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
    private $link;

    /**
     * @var string
     */
    private $tipoMenu;

    /**
     * @var string
     */
    private $orden;

    /**
     * @var string
     */
    private $menuId;

    /**
     * @var string
     */
    private $icono;

    /**
     * @var string
     */
    private $habilitado;


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
     * @return UnivDatUsuMenu
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
     * @return UnivDatUsuMenu
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
     * Set link
     *
     * @param string $link
     * @return UnivDatUsuMenu
     */
    public function setLink($link)
    {
        $this->link = $link;
    
        return $this;
    }

    /**
     * Get link
     *
     * @return string 
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set tipoMenu
     *
     * @param string $tipoMenu
     * @return UnivDatUsuMenu
     */
    public function setTipoMenu($tipoMenu)
    {
        $this->tipoMenu = $tipoMenu;
    
        return $this;
    }

    /**
     * Get tipoMenu
     *
     * @return string 
     */
    public function getTipoMenu()
    {
        return $this->tipoMenu;
    }

    /**
     * Set orden
     *
     * @param string $orden
     * @return UnivDatUsuMenu
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;
    
        return $this;
    }

    /**
     * Get orden
     *
     * @return string 
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set menuId
     *
     * @param string $menuId
     * @return UnivDatUsuMenu
     */
    public function setMenuId($menuId)
    {
        $this->menuId = $menuId;
    
        return $this;
    }

    /**
     * Get menuId
     *
     * @return string 
     */
    public function getMenuId()
    {
        return $this->menuId;
    }

    /**
     * Set icono
     *
     * @param string $icono
     * @return UnivDatUsuMenu
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
     * Set habilitado
     *
     * @param string $habilitado
     * @return UnivDatUsuMenu
     */
    public function setHabilitado($habilitado)
    {
        $this->habilitado = $habilitado;
    
        return $this;
    }

    /**
     * Get habilitado
     *
     * @return string 
     */
    public function getHabilitado()
    {
        return $this->habilitado;
    }
}
