<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PermanenteAreaTematicaTipo
 */
class PermanenteAreaTematicaTipo
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
     * @return PermanenteAreaTematicaTipo
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
     * @return PermanenteAreaTematicaTipo
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
