<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionSocioeconomicoRegular
 */
class EstudianteInscripcionSocioeconomicoRegular
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $seccionivZona;

    /**
     * @var string
     */
    private $seccionivAvenida;

    /**
     * @var integer
     */
    private $seccionivNumero;

    /**
     * @var boolean
     */
    private $seccionvEstudianteEscentroSalud;

    /**
     * @var boolean
     */
    private $seccionvEstudianteEsdiscapacidadSensorialComunicacion;

    /**
     * @var boolean
     */
    private $seccionvEstudianteEsdiscapacidadMotriz;

    /**
     * @var boolean
     */
    private $seccionvEstudianteEsdiscapacidadMental;

    /**
     * @var string
     */
    private $seccionvEstudianteDiscapacidadOtro;

    /**
     * @var boolean
     */
    private $seccionvEstudianteEsEnergiaelectrica;

    /**
     * @var integer
     */
    private $seccionvEstudianteCuantosdiastrabajo;

    /**
     * @var boolean
     */
    private $seccionvEstudianteEspagoTrabajorealizado;

    /**
     * @var string
     */
    private $lugar;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $seccionivTelefonocelular;

    /**
     * @var integer
     */
    private $registroFinalizado;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $seccionvHablaNinezTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegTiempotransTipo
     */
    private $seccionvTiempotransTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegLlegaTipo
     */
    private $seccionvLlegaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegFrecInternetTipo
     */
    private $seccionvFrecInternetTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegActividadTipo
     */
    private $seccionvActividadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegDesagueTipo
     */
    private $seccionvDesagueTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo
     */
    private $seccionvAguaprovieneTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo
     */
    private $seccionvDiscapacidadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo
     */
    private $seccionvCantCentrosaludTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $seccionivLocalidadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;


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
     * Set seccionivZona
     *
     * @param string $seccionivZona
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionivZona($seccionivZona)
    {
        $this->seccionivZona = $seccionivZona;
    
        return $this;
    }

    /**
     * Get seccionivZona
     *
     * @return string 
     */
    public function getSeccionivZona()
    {
        return $this->seccionivZona;
    }

    /**
     * Set seccionivAvenida
     *
     * @param string $seccionivAvenida
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionivAvenida($seccionivAvenida)
    {
        $this->seccionivAvenida = $seccionivAvenida;
    
        return $this;
    }

    /**
     * Get seccionivAvenida
     *
     * @return string 
     */
    public function getSeccionivAvenida()
    {
        return $this->seccionivAvenida;
    }

    /**
     * Set seccionivNumero
     *
     * @param integer $seccionivNumero
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionivNumero($seccionivNumero)
    {
        $this->seccionivNumero = $seccionivNumero;
    
        return $this;
    }

    /**
     * Get seccionivNumero
     *
     * @return integer 
     */
    public function getSeccionivNumero()
    {
        return $this->seccionivNumero;
    }

    /**
     * Set seccionvEstudianteEscentroSalud
     *
     * @param boolean $seccionvEstudianteEscentroSalud
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvEstudianteEscentroSalud($seccionvEstudianteEscentroSalud)
    {
        $this->seccionvEstudianteEscentroSalud = $seccionvEstudianteEscentroSalud;
    
        return $this;
    }

    /**
     * Get seccionvEstudianteEscentroSalud
     *
     * @return boolean 
     */
    public function getSeccionvEstudianteEscentroSalud()
    {
        return $this->seccionvEstudianteEscentroSalud;
    }

    /**
     * Set seccionvEstudianteEsdiscapacidadSensorialComunicacion
     *
     * @param boolean $seccionvEstudianteEsdiscapacidadSensorialComunicacion
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvEstudianteEsdiscapacidadSensorialComunicacion($seccionvEstudianteEsdiscapacidadSensorialComunicacion)
    {
        $this->seccionvEstudianteEsdiscapacidadSensorialComunicacion = $seccionvEstudianteEsdiscapacidadSensorialComunicacion;
    
        return $this;
    }

    /**
     * Get seccionvEstudianteEsdiscapacidadSensorialComunicacion
     *
     * @return boolean 
     */
    public function getSeccionvEstudianteEsdiscapacidadSensorialComunicacion()
    {
        return $this->seccionvEstudianteEsdiscapacidadSensorialComunicacion;
    }

    /**
     * Set seccionvEstudianteEsdiscapacidadMotriz
     *
     * @param boolean $seccionvEstudianteEsdiscapacidadMotriz
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvEstudianteEsdiscapacidadMotriz($seccionvEstudianteEsdiscapacidadMotriz)
    {
        $this->seccionvEstudianteEsdiscapacidadMotriz = $seccionvEstudianteEsdiscapacidadMotriz;
    
        return $this;
    }

    /**
     * Get seccionvEstudianteEsdiscapacidadMotriz
     *
     * @return boolean 
     */
    public function getSeccionvEstudianteEsdiscapacidadMotriz()
    {
        return $this->seccionvEstudianteEsdiscapacidadMotriz;
    }

    /**
     * Set seccionvEstudianteEsdiscapacidadMental
     *
     * @param boolean $seccionvEstudianteEsdiscapacidadMental
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvEstudianteEsdiscapacidadMental($seccionvEstudianteEsdiscapacidadMental)
    {
        $this->seccionvEstudianteEsdiscapacidadMental = $seccionvEstudianteEsdiscapacidadMental;
    
        return $this;
    }

    /**
     * Get seccionvEstudianteEsdiscapacidadMental
     *
     * @return boolean 
     */
    public function getSeccionvEstudianteEsdiscapacidadMental()
    {
        return $this->seccionvEstudianteEsdiscapacidadMental;
    }

    /**
     * Set seccionvEstudianteDiscapacidadOtro
     *
     * @param string $seccionvEstudianteDiscapacidadOtro
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvEstudianteDiscapacidadOtro($seccionvEstudianteDiscapacidadOtro)
    {
        $this->seccionvEstudianteDiscapacidadOtro = $seccionvEstudianteDiscapacidadOtro;
    
        return $this;
    }

    /**
     * Get seccionvEstudianteDiscapacidadOtro
     *
     * @return string 
     */
    public function getSeccionvEstudianteDiscapacidadOtro()
    {
        return $this->seccionvEstudianteDiscapacidadOtro;
    }

    /**
     * Set seccionvEstudianteEsEnergiaelectrica
     *
     * @param boolean $seccionvEstudianteEsEnergiaelectrica
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvEstudianteEsEnergiaelectrica($seccionvEstudianteEsEnergiaelectrica)
    {
        $this->seccionvEstudianteEsEnergiaelectrica = $seccionvEstudianteEsEnergiaelectrica;
    
        return $this;
    }

    /**
     * Get seccionvEstudianteEsEnergiaelectrica
     *
     * @return boolean 
     */
    public function getSeccionvEstudianteEsEnergiaelectrica()
    {
        return $this->seccionvEstudianteEsEnergiaelectrica;
    }

    /**
     * Set seccionvEstudianteCuantosdiastrabajo
     *
     * @param integer $seccionvEstudianteCuantosdiastrabajo
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvEstudianteCuantosdiastrabajo($seccionvEstudianteCuantosdiastrabajo)
    {
        $this->seccionvEstudianteCuantosdiastrabajo = $seccionvEstudianteCuantosdiastrabajo;
    
        return $this;
    }

    /**
     * Get seccionvEstudianteCuantosdiastrabajo
     *
     * @return integer 
     */
    public function getSeccionvEstudianteCuantosdiastrabajo()
    {
        return $this->seccionvEstudianteCuantosdiastrabajo;
    }

    /**
     * Set seccionvEstudianteEspagoTrabajorealizado
     *
     * @param boolean $seccionvEstudianteEspagoTrabajorealizado
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvEstudianteEspagoTrabajorealizado($seccionvEstudianteEspagoTrabajorealizado)
    {
        $this->seccionvEstudianteEspagoTrabajorealizado = $seccionvEstudianteEspagoTrabajorealizado;
    
        return $this;
    }

    /**
     * Get seccionvEstudianteEspagoTrabajorealizado
     *
     * @return boolean 
     */
    public function getSeccionvEstudianteEspagoTrabajorealizado()
    {
        return $this->seccionvEstudianteEspagoTrabajorealizado;
    }

    /**
     * Set lugar
     *
     * @param string $lugar
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setLugar($lugar)
    {
        $this->lugar = $lugar;
    
        return $this;
    }

    /**
     * Get lugar
     *
     * @return string 
     */
    public function getLugar()
    {
        return $this->lugar;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteInscripcionSocioeconomicoRegular
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
     * @return EstudianteInscripcionSocioeconomicoRegular
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
     * Set seccionivTelefonocelular
     *
     * @param string $seccionivTelefonocelular
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionivTelefonocelular($seccionivTelefonocelular)
    {
        $this->seccionivTelefonocelular = $seccionivTelefonocelular;
    
        return $this;
    }

    /**
     * Get seccionivTelefonocelular
     *
     * @return string 
     */
    public function getSeccionivTelefonocelular()
    {
        return $this->seccionivTelefonocelular;
    }

    /**
     * Set registroFinalizado
     *
     * @param integer $registroFinalizado
     * @return EstudianteInscripcionSocioeconomicoRegular
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
     * Set seccionvHablaNinezTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $seccionvHablaNinezTipo
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvHablaNinezTipo(\Sie\AppWebBundle\Entity\IdiomaTipo $seccionvHablaNinezTipo = null)
    {
        $this->seccionvHablaNinezTipo = $seccionvHablaNinezTipo;
    
        return $this;
    }

    /**
     * Get seccionvHablaNinezTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getSeccionvHablaNinezTipo()
    {
        return $this->seccionvHablaNinezTipo;
    }

    /**
     * Set seccionvTiempotransTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegTiempotransTipo $seccionvTiempotransTipo
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvTiempotransTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegTiempotransTipo $seccionvTiempotransTipo = null)
    {
        $this->seccionvTiempotransTipo = $seccionvTiempotransTipo;
    
        return $this;
    }

    /**
     * Get seccionvTiempotransTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegTiempotransTipo 
     */
    public function getSeccionvTiempotransTipo()
    {
        return $this->seccionvTiempotransTipo;
    }

    /**
     * Set seccionvLlegaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegLlegaTipo $seccionvLlegaTipo
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvLlegaTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegLlegaTipo $seccionvLlegaTipo = null)
    {
        $this->seccionvLlegaTipo = $seccionvLlegaTipo;
    
        return $this;
    }

    /**
     * Get seccionvLlegaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegLlegaTipo 
     */
    public function getSeccionvLlegaTipo()
    {
        return $this->seccionvLlegaTipo;
    }

    /**
     * Set seccionvFrecInternetTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegFrecInternetTipo $seccionvFrecInternetTipo
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvFrecInternetTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegFrecInternetTipo $seccionvFrecInternetTipo = null)
    {
        $this->seccionvFrecInternetTipo = $seccionvFrecInternetTipo;
    
        return $this;
    }

    /**
     * Get seccionvFrecInternetTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegFrecInternetTipo 
     */
    public function getSeccionvFrecInternetTipo()
    {
        return $this->seccionvFrecInternetTipo;
    }

    /**
     * Set seccionvActividadTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegActividadTipo $seccionvActividadTipo
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvActividadTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegActividadTipo $seccionvActividadTipo = null)
    {
        $this->seccionvActividadTipo = $seccionvActividadTipo;
    
        return $this;
    }

    /**
     * Get seccionvActividadTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegActividadTipo 
     */
    public function getSeccionvActividadTipo()
    {
        return $this->seccionvActividadTipo;
    }

    /**
     * Set seccionvDesagueTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegDesagueTipo $seccionvDesagueTipo
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvDesagueTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegDesagueTipo $seccionvDesagueTipo = null)
    {
        $this->seccionvDesagueTipo = $seccionvDesagueTipo;
    
        return $this;
    }

    /**
     * Get seccionvDesagueTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegDesagueTipo 
     */
    public function getSeccionvDesagueTipo()
    {
        return $this->seccionvDesagueTipo;
    }

    /**
     * Set seccionvAguaprovieneTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo $seccionvAguaprovieneTipo
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvAguaprovieneTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo $seccionvAguaprovieneTipo = null)
    {
        $this->seccionvAguaprovieneTipo = $seccionvAguaprovieneTipo;
    
        return $this;
    }

    /**
     * Get seccionvAguaprovieneTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegAguaprovieneTipo 
     */
    public function getSeccionvAguaprovieneTipo()
    {
        return $this->seccionvAguaprovieneTipo;
    }

    /**
     * Set seccionvDiscapacidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo $seccionvDiscapacidadTipo
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvDiscapacidadTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo $seccionvDiscapacidadTipo = null)
    {
        $this->seccionvDiscapacidadTipo = $seccionvDiscapacidadTipo;
    
        return $this;
    }

    /**
     * Get seccionvDiscapacidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegDiscapacidadTipo 
     */
    public function getSeccionvDiscapacidadTipo()
    {
        return $this->seccionvDiscapacidadTipo;
    }

    /**
     * Set seccionvCantCentrosaludTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo $seccionvCantCentrosaludTipo
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionvCantCentrosaludTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo $seccionvCantCentrosaludTipo = null)
    {
        $this->seccionvCantCentrosaludTipo = $seccionvCantCentrosaludTipo;
    
        return $this;
    }

    /**
     * Get seccionvCantCentrosaludTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionSocioeconomicoRegCantCentrosaludTipo 
     */
    public function getSeccionvCantCentrosaludTipo()
    {
        return $this->seccionvCantCentrosaludTipo;
    }

    /**
     * Set seccionivLocalidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $seccionivLocalidadTipo
     * @return EstudianteInscripcionSocioeconomicoRegular
     */
    public function setSeccionivLocalidadTipo(\Sie\AppWebBundle\Entity\LugarTipo $seccionivLocalidadTipo = null)
    {
        $this->seccionivLocalidadTipo = $seccionivLocalidadTipo;
    
        return $this;
    }

    /**
     * Get seccionivLocalidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getSeccionivLocalidadTipo()
    {
        return $this->seccionivLocalidadTipo;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EstudianteInscripcionSocioeconomicoRegular
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
}
