<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaCursoOfertaMaestro
 */
class InstitucioneducativaCursoOfertaMaestro
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $horasMes;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var boolean
     */
    private $esVigenteMaestro;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta
     */
    private $institucioneducativaCursoOferta;

    /**
     * @var \Sie\AppWebBundle\Entity\MaestroInscripcion
     */
    private $maestroInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\NotaTipo
     */
    private $notaTipo;


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
     * Set horasMes
     *
     * @param integer $horasMes
     * @return InstitucioneducativaCursoOfertaMaestro
     */
    public function setHorasMes($horasMes)
    {
        $this->horasMes = $horasMes;
    
        return $this;
    }

    /**
     * Get horasMes
     *
     * @return integer 
     */
    public function getHorasMes()
    {
        return $this->horasMes;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaCursoOfertaMaestro
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
     * @return InstitucioneducativaCursoOfertaMaestro
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
     * Set esVigenteMaestro
     *
     * @param boolean $esVigenteMaestro
     * @return InstitucioneducativaCursoOfertaMaestro
     */
    public function setEsVigenteMaestro($esVigenteMaestro)
    {
        $this->esVigenteMaestro = $esVigenteMaestro;
    
        return $this;
    }

    /**
     * Get esVigenteMaestro
     *
     * @return boolean 
     */
    public function getEsVigenteMaestro()
    {
        return $this->esVigenteMaestro;
    }

    /**
     * Set institucioneducativaCursoOferta
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta $institucioneducativaCursoOferta
     * @return InstitucioneducativaCursoOfertaMaestro
     */
    public function setInstitucioneducativaCursoOferta(\Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta $institucioneducativaCursoOferta = null)
    {
        $this->institucioneducativaCursoOferta = $institucioneducativaCursoOferta;
    
        return $this;
    }

    /**
     * Get institucioneducativaCursoOferta
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta 
     */
    public function getInstitucioneducativaCursoOferta()
    {
        return $this->institucioneducativaCursoOferta;
    }

    /**
     * Set maestroInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcion
     * @return InstitucioneducativaCursoOfertaMaestro
     */
    public function setMaestroInscripcion(\Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcion = null)
    {
        $this->maestroInscripcion = $maestroInscripcion;
    
        return $this;
    }

    /**
     * Get maestroInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\MaestroInscripcion 
     */
    public function getMaestroInscripcion()
    {
        return $this->maestroInscripcion;
    }

    /**
     * Set notaTipo
     *
     * @param \Sie\AppWebBundle\Entity\NotaTipo $notaTipo
     * @return InstitucioneducativaCursoOfertaMaestro
     */
    public function setNotaTipo(\Sie\AppWebBundle\Entity\NotaTipo $notaTipo = null)
    {
        $this->notaTipo = $notaTipo;
    
        return $this;
    }

    /**
     * Get notaTipo
     *
     * @return \Sie\AppWebBundle\Entity\NotaTipo 
     */
    public function getNotaTipo()
    {
        return $this->notaTipo;
    }
    /**
     * @var string
     */
    private $item;

    /**
     * @var integer
     */
    private $financiamientoTipoId;

    /**
     * @var boolean
     */
    private $esVigenteAdministrativo;

    /**
     * @var \DateTime
     */
    private $asignacionFechaInicio;

    /**
     * @var \DateTime
     */
    private $asignacionFechaFin;


    /**
     * Set item
     *
     * @param string $item
     * @return InstitucioneducativaCursoOfertaMaestro
     */
    public function setItem($item)
    {
        $this->item = $item;
    
        return $this;
    }

    /**
     * Get item
     *
     * @return string 
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set financiamientoTipoId
     *
     * @param integer $financiamientoTipoId
     * @return InstitucioneducativaCursoOfertaMaestro
     */
    public function setFinanciamientoTipoId($financiamientoTipoId)
    {
        $this->financiamientoTipoId = $financiamientoTipoId;
    
        return $this;
    }

    /**
     * Get financiamientoTipoId
     *
     * @return integer 
     */
    public function getFinanciamientoTipoId()
    {
        return $this->financiamientoTipoId;
    }

    /**
     * Set esVigenteAdministrativo
     *
     * @param boolean $esVigenteAdministrativo
     * @return InstitucioneducativaCursoOfertaMaestro
     */
    public function setEsVigenteAdministrativo($esVigenteAdministrativo)
    {
        $this->esVigenteAdministrativo = $esVigenteAdministrativo;
    
        return $this;
    }

    /**
     * Get esVigenteAdministrativo
     *
     * @return boolean 
     */
    public function getEsVigenteAdministrativo()
    {
        return $this->esVigenteAdministrativo;
    }

    /**
     * Set asignacionFechaInicio
     *
     * @param \DateTime $asignacionFechaInicio
     * @return InstitucioneducativaCursoOfertaMaestro
     */
    public function setAsignacionFechaInicio($asignacionFechaInicio)
    {
        $this->asignacionFechaInicio = $asignacionFechaInicio;
    
        return $this;
    }

    /**
     * Get asignacionFechaInicio
     *
     * @return \DateTime 
     */
    public function getAsignacionFechaInicio()
    {
        return $this->asignacionFechaInicio;
    }

    /**
     * Set asignacionFechaFin
     *
     * @param \DateTime $asignacionFechaFin
     * @return InstitucioneducativaCursoOfertaMaestro
     */
    public function setAsignacionFechaFin($asignacionFechaFin)
    {
        $this->asignacionFechaFin = $asignacionFechaFin;
    
        return $this;
    }

    /**
     * Get asignacionFechaFin
     *
     * @return \DateTime 
     */
    public function getAsignacionFechaFin()
    {
        return $this->asignacionFechaFin;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\FinanciamientoTipo
     */
    private $financiamientoTipo;


    /**
     * Set financiamientoTipo
     *
     * @param \Sie\AppWebBundle\Entity\FinanciamientoTipo $financiamientoTipo
     * @return InstitucioneducativaCursoOfertaMaestro
     */
    public function setFinanciamientoTipo(\Sie\AppWebBundle\Entity\FinanciamientoTipo $financiamientoTipo = null)
    {
        $this->financiamientoTipo = $financiamientoTipo;
    
        return $this;
    }

    /**
     * Get financiamientoTipo
     *
     * @return \Sie\AppWebBundle\Entity\FinanciamientoTipo 
     */
    public function getFinanciamientoTipo()
    {
        return $this->financiamientoTipo;
    }
}
