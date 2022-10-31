<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeParienteDiscapacidad
 */
class RudeParienteDiscapacidad
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nroCarnet;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\DiscapacidadTipo
     */
    private $discapacidadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\AppWebBundle\Entity\ApoderadoTipo
     */
    private $parienteTipo;


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
     * Set nroCarnet
     *
     * @param string $nroCarnet
     * @return RudeParienteDiscapacidad
     */
    public function setNroCarnet($nroCarnet)
    {
        $this->nroCarnet = $nroCarnet;
    
        return $this;
    }

    /**
     * Get nroCarnet
     *
     * @return string 
     */
    public function getNroCarnet()
    {
        return $this->nroCarnet;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RudeParienteDiscapacidad
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set discapacidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\DiscapacidadTipo $discapacidadTipo
     * @return RudeParienteDiscapacidad
     */
    public function setDiscapacidadTipo(\Sie\AppWebBundle\Entity\DiscapacidadTipo $discapacidadTipo = null)
    {
        $this->discapacidadTipo = $discapacidadTipo;
    
        return $this;
    }

    /**
     * Get discapacidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\DiscapacidadTipo 
     */
    public function getDiscapacidadTipo()
    {
        return $this->discapacidadTipo;
    }

    /**
     * Set rude
     *
     * @param \Sie\AppWebBundle\Entity\Rude $rude
     * @return RudeParienteDiscapacidad
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

    /**
     * Set parienteTipo
     *
     * @param \Sie\AppWebBundle\Entity\ApoderadoTipo $parienteTipo
     * @return RudeParienteDiscapacidad
     */
    public function setParienteTipo(\Sie\AppWebBundle\Entity\ApoderadoTipo $parienteTipo = null)
    {
        $this->parienteTipo = $parienteTipo;
    
        return $this;
    }

    /**
     * Get parienteTipo
     *
     * @return \Sie\AppWebBundle\Entity\ApoderadoTipo 
     */
    public function getParienteTipo()
    {
        return $this->parienteTipo;
    }
}
