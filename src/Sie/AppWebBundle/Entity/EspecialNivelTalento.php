<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialNivelTalento
 */
class EspecialNivelTalento
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nivelTalento;

    /**
     * @var boolean
     */
    private $vigente;

    /**
     * @var string
     */
    private $sigla;


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
     * Set nivelTalento
     *
     * @param string $nivelTalento
     * @return EspecialNivelTalento
     */
    public function setNivelTalento($nivelTalento)
    {
        $this->nivelTalento = $nivelTalento;
    
        return $this;
    }

    /**
     * Get nivelTalento
     *
     * @return string 
     */
    public function getNivelTalento()
    {
        return $this->nivelTalento;
    }

    /**
     * Set vigente
     *
     * @param boolean $vigente
     * @return EspecialNivelTalento
     */
    public function setVigente($vigente)
    {
        $this->vigente = $vigente;
    
        return $this;
    }

    /**
     * Get vigente
     *
     * @return boolean 
     */
    public function getVigente()
    {
        return $this->vigente;
    }

    /**
     * Set sigla
     *
     * @param string $sigla
     * @return EspecialNivelTalento
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
}
