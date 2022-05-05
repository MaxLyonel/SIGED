<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivClaPais
 */
class UnivClaPais
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $isoMun;

    /**
     * @var string
     */
    private $iso2;

    /**
     * @var string
     */
    private $iso3;

    /**
     * @var string
     */
    private $pais;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isoMun
     *
     * @param string $isoMun
     * @return UnivClaPais
     */
    public function setIsoMun($isoMun)
    {
        $this->isoMun = $isoMun;
    
        return $this;
    }

    /**
     * Get isoMun
     *
     * @return string 
     */
    public function getIsoMun()
    {
        return $this->isoMun;
    }

    /**
     * Set iso2
     *
     * @param string $iso2
     * @return UnivClaPais
     */
    public function setIso2($iso2)
    {
        $this->iso2 = $iso2;
    
        return $this;
    }

    /**
     * Get iso2
     *
     * @return string 
     */
    public function getIso2()
    {
        return $this->iso2;
    }

    /**
     * Set iso3
     *
     * @param string $iso3
     * @return UnivClaPais
     */
    public function setIso3($iso3)
    {
        $this->iso3 = $iso3;
    
        return $this;
    }

    /**
     * Get iso3
     *
     * @return string 
     */
    public function getIso3()
    {
        return $this->iso3;
    }

    /**
     * Set pais
     *
     * @param string $pais
     * @return UnivClaPais
     */
    public function setPais($pais)
    {
        $this->pais = $pais;
    
        return $this;
    }

    /**
     * Get pais
     *
     * @return string 
     */
    public function getPais()
    {
        return $this->pais;
    }
}
