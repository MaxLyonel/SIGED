<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2CaracteristicaEdificadosPisosRampasTipo
 */
class InfraestructuraH2CaracteristicaEdificadosPisosRampasTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo
     */
    private $n34RampasTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisosGradasTipo
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
     * Set n34RampasTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo $n34RampasTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisosRampasTipo
     */
    public function setN34RampasTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo $n34RampasTipo = null)
    {
        $this->n34RampasTipo = $n34RampasTipo;
    
        return $this;
    }

    /**
     * Get n34RampasTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo 
     */
    public function getN34RampasTipo()
    {
        return $this->n34RampasTipo;
    }

    /**
     * Set infraestructuraH2CaracteristicaEdificadosPisos
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisosGradasTipo $infraestructuraH2CaracteristicaEdificadosPisos
     * @return InfraestructuraH2CaracteristicaEdificadosPisosRampasTipo
     */
    public function setInfraestructuraH2CaracteristicaEdificadosPisos(\Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisosGradasTipo $infraestructuraH2CaracteristicaEdificadosPisos = null)
    {
        $this->infraestructuraH2CaracteristicaEdificadosPisos = $infraestructuraH2CaracteristicaEdificadosPisos;
    
        return $this;
    }

    /**
     * Get infraestructuraH2CaracteristicaEdificadosPisos
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisosGradasTipo 
     */
    public function getInfraestructuraH2CaracteristicaEdificadosPisos()
    {
        return $this->infraestructuraH2CaracteristicaEdificadosPisos;
    }
}
