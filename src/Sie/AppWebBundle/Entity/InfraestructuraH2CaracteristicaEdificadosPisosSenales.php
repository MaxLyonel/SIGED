<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2CaracteristicaEdificadosPisosSenales
 */
class InfraestructuraH2CaracteristicaEdificadosPisosSenales
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo
     */
    private $n36Senalesioma2Tipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo
     */
    private $n36Senalesioma1Tipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo
     */
    private $n36SenalesTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisos
     */
    private $infraestructuraH2CaracteristicaEdificadosPisos;


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
     * Set n36Senalesioma2Tipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n36Senalesioma2Tipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisosSenales
     */
    public function setN36Senalesioma2Tipo(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n36Senalesioma2Tipo = null)
    {
        $this->n36Senalesioma2Tipo = $n36Senalesioma2Tipo;
    
        return $this;
    }

    /**
     * Get n36Senalesioma2Tipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo 
     */
    public function getN36Senalesioma2Tipo()
    {
        return $this->n36Senalesioma2Tipo;
    }

    /**
     * Set n36Senalesioma1Tipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n36Senalesioma1Tipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisosSenales
     */
    public function setN36Senalesioma1Tipo(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n36Senalesioma1Tipo = null)
    {
        $this->n36Senalesioma1Tipo = $n36Senalesioma1Tipo;
    
        return $this;
    }

    /**
     * Get n36Senalesioma1Tipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo 
     */
    public function getN36Senalesioma1Tipo()
    {
        return $this->n36Senalesioma1Tipo;
    }

    /**
     * Set n36SenalesTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo $n36SenalesTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisosSenales
     */
    public function setN36SenalesTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo $n36SenalesTipo = null)
    {
        $this->n36SenalesTipo = $n36SenalesTipo;
    
        return $this;
    }

    /**
     * Get n36SenalesTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo 
     */
    public function getN36SenalesTipo()
    {
        return $this->n36SenalesTipo;
    }

    /**
     * Set infraestructuraH2CaracteristicaEdificadosPisos
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisos $infraestructuraH2CaracteristicaEdificadosPisos
     * @return InfraestructuraH2CaracteristicaEdificadosPisosSenales
     */
    public function setInfraestructuraH2CaracteristicaEdificadosPisos(\Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisos $infraestructuraH2CaracteristicaEdificadosPisos = null)
    {
        $this->infraestructuraH2CaracteristicaEdificadosPisos = $infraestructuraH2CaracteristicaEdificadosPisos;
    
        return $this;
    }

    /**
     * Get infraestructuraH2CaracteristicaEdificadosPisos
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisos 
     */
    public function getInfraestructuraH2CaracteristicaEdificadosPisos()
    {
        return $this->infraestructuraH2CaracteristicaEdificadosPisos;
    }
}
