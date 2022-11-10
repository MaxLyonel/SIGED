<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivTipoUniversidad
 */
class UnivTipoUniversidad
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tipoUniversidad;


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
     * Set tipoUniversidad
     *
     * @param string $tipoUniversidad
     * @return UnivTipoUniversidad
     */
    public function setTipoUniversidad($tipoUniversidad)
    {
        $this->tipoUniversidad = $tipoUniversidad;
    
        return $this;
    }

    /**
     * Get tipoUniversidad
     *
     * @return string 
     */
    public function getTipoUniversidad()
    {
        return $this->tipoUniversidad;
    }
}
