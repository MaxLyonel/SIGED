<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BonojuancitoAsignacion
 */
class BonojuancitoAsignacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $codLe;

    /**
     * @var integer
     */
    private $codSie;

    /**
     * @var string
     */
    private $institucioneducativa;

    /**
     * @var integer
     */
    private $departamentoTipoId;

    /**
     * @var string
     */
    private $departamentoTipo;

    /**
     * @var string
     */
    private $provincia;

    /**
     * @var string
     */
    private $municipio;

    /**
     * @var string
     */
    private $seccion;

    /**
     * @var integer
     */
    private $canton;

    /**
     * @var string
     */
    private $localidad;

    /**
     * @var string
     */
    private $nucleo;

    /**
     * @var integer
     */
    private $distritoTipoId;

    /**
     * @var string
     */
    private $distrito;

    /**
     * @var string
     */
    private $zona;

    /**
     * @var string
     */
    private $direccion;

    /**
     * @var string
     */
    private $areaGeografica;

    /**
     * @var string
     */
    private $ciDirector;

    /**
     * @var string
     */
    private $director;

    /**
     * @var integer
     */
    private $turnoTipoId;

    /**
     * @var string
     */
    private $turno;

    /**
     * @var string
     */
    private $telefono1;

    /**
     * @var string
     */
    private $telefono2;

    /**
     * @var string
     */
    private $fax;

    /**
     * @var string
     */
    private $email;

    /**
     * @var integer
     */
    private $orgcurricularTipoId;

    /**
     * @var string
     */
    private $orgcurricularTipo;

    /**
     * @var string
     */
    private $dependencia;

    /**
     * @var boolean
     */
    private $esnuevoingreso;

    /**
     * @var boolean
     */
    private $esreportadopreliminar;

    /**
     * @var boolean
     */
    private $essinrue;

    /**
     * @var integer
     */
    private $primerop;

    /**
     * @var integer
     */
    private $segundop;

    /**
     * @var integer
     */
    private $tercerop;

    /**
     * @var integer
     */
    private $cuartop;

    /**
     * @var integer
     */
    private $quintop;

    /**
     * @var integer
     */
    private $sextop;

    /**
     * @var integer
     */
    private $primeros;

    /**
     * @var integer
     */
    private $segundos;

    /**
     * @var integer
     */
    private $terceros;

    /**
     * @var integer
     */
    private $cuartos;

    /**
     * @var integer
     */
    private $quintos;

    /**
     * @var integer
     */
    private $sextos;

    /**
     * @var integer
     */
    private $multigrado;

    /**
     * @var \DateTime
     */
    private $fechaEstadisticaRegistro;

    /**
     * @var integer
     */
    private $totalPlanificado;

    /**
     * @var integer
     */
    private $habilitados;

    /**
     * @var integer
     */
    private $pagadoEnPeriodo;

    /**
     * @var integer
     */
    private $pagadoRezagado;

    /**
     * @var boolean
     */
    private $espagado;

    /**
     * @var boolean
     */
    private $esterminado;

    /**
     * @var integer
     */
    private $registrados;

    /**
     * @var integer
     */
    private $abandono;

    /**
     * @var integer
     */
    private $traspaso;

    /**
     * @var integer
     */
    private $otro;

    /**
     * @var integer
     */
    private $noRecibieron;

    /**
     * @var string
     */
    private $ci;

    /**
     * @var string
     */
    private $agente;

    /**
     * @var \DateTime
     */
    private $fechaTerminado;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\BonojuancitoUnidadmilitar
     */
    private $bonojuancitoUnidadmilitar;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa2;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


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
     * Set codLe
     *
     * @param integer $codLe
     * @return BonojuancitoAsignacion
     */
    public function setCodLe($codLe)
    {
        $this->codLe = $codLe;
    
        return $this;
    }

    /**
     * Get codLe
     *
     * @return integer 
     */
    public function getCodLe()
    {
        return $this->codLe;
    }

    /**
     * Set codSie
     *
     * @param integer $codSie
     * @return BonojuancitoAsignacion
     */
    public function setCodSie($codSie)
    {
        $this->codSie = $codSie;
    
        return $this;
    }

    /**
     * Get codSie
     *
     * @return integer 
     */
    public function getCodSie()
    {
        return $this->codSie;
    }

    /**
     * Set institucioneducativa
     *
     * @param string $institucioneducativa
     * @return BonojuancitoAsignacion
     */
    public function setInstitucioneducativa($institucioneducativa)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return string 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set departamentoTipoId
     *
     * @param integer $departamentoTipoId
     * @return BonojuancitoAsignacion
     */
    public function setDepartamentoTipoId($departamentoTipoId)
    {
        $this->departamentoTipoId = $departamentoTipoId;
    
        return $this;
    }

    /**
     * Get departamentoTipoId
     *
     * @return integer 
     */
    public function getDepartamentoTipoId()
    {
        return $this->departamentoTipoId;
    }

    /**
     * Set departamentoTipo
     *
     * @param string $departamentoTipo
     * @return BonojuancitoAsignacion
     */
    public function setDepartamentoTipo($departamentoTipo)
    {
        $this->departamentoTipo = $departamentoTipo;
    
        return $this;
    }

    /**
     * Get departamentoTipo
     *
     * @return string 
     */
    public function getDepartamentoTipo()
    {
        return $this->departamentoTipo;
    }

    /**
     * Set provincia
     *
     * @param string $provincia
     * @return BonojuancitoAsignacion
     */
    public function setProvincia($provincia)
    {
        $this->provincia = $provincia;
    
        return $this;
    }

    /**
     * Get provincia
     *
     * @return string 
     */
    public function getProvincia()
    {
        return $this->provincia;
    }

    /**
     * Set municipio
     *
     * @param string $municipio
     * @return BonojuancitoAsignacion
     */
    public function setMunicipio($municipio)
    {
        $this->municipio = $municipio;
    
        return $this;
    }

    /**
     * Get municipio
     *
     * @return string 
     */
    public function getMunicipio()
    {
        return $this->municipio;
    }

    /**
     * Set seccion
     *
     * @param string $seccion
     * @return BonojuancitoAsignacion
     */
    public function setSeccion($seccion)
    {
        $this->seccion = $seccion;
    
        return $this;
    }

    /**
     * Get seccion
     *
     * @return string 
     */
    public function getSeccion()
    {
        return $this->seccion;
    }

    /**
     * Set canton
     *
     * @param integer $canton
     * @return BonojuancitoAsignacion
     */
    public function setCanton($canton)
    {
        $this->canton = $canton;
    
        return $this;
    }

    /**
     * Get canton
     *
     * @return integer 
     */
    public function getCanton()
    {
        return $this->canton;
    }

    /**
     * Set localidad
     *
     * @param string $localidad
     * @return BonojuancitoAsignacion
     */
    public function setLocalidad($localidad)
    {
        $this->localidad = $localidad;
    
        return $this;
    }

    /**
     * Get localidad
     *
     * @return string 
     */
    public function getLocalidad()
    {
        return $this->localidad;
    }

    /**
     * Set nucleo
     *
     * @param string $nucleo
     * @return BonojuancitoAsignacion
     */
    public function setNucleo($nucleo)
    {
        $this->nucleo = $nucleo;
    
        return $this;
    }

    /**
     * Get nucleo
     *
     * @return string 
     */
    public function getNucleo()
    {
        return $this->nucleo;
    }

    /**
     * Set distritoTipoId
     *
     * @param integer $distritoTipoId
     * @return BonojuancitoAsignacion
     */
    public function setDistritoTipoId($distritoTipoId)
    {
        $this->distritoTipoId = $distritoTipoId;
    
        return $this;
    }

    /**
     * Get distritoTipoId
     *
     * @return integer 
     */
    public function getDistritoTipoId()
    {
        return $this->distritoTipoId;
    }

    /**
     * Set distrito
     *
     * @param string $distrito
     * @return BonojuancitoAsignacion
     */
    public function setDistrito($distrito)
    {
        $this->distrito = $distrito;
    
        return $this;
    }

    /**
     * Get distrito
     *
     * @return string 
     */
    public function getDistrito()
    {
        return $this->distrito;
    }

    /**
     * Set zona
     *
     * @param string $zona
     * @return BonojuancitoAsignacion
     */
    public function setZona($zona)
    {
        $this->zona = $zona;
    
        return $this;
    }

    /**
     * Get zona
     *
     * @return string 
     */
    public function getZona()
    {
        return $this->zona;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return BonojuancitoAsignacion
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    
        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set areaGeografica
     *
     * @param string $areaGeografica
     * @return BonojuancitoAsignacion
     */
    public function setAreaGeografica($areaGeografica)
    {
        $this->areaGeografica = $areaGeografica;
    
        return $this;
    }

    /**
     * Get areaGeografica
     *
     * @return string 
     */
    public function getAreaGeografica()
    {
        return $this->areaGeografica;
    }

    /**
     * Set ciDirector
     *
     * @param string $ciDirector
     * @return BonojuancitoAsignacion
     */
    public function setCiDirector($ciDirector)
    {
        $this->ciDirector = $ciDirector;
    
        return $this;
    }

    /**
     * Get ciDirector
     *
     * @return string 
     */
    public function getCiDirector()
    {
        return $this->ciDirector;
    }

    /**
     * Set director
     *
     * @param string $director
     * @return BonojuancitoAsignacion
     */
    public function setDirector($director)
    {
        $this->director = $director;
    
        return $this;
    }

    /**
     * Get director
     *
     * @return string 
     */
    public function getDirector()
    {
        return $this->director;
    }

    /**
     * Set turnoTipoId
     *
     * @param integer $turnoTipoId
     * @return BonojuancitoAsignacion
     */
    public function setTurnoTipoId($turnoTipoId)
    {
        $this->turnoTipoId = $turnoTipoId;
    
        return $this;
    }

    /**
     * Get turnoTipoId
     *
     * @return integer 
     */
    public function getTurnoTipoId()
    {
        return $this->turnoTipoId;
    }

    /**
     * Set turno
     *
     * @param string $turno
     * @return BonojuancitoAsignacion
     */
    public function setTurno($turno)
    {
        $this->turno = $turno;
    
        return $this;
    }

    /**
     * Get turno
     *
     * @return string 
     */
    public function getTurno()
    {
        return $this->turno;
    }

    /**
     * Set telefono1
     *
     * @param string $telefono1
     * @return BonojuancitoAsignacion
     */
    public function setTelefono1($telefono1)
    {
        $this->telefono1 = $telefono1;
    
        return $this;
    }

    /**
     * Get telefono1
     *
     * @return string 
     */
    public function getTelefono1()
    {
        return $this->telefono1;
    }

    /**
     * Set telefono2
     *
     * @param string $telefono2
     * @return BonojuancitoAsignacion
     */
    public function setTelefono2($telefono2)
    {
        $this->telefono2 = $telefono2;
    
        return $this;
    }

    /**
     * Get telefono2
     *
     * @return string 
     */
    public function getTelefono2()
    {
        return $this->telefono2;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return BonojuancitoAsignacion
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    
        return $this;
    }

    /**
     * Get fax
     *
     * @return string 
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return BonojuancitoAsignacion
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set orgcurricularTipoId
     *
     * @param integer $orgcurricularTipoId
     * @return BonojuancitoAsignacion
     */
    public function setOrgcurricularTipoId($orgcurricularTipoId)
    {
        $this->orgcurricularTipoId = $orgcurricularTipoId;
    
        return $this;
    }

    /**
     * Get orgcurricularTipoId
     *
     * @return integer 
     */
    public function getOrgcurricularTipoId()
    {
        return $this->orgcurricularTipoId;
    }

    /**
     * Set orgcurricularTipo
     *
     * @param string $orgcurricularTipo
     * @return BonojuancitoAsignacion
     */
    public function setOrgcurricularTipo($orgcurricularTipo)
    {
        $this->orgcurricularTipo = $orgcurricularTipo;
    
        return $this;
    }

    /**
     * Get orgcurricularTipo
     *
     * @return string 
     */
    public function getOrgcurricularTipo()
    {
        return $this->orgcurricularTipo;
    }

    /**
     * Set dependencia
     *
     * @param string $dependencia
     * @return BonojuancitoAsignacion
     */
    public function setDependencia($dependencia)
    {
        $this->dependencia = $dependencia;
    
        return $this;
    }

    /**
     * Get dependencia
     *
     * @return string 
     */
    public function getDependencia()
    {
        return $this->dependencia;
    }

    /**
     * Set esnuevoingreso
     *
     * @param boolean $esnuevoingreso
     * @return BonojuancitoAsignacion
     */
    public function setEsnuevoingreso($esnuevoingreso)
    {
        $this->esnuevoingreso = $esnuevoingreso;
    
        return $this;
    }

    /**
     * Get esnuevoingreso
     *
     * @return boolean 
     */
    public function getEsnuevoingreso()
    {
        return $this->esnuevoingreso;
    }

    /**
     * Set esreportadopreliminar
     *
     * @param boolean $esreportadopreliminar
     * @return BonojuancitoAsignacion
     */
    public function setEsreportadopreliminar($esreportadopreliminar)
    {
        $this->esreportadopreliminar = $esreportadopreliminar;
    
        return $this;
    }

    /**
     * Get esreportadopreliminar
     *
     * @return boolean 
     */
    public function getEsreportadopreliminar()
    {
        return $this->esreportadopreliminar;
    }

    /**
     * Set essinrue
     *
     * @param boolean $essinrue
     * @return BonojuancitoAsignacion
     */
    public function setEssinrue($essinrue)
    {
        $this->essinrue = $essinrue;
    
        return $this;
    }

    /**
     * Get essinrue
     *
     * @return boolean 
     */
    public function getEssinrue()
    {
        return $this->essinrue;
    }

    /**
     * Set primerop
     *
     * @param integer $primerop
     * @return BonojuancitoAsignacion
     */
    public function setPrimerop($primerop)
    {
        $this->primerop = $primerop;
    
        return $this;
    }

    /**
     * Get primerop
     *
     * @return integer 
     */
    public function getPrimerop()
    {
        return $this->primerop;
    }

    /**
     * Set segundop
     *
     * @param integer $segundop
     * @return BonojuancitoAsignacion
     */
    public function setSegundop($segundop)
    {
        $this->segundop = $segundop;
    
        return $this;
    }

    /**
     * Get segundop
     *
     * @return integer 
     */
    public function getSegundop()
    {
        return $this->segundop;
    }

    /**
     * Set tercerop
     *
     * @param integer $tercerop
     * @return BonojuancitoAsignacion
     */
    public function setTercerop($tercerop)
    {
        $this->tercerop = $tercerop;
    
        return $this;
    }

    /**
     * Get tercerop
     *
     * @return integer 
     */
    public function getTercerop()
    {
        return $this->tercerop;
    }

    /**
     * Set cuartop
     *
     * @param integer $cuartop
     * @return BonojuancitoAsignacion
     */
    public function setCuartop($cuartop)
    {
        $this->cuartop = $cuartop;
    
        return $this;
    }

    /**
     * Get cuartop
     *
     * @return integer 
     */
    public function getCuartop()
    {
        return $this->cuartop;
    }

    /**
     * Set quintop
     *
     * @param integer $quintop
     * @return BonojuancitoAsignacion
     */
    public function setQuintop($quintop)
    {
        $this->quintop = $quintop;
    
        return $this;
    }

    /**
     * Get quintop
     *
     * @return integer 
     */
    public function getQuintop()
    {
        return $this->quintop;
    }

    /**
     * Set sextop
     *
     * @param integer $sextop
     * @return BonojuancitoAsignacion
     */
    public function setSextop($sextop)
    {
        $this->sextop = $sextop;
    
        return $this;
    }

    /**
     * Get sextop
     *
     * @return integer 
     */
    public function getSextop()
    {
        return $this->sextop;
    }

    /**
     * Set primeros
     *
     * @param integer $primeros
     * @return BonojuancitoAsignacion
     */
    public function setPrimeros($primeros)
    {
        $this->primeros = $primeros;
    
        return $this;
    }

    /**
     * Get primeros
     *
     * @return integer 
     */
    public function getPrimeros()
    {
        return $this->primeros;
    }

    /**
     * Set segundos
     *
     * @param integer $segundos
     * @return BonojuancitoAsignacion
     */
    public function setSegundos($segundos)
    {
        $this->segundos = $segundos;
    
        return $this;
    }

    /**
     * Get segundos
     *
     * @return integer 
     */
    public function getSegundos()
    {
        return $this->segundos;
    }

    /**
     * Set terceros
     *
     * @param integer $terceros
     * @return BonojuancitoAsignacion
     */
    public function setTerceros($terceros)
    {
        $this->terceros = $terceros;
    
        return $this;
    }

    /**
     * Get terceros
     *
     * @return integer 
     */
    public function getTerceros()
    {
        return $this->terceros;
    }

    /**
     * Set cuartos
     *
     * @param integer $cuartos
     * @return BonojuancitoAsignacion
     */
    public function setCuartos($cuartos)
    {
        $this->cuartos = $cuartos;
    
        return $this;
    }

    /**
     * Get cuartos
     *
     * @return integer 
     */
    public function getCuartos()
    {
        return $this->cuartos;
    }

    /**
     * Set quintos
     *
     * @param integer $quintos
     * @return BonojuancitoAsignacion
     */
    public function setQuintos($quintos)
    {
        $this->quintos = $quintos;
    
        return $this;
    }

    /**
     * Get quintos
     *
     * @return integer 
     */
    public function getQuintos()
    {
        return $this->quintos;
    }

    /**
     * Set sextos
     *
     * @param integer $sextos
     * @return BonojuancitoAsignacion
     */
    public function setSextos($sextos)
    {
        $this->sextos = $sextos;
    
        return $this;
    }

    /**
     * Get sextos
     *
     * @return integer 
     */
    public function getSextos()
    {
        return $this->sextos;
    }

    /**
     * Set multigrado
     *
     * @param integer $multigrado
     * @return BonojuancitoAsignacion
     */
    public function setMultigrado($multigrado)
    {
        $this->multigrado = $multigrado;
    
        return $this;
    }

    /**
     * Get multigrado
     *
     * @return integer 
     */
    public function getMultigrado()
    {
        return $this->multigrado;
    }

    /**
     * Set fechaEstadisticaRegistro
     *
     * @param \DateTime $fechaEstadisticaRegistro
     * @return BonojuancitoAsignacion
     */
    public function setFechaEstadisticaRegistro($fechaEstadisticaRegistro)
    {
        $this->fechaEstadisticaRegistro = $fechaEstadisticaRegistro;
    
        return $this;
    }

    /**
     * Get fechaEstadisticaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaEstadisticaRegistro()
    {
        return $this->fechaEstadisticaRegistro;
    }

    /**
     * Set totalPlanificado
     *
     * @param integer $totalPlanificado
     * @return BonojuancitoAsignacion
     */
    public function setTotalPlanificado($totalPlanificado)
    {
        $this->totalPlanificado = $totalPlanificado;
    
        return $this;
    }

    /**
     * Get totalPlanificado
     *
     * @return integer 
     */
    public function getTotalPlanificado()
    {
        return $this->totalPlanificado;
    }

    /**
     * Set habilitados
     *
     * @param integer $habilitados
     * @return BonojuancitoAsignacion
     */
    public function setHabilitados($habilitados)
    {
        $this->habilitados = $habilitados;
    
        return $this;
    }

    /**
     * Get habilitados
     *
     * @return integer 
     */
    public function getHabilitados()
    {
        return $this->habilitados;
    }

    /**
     * Set pagadoEnPeriodo
     *
     * @param integer $pagadoEnPeriodo
     * @return BonojuancitoAsignacion
     */
    public function setPagadoEnPeriodo($pagadoEnPeriodo)
    {
        $this->pagadoEnPeriodo = $pagadoEnPeriodo;
    
        return $this;
    }

    /**
     * Get pagadoEnPeriodo
     *
     * @return integer 
     */
    public function getPagadoEnPeriodo()
    {
        return $this->pagadoEnPeriodo;
    }

    /**
     * Set pagadoRezagado
     *
     * @param integer $pagadoRezagado
     * @return BonojuancitoAsignacion
     */
    public function setPagadoRezagado($pagadoRezagado)
    {
        $this->pagadoRezagado = $pagadoRezagado;
    
        return $this;
    }

    /**
     * Get pagadoRezagado
     *
     * @return integer 
     */
    public function getPagadoRezagado()
    {
        return $this->pagadoRezagado;
    }

    /**
     * Set espagado
     *
     * @param boolean $espagado
     * @return BonojuancitoAsignacion
     */
    public function setEspagado($espagado)
    {
        $this->espagado = $espagado;
    
        return $this;
    }

    /**
     * Get espagado
     *
     * @return boolean 
     */
    public function getEspagado()
    {
        return $this->espagado;
    }

    /**
     * Set esterminado
     *
     * @param boolean $esterminado
     * @return BonojuancitoAsignacion
     */
    public function setEsterminado($esterminado)
    {
        $this->esterminado = $esterminado;
    
        return $this;
    }

    /**
     * Get esterminado
     *
     * @return boolean 
     */
    public function getEsterminado()
    {
        return $this->esterminado;
    }

    /**
     * Set registrados
     *
     * @param integer $registrados
     * @return BonojuancitoAsignacion
     */
    public function setRegistrados($registrados)
    {
        $this->registrados = $registrados;
    
        return $this;
    }

    /**
     * Get registrados
     *
     * @return integer 
     */
    public function getRegistrados()
    {
        return $this->registrados;
    }

    /**
     * Set abandono
     *
     * @param integer $abandono
     * @return BonojuancitoAsignacion
     */
    public function setAbandono($abandono)
    {
        $this->abandono = $abandono;
    
        return $this;
    }

    /**
     * Get abandono
     *
     * @return integer 
     */
    public function getAbandono()
    {
        return $this->abandono;
    }

    /**
     * Set traspaso
     *
     * @param integer $traspaso
     * @return BonojuancitoAsignacion
     */
    public function setTraspaso($traspaso)
    {
        $this->traspaso = $traspaso;
    
        return $this;
    }

    /**
     * Get traspaso
     *
     * @return integer 
     */
    public function getTraspaso()
    {
        return $this->traspaso;
    }

    /**
     * Set otro
     *
     * @param integer $otro
     * @return BonojuancitoAsignacion
     */
    public function setOtro($otro)
    {
        $this->otro = $otro;
    
        return $this;
    }

    /**
     * Get otro
     *
     * @return integer 
     */
    public function getOtro()
    {
        return $this->otro;
    }

    /**
     * Set noRecibieron
     *
     * @param integer $noRecibieron
     * @return BonojuancitoAsignacion
     */
    public function setNoRecibieron($noRecibieron)
    {
        $this->noRecibieron = $noRecibieron;
    
        return $this;
    }

    /**
     * Get noRecibieron
     *
     * @return integer 
     */
    public function getNoRecibieron()
    {
        return $this->noRecibieron;
    }

    /**
     * Set ci
     *
     * @param string $ci
     * @return BonojuancitoAsignacion
     */
    public function setCi($ci)
    {
        $this->ci = $ci;
    
        return $this;
    }

    /**
     * Get ci
     *
     * @return string 
     */
    public function getCi()
    {
        return $this->ci;
    }

    /**
     * Set agente
     *
     * @param string $agente
     * @return BonojuancitoAsignacion
     */
    public function setAgente($agente)
    {
        $this->agente = $agente;
    
        return $this;
    }

    /**
     * Get agente
     *
     * @return string 
     */
    public function getAgente()
    {
        return $this->agente;
    }

    /**
     * Set fechaTerminado
     *
     * @param \DateTime $fechaTerminado
     * @return BonojuancitoAsignacion
     */
    public function setFechaTerminado($fechaTerminado)
    {
        $this->fechaTerminado = $fechaTerminado;
    
        return $this;
    }

    /**
     * Get fechaTerminado
     *
     * @return \DateTime 
     */
    public function getFechaTerminado()
    {
        return $this->fechaTerminado;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return BonojuancitoAsignacion
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
     * Set obs
     *
     * @param string $obs
     * @return BonojuancitoAsignacion
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
     * Set bonojuancitoUnidadmilitar
     *
     * @param \Sie\AppWebBundle\Entity\BonojuancitoUnidadmilitar $bonojuancitoUnidadmilitar
     * @return BonojuancitoAsignacion
     */
    public function setBonojuancitoUnidadmilitar(\Sie\AppWebBundle\Entity\BonojuancitoUnidadmilitar $bonojuancitoUnidadmilitar = null)
    {
        $this->bonojuancitoUnidadmilitar = $bonojuancitoUnidadmilitar;
    
        return $this;
    }

    /**
     * Get bonojuancitoUnidadmilitar
     *
     * @return \Sie\AppWebBundle\Entity\BonojuancitoUnidadmilitar 
     */
    public function getBonojuancitoUnidadmilitar()
    {
        return $this->bonojuancitoUnidadmilitar;
    }

    /**
     * Set institucioneducativa2
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa2
     * @return BonojuancitoAsignacion
     */
    public function setInstitucioneducativa2(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa2 = null)
    {
        $this->institucioneducativa2 = $institucioneducativa2;
    
        return $this;
    }

    /**
     * Get institucioneducativa2
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa2()
    {
        return $this->institucioneducativa2;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return BonojuancitoAsignacion
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
}
