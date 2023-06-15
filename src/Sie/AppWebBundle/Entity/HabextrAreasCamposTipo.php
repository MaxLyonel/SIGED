<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HabextrAreasCamposTipo
 */
class HabextrAreasCamposTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $areasCampos;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var integer
     */
    private $edad;


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
     * Set areasCampos
     *
     * @param string $areasCampos
     * @return HabextrAreasCamposTipo
     */
    public function setAreasCampos($areasCampos)
    {
        $this->areasCampos = $areasCampos;
    
        return $this;
    }

    /**
     * Get areasCampos
     *
     * @return string 
     */
    public function getAreasCampos()
    {
        return $this->areasCampos;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return HabextrAreasCamposTipo
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set edad
     *
     * @param integer $edad
     * @return HabextrAreasCamposTipo
     */
    public function setEdad($edad)
    {
        $this->edad = $edad;
    
        return $this;
    }

    /**
     * Get edad
     *
     * @return integer 
     */
    public function getEdad()
    {
        return $this->edad;
    }
}
