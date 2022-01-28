<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RolProceso
 */
class RolProceso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\ProcesoTipo
     */
    private $procesoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\RolTipo
     */
    private $rolTipo;


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
     * Set obs
     *
     * @param string $obs
     * @return RolProceso
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
     * Set procesoTipo
     *
     * @param \Sie\AppWebBundle\Entity\ProcesoTipo $procesoTipo
     * @return RolProceso
     */
    public function setProcesoTipo(\Sie\AppWebBundle\Entity\ProcesoTipo $procesoTipo = null)
    {
        $this->procesoTipo = $procesoTipo;
    
        return $this;
    }

    /**
     * Get procesoTipo
     *
     * @return \Sie\AppWebBundle\Entity\ProcesoTipo 
     */
    public function getProcesoTipo()
    {
        return $this->procesoTipo;
    }

    /**
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return RolProceso
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
}
