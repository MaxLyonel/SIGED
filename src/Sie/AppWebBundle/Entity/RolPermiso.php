<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RolPermiso
 */
class RolPermiso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\RolTipo
     */
    private $rolTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Permiso
     */
    private $permiso;


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
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return RolPermiso
     */
    public function setRolTipo(\Sie\AppWebBundle\Entity\RolTipo $rolTipo = null)
    {
        $this->rolTipo = $rolTipo;

        return $this;
    }

    /**
     * Get rolTipo
     *
     * @return \Sie\AppWebBundle\Entity\RolTipo 
     */
    public function getRolTipo()
    {
        return $this->rolTipo;
    }

    /**
     * Set permiso
     *
     * @param \Sie\AppWebBundle\Entity\Permiso $permiso
     * @return RolPermiso
     */
    public function setPermiso(\Sie\AppWebBundle\Entity\Permiso $permiso = null)
    {
        $this->permiso = $permiso;

        return $this;
    }

    /**
     * Get permiso
     *
     * @return \Sie\AppWebBundle\Entity\Permiso 
     */
    public function getPermiso()
    {
        return $this->permiso;
    }
    /**
     * @var integer
     */
    private $permisoId;


    /**
     * Set permisoId
     *
     * @param integer $permisoId
     * @return RolPermiso
     */
    public function setPermisoId($permisoId)
    {
        $this->permisoId = $permisoId;
    
        return $this;
    }

    /**
     * Get permisoId
     *
     * @return integer 
     */
    public function getPermisoId()
    {
        return $this->permisoId;
    }
}
