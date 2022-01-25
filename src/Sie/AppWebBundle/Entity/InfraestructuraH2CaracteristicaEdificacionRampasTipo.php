<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2CaracteristicaEdificacionRampasTipo
 */
class InfraestructuraH2CaracteristicaEdificacionRampasTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo
     */
    private $n215TipoRampas;

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
     * Set n215TipoRampas
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo $n215TipoRampas
     * @return InfraestructuraH2CaracteristicaEdificacionRampasTipo
     */
    public function setN215TipoRampas(\Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo $n215TipoRampas = null)
    {
        $this->n215TipoRampas = $n215TipoRampas;
    
        return $this;
    }

    /**
     * Get n215TipoRampas
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo 
     */
    public function getN215TipoRampas()
    {
        return $this->n215TipoRampas;
    }

    /**
     * Set infraestructuraH2Caracteristica
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica $infraestructuraH2Caracteristica
     * @return InfraestructuraH2CaracteristicaEdificacionRampasTipo
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
