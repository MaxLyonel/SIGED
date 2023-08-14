<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EliminaRegistroPlanillaTipo
 */
class EliminaRegistroPlanillaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $razonElimina;


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
     * Set razonElimina
     *
     * @param string $razonElimina
     * @return EliminaRegistroPlanillaTipo
     */
    public function setRazonElimina($razonElimina)
    {
        $this->razonElimina = $razonElimina;
    
        return $this;
    }

    /**
     * Get razonElimina
     *
     * @return string 
     */
    public function getRazonElimina()
    {
        return $this->razonElimina;
    }
}
