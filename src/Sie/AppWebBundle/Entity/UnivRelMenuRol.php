<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivRelMenuRol
 */
class UnivRelMenuRol
{
    /**
     * @var string
     */
    private $menuId;

    /**
     * @var string
     */
    private $rolId;


    /**
     * Set menuId
     *
     * @param string $menuId
     * @return UnivRelMenuRol
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
     * Set rolId
     *
     * @param string $rolId
     * @return UnivRelMenuRol
     */
    public function setRolId($rolId)
    {
        $this->rolId = $rolId;
    
        return $this;
    }

    /**
     * Get rolId
     *
     * @return string 
     */
    public function getRolId()
    {
        return $this->rolId;
    }
}
