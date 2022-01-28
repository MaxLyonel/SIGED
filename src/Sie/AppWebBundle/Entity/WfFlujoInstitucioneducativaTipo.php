<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * WfFlujoInstitucioneducativaTipo
 */
class WfFlujoInstitucioneducativaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaTipo
     */
    private $institucioneducativaTipo;

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
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return WfFlujoInstitucioneducativaTipo
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }

    /**
     * Set institucioneducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo
     * @return WfFlujoInstitucioneducativaTipo
     */
    public function setInstitucioneducativaTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo = null)
    {
        $this->institucioneducativaTipo = $institucioneducativaTipo;
    
        return $this;
    }

    /**
     * Get institucioneducativaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaTipo 
     */
    public function getInstitucioneducativaTipo()
    {
        return $this->institucioneducativaTipo;
    }

    /**
     * Set flujoTipo
     *
     * @param \Sie\AppWebBundle\Entity\FlujoTipo $flujoTipo
     * @return WfFlujoInstitucioneducativaTipo
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
}
