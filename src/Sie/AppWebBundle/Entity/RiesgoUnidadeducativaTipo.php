<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RiesgoUnidadeducativaTipo
 */
class RiesgoUnidadeducativaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $riesgoUnidadeducativa;

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
     * Set riesgoUnidadeducativa
     *
     * @param string $riesgoUnidadeducativa
     * @return RiesgoUnidadeducativaTipo
     */
    public function setRiesgoUnidadeducativa($riesgoUnidadeducativa)
    {
        $this->riesgoUnidadeducativa = $riesgoUnidadeducativa;
    
        return $this;
    }

    /**
     * Get riesgoUnidadeducativa
     *
     * @return string 
     */
    public function getRiesgoUnidadeducativa()
    {
        return $this->riesgoUnidadeducativa;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return RiesgoUnidadeducativaTipo
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
