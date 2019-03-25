<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EquipLaboFisiQuimFotos
 */
class EquipLaboFisiQuimFotos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $foto;

    /**
     * @var \Sie\AppWebBundle\Entity\EquipLaboFisiQuim
     */
    private $equipLaboFisiQuim;

    /**
     * @var \Sie\AppWebBundle\Entity\EquipLaboFisiQuimTipoFoto
     */
    private $tipoFoto;


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
     * Set foto
     *
     * @param string $foto
     * @return EquipLaboFisiQuimFotos
     */
    public function setFoto($foto)
    {
        $this->foto = $foto;
    
        return $this;
    }

    /**
     * Get foto
     *
     * @return string 
     */
    public function getFoto()
    {
        return $this->foto;
    }

    /**
     * Set equipLaboFisiQuim
     *
     * @param \Sie\AppWebBundle\Entity\EquipLaboFisiQuim $equipLaboFisiQuim
     * @return EquipLaboFisiQuimFotos
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

    /**
     * Set tipoFoto
     *
     * @param \Sie\AppWebBundle\Entity\EquipLaboFisiQuimTipoFoto $tipoFoto
     * @return EquipLaboFisiQuimFotos
     */
    public function setTipoFoto(\Sie\AppWebBundle\Entity\EquipLaboFisiQuimTipoFoto $tipoFoto = null)
    {
        $this->tipoFoto = $tipoFoto;
    
        return $this;
    }

    /**
     * Get tipoFoto
     *
     * @return \Sie\AppWebBundle\Entity\EquipLaboFisiQuimTipoFoto 
     */
    public function getTipoFoto()
    {
        return $this->tipoFoto;
    }
}
