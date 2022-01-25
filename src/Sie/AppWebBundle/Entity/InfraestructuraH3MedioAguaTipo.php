<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH3MedioAguaTipo
 */
class InfraestructuraH3MedioAguaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraMedioAgua;

    /**
     * @var string
     */
    private $obs;

    public function __toString(){
        return $this->infraestructuraMedioAgua;
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
     * Set infraestructuraMedioAgua
     *
     * @param string $infraestructuraMedioAgua
     * @return InfraestructuraH3MedioAguaTipo
     */
    public function setInfraestructuraMedioAgua($infraestructuraMedioAgua)
    {
        $this->infraestructuraMedioAgua = $infraestructuraMedioAgua;
    
        return $this;
    }

    /**
     * Get infraestructuraMedioAgua
     *
     * @return string 
     */
    public function getInfraestructuraMedioAgua()
    {
        return $this->infraestructuraMedioAgua;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH3MedioAguaTipo
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
