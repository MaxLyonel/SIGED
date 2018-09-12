<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FlujoProceso
 */
class FlujoProceso
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
    private $proceso;

    /**
     * @var \Sie\AppWebBundle\Entity\FlujoTipo
     */
    private $flujoTipo;


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
     * @return FlujoProceso
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
     * Set proceso
     *
     * @param \Sie\AppWebBundle\Entity\ProcesoTipo $proceso
     * @return FlujoProceso
     */
    public function setProceso(\Sie\AppWebBundle\Entity\ProcesoTipo $proceso = null)
    {
        $this->proceso = $proceso;
    
        return $this;
    }

    /**
     * Get proceso
     *
     * @return \Sie\AppWebBundle\Entity\ProcesoTipo 
     */
    public function getProceso()
    {
        return $this->proceso;
    }

    /**
     * Set flujoTipo
     *
     * @param \Sie\AppWebBundle\Entity\FlujoTipo $flujoTipo
     * @return FlujoProceso
     */
    public function setFlujoTipo(\Sie\AppWebBundle\Entity\FlujoTipo $flujoTipo = null)
    {
        $this->flujoTipo = $flujoTipo;
    
        return $this;
    }

    /**
     * Get flujoTipo
     *
     * @return \Sie\AppWebBundle\Entity\FlujoTipo 
     */
    public function getFlujoTipo()
    {
        return $this->flujoTipo;
    }
    /**
     * @var integer
     */
    private $orden;

    /**
     * @var \Sie\AppWebBundle\Entity\RolTipo
     */
    private $rolTipo;


    /**
     * Set orden
     *
     * @param integer $orden
     * @return FlujoProceso
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
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return FlujoProceso
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
