<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CamaraInstitucioneducativa
 */
class CamaraInstitucioneducativa
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $tiene;

    /**
     * @var integer
     */
    private $cantidad;

    /**
     * @var integer
     */
    private $grabacion;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\CamaraGrabacionTipo
     */
    private $grabacionTipo;


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
     * Set tiene
     *
     * @param boolean $tiene
     * @return CamaraInstitucioneducativa
     */
    public function setTiene($tiene)
    {
        $this->tiene = $tiene;
    
        return $this;
    }

    /**
     * Get tiene
     *
     * @return boolean 
     */
    public function getTiene()
    {
        return $this->tiene;
    }

    /**
     * Set cantidad
     *
     * @param integer $cantidad
     * @return CamaraInstitucioneducativa
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;
    
        return $this;
    }

    /**
     * Get cantidad
     *
     * @return integer 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set grabacion
     *
     * @param integer $grabacion
     * @return CamaraInstitucioneducativa
     */
    public function setGrabacion($grabacion)
    {
        $this->grabacion = $grabacion;
    
        return $this;
    }

    /**
     * Get grabacion
     *
     * @return integer 
     */
    public function getGrabacion()
    {
        return $this->grabacion;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return CamaraInstitucioneducativa
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return CamaraInstitucioneducativa
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
     * @return CamaraInstitucioneducativa
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return CamaraInstitucioneducativa
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return CamaraInstitucioneducativa
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }

    /**
     * Set grabacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\CamaraGrabacionTipo $grabacionTipo
     * @return CamaraInstitucioneducativa
     */
    public function setGrabacionTipo(\Sie\AppWebBundle\Entity\CamaraGrabacionTipo $grabacionTipo = null)
    {
        $this->grabacionTipo = $grabacionTipo;
    
        return $this;
    }

    /**
     * Get grabacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\CamaraGrabacionTipo 
     */
    public function getGrabacionTipo()
    {
        return $this->grabacionTipo;
    }
}
