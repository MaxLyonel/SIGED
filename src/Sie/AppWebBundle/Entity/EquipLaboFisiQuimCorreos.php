<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EquipLaboFisiQuimCorreos
 */
class EquipLaboFisiQuimCorreos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $correoElectronico;

    /**
     * @var \Sie\AppWebBundle\Entity\EquipLaboFisiQuim
     */
    private $equipLaboFisiQuim;


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
     * Set correoElectronico
     *
     * @param string $correoElectronico
     * @return EquipLaboFisiQuimCorreos
     */
    public function setCorreoElectronico($correoElectronico)
    {
        $this->correoElectronico = $correoElectronico;
    
        return $this;
    }

    /**
     * Get correoElectronico
     *
     * @return string 
     */
    public function getCorreoElectronico()
    {
        return $this->correoElectronico;
    }

    /**
     * Set equipLaboFisiQuim
     *
     * @param \Sie\AppWebBundle\Entity\EquipLaboFisiQuim $equipLaboFisiQuim
     * @return EquipLaboFisiQuimCorreos
     */
    public function setEquipLaboFisiQuim(\Sie\AppWebBundle\Entity\EquipLaboFisiQuim $equipLaboFisiQuim = null)
    {
        $this->equipLaboFisiQuim = $equipLaboFisiQuim;
    
        return $this;
    }

    /**
     * Get equipLaboFisiQuim
     *
     * @return \Sie\AppWebBundle\Entity\EquipLaboFisiQuim 
     */
    public function getEquipLaboFisiQuim()
    {
        return $this->equipLaboFisiQuim;
    }
}
