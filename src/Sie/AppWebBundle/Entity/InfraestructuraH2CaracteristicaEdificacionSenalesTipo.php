<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2CaracteristicaEdificacionSenalesTipo
 */
class InfraestructuraH2CaracteristicaEdificacionSenalesTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo
     */
    private $n213TipoSenalesioma2;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo
     */
    private $n213TipoSenalesioma1;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo
     */
    private $n213TipoSenales;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica
     */
    private $infraestructuraH2Caracteristica;


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
     * Set n213TipoSenalesioma2
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n213TipoSenalesioma2
     * @return InfraestructuraH2CaracteristicaEdificacionSenalesTipo
     */
    public function setN213TipoSenalesioma2(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n213TipoSenalesioma2 = null)
    {
        $this->n213TipoSenalesioma2 = $n213TipoSenalesioma2;
    
        return $this;
    }

    /**
     * Get n213TipoSenalesioma2
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo 
     */
    public function getN213TipoSenalesioma2()
    {
        return $this->n213TipoSenalesioma2;
    }

    /**
     * Set n213TipoSenalesioma1
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n213TipoSenalesioma1
     * @return InfraestructuraH2CaracteristicaEdificacionSenalesTipo
     */
    public function setN213TipoSenalesioma1(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n213TipoSenalesioma1 = null)
    {
        $this->n213TipoSenalesioma1 = $n213TipoSenalesioma1;
    
        return $this;
    }

    /**
     * Get n213TipoSenalesioma1
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo 
     */
    public function getN213TipoSenalesioma1()
    {
        return $this->n213TipoSenalesioma1;
    }

    /**
     * Set n213TipoSenales
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo $n213TipoSenales
     * @return InfraestructuraH2CaracteristicaEdificacionSenalesTipo
     */
    public function setN213TipoSenales(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo $n213TipoSenales = null)
    {
        $this->n213TipoSenales = $n213TipoSenales;
    
        return $this;
    }

    /**
     * Get n213TipoSenales
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo 
     */
    public function getN213TipoSenales()
    {
        return $this->n213TipoSenales;
    }

    /**
     * Set infraestructuraH2Caracteristica
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica $infraestructuraH2Caracteristica
     * @return InfraestructuraH2CaracteristicaEdificacionSenalesTipo
     */
    public function setInfraestructuraH2Caracteristica(\Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica $infraestructuraH2Caracteristica = null)
    {
        $this->infraestructuraH2Caracteristica = $infraestructuraH2Caracteristica;
    
        return $this;
    }

    /**
     * Get infraestructuraH2Caracteristica
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica 
     */
    public function getInfraestructuraH2Caracteristica()
    {
        return $this->infraestructuraH2Caracteristica;
    }
}
