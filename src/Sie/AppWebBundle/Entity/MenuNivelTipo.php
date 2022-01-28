<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuNivelTipo
 */
class MenuNivelTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $menuNivelTipo;

    /**
     * @var string
     */
    private $observacion;


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
     * Set menuNivelTipo
     *
     * @param string $menuNivelTipo
     * @return MenuNivelTipo
     */
    public function setMenuNivelTipo($menuNivelTipo)
    {
        $this->menuNivelTipo = $menuNivelTipo;
    
        return $this;
    }

    /**
     * Get menuNivelTipo
     *
     * @return string 
     */
    public function getMenuNivelTipo()
    {
        return $this->menuNivelTipo;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return MenuNivelTipo
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }
}
