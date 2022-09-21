<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivCategoriaTipo
 */
class UnivCategoriaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $categoriauniversidad;


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
     * Set categoriauniversidad
     *
     * @param string $categoriauniversidad
     * @return UnivCategoriaTipo
     */
    public function setCategoriauniversidad($categoriauniversidad)
    {
        $this->categoriauniversidad = $categoriauniversidad;
    
        return $this;
    }

    /**
     * Get categoriauniversidad
     *
     * @return string 
     */
    public function getCategoriauniversidad()
    {
        return $this->categoriauniversidad;
    }
}
