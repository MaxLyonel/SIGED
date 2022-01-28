<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2CaracteristicaEdificadosPisosGradasTipo
 */
class InfraestructuraH2CaracteristicaEdificadosPisosGradasTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisos
     */
    private $infraestructuraH2CaracteristicaEdificadosPisos;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2PisosGradasTipo
     */
    private $n32GradasTipo;


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
     * Set infraestructuraH2CaracteristicaEdificadosPisos
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificadosPisos $infraestructuraH2CaracteristicaEdificadosPisos
     * @return InfraestructuraH2CaracteristicaEdificadosPisosGradasTipo
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

    /**
     * Set n32GradasTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2PisosGradasTipo $n32GradasTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisosGradasTipo
     */
    public function setN32GradasTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2PisosGradasTipo $n32GradasTipo = null)
    {
        $this->n32GradasTipo = $n32GradasTipo;
    
        return $this;
    }

    /**
     * Get n32GradasTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2PisosGradasTipo 
     */
    public function getN32GradasTipo()
    {
        return $this->n32GradasTipo;
    }
}
