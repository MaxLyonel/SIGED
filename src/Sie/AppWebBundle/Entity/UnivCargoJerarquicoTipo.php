<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivCargoJerarquicoTipo
 */
class UnivCargoJerarquicoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $cargo;


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
     * Set cargo
     *
     * @param string $cargo
     * @return UnivCargoJerarquicoTipo
     */
    public function setCargo($cargo)
    {
        $this->cargo = $cargo;
    
        return $this;
    }

    /**
     * Get cargo
     *
     * @return string 
     */
    public function getCargo()
    {
        return $this->cargo;
    }
}
