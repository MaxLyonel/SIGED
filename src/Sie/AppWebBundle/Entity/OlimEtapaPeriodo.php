<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimEtapaPeriodo
 */
class OlimEtapaPeriodo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimEtapaTipo
     */
    private $olimEtapaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimRegistroOlimpiada
     */
    private $olimRegistroOlimpiada;


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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return OlimEtapaPeriodo
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    
        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime 
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     * @return OlimEtapaPeriodo
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    
        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime 
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set olimEtapaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimEtapaTipo $olimEtapaTipo
     * @return OlimEtapaPeriodo
     */
    public function setOlimEtapaTipo(\Sie\AppWebBundle\Entity\OlimEtapaTipo $olimEtapaTipo = null)
    {
        $this->olimEtapaTipo = $olimEtapaTipo;
    
        return $this;
    }

    /**
     * Get olimEtapaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimEtapaTipo 
     */
    public function getOlimEtapaTipo()
    {
        return $this->olimEtapaTipo;
    }

    /**
     * Set olimRegistroOlimpiada
     *
     * @param \Sie\AppWebBundle\Entity\OlimRegistroOlimpiada $olimRegistroOlimpiada
     * @return OlimEtapaPeriodo
     */
    public function setOlimRegistroOlimpiada(\Sie\AppWebBundle\Entity\OlimRegistroOlimpiada $olimRegistroOlimpiada = null)
    {
        $this->olimRegistroOlimpiada = $olimRegistroOlimpiada;
    
        return $this;
    }

    /**
     * Get olimRegistroOlimpiada
     *
     * @return \Sie\AppWebBundle\Entity\OlimRegistroOlimpiada 
     */
    public function getOlimRegistroOlimpiada()
    {
        return $this->olimRegistroOlimpiada;
    }
}
