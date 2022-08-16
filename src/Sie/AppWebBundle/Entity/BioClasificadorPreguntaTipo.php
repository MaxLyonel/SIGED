<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BioClasificadorPreguntaTipo
 */
class BioClasificadorPreguntaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $clasificadorPregunta;

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
     * Set clasificadorPregunta
     *
     * @param string $clasificadorPregunta
     * @return BioClasificadorPreguntaTipo
     */
    public function setClasificadorPregunta($clasificadorPregunta)
    {
        $this->clasificadorPregunta = $clasificadorPregunta;
    
        return $this;
    }

    /**
     * Get clasificadorPregunta
     *
     * @return string 
     */
    public function getClasificadorPregunta()
    {
        return $this->clasificadorPregunta;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return BioClasificadorPreguntaTipo
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
