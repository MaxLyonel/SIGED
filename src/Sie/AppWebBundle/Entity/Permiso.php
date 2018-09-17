<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Permiso
 */
class Permiso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $permiso;

    /**
     * @var boolean
     */
    private $create;

    /**
     * @var boolean
     */
    private $read;

    /**
     * @var boolean
     */
    private $delete;

    /**
     * @var boolean
     */
    private $update;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\MenuObjeto
     */
    private $menuObjeto;


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
     * Set permiso
     *
     * @param string $permiso
     * @return Permiso
     */
    public function setPermiso($permiso)
    {
        $this->permiso = $permiso;
    
        return $this;
    }

    /**
     * Get permiso
     *
     * @return string 
     */
    public function getPermiso()
    {
        return $this->permiso;
    }

    /**
     * Set create
     *
     * @param boolean $create
     * @return Permiso
     */
    public function setCreate($create)
    {
        $this->create = $create;
    
        return $this;
    }

    /**
     * Get create
     *
     * @return boolean 
     */
    public function getCreate()
    {
        return $this->create;
    }

    /**
     * Set read
     *
     * @param boolean $read
     * @return Permiso
     */
    public function setRead($read)
    {
        $this->read = $read;
    
        return $this;
    }

    /**
     * Get read
     *
     * @return boolean 
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * Set delete
     *
     * @param boolean $delete
     * @return Permiso
     */
    public function setDelete($delete)
    {
        $this->delete = $delete;
    
        return $this;
    }

    /**
     * Get delete
     *
     * @return boolean 
     */
    public function getDelete()
    {
        return $this->delete;
    }

    /**
     * Set update
     *
     * @param boolean $update
     * @return Permiso
     */
    public function setUpdate($update)
    {
        $this->update = $update;
    
        return $this;
    }

    /**
     * Get update
     *
     * @return boolean 
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return Permiso
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
     * Set menuObjeto
     *
     * @param \Sie\AppWebBundle\Entity\MenuObjeto $menuObjeto
     * @return Permiso
     */
    public function setMenuObjeto(\Sie\AppWebBundle\Entity\MenuObjeto $menuObjeto = null)
    {
        $this->menuObjeto = $menuObjeto;
    
        return $this;
    }

    /**
     * Get menuObjeto
     *
     * @return \Sie\AppWebBundle\Entity\MenuObjeto 
     */
    public function getMenuObjeto()
    {
        return $this->menuObjeto;
    }
}
