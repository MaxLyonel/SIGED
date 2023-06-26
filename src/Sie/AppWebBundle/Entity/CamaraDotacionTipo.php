<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CamaraDotacionTipo
 */
class CamaraDotacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $dotacion;


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
     * Set dotacion
     *
     * @param string $dotacion
     * @return CamaraDotacionTipo
     */
    public function setDotacion($dotacion)
    {
        $this->dotacion = $dotacion;
    
        return $this;
    }

    /**
     * Get dotacion
     *
     * @return string 
     */
    public function getDotacion()
    {
        return $this->dotacion;
    }
}
