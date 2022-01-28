<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AccesoCanaltvTipo
 */
class AccesoCanaltvTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $canaltv;

    /**
     * @var boolean
     */
    private $esactivo;


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
     * Set canaltv
     *
     * @param string $canaltv
     * @return AccesoCanaltvTipo
     */
    public function setCanaltv($canaltv)
    {
        $this->canaltv = $canaltv;
    
        return $this;
    }

    /**
     * Get canaltv
     *
     * @return string 
     */
    public function getCanaltv()
    {
        return $this->canaltv;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return AccesoCanaltvTipo
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }
}
