<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH3DelitosEdificioDetalle
 */
class InfraestructuraH3DelitosEdificioDetalle
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n22CantVeces;

    /**
     * @var string
     */
    private $n22ObsAcciones;

    /**
     * @var boolean
     */
    private $n22EsRoboEdificio;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH3RiesgosDelitos
     */
    private $infraestructuraH3RiesgosDelitos;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEquipamientoTipo
     */
    private $n22EquipamientoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMobiliarioTipo
     */
    private $n22MobiliarioTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenAmbientesTipo
     */
    private $n22AmbientesTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenHorarioTipo
     */
    private $n22HorarioTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $n22GestionTipo;


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
     * Set n22CantVeces
     *
     * @param integer $n22CantVeces
     * @return InfraestructuraH3DelitosEdificioDetalle
     */
    public function setN22CantVeces($n22CantVeces)
    {
        $this->n22CantVeces = $n22CantVeces;
    
        return $this;
    }

    /**
     * Get n22CantVeces
     *
     * @return integer 
     */
    public function getN22CantVeces()
    {
        return $this->n22CantVeces;
    }

    /**
     * Set n22ObsAcciones
     *
     * @param string $n22ObsAcciones
     * @return InfraestructuraH3DelitosEdificioDetalle
     */
    public function setN22ObsAcciones($n22ObsAcciones)
    {
        $this->n22ObsAcciones = $n22ObsAcciones;
    
        return $this;
    }

    /**
     * Get n22ObsAcciones
     *
     * @return string 
     */
    public function getN22ObsAcciones()
    {
        return $this->n22ObsAcciones;
    }

    /**
     * Set n22EsRoboEdificio
     *
     * @param boolean $n22EsRoboEdificio
     * @return InfraestructuraH3DelitosEdificioDetalle
     */
    public function setN22EsRoboEdificio($n22EsRoboEdificio)
    {
        $this->n22EsRoboEdificio = $n22EsRoboEdificio;
    
        return $this;
    }

    /**
     * Get n22EsRoboEdificio
     *
     * @return boolean 
     */
    public function getN22EsRoboEdificio()
    {
        return $this->n22EsRoboEdificio;
    }

    /**
     * Set infraestructuraH3RiesgosDelitos
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH3RiesgosDelitos $infraestructuraH3RiesgosDelitos
     * @return InfraestructuraH3DelitosEdificioDetalle
     */
    public function setInfraestructuraH3RiesgosDelitos(\Sie\AppWebBundle\Entity\InfraestructuraH3RiesgosDelitos $infraestructuraH3RiesgosDelitos = null)
    {
        $this->infraestructuraH3RiesgosDelitos = $infraestructuraH3RiesgosDelitos;
    
        return $this;
    }

    /**
     * Get infraestructuraH3RiesgosDelitos
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH3RiesgosDelitos 
     */
    public function getInfraestructuraH3RiesgosDelitos()
    {
        return $this->infraestructuraH3RiesgosDelitos;
    }

    /**
     * Set n22EquipamientoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEquipamientoTipo $n22EquipamientoTipo
     * @return InfraestructuraH3DelitosEdificioDetalle
     */
    public function setN22EquipamientoTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEquipamientoTipo $n22EquipamientoTipo = null)
    {
        $this->n22EquipamientoTipo = $n22EquipamientoTipo;
    
        return $this;
    }

    /**
     * Get n22EquipamientoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEquipamientoTipo 
     */
    public function getN22EquipamientoTipo()
    {
        return $this->n22EquipamientoTipo;
    }

    /**
     * Set n22MobiliarioTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMobiliarioTipo $n22MobiliarioTipo
     * @return InfraestructuraH3DelitosEdificioDetalle
     */
    public function setN22MobiliarioTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMobiliarioTipo $n22MobiliarioTipo = null)
    {
        $this->n22MobiliarioTipo = $n22MobiliarioTipo;
    
        return $this;
    }

    /**
     * Get n22MobiliarioTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMobiliarioTipo 
     */
    public function getN22MobiliarioTipo()
    {
        return $this->n22MobiliarioTipo;
    }

    /**
     * Set n22AmbientesTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenAmbientesTipo $n22AmbientesTipo
     * @return InfraestructuraH3DelitosEdificioDetalle
     */
    public function setN22AmbientesTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenAmbientesTipo $n22AmbientesTipo = null)
    {
        $this->n22AmbientesTipo = $n22AmbientesTipo;
    
        return $this;
    }

    /**
     * Get n22AmbientesTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenAmbientesTipo 
     */
    public function getN22AmbientesTipo()
    {
        return $this->n22AmbientesTipo;
    }

    /**
     * Set n22HorarioTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenHorarioTipo $n22HorarioTipo
     * @return InfraestructuraH3DelitosEdificioDetalle
     */
    public function setN22HorarioTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenHorarioTipo $n22HorarioTipo = null)
    {
        $this->n22HorarioTipo = $n22HorarioTipo;
    
        return $this;
    }

    /**
     * Get n22HorarioTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenHorarioTipo 
     */
    public function getN22HorarioTipo()
    {
        return $this->n22HorarioTipo;
    }

    /**
     * Set n22GestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $n22GestionTipo
     * @return InfraestructuraH3DelitosEdificioDetalle
     */
    public function setN22GestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $n22GestionTipo = null)
    {
        $this->n22GestionTipo = $n22GestionTipo;
    
        return $this;
    }

    /**
     * Get n22GestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getN22GestionTipo()
    {
        return $this->n22GestionTipo;
    }
}
