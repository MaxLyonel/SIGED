<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EquipLaboFisiQuimConstruidaTipo
 */
class EquipLaboFisiQuimConstruidaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $construccion;


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
     * Set construccion
     *
     * @param string $construccion
     * @return EquipLaboFisiQuimConstruidaTipo
     */
    public function setConstruccion($construccion)
    {
        $this->construccion = $construccion;
    
        return $this;
    }

    /**
     * Get construccion
     *
     * @return string 
     */
    public function getConstruccion()
    {
        return $this->construccion;
    }
}
