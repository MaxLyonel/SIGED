<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperiorInstitucioneducativaAcreditacion
 */
class SuperiorInstitucioneducativaAcreditacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var integer
     */
    private $pensumNumero;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var integer
     */
    private $horasEspecialidad;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorAcreditacionEspecialidad
     */
    private $acreditacionEspecialidad;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal
     */
    private $institucioneducativaSucursal;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorTurnoTipo
     */
    private $superiorTurnoTipo;


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
     * Set obs
     *
     * @param string $obs
     * @return SuperiorInstitucioneducativaAcreditacion
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }

    /**
     * Set pensumNumero
     *
     * @param integer $pensumNumero
     * @return SuperiorInstitucioneducativaAcreditacion
     */
    public function setPensumNumero($pensumNumero)
    {
        $this->pensumNumero = $pensumNumero;
    
        return $this;
    }

    /**
     * Get pensumNumero
     *
     * @return integer 
     */
    public function getPensumNumero()
    {
        return $this->pensumNumero;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return SuperiorInstitucioneducativaAcreditacion
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    
        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime 
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     * @return SuperiorInstitucioneducativaAcreditacion
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    
        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime 
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return SuperiorInstitucioneducativaAcreditacion
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }

    /**
     * Set horasEspecialidad
     *
     * @param integer $horasEspecialidad
     * @return SuperiorInstitucioneducativaAcreditacion
     */
    public function setHorasEspecialidad($horasEspecialidad)
    {
        $this->horasEspecialidad = $horasEspecialidad;
    
        return $this;
    }

    /**
     * Get horasEspecialidad
     *
     * @return integer 
     */
    public function getHorasEspecialidad()
    {
        return $this->horasEspecialidad;
    }

    /**
     * Set acreditacionEspecialidad
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorAcreditacionEspecialidad $acreditacionEspecialidad
     * @return SuperiorInstitucioneducativaAcreditacion
     */
    public function setAcreditacionEspecialidad(\Sie\AppWebBundle\Entity\SuperiorAcreditacionEspecialidad $acreditacionEspecialidad = null)
    {
        $this->acreditacionEspecialidad = $acreditacionEspecialidad;
    
        return $this;
    }

    /**
     * Get acreditacionEspecialidad
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorAcreditacionEspecialidad 
     */
    public function getAcreditacionEspecialidad()
    {
        return $this->acreditacionEspecialidad;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return SuperiorInstitucioneducativaAcreditacion
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return SuperiorInstitucioneducativaAcreditacion
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
     * Set institucioneducativaSucursal
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal
     * @return SuperiorInstitucioneducativaAcreditacion
     */
    public function setInstitucioneducativaSucursal(\Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal = null)
    {
        $this->institucioneducativaSucursal = $institucioneducativaSucursal;
    
        return $this;
    }

    /**
     * Get institucioneducativaSucursal
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal 
     */
    public function getInstitucioneducativaSucursal()
    {
        return $this->institucioneducativaSucursal;
    }

    /**
     * Set superiorTurnoTipo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorTurnoTipo $superiorTurnoTipo
     * @return SuperiorInstitucioneducativaAcreditacion
     */
    public function setSuperiorTurnoTipo(\Sie\AppWebBundle\Entity\SuperiorTurnoTipo $superiorTurnoTipo = null)
    {
        $this->superiorTurnoTipo = $superiorTurnoTipo;
    
        return $this;
    }

    /**
     * Get superiorTurnoTipo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorTurnoTipo 
     */
    public function getSuperiorTurnoTipo()
    {
        return $this->superiorTurnoTipo;
    }
}
