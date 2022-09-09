<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivEstadomatriculaTipo
 */
class UnivEstadomatriculaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $estadomatricula;


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
     * Set estadomatricula
     *
     * @param string $estadomatricula
     * @return UnivEstadomatriculaTipo
     */
    public function setEstadomatricula($estadomatricula)
    {
        $this->estadomatricula = $estadomatricula;
    
        return $this;
    }

    /**
     * Get estadomatricula
     *
     * @return string 
     */
    public function getEstadomatricula()
    {
        return $this->estadomatricula;
    }
}
