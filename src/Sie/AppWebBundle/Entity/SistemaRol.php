<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SistemaRol
 */
class SistemaRol
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
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return SistemaRol
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
     * Set sistemaTipo
     *
     * @param \Sie\AppWebBundle\Entity\SistemaTipo $sistemaTipo
     * @return SistemaRol
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
