<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialCargaHoraria
 */
class EspecialCargaHoraria
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $horas;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var integer
     */
    private $subarea;

    /**
     * @var integer
     */
    private $modalidad;

    /**
     * @var integer
     */
    private $oferta;

    /**
     * @var integer
     */
    private $notaTipoInicio;

    /**
     * @var integer
     */
    private $notaTipoFin;

    /**
     * @var boolean
     */
    private $status;


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
     * Set horas
     *
     * @param integer $horas
     * @return EspecialCargaHoraria
     */
    public function setHoras($horas)
    {
        $this->horas = $horas;
    
        return $this;
    }

    /**
     * Get horas
     *
     * @return integer 
     */
    public function getHoras()
    {
        return $this->horas;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return EspecialCargaHoraria
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }

    /**
     * Set subarea
     *
     * @param integer $subarea
     * @return EspecialCargaHoraria
     */
    public function setSubarea($subarea)
    {
        $this->subarea = $subarea;
    
        return $this;
    }

    /**
     * Get subarea
     *
     * @return integer 
     */
    public function getSubarea()
    {
        return $this->subarea;
    }

    /**
     * Set modalidad
     *
     * @param integer $modalidad
     * @return EspecialCargaHoraria
     */
    public function setModalidad($modalidad)
    {
        $this->modalidad = $modalidad;
    
        return $this;
    }

    /**
     * Get modalidad
     *
     * @return integer 
     */
    public function getModalidad()
    {
        return $this->modalidad;
    }

    /**
     * Set oferta
     *
     * @param integer $oferta
     * @return EspecialCargaHoraria
     */
    public function setOferta($oferta)
    {
        $this->oferta = $oferta;
    
        return $this;
    }

    /**
     * Get oferta
     *
     * @return integer 
     */
    public function getOferta()
    {
        return $this->oferta;
    }

    /**
     * Set notaTipoInicio
     *
     * @param integer $notaTipoInicio
     * @return EspecialCargaHoraria
     */
    public function setNotaTipoInicio($notaTipoInicio)
    {
        $this->notaTipoInicio = $notaTipoInicio;
    
        return $this;
    }

    /**
     * Get notaTipoInicio
     *
     * @return integer 
     */
    public function getNotaTipoInicio()
    {
        return $this->notaTipoInicio;
    }

    /**
     * Set notaTipoFin
     *
     * @param integer $notaTipoFin
     * @return EspecialCargaHoraria
     */
    public function setNotaTipoFin($notaTipoFin)
    {
        $this->notaTipoFin = $notaTipoFin;
    
        return $this;
    }

    /**
     * Get notaTipoFin
     *
     * @return integer 
     */
    public function getNotaTipoFin()
    {
        return $this->notaTipoFin;
    }

    /**
     * Set status
     *
     * @param boolean $status
     * @return EspecialCargaHoraria
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return boolean 
     */
    public function getStatus()
    {
        return $this->status;
    }
}
