<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivNaturalezajuridicaTipo
 */
class UnivNaturalezajuridicaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $naturalezajuridica;


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
     * Set naturalezajuridica
     *
     * @param string $naturalezajuridica
     * @return UnivNaturalezajuridicaTipo
     */
    public function setNaturalezajuridica($naturalezajuridica)
    {
        $this->naturalezajuridica = $naturalezajuridica;
    
        return $this;
    }

    /**
     * Get naturalezajuridica
     *
     * @return string 
     */
    public function getNaturalezajuridica()
    {
        return $this->naturalezajuridica;
    }
}
