<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeEstrategiaAtencionIntegral
 */
class RudeEstrategiaAtencionIntegral
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
     * @var \Sie\AppWebBundle\Entity\EstrategiaAtencionIntegralTipo
     */
    private $estrategiaAtencionIntegralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;


    /**
     * Set id
     *
     * @param integer $id
     * @return RudeEstrategiaAtencionIntegral
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }

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
     * @return RudeEstrategiaAtencionIntegral
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
     * @return RudeEstrategiaAtencionIntegral
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
     * Set estrategiaAtencionIntegralTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstrategiaAtencionIntegralTipo $estrategiaAtencionIntegralTipo
     * @return RudeEstrategiaAtencionIntegral
     */
    public function setEstrategiaAtencionIntegralTipo(\Sie\AppWebBundle\Entity\EstrategiaAtencionIntegralTipo $estrategiaAtencionIntegralTipo = null)
    {
        $this->estrategiaAtencionIntegralTipo = $estrategiaAtencionIntegralTipo;
    
        return $this;
    }

    /**
     * Get estrategiaAtencionIntegralTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstrategiaAtencionIntegralTipo 
     */
    public function getEstrategiaAtencionIntegralTipo()
    {
        return $this->estrategiaAtencionIntegralTipo;
    }

    /**
     * Set rude
     *
     * @param \Sie\AppWebBundle\Entity\Rude $rude
     * @return RudeEstrategiaAtencionIntegral
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
