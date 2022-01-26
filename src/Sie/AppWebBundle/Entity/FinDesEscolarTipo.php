<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FinDesEscolarTipo
 */
class FinDesEscolarTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $finDesEscolar;

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
     * Set finDesEscolar
     *
     * @param string $finDesEscolar
     * @return FinDesEscolarTipo
     */
    public function setFinDesEscolar($finDesEscolar)
    {
        $this->finDesEscolar = $finDesEscolar;
    
        return $this;
    }

    /**
     * Get finDesEscolar
     *
     * @return string 
     */
    public function getFinDesEscolar()
    {
        return $this->finDesEscolar;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return FinDesEscolarTipo
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
