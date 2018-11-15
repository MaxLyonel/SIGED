<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SistemaTipo
 */
class SistemaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $sistema;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var string
     */
    private $abreviatura;

    /**
     * @var string
     */
    private $bundle;

    /**
     * @var string
     */
    private $url;

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
     * Set sistema
     *
     * @param string $sistema
     * @return SistemaTipo
     */
    public function setSistema($sistema)
    {
        $this->sistema = $sistema;
    
        return $this;
    }

    /**
     * Get sistema
     *
     * @return string 
     */
    public function getSistema()
    {
        return $this->sistema;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return SistemaTipo
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
     * Set abreviatura
     *
     * @param string $abreviatura
     * @return SistemaTipo
     */
    public function setAbreviatura($abreviatura)
    {
        $this->abreviatura = $abreviatura;
    
        return $this;
    }

    /**
     * Get abreviatura
     *
     * @return string 
     */
    public function getAbreviatura()
    {
        return $this->abreviatura;
    }

    /**
     * Set bundle
     *
     * @param string $bundle
     * @return SistemaTipo
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
    
        return $this;
    }

    /**
     * Get bundle
     *
     * @return string 
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return SistemaTipo
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

}
