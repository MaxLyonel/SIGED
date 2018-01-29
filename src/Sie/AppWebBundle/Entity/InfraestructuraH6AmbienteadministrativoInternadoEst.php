<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH6AmbienteadministrativoInternadoEst
 */
class InfraestructuraH6AmbienteadministrativoInternadoEst
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $n31EsInternadoEst;

    /**
     * @var string
     */
    private $n32DistMetrosFemMas;

    /**
     * @var integer
     */
    private $n34DormitorioTipoId;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6ResponsableTipo
     */
    private $n33ResponsableTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo
     */
    private $infraestructuraH6Ambienteadministrativo;


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
     * Set n31EsInternadoEst
     *
     * @param boolean $n31EsInternadoEst
     * @return InfraestructuraH6AmbienteadministrativoInternadoEst
     */
    public function setN31EsInternadoEst($n31EsInternadoEst)
    {
        $this->n31EsInternadoEst = $n31EsInternadoEst;
    
        return $this;
    }

    /**
     * Get n31EsInternadoEst
     *
     * @return boolean 
     */
    public function getN31EsInternadoEst()
    {
        return $this->n31EsInternadoEst;
    }

    /**
     * Set n32DistMetrosFemMas
     *
     * @param string $n32DistMetrosFemMas
     * @return InfraestructuraH6AmbienteadministrativoInternadoEst
     */
    public function setN32DistMetrosFemMas($n32DistMetrosFemMas)
    {
        $this->n32DistMetrosFemMas = $n32DistMetrosFemMas;
    
        return $this;
    }

    /**
     * Get n32DistMetrosFemMas
     *
     * @return string 
     */
    public function getN32DistMetrosFemMas()
    {
        return $this->n32DistMetrosFemMas;
    }

    /**
     * Set n34DormitorioTipoId
     *
     * @param integer $n34DormitorioTipoId
     * @return InfraestructuraH6AmbienteadministrativoInternadoEst
     */
    public function setN34DormitorioTipoId($n34DormitorioTipoId)
    {
        $this->n34DormitorioTipoId = $n34DormitorioTipoId;
    
        return $this;
    }

    /**
     * Get n34DormitorioTipoId
     *
     * @return integer 
     */
    public function getN34DormitorioTipoId()
    {
        return $this->n34DormitorioTipoId;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH6AmbienteadministrativoInternadoEst
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
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH6AmbienteadministrativoInternadoEst
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
     * Set n33ResponsableTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6ResponsableTipo $n33ResponsableTipo
     * @return InfraestructuraH6AmbienteadministrativoInternadoEst
     */
    public function setN33ResponsableTipo(\Sie\AppWebBundle\Entity\InfraestructuraH6ResponsableTipo $n33ResponsableTipo = null)
    {
        $this->n33ResponsableTipo = $n33ResponsableTipo;
    
        return $this;
    }

    /**
     * Get n33ResponsableTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6ResponsableTipo 
     */
    public function getN33ResponsableTipo()
    {
        return $this->n33ResponsableTipo;
    }

    /**
     * Set infraestructuraH6Ambienteadministrativo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo $infraestructuraH6Ambienteadministrativo
     * @return InfraestructuraH6AmbienteadministrativoInternadoEst
     */
    public function setInfraestructuraH6Ambienteadministrativo(\Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo $infraestructuraH6Ambienteadministrativo = null)
    {
        $this->infraestructuraH6Ambienteadministrativo = $infraestructuraH6Ambienteadministrativo;
    
        return $this;
    }

    /**
     * Get infraestructuraH6Ambienteadministrativo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo 
     */
    public function getInfraestructuraH6Ambienteadministrativo()
    {
        return $this->infraestructuraH6Ambienteadministrativo;
    }
}
