<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaHumanisticoTecnicoTipo
 */
class InstitucioneducativaHumanisticoTecnicoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $institucioneducativaHumanisticoTecnicoTipo;

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
     * Set institucioneducativaHumanisticoTecnicoTipo
     *
     * @param string $institucioneducativaHumanisticoTecnicoTipo
     * @return InstitucioneducativaHumanisticoTecnicoTipo
     */
    public function setInstitucioneducativaHumanisticoTecnicoTipo($institucioneducativaHumanisticoTecnicoTipo)
    {
        $this->institucioneducativaHumanisticoTecnicoTipo = $institucioneducativaHumanisticoTecnicoTipo;
    
        return $this;
    }

    /**
     * Get institucioneducativaHumanisticoTecnicoTipo
     *
     * @return string 
     */
    public function getInstitucioneducativaHumanisticoTecnicoTipo()
    {
        return $this->institucioneducativaHumanisticoTecnicoTipo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InstitucioneducativaHumanisticoTecnicoTipo
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
