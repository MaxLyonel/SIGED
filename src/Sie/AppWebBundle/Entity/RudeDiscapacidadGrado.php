<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeDiscapacidadGrado
 */
class RudeDiscapacidadGrado
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var integer
     */
    private $porcentaje;

    /**
     * @var string
     */
    private $gradoOtro;

    /**
     * @var \Sie\AppWebBundle\Entity\DiscapacidadTipo
     */
    private $discapacidadOtroGrado;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\AppWebBundle\Entity\GradoDiscapacidadTipo
     */
    private $gradoDiscapacidadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\DiscapacidadTipo
     */
    private $discapacidadTipo;


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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RudeDiscapacidadGrado
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
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return RudeDiscapacidadGrado
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set porcentaje
     *
     * @param integer $porcentaje
     * @return RudeDiscapacidadGrado
     */
    public function setPorcentaje($porcentaje)
    {
        $this->porcentaje = $porcentaje;
    
        return $this;
    }

    /**
     * Get porcentaje
     *
     * @return integer 
     */
    public function getPorcentaje()
    {
        return $this->porcentaje;
    }

    /**
     * Set gradoOtro
     *
     * @param string $gradoOtro
     * @return RudeDiscapacidadGrado
     */
    public function setGradoOtro($gradoOtro)
    {
        $this->gradoOtro = $gradoOtro;
    
        return $this;
    }

    /**
     * Get gradoOtro
     *
     * @return string 
     */
    public function getGradoOtro()
    {
        return $this->gradoOtro;
    }

    /**
     * Set discapacidadOtroGrado
     *
     * @param \Sie\AppWebBundle\Entity\DiscapacidadTipo $discapacidadOtroGrado
     * @return RudeDiscapacidadGrado
     */
    public function setDiscapacidadOtroGrado(\Sie\AppWebBundle\Entity\DiscapacidadTipo $discapacidadOtroGrado = null)
    {
        $this->discapacidadOtroGrado = $discapacidadOtroGrado;
    
        return $this;
    }

    /**
     * Get discapacidadOtroGrado
     *
     * @return \Sie\AppWebBundle\Entity\DiscapacidadTipo 
     */
    public function getDiscapacidadOtroGrado()
    {
        return $this->discapacidadOtroGrado;
    }

    /**
     * Set rude
     *
     * @param \Sie\AppWebBundle\Entity\Rude $rude
     * @return RudeDiscapacidadGrado
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
     * Set gradoDiscapacidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\GradoDiscapacidadTipo $gradoDiscapacidadTipo
     * @return RudeDiscapacidadGrado
     */
    public function setGradoDiscapacidadTipo(\Sie\AppWebBundle\Entity\GradoDiscapacidadTipo $gradoDiscapacidadTipo = null)
    {
        $this->gradoDiscapacidadTipo = $gradoDiscapacidadTipo;
    
        return $this;
    }

    /**
     * Get gradoDiscapacidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\GradoDiscapacidadTipo 
     */
    public function getGradoDiscapacidadTipo()
    {
        return $this->gradoDiscapacidadTipo;
    }

    /**
     * Set discapacidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\DiscapacidadTipo $discapacidadTipo
     * @return RudeDiscapacidadGrado
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
}
