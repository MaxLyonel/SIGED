<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnidadMilitarTipo
 */
class UnidadMilitarTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $unidadMilitarTipo;


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
     * Set unidadMilitarTipo
     *
     * @param string $unidadMilitarTipo
     * @return UnidadMilitarTipo
     */
    public function setUnidadMilitarTipo($unidadMilitarTipo)
    {
        $this->unidadMilitarTipo = $unidadMilitarTipo;
    
        return $this;
    }

    /**
     * Get unidadMilitarTipo
     *
     * @return string 
     */
    public function getUnidadMilitarTipo()
    {
        return $this->unidadMilitarTipo;
    }
}
