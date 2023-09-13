<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeObservacionExtranjero
 */
class RudeObservacionExtranjero
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $observacionOtro;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\AppWebBundle\Entity\ObservacionExtranjeroTipo
     */
    private $observacionExtranjeroTipo;


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
     * Set observacionOtro
     *
     * @param string $observacionOtro
     * @return RudeObservacionExtranjero
     */
    public function setObservacionOtro($observacionOtro)
    {
        $this->observacionOtro = $observacionOtro;
    
        return $this;
    }

    /**
     * Get observacionOtro
     *
     * @return string 
     */
    public function getObservacionOtro()
    {
        return $this->observacionOtro;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RudeObservacionExtranjero
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
     * @return RudeObservacionExtranjero
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
     * Set rude
     *
     * @param \Sie\AppWebBundle\Entity\Rude $rude
     * @return RudeObservacionExtranjero
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
     * Set observacionExtranjeroTipo
     *
     * @param \Sie\AppWebBundle\Entity\ObservacionExtranjeroTipo $observacionExtranjeroTipo
     * @return RudeObservacionExtranjero
     */
    public function setObservacionExtranjeroTipo(\Sie\AppWebBundle\Entity\ObservacionExtranjeroTipo $observacionExtranjeroTipo = null)
    {
        $this->observacionExtranjeroTipo = $observacionExtranjeroTipo;
    
        return $this;
    }

    /**
     * Get observacionExtranjeroTipo
     *
     * @return \Sie\AppWebBundle\Entity\ObservacionExtranjeroTipo 
     */
    public function getObservacionExtranjeroTipo()
    {
        return $this->observacionExtranjeroTipo;
    }
}
