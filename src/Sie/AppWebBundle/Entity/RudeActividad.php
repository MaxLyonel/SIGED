<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeActividad
 */
class RudeActividad
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
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\AppWebBundle\Entity\ActividadTipo
     */
    private $actividadTipo;


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
     * @return RudeActividad
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
     * @return RudeActividad
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
     * @return RudeActividad
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
     * Set actividadTipo
     *
     * @param \Sie\AppWebBundle\Entity\ActividadTipo $actividadTipo
     * @return RudeActividad
     */
    public function setActividadTipo(\Sie\AppWebBundle\Entity\ActividadTipo $actividadTipo = null)
    {
        $this->actividadTipo = $actividadTipo;
    
        return $this;
    }

    /**
     * Get actividadTipo
     *
     * @return \Sie\AppWebBundle\Entity\ActividadTipo 
     */
    public function getActividadTipo()
    {
        return $this->actividadTipo;
    }
    /**
     * @var string
     */
    private $actividadOtro;


    /**
     * Set actividadOtro
     *
     * @param string $actividadOtro
     * @return RudeActividad
     */
    public function setActividadOtro($actividadOtro)
    {
        $this->actividadOtro = $actividadOtro;
    
        return $this;
    }

    /**
     * Get actividadOtro
     *
     * @return string 
     */
    public function getActividadOtro()
    {
        return $this->actividadOtro;
    }
}
