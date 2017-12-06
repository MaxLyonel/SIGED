<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH3FuenteElectricaTipo
 */
class InfraestructuraH3FuenteElectricaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraFuenteelectrica;

    /**
     * @var string
     */
    private $obs;

    public function __toString(){
        return $this->infraestructuraFuenteelectrica;
    }


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
     * Set infraestructuraFuenteelectrica
     *
     * @param string $infraestructuraFuenteelectrica
     * @return InfraestructuraH3FuenteElectricaTipo
     */
    public function setInfraestructuraFuenteelectrica($infraestructuraFuenteelectrica)
    {
        $this->infraestructuraFuenteelectrica = $infraestructuraFuenteelectrica;
    
        return $this;
    }

    /**
     * Get infraestructuraFuenteelectrica
     *
     * @return string 
     */
    public function getInfraestructuraFuenteelectrica()
    {
        return $this->infraestructuraFuenteelectrica;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH3FuenteElectricaTipo
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
}
