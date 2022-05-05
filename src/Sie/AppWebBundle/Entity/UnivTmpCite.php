<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivTmpCite
 */
class UnivTmpCite
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $numero;

    /**
     * @var string
     */
    private $gestion;

    /**
     * @var string
     */
    private $estado;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set numero
     *
     * @param string $numero
     * @return UnivTmpCite
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    
        return $this;
    }

    /**
     * Get numero
     *
     * @return string 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set gestion
     *
     * @param string $gestion
     * @return UnivTmpCite
     */
    public function setGestion($gestion)
    {
        $this->gestion = $gestion;
    
        return $this;
    }

    /**
     * Get gestion
     *
     * @return string 
     */
    public function getGestion()
    {
        return $this->gestion;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return UnivTmpCite
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return string 
     */
    public function getEstado()
    {
        return $this->estado;
    }
}
