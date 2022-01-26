<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperiorModuloPeriodo
 */
class SuperiorModuloPeriodo
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
     * @var integer
     */
    private $horasModulo;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaPeriodo
     */
    private $institucioneducativaPeriodo;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorModuloTipo
     */
    private $superiorModuloTipo;


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
     * @return SuperiorModuloPeriodo
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
     * Set horasModulo
     *
     * @param integer $horasModulo
     * @return SuperiorModuloPeriodo
     */
    public function setHorasModulo($horasModulo)
    {
        $this->horasModulo = $horasModulo;
    
        return $this;
    }

    /**
     * Get horasModulo
     *
     * @return integer 
     */
    public function getHorasModulo()
    {
        return $this->horasModulo;
    }

    /**
     * Set institucioneducativaPeriodo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaPeriodo $institucioneducativaPeriodo
     * @return SuperiorModuloPeriodo
     */
    public function setInstitucioneducativaPeriodo(\Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaPeriodo $institucioneducativaPeriodo = null)
    {
        $this->institucioneducativaPeriodo = $institucioneducativaPeriodo;
    
        return $this;
    }

    /**
     * Get institucioneducativaPeriodo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaPeriodo 
     */
    public function getInstitucioneducativaPeriodo()
    {
        return $this->institucioneducativaPeriodo;
    }

    /**
     * Set superiorModuloTipo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorModuloTipo $superiorModuloTipo
     * @return SuperiorModuloPeriodo
     */
    public function setSuperiorModuloTipo(\Sie\AppWebBundle\Entity\SuperiorModuloTipo $superiorModuloTipo = null)
    {
        $this->superiorModuloTipo = $superiorModuloTipo;
    
        return $this;
    }

    /**
     * Get superiorModuloTipo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorModuloTipo 
     */
    public function getSuperiorModuloTipo()
    {
        return $this->superiorModuloTipo;
    }
}
