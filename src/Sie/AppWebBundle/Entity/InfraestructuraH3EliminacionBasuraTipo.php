<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH3EliminacionBasuraTipo
 */
class InfraestructuraH3EliminacionBasuraTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $infraestructuraEliminacionBasura;

    /**
     * @var string
     */
    private $obs;

    public function __toString(){
        return $this->infraestructuraEliminacionBasura;
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
     * Set infraestructuraEliminacionBasura
     *
     * @param string $infraestructuraEliminacionBasura
     * @return InfraestructuraH3EliminacionBasuraTipo
     */
    public function setInfraestructuraEliminacionBasura($infraestructuraEliminacionBasura)
    {
        $this->infraestructuraEliminacionBasura = $infraestructuraEliminacionBasura;
    
        return $this;
    }

    /**
     * Get infraestructuraEliminacionBasura
     *
     * @return string 
     */
    public function getInfraestructuraEliminacionBasura()
    {
        return $this->infraestructuraEliminacionBasura;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH3EliminacionBasuraTipo
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }
}
