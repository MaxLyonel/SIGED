<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ImagenTipo
 */
class ImagenTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $imagen;

    /**
     * @var string
     */
    private $descAbreviada;


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
     * Set imagen
     *
     * @param string $imagen
     * @return ImagenTipo
     */
    public function setImagen($imagen)
    {
        $this->imagen = $imagen;
    
        return $this;
    }

    /**
     * Get imagen
     *
     * @return string 
     */
    public function getImagen()
    {
        return $this->imagen;
    }

    /**
     * Set descAbreviada
     *
     * @param string $descAbreviada
     * @return ImagenTipo
     */
    public function setDescAbreviada($descAbreviada)
    {
        $this->descAbreviada = $descAbreviada;
    
        return $this;
    }

    /**
     * Get descAbreviada
     *
     * @return string 
     */
    public function getDescAbreviada()
    {
        return $this->descAbreviada;
    }
}
