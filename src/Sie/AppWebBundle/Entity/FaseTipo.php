<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FaseTipo
 */
class FaseTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fase;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var boolean
     */
    private $esactivo;


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
     * Set fase
     *
     * @param string $fase
     * @return FaseTipo
     */
    public function setFase($fase)
    {
        $this->fase = $fase;

        return $this;
    }

    /**
     * Get fase
     *
     * @return string 
     */
    public function getFase()
    {
        return $this->fase;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return FaseTipo
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
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return FaseTipo
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;

        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }
    /**
     * @var boolean
     */
    private $esactivoPrimaria;

    /**
     * @var boolean
     */
    private $esactivoSecundaria;


    /**
     * Set esactivoPrimaria
     *
     * @param boolean $esactivoPrimaria
     * @return FaseTipo
     */
    public function setEsactivoPrimaria($esactivoPrimaria)
    {
        $this->esactivoPrimaria = $esactivoPrimaria;
    
        return $this;
    }

    /**
     * Get esactivoPrimaria
     *
     * @return boolean 
     */
    public function getEsactivoPrimaria()
    {
        return $this->esactivoPrimaria;
    }

    /**
     * Set esactivoSecundaria
     *
     * @param boolean $esactivoSecundaria
     * @return FaseTipo
     */
    public function setEsactivoSecundaria($esactivoSecundaria)
    {
        $this->esactivoSecundaria = $esactivoSecundaria;
    
        return $this;
    }

    /**
     * Get esactivoSecundaria
     *
     * @return boolean 
     */
    public function getEsactivoSecundaria()
    {
        return $this->esactivoSecundaria;
    }
}
