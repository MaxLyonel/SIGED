<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5AmbienteadministrativoPatio
 */
class InfraestructuraH5AmbienteadministrativoPatio
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n22AreaCanchaMt2;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n22PisoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5MaterialPisoTipo
     */
    private $n22MaterialPisoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5Ambienteadministrativo
     */
    private $infraestructuraH5Ambienteadministrativo;


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
     * Set n22AreaCanchaMt2
     *
     * @param integer $n22AreaCanchaMt2
     * @return InfraestructuraH5AmbienteadministrativoPatio
     */
    public function setN22AreaCanchaMt2($n22AreaCanchaMt2)
    {
        $this->n22AreaCanchaMt2 = $n22AreaCanchaMt2;
    
        return $this;
    }

    /**
     * Get n22AreaCanchaMt2
     *
     * @return integer 
     */
    public function getN22AreaCanchaMt2()
    {
        return $this->n22AreaCanchaMt2;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH5AmbienteadministrativoPatio
     */
    public function setFecharegistro($fecharegistro)
    {
        $this->fecharegistro = $fecharegistro;
    
        return $this;
    }

    /**
     * Get fecharegistro
     *
     * @return \DateTime 
     */
    public function getFecharegistro()
    {
        return $this->fecharegistro;
    }

    /**
     * Set n22PisoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n22PisoEstadogeneralTipo
     * @return InfraestructuraH5AmbienteadministrativoPatio
     */
    public function setN22PisoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n22PisoEstadogeneralTipo = null)
    {
        $this->n22PisoEstadogeneralTipo = $n22PisoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n22PisoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN22PisoEstadogeneralTipo()
    {
        return $this->n22PisoEstadogeneralTipo;
    }

    /**
     * Set n22MaterialPisoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5MaterialPisoTipo $n22MaterialPisoTipo
     * @return InfraestructuraH5AmbienteadministrativoPatio
     */
    public function setN22MaterialPisoTipo(\Sie\AppWebBundle\Entity\InfraestructuraH5MaterialPisoTipo $n22MaterialPisoTipo = null)
    {
        $this->n22MaterialPisoTipo = $n22MaterialPisoTipo;
    
        return $this;
    }

    /**
     * Get n22MaterialPisoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5MaterialPisoTipo 
     */
    public function getN22MaterialPisoTipo()
    {
        return $this->n22MaterialPisoTipo;
    }

    /**
     * Set infraestructuraH5Ambienteadministrativo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5Ambienteadministrativo $infraestructuraH5Ambienteadministrativo
     * @return InfraestructuraH5AmbienteadministrativoPatio
     */
    public function setInfraestructuraH5Ambienteadministrativo(\Sie\AppWebBundle\Entity\InfraestructuraH5Ambienteadministrativo $infraestructuraH5Ambienteadministrativo = null)
    {
        $this->infraestructuraH5Ambienteadministrativo = $infraestructuraH5Ambienteadministrativo;
    
        return $this;
    }

    /**
     * Get infraestructuraH5Ambienteadministrativo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5Ambienteadministrativo 
     */
    public function getInfraestructuraH5Ambienteadministrativo()
    {
        return $this->infraestructuraH5Ambienteadministrativo;
    }
}
