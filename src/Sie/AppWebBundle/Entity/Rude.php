<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Rude
 */
class Rude
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $esPertenceNacionOriginaria;

    /**
     * @var boolean
     */
    private $seguroSalud;

    /**
     * @var boolean
     */
    private $centroSalud;

    /**
     * @var boolean
     */
    private $trabajoGestionPasada;

    /**
     * @var boolean
     */
    private $tieneOcupacionTrabajo;

    /**
     * @var boolean
     */
    private $tieneServicioBasicoId;

    /**
     * @var boolean
     */
    private $tieneAbandono;

    /**
     * @var integer
     */
    private $educacionDiversaTipoId;

    /**
     * @var string
     */
    private $zona;

    /**
     * @var string
     */
    private $avenida;

    /**
     * @var string
     */
    private $numero;

    /**
     * @var string
     */
    private $celular;

    /**
     * @var string
     */
    private $telefonoFijo;

    /**
     * @var integer
     */
    private $cantHijos;

    /**
     * @var boolean
     */
    private $esServicioMilitar;

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
    private $tieneDiscapacidad;

    /**
     * @var string
     */
    private $lugarRegistroRude;

    /**
     * @var \DateTime
     */
    private $fechaRegistroRude;

    /**
     * @var string
     */
    private $localidad;

    /**
     * @var boolean
     */
    private $respuestaPago;

    /**
     * @var boolean
     */
    private $tieneCi;

    /**
     * @var boolean
     */
    private $tienePasaporte;

    /**
     * @var boolean
     */
    private $tieneCarnetDiscapacidad;

    /**
     * @var string
     */
    private $tiempoLlegadaHoras;

    /**
     * @var string
     */
    private $tiempoLlegadaMinutos;

    /**
     * @var \Sie\AppWebBundle\Entity\NacionOriginariaTipo
     */
    private $nacionOriginariaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ViviendaOcupaTipo
     */
    private $viviendaOcupaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ModalidadEstudioTipo
     */
    private $modalidadEstudioTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ProcedenciaTipo
     */
    private $procedenciaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\CantidadCentroSaludTipo
     */
    private $cantidadCentroSaludTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\FrecuenciaUsoInternetTipo
     */
    private $frecuenciaUsoInternetTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\FrecuenciaTrabajoTipo
     */
    private $frecuenciaTrabajoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ServicioMilitarTipo
     */
    private $servicioMilitarTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaTipo
     */
    private $institucioneducativaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ViveHabitualmenteTipo
     */
    private $viveHabitualmenteTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $municipioLugarTipo;


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
     * Set esPertenceNacionOriginaria
     *
     * @param string $esPertenceNacionOriginaria
     * @return Rude
     */
    public function setEsPertenceNacionOriginaria($esPertenceNacionOriginaria)
    {
        $this->esPertenceNacionOriginaria = $esPertenceNacionOriginaria;
    
        return $this;
    }

    /**
     * Get esPertenceNacionOriginaria
     *
     * @return string 
     */
    public function getEsPertenceNacionOriginaria()
    {
        return $this->esPertenceNacionOriginaria;
    }

    /**
     * Set seguroSalud
     *
     * @param boolean $seguroSalud
     * @return Rude
     */
    public function setSeguroSalud($seguroSalud)
    {
        $this->seguroSalud = $seguroSalud;
    
        return $this;
    }

    /**
     * Get seguroSalud
     *
     * @return boolean 
     */
    public function getSeguroSalud()
    {
        return $this->seguroSalud;
    }

    /**
     * Set centroSalud
     *
     * @param boolean $centroSalud
     * @return Rude
     */
    public function setCentroSalud($centroSalud)
    {
        $this->centroSalud = $centroSalud;
    
        return $this;
    }

    /**
     * Get centroSalud
     *
     * @return boolean 
     */
    public function getCentroSalud()
    {
        return $this->centroSalud;
    }

    /**
     * Set trabajoGestionPasada
     *
     * @param boolean $trabajoGestionPasada
     * @return Rude
     */
    public function setTrabajoGestionPasada($trabajoGestionPasada)
    {
        $this->trabajoGestionPasada = $trabajoGestionPasada;
    
        return $this;
    }

    /**
     * Get trabajoGestionPasada
     *
     * @return boolean 
     */
    public function getTrabajoGestionPasada()
    {
        return $this->trabajoGestionPasada;
    }

    /**
     * Set tieneOcupacionTrabajo
     *
     * @param boolean $tieneOcupacionTrabajo
     * @return Rude
     */
    public function setTieneOcupacionTrabajo($tieneOcupacionTrabajo)
    {
        $this->tieneOcupacionTrabajo = $tieneOcupacionTrabajo;
    
        return $this;
    }

    /**
     * Get tieneOcupacionTrabajo
     *
     * @return boolean 
     */
    public function getTieneOcupacionTrabajo()
    {
        return $this->tieneOcupacionTrabajo;
    }

    /**
     * Set tieneServicioBasicoId
     *
     * @param boolean $tieneServicioBasicoId
     * @return Rude
     */
    public function setTieneServicioBasicoId($tieneServicioBasicoId)
    {
        $this->tieneServicioBasicoId = $tieneServicioBasicoId;
    
        return $this;
    }

    /**
     * Get tieneServicioBasicoId
     *
     * @return boolean 
     */
    public function getTieneServicioBasicoId()
    {
        return $this->tieneServicioBasicoId;
    }

    /**
     * Set tieneAbandono
     *
     * @param boolean $tieneAbandono
     * @return Rude
     */
    public function setTieneAbandono($tieneAbandono)
    {
        $this->tieneAbandono = $tieneAbandono;
    
        return $this;
    }

    /**
     * Get tieneAbandono
     *
     * @return boolean 
     */
    public function getTieneAbandono()
    {
        return $this->tieneAbandono;
    }

    /**
     * Set educacionDiversaTipoId
     *
     * @param integer $educacionDiversaTipoId
     * @return Rude
     */
    public function setEducacionDiversaTipoId($educacionDiversaTipoId)
    {
        $this->educacionDiversaTipoId = $educacionDiversaTipoId;
    
        return $this;
    }

    /**
     * Get educacionDiversaTipoId
     *
     * @return integer 
     */
    public function getEducacionDiversaTipoId()
    {
        return $this->educacionDiversaTipoId;
    }

    /**
     * Set zona
     *
     * @param string $zona
     * @return Rude
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
     * Set avenida
     *
     * @param string $avenida
     * @return Rude
     */
    public function setAvenida($avenida)
    {
        $this->avenida = $avenida;
    
        return $this;
    }

    /**
     * Get avenida
     *
     * @return string 
     */
    public function getAvenida()
    {
        return $this->avenida;
    }

    /**
     * Set numero
     *
     * @param string $numero
     * @return Rude
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    
        return $this;
    }

    /**
     * Get numero
     *
     * @return string 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set celular
     *
     * @param string $celular
     * @return Rude
     */
    public function setCelular($celular)
    {
        $this->celular = $celular;
    
        return $this;
    }

    /**
     * Get celular
     *
     * @return string 
     */
    public function getCelular()
    {
        return $this->celular;
    }

    /**
     * Set telefonoFijo
     *
     * @param string $telefonoFijo
     * @return Rude
     */
    public function setTelefonoFijo($telefonoFijo)
    {
        $this->telefonoFijo = $telefonoFijo;
    
        return $this;
    }

    /**
     * Get telefonoFijo
     *
     * @return string 
     */
    public function getTelefonoFijo()
    {
        return $this->telefonoFijo;
    }

    /**
     * Set cantHijos
     *
     * @param integer $cantHijos
     * @return Rude
     */
    public function setCantHijos($cantHijos)
    {
        $this->cantHijos = $cantHijos;
    
        return $this;
    }

    /**
     * Get cantHijos
     *
     * @return integer 
     */
    public function getCantHijos()
    {
        return $this->cantHijos;
    }

    /**
     * Set esServicioMilitar
     *
     * @param boolean $esServicioMilitar
     * @return Rude
     */
    public function setEsServicioMilitar($esServicioMilitar)
    {
        $this->esServicioMilitar = $esServicioMilitar;
    
        return $this;
    }

    /**
     * Get esServicioMilitar
     *
     * @return boolean 
     */
    public function getEsServicioMilitar()
    {
        return $this->esServicioMilitar;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return Rude
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
     * @return Rude
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
     * Set tieneDiscapacidad
     *
     * @param boolean $tieneDiscapacidad
     * @return Rude
     */
    public function setTieneDiscapacidad($tieneDiscapacidad)
    {
        $this->tieneDiscapacidad = $tieneDiscapacidad;
    
        return $this;
    }

    /**
     * Get tieneDiscapacidad
     *
     * @return boolean 
     */
    public function getTieneDiscapacidad()
    {
        return $this->tieneDiscapacidad;
    }

    /**
     * Set lugarRegistroRude
     *
     * @param string $lugarRegistroRude
     * @return Rude
     */
    public function setLugarRegistroRude($lugarRegistroRude)
    {
        $this->lugarRegistroRude = $lugarRegistroRude;
    
        return $this;
    }

    /**
     * Get lugarRegistroRude
     *
     * @return string 
     */
    public function getLugarRegistroRude()
    {
        return $this->lugarRegistroRude;
    }

    /**
     * Set fechaRegistroRude
     *
     * @param \DateTime $fechaRegistroRude
     * @return Rude
     */
    public function setFechaRegistroRude($fechaRegistroRude)
    {
        $this->fechaRegistroRude = $fechaRegistroRude;
    
        return $this;
    }

    /**
     * Get fechaRegistroRude
     *
     * @return \DateTime 
     */
    public function getFechaRegistroRude()
    {
        return $this->fechaRegistroRude;
    }

    /**
     * Set localidad
     *
     * @param string $localidad
     * @return Rude
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
     * Set respuestaPago
     *
     * @param boolean $respuestaPago
     * @return Rude
     */
    public function setRespuestaPago($respuestaPago)
    {
        $this->respuestaPago = $respuestaPago;
    
        return $this;
    }

    /**
     * Get respuestaPago
     *
     * @return boolean 
     */
    public function getRespuestaPago()
    {
        return $this->respuestaPago;
    }

    /**
     * Set tieneCi
     *
     * @param boolean $tieneCi
     * @return Rude
     */
    public function setTieneCi($tieneCi)
    {
        $this->tieneCi = $tieneCi;
    
        return $this;
    }

    /**
     * Get tieneCi
     *
     * @return boolean 
     */
    public function getTieneCi()
    {
        return $this->tieneCi;
    }

    /**
     * Set tienePasaporte
     *
     * @param boolean $tienePasaporte
     * @return Rude
     */
    public function setTienePasaporte($tienePasaporte)
    {
        $this->tienePasaporte = $tienePasaporte;
    
        return $this;
    }

    /**
     * Get tienePasaporte
     *
     * @return boolean 
     */
    public function getTienePasaporte()
    {
        return $this->tienePasaporte;
    }

    /**
     * Set tieneCarnetDiscapacidad
     *
     * @param boolean $tieneCarnetDiscapacidad
     * @return Rude
     */
    public function setTieneCarnetDiscapacidad($tieneCarnetDiscapacidad)
    {
        $this->tieneCarnetDiscapacidad = $tieneCarnetDiscapacidad;
    
        return $this;
    }

    /**
     * Get tieneCarnetDiscapacidad
     *
     * @return boolean 
     */
    public function getTieneCarnetDiscapacidad()
    {
        return $this->tieneCarnetDiscapacidad;
    }

    /**
     * Set tiempoLlegadaHoras
     *
     * @param string $tiempoLlegadaHoras
     * @return Rude
     */
    public function setTiempoLlegadaHoras($tiempoLlegadaHoras)
    {
        $this->tiempoLlegadaHoras = $tiempoLlegadaHoras;
    
        return $this;
    }

    /**
     * Get tiempoLlegadaHoras
     *
     * @return string 
     */
    public function getTiempoLlegadaHoras()
    {
        return $this->tiempoLlegadaHoras;
    }

    /**
     * Set tiempoLlegadaMinutos
     *
     * @param string $tiempoLlegadaMinutos
     * @return Rude
     */
    public function setTiempoLlegadaMinutos($tiempoLlegadaMinutos)
    {
        $this->tiempoLlegadaMinutos = $tiempoLlegadaMinutos;
    
        return $this;
    }

    /**
     * Get tiempoLlegadaMinutos
     *
     * @return string 
     */
    public function getTiempoLlegadaMinutos()
    {
        return $this->tiempoLlegadaMinutos;
    }

    /**
     * Set nacionOriginariaTipo
     *
     * @param \Sie\AppWebBundle\Entity\NacionOriginariaTipo $nacionOriginariaTipo
     * @return Rude
     */
    public function setNacionOriginariaTipo(\Sie\AppWebBundle\Entity\NacionOriginariaTipo $nacionOriginariaTipo = null)
    {
        $this->nacionOriginariaTipo = $nacionOriginariaTipo;
    
        return $this;
    }

    /**
     * Get nacionOriginariaTipo
     *
     * @return \Sie\AppWebBundle\Entity\NacionOriginariaTipo 
     */
    public function getNacionOriginariaTipo()
    {
        return $this->nacionOriginariaTipo;
    }

    /**
     * Set viviendaOcupaTipo
     *
     * @param \Sie\AppWebBundle\Entity\ViviendaOcupaTipo $viviendaOcupaTipo
     * @return Rude
     */
    public function setViviendaOcupaTipo(\Sie\AppWebBundle\Entity\ViviendaOcupaTipo $viviendaOcupaTipo = null)
    {
        $this->viviendaOcupaTipo = $viviendaOcupaTipo;
    
        return $this;
    }

    /**
     * Get viviendaOcupaTipo
     *
     * @return \Sie\AppWebBundle\Entity\ViviendaOcupaTipo 
     */
    public function getViviendaOcupaTipo()
    {
        return $this->viviendaOcupaTipo;
    }

    /**
     * Set modalidadEstudioTipo
     *
     * @param \Sie\AppWebBundle\Entity\ModalidadEstudioTipo $modalidadEstudioTipo
     * @return Rude
     */
    public function setModalidadEstudioTipo(\Sie\AppWebBundle\Entity\ModalidadEstudioTipo $modalidadEstudioTipo = null)
    {
        $this->modalidadEstudioTipo = $modalidadEstudioTipo;
    
        return $this;
    }

    /**
     * Get modalidadEstudioTipo
     *
     * @return \Sie\AppWebBundle\Entity\ModalidadEstudioTipo 
     */
    public function getModalidadEstudioTipo()
    {
        return $this->modalidadEstudioTipo;
    }

    /**
     * Set procedenciaTipo
     *
     * @param \Sie\AppWebBundle\Entity\ProcedenciaTipo $procedenciaTipo
     * @return Rude
     */
    public function setProcedenciaTipo(\Sie\AppWebBundle\Entity\ProcedenciaTipo $procedenciaTipo = null)
    {
        $this->procedenciaTipo = $procedenciaTipo;
    
        return $this;
    }

    /**
     * Get procedenciaTipo
     *
     * @return \Sie\AppWebBundle\Entity\ProcedenciaTipo 
     */
    public function getProcedenciaTipo()
    {
        return $this->procedenciaTipo;
    }

    /**
     * Set cantidadCentroSaludTipo
     *
     * @param \Sie\AppWebBundle\Entity\CantidadCentroSaludTipo $cantidadCentroSaludTipo
     * @return Rude
     */
    public function setCantidadCentroSaludTipo(\Sie\AppWebBundle\Entity\CantidadCentroSaludTipo $cantidadCentroSaludTipo = null)
    {
        $this->cantidadCentroSaludTipo = $cantidadCentroSaludTipo;
    
        return $this;
    }

    /**
     * Get cantidadCentroSaludTipo
     *
     * @return \Sie\AppWebBundle\Entity\CantidadCentroSaludTipo 
     */
    public function getCantidadCentroSaludTipo()
    {
        return $this->cantidadCentroSaludTipo;
    }

    /**
     * Set frecuenciaUsoInternetTipo
     *
     * @param \Sie\AppWebBundle\Entity\FrecuenciaUsoInternetTipo $frecuenciaUsoInternetTipo
     * @return Rude
     */
    public function setFrecuenciaUsoInternetTipo(\Sie\AppWebBundle\Entity\FrecuenciaUsoInternetTipo $frecuenciaUsoInternetTipo = null)
    {
        $this->frecuenciaUsoInternetTipo = $frecuenciaUsoInternetTipo;
    
        return $this;
    }

    /**
     * Get frecuenciaUsoInternetTipo
     *
     * @return \Sie\AppWebBundle\Entity\FrecuenciaUsoInternetTipo 
     */
    public function getFrecuenciaUsoInternetTipo()
    {
        return $this->frecuenciaUsoInternetTipo;
    }

    /**
     * Set frecuenciaTrabajoTipo
     *
     * @param \Sie\AppWebBundle\Entity\FrecuenciaTrabajoTipo $frecuenciaTrabajoTipo
     * @return Rude
     */
    public function setFrecuenciaTrabajoTipo(\Sie\AppWebBundle\Entity\FrecuenciaTrabajoTipo $frecuenciaTrabajoTipo = null)
    {
        $this->frecuenciaTrabajoTipo = $frecuenciaTrabajoTipo;
    
        return $this;
    }

    /**
     * Get frecuenciaTrabajoTipo
     *
     * @return \Sie\AppWebBundle\Entity\FrecuenciaTrabajoTipo 
     */
    public function getFrecuenciaTrabajoTipo()
    {
        return $this->frecuenciaTrabajoTipo;
    }

    /**
     * Set servicioMilitarTipo
     *
     * @param \Sie\AppWebBundle\Entity\ServicioMilitarTipo $servicioMilitarTipo
     * @return Rude
     */
    public function setServicioMilitarTipo(\Sie\AppWebBundle\Entity\ServicioMilitarTipo $servicioMilitarTipo = null)
    {
        $this->servicioMilitarTipo = $servicioMilitarTipo;
    
        return $this;
    }

    /**
     * Get servicioMilitarTipo
     *
     * @return \Sie\AppWebBundle\Entity\ServicioMilitarTipo 
     */
    public function getServicioMilitarTipo()
    {
        return $this->servicioMilitarTipo;
    }

    /**
     * Set institucioneducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo
     * @return Rude
     */
    public function setInstitucioneducativaTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo = null)
    {
        $this->institucioneducativaTipo = $institucioneducativaTipo;
    
        return $this;
    }

    /**
     * Get institucioneducativaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaTipo 
     */
    public function getInstitucioneducativaTipo()
    {
        return $this->institucioneducativaTipo;
    }

    /**
     * Set viveHabitualmenteTipo
     *
     * @param \Sie\AppWebBundle\Entity\ViveHabitualmenteTipo $viveHabitualmenteTipo
     * @return Rude
     */
    public function setViveHabitualmenteTipo(\Sie\AppWebBundle\Entity\ViveHabitualmenteTipo $viveHabitualmenteTipo = null)
    {
        $this->viveHabitualmenteTipo = $viveHabitualmenteTipo;
    
        return $this;
    }

    /**
     * Get viveHabitualmenteTipo
     *
     * @return \Sie\AppWebBundle\Entity\ViveHabitualmenteTipo 
     */
    public function getViveHabitualmenteTipo()
    {
        return $this->viveHabitualmenteTipo;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return Rude
     */
    public function setEstudianteInscripcion(\Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion = null)
    {
        $this->estudianteInscripcion = $estudianteInscripcion;
    
        return $this;
    }

    /**
     * Get estudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcion 
     */
    public function getEstudianteInscripcion()
    {
        return $this->estudianteInscripcion;
    }

    /**
     * Set municipioLugarTipo
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $municipioLugarTipo
     * @return Rude
     */
    public function setMunicipioLugarTipo(\Sie\AppWebBundle\Entity\LugarTipo $municipioLugarTipo = null)
    {
        $this->municipioLugarTipo = $municipioLugarTipo;
    
        return $this;
    }

    /**
     * Get municipioLugarTipo
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getMunicipioLugarTipo()
    {
        return $this->municipioLugarTipo;
    }
    /**
     * @var integer
     */
    private $registroFinalizado;


    /**
     * Set registroFinalizado
     *
     * @param integer $registroFinalizado
     * @return Rude
     */
    public function setRegistroFinalizado($registroFinalizado)
    {
        $this->registroFinalizado = $registroFinalizado;
    
        return $this;
    }

    /**
     * Get registroFinalizado
     *
     * @return integer 
     */
    public function getRegistroFinalizado()
    {
        return $this->registroFinalizado;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $localidadLugarTipo;


    /**
     * Set localidadLugarTipo
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $localidadLugarTipo
     * @return Rude
     */
    public function setLocalidadLugarTipo(\Sie\AppWebBundle\Entity\LugarTipo $localidadLugarTipo = null)
    {
        $this->localidadLugarTipo = $localidadLugarTipo;
    
        return $this;
    }

    /**
     * Get localidadLugarTipo
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getLocalidadLugarTipo()
    {
        return $this->localidadLugarTipo;
    }
}
