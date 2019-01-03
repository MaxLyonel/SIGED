<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IdiomaTipo
 */
class IdiomaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $idioma;


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
     * Set idioma
     *
     * @param string $idioma
     * @return IdiomaTipo
     */
    public function setIdioma($idioma)
    {
        $this->idioma = $idioma;

        return $this;
    }

    /**
     * Get idioma
     *
     * @return string 
     */
    public function getIdioma()
    {
        return $this->idioma;
    }
    
    public function __toString() {
        return $this->idioma;
    }
    /**
     * @var integer
     */
    private $obs;


    /**
     * Set obs
     *
     * @param integer $obs
     * @return IdiomaTipo
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return integer 
     */
    public function getObs()
    {
        return $this->obs;
    }
}
