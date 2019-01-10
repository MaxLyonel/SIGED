<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DiscapacidadTipo
 */
class DiscapacidadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $origendiscapacidad;


    public function __toString(){
        return $this->origendiscapacidad;
    }
    
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
     * Set origendiscapacidad
     *
     * @param string $origendiscapacidad
     * @return DiscapacidadTipo
     */
    public function setOrigendiscapacidad($origendiscapacidad)
    {
        $this->origendiscapacidad = $origendiscapacidad;

        return $this;
    }

    /**
     * Get origendiscapacidad
     *
     * @return string 
     */
    public function getOrigendiscapacidad()
    {
        return $this->origendiscapacidad;
    }
}
