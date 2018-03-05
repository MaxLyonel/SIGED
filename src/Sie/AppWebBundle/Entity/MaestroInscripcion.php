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
     * @var integer
     */
    private $cargoTipoId;

    /**
     * @var integer
     */
    private $formacionTipoId;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var string
     */
    private $rdaPlanillasId;

    /**
     * @var integer
     */
    private $personaId;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var integer
     */
    private $especialidadTipoId;

    /**
     * @var integer
     */
    private $financiamientoTipoId;

    /**
     * @var integer
     */
    private $periodoTipoId;

    /**
     * @var string
     */
    private $ref;

    /**
     * @var integer
     */
    private $estadomaestroId;

    /**
     * @var integer
     */
    private $rolTipoId;

    /**
     * @var integer
     */
    private $institucioneducativaSucursalId;

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
     * @var \Sie\AppWebBundle\Entity\IdiomaMaterno
     */
    private $estudiaiomaMaterno;


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
     * Set cargoTipoId
     *
     * @param integer $cargoTipoId
     * @return MaestroInscripcion
     */
    public function setCargoTipoId($cargoTipoId)
    {
        $this->cargoTipoId = $cargoTipoId;
    
        return $this;
    }

    /**
     * Get cargoTipoId
     *
     * @return integer 
     */
    public function getCargoTipoId()
    {
        return $this->cargoTipoId;
    }

    /**
     * Set formacionTipoId
     *
     * @param integer $formacionTipoId
     * @return MaestroInscripcion
     */
    public function setFormacionTipoId($formacionTipoId)
    {
        $this->formacionTipoId = $formacionTipoId;
    
        return $this;
    }

    /**
     * Get formacionTipoId
     *
     * @return integer 
     */
    public function getFormacionTipoId()
    {
        return $this->formacionTipoId;
    }

    /**
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return MaestroInscripcion
     */
    public function setInstitucioneducativaId($institucioneducativaId)
    {
        $this->institucioneducativaId = $institucioneducativaId;
    
        return $this;
    }

    /**
     * Get institucioneducativaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaId()
    {
        return $this->institucioneducativaId;
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
     * Set personaId
     *
     * @param integer $personaId
     * @return MaestroInscripcion
     */
    public function setPersonaId($personaId)
    {
        $this->personaId = $personaId;
    
        return $this;
    }

    /**
     * Get personaId
     *
     * @return integer 
     */
    public function getPersonaId()
    {
        return $this->personaId;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return MaestroInscripcion
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
     * Set especialidadTipoId
     *
     * @param integer $especialidadTipoId
     * @return MaestroInscripcion
     */
    public function setEspecialidadTipoId($especialidadTipoId)
    {
        $this->especialidadTipoId = $especialidadTipoId;
    
        return $this;
    }

    /**
     * Get especialidadTipoId
     *
     * @return integer 
     */
    public function getEspecialidadTipoId()
    {
        return $this->especialidadTipoId;
    }

    /**
     * Set financiamientoTipoId
     *
     * @param integer $financiamientoTipoId
     * @return MaestroInscripcion
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
     * Set periodoTipoId
     *
     * @param integer $periodoTipoId
     * @return MaestroInscripcion
     */
    public function setPeriodoTipoId($periodoTipoId)
    {
        $this->periodoTipoId = $periodoTipoId;
    
        return $this;
    }

    /**
     * Get periodoTipoId
     *
     * @return integer 
     */
    public function getPeriodoTipoId()
    {
        return $this->periodoTipoId;
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
     * Set estadomaestroId
     *
     * @param integer $estadomaestroId
     * @return MaestroInscripcion
     */
    public function setEstadomaestroId($estadomaestroId)
    {
        $this->estadomaestroId = $estadomaestroId;
    
        return $this;
    }

    /**
     * Get estadomaestroId
     *
     * @return integer 
     */
    public function getEstadomaestroId()
    {
        return $this->estadomaestroId;
    }

    /**
     * Set rolTipoId
     *
     * @param integer $rolTipoId
     * @return MaestroInscripcion
     */
    public function setRolTipoId($rolTipoId)
    {
        $this->rolTipoId = $rolTipoId;
    
        return $this;
    }

    /**
     * Get rolTipoId
     *
     * @return integer 
     */
    public function getRolTipoId()
    {
        return $this->rolTipoId;
    }

    /**
     * Set institucioneducativaSucursalId
     *
     * @param integer $institucioneducativaSucursalId
     * @return MaestroInscripcion
     */
    public function setInstitucioneducativaSucursalId($institucioneducativaSucursalId)
    {
        $this->institucioneducativaSucursalId = $institucioneducativaSucursalId;
    
        return $this;
    }

    /**
     * Get institucioneducativaSucursalId
     *
     * @return integer 
     */
    public function getInstitucioneducativaSucursalId()
    {
        return $this->institucioneducativaSucursalId;
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
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal
     */
    private $institucioneducativaSucursal;

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
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

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
    /**
     * @var \Sie\AppWebBundle\Entity\EducacionDiversa
     */
    private $educacionDiversa;

    /**
     * @var \Sie\AppWebBundle\Entity\UnidadMilitar
     */
    private $unidadMilitar;

    /**
     * @var \Sie\AppWebBundle\Entity\Penal
     */
    private $penal;


    /**
     * Set educacionDiversa
     *
     * @param \Sie\AppWebBundle\Entity\EducacionDiversa $educacionDiversa
     * @return MaestroInscripcion
     */
    public function setEducacionDiversa(\Sie\AppWebBundle\Entity\EducacionDiversa $educacionDiversa = null)
    {
        $this->educacionDiversa = $educacionDiversa;
    
        return $this;
    }

    /**
     * Get educacionDiversa
     *
     * @return \Sie\AppWebBundle\Entity\EducacionDiversa 
     */
    public function getEducacionDiversa()
    {
        return $this->educacionDiversa;
    }

    /**
     * Set unidadMilitar
     *
     * @param \Sie\AppWebBundle\Entity\UnidadMilitar $unidadMilitar
     * @return MaestroInscripcion
     */
    public function setUnidadMilitar(\Sie\AppWebBundle\Entity\UnidadMilitar $unidadMilitar = null)
    {
        $this->unidadMilitar = $unidadMilitar;
    
        return $this;
    }

    /**
     * Get unidadMilitar
     *
     * @return \Sie\AppWebBundle\Entity\UnidadMilitar 
     */
    public function getUnidadMilitar()
    {
        return $this->unidadMilitar;
    }

    /**
     * Set penal
     *
     * @param \Sie\AppWebBundle\Entity\Penal $penal
     * @return MaestroInscripcion
     */
    public function setPenal(\Sie\AppWebBundle\Entity\Penal $penal = null)
    {
        $this->penal = $penal;
    
        return $this;
    }

    /**
     * Get penal
     *
     * @return \Sie\AppWebBundle\Entity\Penal 
     */
    public function getPenal()
    {
        return $this->penal;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\EducacionDiversaTipo
     */
    private $educacionDiversaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnidadMilitarTipo
     */
    private $unidadMilitarTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\RecintoPenitenciarioTipo
     */
    private $recintoPenitenciarioTipo;


    /**
     * Set educacionDiversaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EducacionDiversaTipo $educacionDiversaTipo
     * @return MaestroInscripcion
     */
    public function setEducacionDiversaTipo(\Sie\AppWebBundle\Entity\EducacionDiversaTipo $educacionDiversaTipo = null)
    {
        $this->educacionDiversaTipo = $educacionDiversaTipo;
    
        return $this;
    }

    /**
     * Get educacionDiversaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EducacionDiversaTipo 
     */
    public function getEducacionDiversaTipo()
    {
        return $this->educacionDiversaTipo;
    }

    /**
     * Set unidadMilitarTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnidadMilitarTipo $unidadMilitarTipo
     * @return MaestroInscripcion
     */
    public function setUnidadMilitarTipo(\Sie\AppWebBundle\Entity\UnidadMilitarTipo $unidadMilitarTipo = null)
    {
        $this->unidadMilitarTipo = $unidadMilitarTipo;
    
        return $this;
    }

    /**
     * Get unidadMilitarTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnidadMilitarTipo 
     */
    public function getUnidadMilitarTipo()
    {
        return $this->unidadMilitarTipo;
    }

    /**
     * Set recintoPenitenciarioTipo
     *
     * @param \Sie\AppWebBundle\Entity\RecintoPenitenciarioTipo $recintoPenitenciarioTipo
     * @return MaestroInscripcion
     */
    public function setRecintoPenitenciarioTipo(\Sie\AppWebBundle\Entity\RecintoPenitenciarioTipo $recintoPenitenciarioTipo = null)
    {
        $this->recintoPenitenciarioTipo = $recintoPenitenciarioTipo;
    
        return $this;
    }

    /**
     * Get recintoPenitenciarioTipo
     *
     * @return \Sie\AppWebBundle\Entity\RecintoPenitenciarioTipo 
     */
    public function getRecintoPenitenciarioTipo()
    {
        return $this->recintoPenitenciarioTipo;
    }
}
