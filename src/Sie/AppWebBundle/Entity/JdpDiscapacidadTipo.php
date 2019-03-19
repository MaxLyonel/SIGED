<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpDiscapacidadTipo
 */
class JdpDiscapacidadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $origendiscapacidad;

    /**
     * @var boolean
     */
    private $esVigente;


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
     * @return JdpDiscapacidadTipo
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

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return JdpDiscapacidadTipo
     */
    public function setEsVigente($esVigente)
    {
        $this->esVigente = $esVigente;
    
        return $this;
    }

    /**
     * Get esVigente
     *
     * @return boolean 
     */
    public function getEsVigente()
    {
        return $this->esVigente;
    }
}
