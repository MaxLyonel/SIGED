<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AreatematicaTipo
 */
class AreatematicaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $areatematica;

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
     * Set areatematica
     *
     * @param string $areatematica
     * @return AreatematicaTipo
     */
    public function setAreatematica($areatematica)
    {
        $this->areatematica = $areatematica;
    
        return $this;
    }

    /**
     * Get areatematica
     *
     * @return string 
     */
    public function getAreatematica()
    {
        return $this->areatematica;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return AreatematicaTipo
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
