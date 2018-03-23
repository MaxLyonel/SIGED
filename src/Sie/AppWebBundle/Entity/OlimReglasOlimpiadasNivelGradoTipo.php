<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimReglasOlimpiadasNivelGradoTipo
 */
class OlimReglasOlimpiadasNivelGradoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \Sie\AppWebBundle\Entity\GradoTipo
     */
    private $gradoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\NivelTipo
     */
    private $nivelTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo
     */
    private $olimReglasOlimpiadasTipo;


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
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return OlimReglasOlimpiadasNivelGradoTipo
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

    /**
     * Set gradoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GradoTipo $gradoTipo
     * @return OlimReglasOlimpiadasNivelGradoTipo
     */
    public function setGradoTipo(\Sie\AppWebBundle\Entity\GradoTipo $gradoTipo = null)
    {
        $this->gradoTipo = $gradoTipo;
    
        return $this;
    }

    /**
     * Get gradoTipo
     *
     * @return \Sie\AppWebBundle\Entity\GradoTipo 
     */
    public function getGradoTipo()
    {
        return $this->gradoTipo;
    }

    /**
     * Set nivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\NivelTipo $nivelTipo
     * @return OlimReglasOlimpiadasNivelGradoTipo
     */
    public function setNivelTipo(\Sie\AppWebBundle\Entity\NivelTipo $nivelTipo = null)
    {
        $this->nivelTipo = $nivelTipo;
    
        return $this;
    }

    /**
     * Get nivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\NivelTipo 
     */
    public function getNivelTipo()
    {
        return $this->nivelTipo;
    }

    /**
     * Set olimReglasOlimpiadasTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo $olimReglasOlimpiadasTipo
     * @return OlimReglasOlimpiadasNivelGradoTipo
     */
    public function setOlimReglasOlimpiadasTipo(\Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo $olimReglasOlimpiadasTipo = null)
    {
        $this->olimReglasOlimpiadasTipo = $olimReglasOlimpiadasTipo;
    
        return $this;
    }

    /**
     * Get olimReglasOlimpiadasTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo 
     */
    public function getOlimReglasOlimpiadasTipo()
    {
        return $this->olimReglasOlimpiadasTipo;
    }
}
