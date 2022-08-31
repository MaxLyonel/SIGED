<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstTecNivelTipo
 */
class EstTecNivelTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nivelTipo;


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
     * Set nivelTipo
     *
     * @param string $nivelTipo
     * @return EstTecNivelTipo
     */
    public function setNivelTipo($nivelTipo)
    {
        $this->nivelTipo = $nivelTipo;
    
        return $this;
    }

    /**
     * Get nivelTipo
     *
     * @return string 
     */
    public function getNivelTipo()
    {
        return $this->nivelTipo;
    }
}
