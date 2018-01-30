<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MaestroInscripcion
 */
class MaestroInscripcion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $rdaPlanillasId;

    /**
     * @var string
     */
    private $ref;

    /**
     * @var boolean
     */
    private $normalista;

    /**
     * @var integer
     */
    private $idiomaMaternoId;

    /**
     * @var boolean
     */
    private $leeescribebraile;

    /**
     * @var string
     */
    private $formaciondescripcion;

    /**
     * @var integer
     */
    private $lugarTipo;

    /**
     * @var integer
     */
    private $itemDirector;

    /**
     * @var integer
     */
    private $horas;

    /**
     * @var string
     */
    private $item;

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
    private $esVigenteAdministrativo;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal
     */
    private $institucioneducativaSucursal;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaMaterno
     */
    private $estudiaiomaMaterno;

    /**
     * @var \Sie\AppWebBundle\Entity\CargoTipo
     */
    private $cargoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EspecialidadMaestroTipo
     */
    private $especialidadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\FinanciamientoTipo
     */
    private $financiamientoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\FormacionTipo
     */
    private $formacionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadomaestroTipo
     */
    private $estadomaestro;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\PeriodoTipo
     */
    private $periodoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\RolTipo
     */
    private $rolTipo;


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
     * Set rdaPlanillasId
     *
     * @param string $rdaPlanillasId
     * @return MaestroInscripcion
     */
    public function setRdaPlanillasId($rdaPlanillasId)
    {
        $this->rdaPlanillasId = $rdaPlanillasId;
    
        return $this;
    }

    /**
     * Get rdaPlanillasId
     *
     * @return string 
     */
    public function getRdaPlanillasId()
    {
        return $this->rdaPlanillasId;
    }

    /**
     * Set ref
     *
     * @param string $ref
     * @return MaestroInscripcion
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    
        return $this;
    }

    /**
     * Get ref
     *
     * @return string 
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set normalista
     *
     * @param boolean $normalista
     * @return MaestroInscripcion
     */
    public function setNormalista($normalista)
    {
        $this->normalista = $normalista;
    
        return $this;
    }

    /**
     * Get normalista
     *
     * @return boolean 
     */
    public function getNormalista()
    {
        return $this->normalista;
    }

    /**
     * Set idiomaMaternoId
     *
     * @param integer $idiomaMaternoId
     * @return MaestroInscripcion
     */
    public function setIdiomaMaternoId($idiomaMaternoId)
    {
        $this->idiomaMaternoId = $idiomaMaternoId;
    
        return $this;
    }

    /**
     * Get idiomaMaternoId
     *
     * @return integer 
     */
    public function getIdiomaMaternoId()
    {
        return $this->idiomaMaternoId;
    }

    /**
     * Set leeescribebraile
     *
     * @param boolean $leeescribebraile
     * @return MaestroInscripcion
     */
    public function setLeeescribebraile($leeescribebraile)
    {
        $this->leeescribebraile = $leeescribebraile;
    
        return $this;
    }

    /**
     * Get leeescribebraile
     *
     * @return boolean 
     */
    public function getLeeescribebraile()
    {
        return $this->leeescribebraile;
    }

    /**
     * Set formaciondescripcion
     *
     * @param string $formaciondescripcion
     * @return MaestroInscripcion
     */
    public function setFormaciondescripcion($formaciondescripcion)
    {
        $this->formaciondescripcion = $formaciondescripcion;
    
        return $this;
    }

    /**
     * Get formaciondescripcion
     *
     * @return string 
     */
    public function getFormaciondescripcion()
    {
        return $this->formaciondescripcion;
    }

    /**
     * Set lugarTipo
     *
     * @param integer $lugarTipo
     * @return MaestroInscripcion
     */
    public function setLugarTipo($lugarTipo)
    {
        $this->lugarTipo = $lugarTipo;
    
        return $this;
    }

    /**
     * Get lugarTipo
     *
     * @return integer 
     */
    public function getLugarTipo()
    {
        return $this->lugarTipo;
    }

    /**
     * Set itemDirector
     *
     * @param integer $itemDirector
     * @return MaestroInscripcion
     */
    public function setItemDirector($itemDirector)
    {
        $this->itemDirector = $itemDirector;
    
        return $this;
    }

    /**
     * Get itemDirector
     *
     * @return integer 
     */
    public function getItemDirector()
    {
        return $this->itemDirector;
    }

    /**
     * Set horas
     *
     * @param integer $horas
     * @return MaestroInscripcion
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
     * Set item
     *
     * @param string $item
     * @return MaestroInscripcion
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return MaestroInscripcion
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
     * @return MaestroInscripcion
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
     * Set esVigenteAdministrativo
     *
     * @param boolean $esVigenteAdministrativo
     * @return MaestroInscripcion
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
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return MaestroInscripcion
     */
    public function setPersona(\Sie\AppWebBundle\Entity\Persona $persona = null)
    {
        $this->persona = $persona;
    
        return $this;
    }

    /**
     * Get persona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getPersona()
    {
        return $this->persona;
    }

    /**
     * Set institucioneducativaSucursal
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal
     * @return MaestroInscripcion
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
     * Set estudiaiomaMaterno
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaMaterno $estudiaiomaMaterno
     * @return MaestroInscripcion
     */
    public function setEstudiaiomaMaterno(\Sie\AppWebBundle\Entity\IdiomaMaterno $estudiaiomaMaterno = null)
    {
        $this->estudiaiomaMaterno = $estudiaiomaMaterno;
    
        return $this;
    }

    /**
     * Get estudiaiomaMaterno
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaMaterno 
     */
    public function getEstudiaiomaMaterno()
    {
        return $this->estudiaiomaMaterno;
    }

    /**
     * Set cargoTipo
     *
     * @param \Sie\AppWebBundle\Entity\CargoTipo $cargoTipo
     * @return MaestroInscripcion
     */
    public function setCargoTipo(\Sie\AppWebBundle\Entity\CargoTipo $cargoTipo = null)
    {
        $this->cargoTipo = $cargoTipo;
    
        return $this;
    }

    /**
     * Get cargoTipo
     *
     * @return \Sie\AppWebBundle\Entity\CargoTipo 
     */
    public function getCargoTipo()
    {
        return $this->cargoTipo;
    }

    /**
     * Set especialidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\EspecialidadMaestroTipo $especialidadTipo
     * @return MaestroInscripcion
     */
    public function setEspecialidadTipo(\Sie\AppWebBundle\Entity\EspecialidadMaestroTipo $especialidadTipo = null)
    {
        $this->especialidadTipo = $especialidadTipo;
    
        return $this;
    }

    /**
     * Get especialidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\EspecialidadMaestroTipo 
     */
    public function getEspecialidadTipo()
    {
        return $this->especialidadTipo;
    }

    /**
     * Set financiamientoTipo
     *
     * @param \Sie\AppWebBundle\Entity\FinanciamientoTipo $financiamientoTipo
     * @return MaestroInscripcion
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

    /**
     * Set formacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\FormacionTipo $formacionTipo
     * @return MaestroInscripcion
     */
    public function setFormacionTipo(\Sie\AppWebBundle\Entity\FormacionTipo $formacionTipo = null)
    {
        $this->formacionTipo = $formacionTipo;
    
        return $this;
    }

    /**
     * Get formacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\FormacionTipo 
     */
    public function getFormacionTipo()
    {
        return $this->formacionTipo;
    }

    /**
     * Set estadomaestro
     *
     * @param \Sie\AppWebBundle\Entity\EstadomaestroTipo $estadomaestro
     * @return MaestroInscripcion
     */
    public function setEstadomaestro(\Sie\AppWebBundle\Entity\EstadomaestroTipo $estadomaestro = null)
    {
        $this->estadomaestro = $estadomaestro;
    
        return $this;
    }

    /**
     * Get estadomaestro
     *
     * @return \Sie\AppWebBundle\Entity\EstadomaestroTipo 
     */
    public function getEstadomaestro()
    {
        return $this->estadomaestro;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return MaestroInscripcion
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
     * @return MaestroInscripcion
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
     * Set periodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\PeriodoTipo $periodoTipo
     * @return MaestroInscripcion
     */
    public function setPeriodoTipo(\Sie\AppWebBundle\Entity\PeriodoTipo $periodoTipo = null)
    {
        $this->periodoTipo = $periodoTipo;
    
        return $this;
    }

    /**
     * Get periodoTipo
     *
     * @return \Sie\AppWebBundle\Entity\PeriodoTipo 
     */
    public function getPeriodoTipo()
    {
        return $this->periodoTipo;
    }

    /**
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return MaestroInscripcion
     */
    public function setRolTipo(\Sie\AppWebBundle\Entity\RolTipo $rolTipo = null)
    {
        $this->rolTipo = $rolTipo;
    
        return $this;
    }

    /**
     * Get rolTipo
     *
     * @return \Sie\AppWebBundle\Entity\RolTipo 
     */
    public function getRolTipo()
    {
        return $this->rolTipo;
    }
}
