<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
//RUDE 2024
/** 
 * RudeAtencionIndirecta
 */
class RudeAtencionIndirecta
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nivel;

    /**
     * @var string
     */
    private $grado;

    /**
     * @var string
     */
    private $institucion;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;


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
     * Set nivel
     *
     * @param string $nivel
     * @return RudeAtencionIndirecta
     */
    public function setNivel($nivel)
    {
        $this->nivel = $nivel;
    
        return $this;
    }

    /**
     * Get nivel
     *
     * @return string 
     */
    public function getNivel()
    {
        return $this->nivel;
    }

    /**
     * Set grado
     *
     * @param string $grado
     * @return RudeAtencionIndirecta
     */
    public function setGrado($grado)
    {
        $this->grado = $grado;
    
        return $this;
    }

    /**
     * Get grado
     *
     * @return string 
     */
    public function getGrado()
    {
        return $this->grado;
    }

    /**
     * Set institucion
     *
     * @param string $institucion
     * @return RudeAtencionIndirecta
     */
    public function setInstitucion($institucion)
    {
        $this->institucion = $institucion;
    
        return $this;
    }

    /**
     * Get institucion
     *
     * @return string 
     */
    public function getInstitucion()
    {
        return $this->institucion;
    }

    /**
     * Set rude
     *
     * @param \Sie\AppWebBundle\Entity\Rude $rude
     * @return RudeAtencionIndirecta
     */
    public function setRude(\Sie\AppWebBundle\Entity\Rude $rude = null)
    {
        $this->rude = $rude;
    
        return $this;
    }

    /**
     * Get rude
     *
     * @return \Sie\AppWebBundle\Entity\Rude 
     */
    public function getRude()
    {
        return $this->rude;
    }
}
