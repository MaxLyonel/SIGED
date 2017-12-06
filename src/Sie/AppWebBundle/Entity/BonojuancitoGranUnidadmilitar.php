<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonojuancitoGranUnidadmilitar
 */
class BonojuancitoGranUnidadmilitar
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $granUnidadmilitar;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\BonojuancitoFuerzaTipo
     */
    private $bonojuancitoFuerzaTipo;


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
     * Set granUnidadmilitar
     *
     * @param string $granUnidadmilitar
     * @return BonojuancitoGranUnidadmilitar
     */
    public function setGranUnidadmilitar($granUnidadmilitar)
    {
        $this->granUnidadmilitar = $granUnidadmilitar;
    
        return $this;
    }

    /**
     * Get granUnidadmilitar
     *
     * @return string 
     */
    public function getGranUnidadmilitar()
    {
        return $this->granUnidadmilitar;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BonojuancitoGranUnidadmilitar
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
     * Set bonojuancitoFuerzaTipo
     *
     * @param \Sie\AppWebBundle\Entity\BonojuancitoFuerzaTipo $bonojuancitoFuerzaTipo
     * @return BonojuancitoGranUnidadmilitar
     */
    public function setBonojuancitoFuerzaTipo(\Sie\AppWebBundle\Entity\BonojuancitoFuerzaTipo $bonojuancitoFuerzaTipo = null)
    {
        $this->bonojuancitoFuerzaTipo = $bonojuancitoFuerzaTipo;
    
        return $this;
    }

    /**
     * Get bonojuancitoFuerzaTipo
     *
     * @return \Sie\AppWebBundle\Entity\BonojuancitoFuerzaTipo 
     */
    public function getBonojuancitoFuerzaTipo()
    {
        return $this->bonojuancitoFuerzaTipo;
    }
}
