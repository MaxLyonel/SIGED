<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimClasificadorMateriaTipo
 */
class OlimClasificadorMateriaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $clasificadorMateria;

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
     * Set clasificadorMateria
     *
     * @param string $clasificadorMateria
     * @return OlimClasificadorMateriaTipo
     */
    public function setClasificadorMateria($clasificadorMateria)
    {
        $this->clasificadorMateria = $clasificadorMateria;
    
        return $this;
    }

    /**
     * Get clasificadorMateria
     *
     * @return string 
     */
    public function getClasificadorMateria()
    {
        return $this->clasificadorMateria;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return OlimClasificadorMateriaTipo
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
