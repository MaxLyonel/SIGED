<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PermanenteCursocortoTipo
 */
class PermanenteCursocortoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $cursocorto;

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
     * Set cursocorto
     *
     * @param string $cursocorto
     * @return PermanenteCursocortoTipo
     */
    public function setCursocorto($cursocorto)
    {
        $this->cursocorto = $cursocorto;
    
        return $this;
    }

    /**
     * Get cursocorto
     *
     * @return string 
     */
    public function getCursocorto()
    {
        return $this->cursocorto;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return PermanenteCursocortoTipo
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
