<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeAbandono
 */
class RudeAbandono
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $motivoAbandono;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $abandonoOtro;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\AppWebBundle\Entity\AbandonoTipo
     */
    private $abandonoTipo;


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
     * Set motivoAbandono
     *
     * @param string $motivoAbandono
     * @return RudeAbandono
     */
    public function setMotivoAbandono($motivoAbandono)
    {
        $this->motivoAbandono = $motivoAbandono;
    
        return $this;
    }

    /**
     * Get motivoAbandono
     *
     * @return string 
     */
    public function getMotivoAbandono()
    {
        return $this->motivoAbandono;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RudeAbandono
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
     * @return RudeAbandono
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
     * Set abandonoOtro
     *
     * @param string $abandonoOtro
     * @return RudeAbandono
     */
    public function setAbandonoOtro($abandonoOtro)
    {
        $this->abandonoOtro = $abandonoOtro;
    
        return $this;
    }

    /**
     * Get abandonoOtro
     *
     * @return string 
     */
    public function getAbandonoOtro()
    {
        return $this->abandonoOtro;
    }

    /**
     * Set rude
     *
     * @param \Sie\AppWebBundle\Entity\Rude $rude
     * @return RudeAbandono
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
     * Set abandonoTipo
     *
     * @param \Sie\AppWebBundle\Entity\AbandonoTipo $abandonoTipo
     * @return RudeAbandono
     */
    public function setAbandonoTipo(\Sie\AppWebBundle\Entity\AbandonoTipo $abandonoTipo = null)
    {
        $this->abandonoTipo = $abandonoTipo;
    
        return $this;
    }

    /**
     * Get abandonoTipo
     *
     * @return \Sie\AppWebBundle\Entity\AbandonoTipo 
     */
    public function getAbandonoTipo()
    {
        return $this->abandonoTipo;
    }
}
