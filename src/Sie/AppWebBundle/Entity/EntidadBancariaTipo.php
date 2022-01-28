<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntidadBancariaTipo
 */
class EntidadBancariaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $entidad;

    /**
     * @var string
     */
    private $sigla;

    /**
     * @var boolean
     */
    private $esVigente;


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
     * Set entidad
     *
     * @param string $entidad
     * @return EntidadBancariaTipo
     */
    public function setEntidad($entidad)
    {
        $this->entidad = $entidad;
    
        return $this;
    }

    /**
     * Get entidad
     *
     * @return string 
     */
    public function getEntidad()
    {
        return $this->entidad;
    }

    /**
     * Set sigla
     *
     * @param string $sigla
     * @return EntidadBancariaTipo
     */
    public function setSigla($sigla)
    {
        $this->sigla = $sigla;
    
        return $this;
    }

    /**
     * Get sigla
     *
     * @return string 
     */
    public function getSigla()
    {
        return $this->sigla;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return EntidadBancariaTipo
     */
    public function setEsVigente($esVigente)
    {
        $this->esVigente = $esVigente;
    
        return $this;
    }

    /**
     * Get esVigente
     *
     * @return boolean 
     */
    public function getEsVigente()
    {
        return $this->esVigente;
    }
}
