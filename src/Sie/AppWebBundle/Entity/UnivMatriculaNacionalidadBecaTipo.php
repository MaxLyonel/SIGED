<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivMatriculaNacionalidadBecaTipo
 */
class UnivMatriculaNacionalidadBecaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nacionalidadBeca;


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
     * Set nacionalidadBeca
     *
     * @param string $nacionalidadBeca
     * @return UnivMatriculaNacionalidadBecaTipo
     */
    public function setNacionalidadBeca($nacionalidadBeca)
    {
        $this->nacionalidadBeca = $nacionalidadBeca;
    
        return $this;
    }

    /**
     * Get nacionalidadBeca
     *
     * @return string 
     */
    public function getNacionalidadBeca()
    {
        return $this->nacionalidadBeca;
    }
}
