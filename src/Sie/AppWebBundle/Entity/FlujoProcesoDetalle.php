<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FlujoProcesoDetalle
 */
class FlujoProcesoDetalle
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
     * @var \Sie\AppWebBundle\Entity\FlujoProceso
     */
    private $flujoProcesoSig;

    /**
     * @var \Sie\AppWebBundle\Entity\FlujoProceso
     */
    private $flujoProcesoAnt;


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
     * @return FlujoProcesoDetalle
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
     * Set flujoProcesoSig
     *
     * @param \Sie\AppWebBundle\Entity\FlujoProceso $flujoProcesoSig
     * @return FlujoProcesoDetalle
     */
    public function setFlujoProcesoSig(\Sie\AppWebBundle\Entity\FlujoProceso $flujoProcesoSig = null)
    {
        $this->flujoProcesoSig = $flujoProcesoSig;
    
        return $this;
    }

    /**
     * Get flujoProcesoSig
     *
     * @return \Sie\AppWebBundle\Entity\FlujoProceso 
     */
    public function getFlujoProcesoSig()
    {
        return $this->flujoProcesoSig;
    }

    /**
     * Set flujoProcesoAnt
     *
     * @param \Sie\AppWebBundle\Entity\FlujoProceso $flujoProcesoAnt
     * @return FlujoProcesoDetalle
     */
    public function setFlujoProcesoAnt(\Sie\AppWebBundle\Entity\FlujoProceso $flujoProcesoAnt = null)
    {
        $this->flujoProcesoAnt = $flujoProcesoAnt;
    
        return $this;
    }

    /**
     * Get flujoProcesoAnt
     *
     * @return \Sie\AppWebBundle\Entity\FlujoProceso 
     */
    public function getFlujoProcesoAnt()
    {
        return $this->flujoProcesoAnt;
    }
}
