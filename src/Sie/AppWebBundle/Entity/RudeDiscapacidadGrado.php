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
     * @var \Sie\AppWebBundle\Entity\DiscapacidadTipo
     */
    private $discapacidadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\AppWebBundle\Entity\GradoDiscapacidadTipo
     */
    private $gradoDiscapacidadTipo;


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
}
