<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivOperativoTipo
 */
class UnivOperativoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $operativo;

    public function __toString(){
        return $this->operativo;
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
     * Set operativo
     *
     * @param string $operativo
     * @return UnivOperativoTipo
     */
    public function setOperativo($operativo)
    {
        $this->operativo = $operativo;
    
        return $this;
    }

    /**
     * Get operativo
     *
     * @return string 
     */
    public function getOperativo()
    {
        return $this->operativo;
    }
}
