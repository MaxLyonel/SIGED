<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeServicioBasico
 */
class RudeServicioBasico
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
     * @var \Sie\AppWebBundle\Entity\ServicioBasicoTipo
     */
    private $servicioBasicoTipo;


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
     * @return RudeServicioBasico
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
     * @return RudeServicioBasico
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
     * @return RudeServicioBasico
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
     * Set servicioBasicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\ServicioBasicoTipo $servicioBasicoTipo
     * @return RudeServicioBasico
     */
    public function setServicioBasicoTipo(\Sie\AppWebBundle\Entity\ServicioBasicoTipo $servicioBasicoTipo = null)
    {
        $this->servicioBasicoTipo = $servicioBasicoTipo;
    
        return $this;
    }

    /**
     * Get servicioBasicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\ServicioBasicoTipo 
     */
    public function getServicioBasicoTipo()
    {
        return $this->servicioBasicoTipo;
    }
}
