<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2ParedMaterialTipo
 */
class InfraestructuraH2ParedMaterialTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraParedMaterialTipo;

    /**
     * @var string
     */
    private $obs;


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
     * Set infraestructuraParedMaterialTipo
     *
     * @param string $infraestructuraParedMaterialTipo
     * @return InfraestructuraH2ParedMaterialTipo
     */
    public function setInfraestructuraParedMaterialTipo($infraestructuraParedMaterialTipo)
    {
        $this->infraestructuraParedMaterialTipo = $infraestructuraParedMaterialTipo;
    
        return $this;
    }

    /**
     * Get infraestructuraParedMaterialTipo
     *
     * @return string 
     */
    public function getInfraestructuraParedMaterialTipo()
    {
        return $this->infraestructuraParedMaterialTipo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH2ParedMaterialTipo
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
