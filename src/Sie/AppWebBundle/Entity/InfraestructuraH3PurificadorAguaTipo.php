<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH3PurificadorAguaTipo
 */
class InfraestructuraH3PurificadorAguaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraPurificadorAgua;

    /**
     * @var string
     */
    private $obs;

    public function __toString(){
        return $this->infraestructuraPurificadorAgua;
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
     * Set infraestructuraPurificadorAgua
     *
     * @param string $infraestructuraPurificadorAgua
     * @return InfraestructuraH3PurificadorAguaTipo
     */
    public function setInfraestructuraPurificadorAgua($infraestructuraPurificadorAgua)
    {
        $this->infraestructuraPurificadorAgua = $infraestructuraPurificadorAgua;
    
        return $this;
    }

    /**
     * Get infraestructuraPurificadorAgua
     *
     * @return string 
     */
    public function getInfraestructuraPurificadorAgua()
    {
        return $this->infraestructuraPurificadorAgua;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH3PurificadorAguaTipo
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
