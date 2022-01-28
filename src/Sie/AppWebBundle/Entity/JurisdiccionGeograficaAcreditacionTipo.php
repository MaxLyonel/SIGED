<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JurisdiccionGeograficaAcreditacionTipo
 */
class JurisdiccionGeograficaAcreditacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $jurisdiccionGeograficaAcreditacion;

    /**
     * @var string
     */
    private $obs;


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
     * Set jurisdiccionGeograficaAcreditacion
     *
     * @param string $jurisdiccionGeograficaAcreditacion
     * @return JurisdiccionGeograficaAcreditacionTipo
     */
    public function setJurisdiccionGeograficaAcreditacion($jurisdiccionGeograficaAcreditacion)
    {
        $this->jurisdiccionGeograficaAcreditacion = $jurisdiccionGeograficaAcreditacion;
    
        return $this;
    }

    /**
     * Get jurisdiccionGeograficaAcreditacion
     *
     * @return string 
     */
    public function getJurisdiccionGeograficaAcreditacion()
    {
        return $this->jurisdiccionGeograficaAcreditacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return JurisdiccionGeograficaAcreditacionTipo
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
