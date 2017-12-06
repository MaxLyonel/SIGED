<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonojuancitoInstitucioneducativaTipo
 */
class BonojuancitoInstitucioneducativaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $bonojuancitoInstitucioneducativa;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var boolean
     */
    private $esactivo;


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
     * Set bonojuancitoInstitucioneducativa
     *
     * @param string $bonojuancitoInstitucioneducativa
     * @return BonojuancitoInstitucioneducativaTipo
     */
    public function setBonojuancitoInstitucioneducativa($bonojuancitoInstitucioneducativa)
    {
        $this->bonojuancitoInstitucioneducativa = $bonojuancitoInstitucioneducativa;
    
        return $this;
    }

    /**
     * Get bonojuancitoInstitucioneducativa
     *
     * @return string 
     */
    public function getBonojuancitoInstitucioneducativa()
    {
        return $this->bonojuancitoInstitucioneducativa;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BonojuancitoInstitucioneducativaTipo
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

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return BonojuancitoInstitucioneducativaTipo
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }
}
