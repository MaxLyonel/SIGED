<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FuerzaMilitarTipo
 */
class FuerzaMilitarTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fuerzaMilitar;


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
     * Set fuerzaMilitar
     *
     * @param string $fuerzaMilitar
     * @return FuerzaMilitarTipo
     */
    public function setFuerzaMilitar($fuerzaMilitar)
    {
        $this->fuerzaMilitar = $fuerzaMilitar;
    
        return $this;
    }

    /**
     * Get fuerzaMilitar
     *
     * @return string 
     */
    public function getFuerzaMilitar()
    {
        return $this->fuerzaMilitar;
    }
}
