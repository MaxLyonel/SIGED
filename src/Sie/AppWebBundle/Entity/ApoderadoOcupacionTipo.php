<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApoderadoOcupacionTipo
 */
class ApoderadoOcupacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $ocupacion;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var boolean
     */
    private $esOcupacion;


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
     * Set ocupacion
     *
     * @param string $ocupacion
     * @return ApoderadoOcupacionTipo
     */
    public function setOcupacion($ocupacion)
    {
        $this->ocupacion = $ocupacion;
    
        return $this;
    }

    /**
     * Get ocupacion
     *
     * @return string 
     */
    public function getOcupacion()
    {
        return $this->ocupacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return ApoderadoOcupacionTipo
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

    /**
     * Set esOcupacion
     *
     * @param boolean $esOcupacion
     * @return ApoderadoOcupacionTipo
     */
    public function setEsOcupacion($esOcupacion)
    {
        $this->esOcupacion = $esOcupacion;
    
        return $this;
    }

    /**
     * Get esOcupacion
     *
     * @return boolean 
     */
    public function getEsOcupacion()
    {
        return $this->esOcupacion;
    }
    /**
     * @var boolean
     */
    private $esVigente;


    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return ApoderadoOcupacionTipo
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
