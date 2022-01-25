<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5AmbienteadministrativoParque
 */
class InfraestructuraH5AmbienteadministrativoParque
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n23AreaCanchaMt2;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n23PisoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5MaterialPisoTipo
     */
    private $n23MaterialPisoTipo;

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
     * Set n23AreaCanchaMt2
     *
     * @param integer $n23AreaCanchaMt2
     * @return InfraestructuraH5AmbienteadministrativoParque
     */
    public function setN23AreaCanchaMt2($n23AreaCanchaMt2)
    {
        $this->n23AreaCanchaMt2 = $n23AreaCanchaMt2;
    
        return $this;
    }

    /**
     * Get n23AreaCanchaMt2
     *
     * @return integer 
     */
    public function getN23AreaCanchaMt2()
    {
        return $this->n23AreaCanchaMt2;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH5AmbienteadministrativoParque
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
     * Set n23PisoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n23PisoEstadogeneralTipo
     * @return InfraestructuraH5AmbienteadministrativoParque
     */
    public function setN23PisoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n23PisoEstadogeneralTipo = null)
    {
        $this->n23PisoEstadogeneralTipo = $n23PisoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n23PisoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN23PisoEstadogeneralTipo()
    {
        return $this->n23PisoEstadogeneralTipo;
    }

    /**
     * Set n23MaterialPisoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5MaterialPisoTipo $n23MaterialPisoTipo
     * @return InfraestructuraH5AmbienteadministrativoParque
     */
    public function setN23MaterialPisoTipo(\Sie\AppWebBundle\Entity\InfraestructuraH5MaterialPisoTipo $n23MaterialPisoTipo = null)
    {
        $this->n23MaterialPisoTipo = $n23MaterialPisoTipo;
    
        return $this;
    }

    /**
     * Get n23MaterialPisoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5MaterialPisoTipo 
     */
    public function getN23MaterialPisoTipo()
    {
        return $this->n23MaterialPisoTipo;
    }

    /**
     * Set infraestructuraH5Ambienteadministrativo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5Ambienteadministrativo $infraestructuraH5Ambienteadministrativo
     * @return InfraestructuraH5AmbienteadministrativoParque
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
