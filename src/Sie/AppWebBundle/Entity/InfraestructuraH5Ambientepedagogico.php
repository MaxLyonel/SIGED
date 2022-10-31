<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5Ambientepedagogico
 */
class InfraestructuraH5Ambientepedagogico
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $n51AmbienteAnchoMts;

    /**
     * @var string
     */
    private $n51AmbienteLargoMts;

    /**
     * @var string
     */
    private $n51AmbienteAltoMts;

    /**
     * @var integer
     */
    private $n51CapacidadAmbiente;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var boolean
     */
    private $n51EsUsoAmbiente;

    /**
     * @var integer
     */
    private $n51EspecialidadTipoId;

    /**
     * @var string
     */
    private $n51TalleresEspOtro;

    /**
     * @var boolean
     */
    private $n51EsUsoUniversal;

    /**
     * @var boolean
     */
    private $n51EsAmbienteCieloFal;

    /**
     * @var boolean
     */
    private $n51EsAmbientePuerta;

    /**
     * @var boolean
     */
    private $n51EsAmbienteVentana;

    /**
     * @var boolean
     */
    private $n51EsIluminacionElectrica;

    /**
     * @var boolean
     */
    private $n51EsIluminacionNatural;

    /**
     * @var boolean
     */
    private $n51EsAmbienteMuros;

    /**
     * @var boolean
     */
    private $n51EsAmbienteRevestimiento;

    /**
     * @var boolean
     */
    private $n51EsAmbientePiso;

    /**
     * @var boolean
     */
    private $n51EsAmbienteTecho;

    /**
     * @var string
     */
    private $n51NroBloque;

    /**
     * @var integer
     */
    private $n51NroPiso;

    /**
     * @var boolean
     */
    private $n51EsUsoBth;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral
     */
    private $estadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n51AmbientePisoCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo
     */
    private $n51AmbientePisoMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n51AmbienteRevestCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo
     */
    private $n51AmbienteRevestMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo
     */
    private $n51AmbienteMuroCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo
     */
    private $n51AmbienteMuroMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo
     */
    private $n51AmbienteVentanaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo
     */
    private $n512AbreTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo
     */
    private $n511SeguroTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n51AmbienteCieloFalTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenTalleresEspecializadosTipo
     */
    private $n51TalleresEspTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenAreaTipo
     */
    private $n51AreaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4OrgcurricularTipo
     */
    private $n15UsoOrgcurricularTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13CielorasoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13PinturaEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13PuertasEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13VentanasEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13TechoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13ParedEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13PisoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13SeguridadEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13IluminacionelectricaEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13IluminacionnaturalEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n13AmbienteEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteTipo
     */
    private $n51AmbienteTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica
     */
    private $infraestructuraJuridiccionGeografica;


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
     * Set n51AmbienteAnchoMts
     *
     * @param string $n51AmbienteAnchoMts
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbienteAnchoMts($n51AmbienteAnchoMts)
    {
        $this->n51AmbienteAnchoMts = $n51AmbienteAnchoMts;
    
        return $this;
    }

    /**
     * Get n51AmbienteAnchoMts
     *
     * @return string 
     */
    public function getN51AmbienteAnchoMts()
    {
        return $this->n51AmbienteAnchoMts;
    }

    /**
     * Set n51AmbienteLargoMts
     *
     * @param string $n51AmbienteLargoMts
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbienteLargoMts($n51AmbienteLargoMts)
    {
        $this->n51AmbienteLargoMts = $n51AmbienteLargoMts;
    
        return $this;
    }

    /**
     * Get n51AmbienteLargoMts
     *
     * @return string 
     */
    public function getN51AmbienteLargoMts()
    {
        return $this->n51AmbienteLargoMts;
    }

    /**
     * Set n51AmbienteAltoMts
     *
     * @param string $n51AmbienteAltoMts
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbienteAltoMts($n51AmbienteAltoMts)
    {
        $this->n51AmbienteAltoMts = $n51AmbienteAltoMts;
    
        return $this;
    }

    /**
     * Get n51AmbienteAltoMts
     *
     * @return string 
     */
    public function getN51AmbienteAltoMts()
    {
        return $this->n51AmbienteAltoMts;
    }

    /**
     * Set n51CapacidadAmbiente
     *
     * @param integer $n51CapacidadAmbiente
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51CapacidadAmbiente($n51CapacidadAmbiente)
    {
        $this->n51CapacidadAmbiente = $n51CapacidadAmbiente;
    
        return $this;
    }

    /**
     * Get n51CapacidadAmbiente
     *
     * @return integer 
     */
    public function getN51CapacidadAmbiente()
    {
        return $this->n51CapacidadAmbiente;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setFecharegistro($fecharegistro)
    {
        $this->fecharegistro = $fecharegistro;
    
        return $this;
    }

    /**
     * Get fecharegistro
     *
     * @return \DateTime 
     */
    public function getFecharegistro()
    {
        return $this->fecharegistro;
    }

    /**
     * Set n51EsUsoAmbiente
     *
     * @param boolean $n51EsUsoAmbiente
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsUsoAmbiente($n51EsUsoAmbiente)
    {
        $this->n51EsUsoAmbiente = $n51EsUsoAmbiente;
    
        return $this;
    }

    /**
     * Get n51EsUsoAmbiente
     *
     * @return boolean 
     */
    public function getN51EsUsoAmbiente()
    {
        return $this->n51EsUsoAmbiente;
    }

    /**
     * Set n51EspecialidadTipoId
     *
     * @param integer $n51EspecialidadTipoId
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EspecialidadTipoId($n51EspecialidadTipoId)
    {
        $this->n51EspecialidadTipoId = $n51EspecialidadTipoId;
    
        return $this;
    }

    /**
     * Get n51EspecialidadTipoId
     *
     * @return integer 
     */
    public function getN51EspecialidadTipoId()
    {
        return $this->n51EspecialidadTipoId;
    }

    /**
     * Set n51TalleresEspOtro
     *
     * @param string $n51TalleresEspOtro
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51TalleresEspOtro($n51TalleresEspOtro)
    {
        $this->n51TalleresEspOtro = $n51TalleresEspOtro;
    
        return $this;
    }

    /**
     * Get n51TalleresEspOtro
     *
     * @return string 
     */
    public function getN51TalleresEspOtro()
    {
        return $this->n51TalleresEspOtro;
    }

    /**
     * Set n51EsUsoUniversal
     *
     * @param boolean $n51EsUsoUniversal
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsUsoUniversal($n51EsUsoUniversal)
    {
        $this->n51EsUsoUniversal = $n51EsUsoUniversal;
    
        return $this;
    }

    /**
     * Get n51EsUsoUniversal
     *
     * @return boolean 
     */
    public function getN51EsUsoUniversal()
    {
        return $this->n51EsUsoUniversal;
    }

    /**
     * Set n51EsAmbienteCieloFal
     *
     * @param boolean $n51EsAmbienteCieloFal
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsAmbienteCieloFal($n51EsAmbienteCieloFal)
    {
        $this->n51EsAmbienteCieloFal = $n51EsAmbienteCieloFal;
    
        return $this;
    }

    /**
     * Get n51EsAmbienteCieloFal
     *
     * @return boolean 
     */
    public function getN51EsAmbienteCieloFal()
    {
        return $this->n51EsAmbienteCieloFal;
    }

    /**
     * Set n51EsAmbientePuerta
     *
     * @param boolean $n51EsAmbientePuerta
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsAmbientePuerta($n51EsAmbientePuerta)
    {
        $this->n51EsAmbientePuerta = $n51EsAmbientePuerta;
    
        return $this;
    }

    /**
     * Get n51EsAmbientePuerta
     *
     * @return boolean 
     */
    public function getN51EsAmbientePuerta()
    {
        return $this->n51EsAmbientePuerta;
    }

    /**
     * Set n51EsAmbienteVentana
     *
     * @param boolean $n51EsAmbienteVentana
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsAmbienteVentana($n51EsAmbienteVentana)
    {
        $this->n51EsAmbienteVentana = $n51EsAmbienteVentana;
    
        return $this;
    }

    /**
     * Get n51EsAmbienteVentana
     *
     * @return boolean 
     */
    public function getN51EsAmbienteVentana()
    {
        return $this->n51EsAmbienteVentana;
    }

    /**
     * Set n51EsIluminacionElectrica
     *
     * @param boolean $n51EsIluminacionElectrica
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsIluminacionElectrica($n51EsIluminacionElectrica)
    {
        $this->n51EsIluminacionElectrica = $n51EsIluminacionElectrica;
    
        return $this;
    }

    /**
     * Get n51EsIluminacionElectrica
     *
     * @return boolean 
     */
    public function getN51EsIluminacionElectrica()
    {
        return $this->n51EsIluminacionElectrica;
    }

    /**
     * Set n51EsIluminacionNatural
     *
     * @param boolean $n51EsIluminacionNatural
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsIluminacionNatural($n51EsIluminacionNatural)
    {
        $this->n51EsIluminacionNatural = $n51EsIluminacionNatural;
    
        return $this;
    }

    /**
     * Get n51EsIluminacionNatural
     *
     * @return boolean 
     */
    public function getN51EsIluminacionNatural()
    {
        return $this->n51EsIluminacionNatural;
    }

    /**
     * Set n51EsAmbienteMuros
     *
     * @param boolean $n51EsAmbienteMuros
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsAmbienteMuros($n51EsAmbienteMuros)
    {
        $this->n51EsAmbienteMuros = $n51EsAmbienteMuros;
    
        return $this;
    }

    /**
     * Get n51EsAmbienteMuros
     *
     * @return boolean 
     */
    public function getN51EsAmbienteMuros()
    {
        return $this->n51EsAmbienteMuros;
    }

    /**
     * Set n51EsAmbienteRevestimiento
     *
     * @param boolean $n51EsAmbienteRevestimiento
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsAmbienteRevestimiento($n51EsAmbienteRevestimiento)
    {
        $this->n51EsAmbienteRevestimiento = $n51EsAmbienteRevestimiento;
    
        return $this;
    }

    /**
     * Get n51EsAmbienteRevestimiento
     *
     * @return boolean 
     */
    public function getN51EsAmbienteRevestimiento()
    {
        return $this->n51EsAmbienteRevestimiento;
    }

    /**
     * Set n51EsAmbientePiso
     *
     * @param boolean $n51EsAmbientePiso
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsAmbientePiso($n51EsAmbientePiso)
    {
        $this->n51EsAmbientePiso = $n51EsAmbientePiso;
    
        return $this;
    }

    /**
     * Get n51EsAmbientePiso
     *
     * @return boolean 
     */
    public function getN51EsAmbientePiso()
    {
        return $this->n51EsAmbientePiso;
    }

    /**
     * Set n51EsAmbienteTecho
     *
     * @param boolean $n51EsAmbienteTecho
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsAmbienteTecho($n51EsAmbienteTecho)
    {
        $this->n51EsAmbienteTecho = $n51EsAmbienteTecho;
    
        return $this;
    }

    /**
     * Get n51EsAmbienteTecho
     *
     * @return boolean 
     */
    public function getN51EsAmbienteTecho()
    {
        return $this->n51EsAmbienteTecho;
    }

    /**
     * Set n51NroBloque
     *
     * @param string $n51NroBloque
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51NroBloque($n51NroBloque)
    {
        $this->n51NroBloque = $n51NroBloque;
    
        return $this;
    }

    /**
     * Get n51NroBloque
     *
     * @return string 
     */
    public function getN51NroBloque()
    {
        return $this->n51NroBloque;
    }

    /**
     * Set n51NroPiso
     *
     * @param integer $n51NroPiso
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51NroPiso($n51NroPiso)
    {
        $this->n51NroPiso = $n51NroPiso;
    
        return $this;
    }

    /**
     * Get n51NroPiso
     *
     * @return integer 
     */
    public function getN51NroPiso()
    {
        return $this->n51NroPiso;
    }

    /**
     * Set n51EsUsoBth
     *
     * @param boolean $n51EsUsoBth
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51EsUsoBth($n51EsUsoBth)
    {
        $this->n51EsUsoBth = $n51EsUsoBth;
    
        return $this;
    }

    /**
     * Get n51EsUsoBth
     *
     * @return boolean 
     */
    public function getN51EsUsoBth()
    {
        return $this->n51EsUsoBth;
    }

    /**
     * Set estadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral $estadoTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setEstadoTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral $estadoTipo = null)
    {
        $this->estadoTipo = $estadoTipo;
    
        return $this;
    }

    /**
     * Get estadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral 
     */
    public function getEstadoTipo()
    {
        return $this->estadoTipo;
    }

    /**
     * Set n51AmbientePisoCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n51AmbientePisoCaracTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbientePisoCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n51AmbientePisoCaracTipo = null)
    {
        $this->n51AmbientePisoCaracTipo = $n51AmbientePisoCaracTipo;
    
        return $this;
    }

    /**
     * Get n51AmbientePisoCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN51AmbientePisoCaracTipo()
    {
        return $this->n51AmbientePisoCaracTipo;
    }

    /**
     * Set n51AmbientePisoMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n51AmbientePisoMatTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbientePisoMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n51AmbientePisoMatTipo = null)
    {
        $this->n51AmbientePisoMatTipo = $n51AmbientePisoMatTipo;
    
        return $this;
    }

    /**
     * Get n51AmbientePisoMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo 
     */
    public function getN51AmbientePisoMatTipo()
    {
        return $this->n51AmbientePisoMatTipo;
    }

    /**
     * Set n51AmbienteRevestCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n51AmbienteRevestCaracTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbienteRevestCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n51AmbienteRevestCaracTipo = null)
    {
        $this->n51AmbienteRevestCaracTipo = $n51AmbienteRevestCaracTipo;
    
        return $this;
    }

    /**
     * Get n51AmbienteRevestCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN51AmbienteRevestCaracTipo()
    {
        return $this->n51AmbienteRevestCaracTipo;
    }

    /**
     * Set n51AmbienteRevestMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n51AmbienteRevestMatTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbienteRevestMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n51AmbienteRevestMatTipo = null)
    {
        $this->n51AmbienteRevestMatTipo = $n51AmbienteRevestMatTipo;
    
        return $this;
    }

    /**
     * Get n51AmbienteRevestMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo 
     */
    public function getN51AmbienteRevestMatTipo()
    {
        return $this->n51AmbienteRevestMatTipo;
    }

    /**
     * Set n51AmbienteMuroCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n51AmbienteMuroCaracTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbienteMuroCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n51AmbienteMuroCaracTipo = null)
    {
        $this->n51AmbienteMuroCaracTipo = $n51AmbienteMuroCaracTipo;
    
        return $this;
    }

    /**
     * Get n51AmbienteMuroCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo 
     */
    public function getN51AmbienteMuroCaracTipo()
    {
        return $this->n51AmbienteMuroCaracTipo;
    }

    /**
     * Set n51AmbienteMuroMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n51AmbienteMuroMatTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbienteMuroMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n51AmbienteMuroMatTipo = null)
    {
        $this->n51AmbienteMuroMatTipo = $n51AmbienteMuroMatTipo;
    
        return $this;
    }

    /**
     * Get n51AmbienteMuroMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo 
     */
    public function getN51AmbienteMuroMatTipo()
    {
        return $this->n51AmbienteMuroMatTipo;
    }

    /**
     * Set n51AmbienteVentanaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n51AmbienteVentanaTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbienteVentanaTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n51AmbienteVentanaTipo = null)
    {
        $this->n51AmbienteVentanaTipo = $n51AmbienteVentanaTipo;
    
        return $this;
    }

    /**
     * Get n51AmbienteVentanaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo 
     */
    public function getN51AmbienteVentanaTipo()
    {
        return $this->n51AmbienteVentanaTipo;
    }

    /**
     * Set n512AbreTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n512AbreTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN512AbreTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n512AbreTipo = null)
    {
        $this->n512AbreTipo = $n512AbreTipo;
    
        return $this;
    }

    /**
     * Get n512AbreTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo 
     */
    public function getN512AbreTipo()
    {
        return $this->n512AbreTipo;
    }

    /**
     * Set n511SeguroTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n511SeguroTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN511SeguroTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n511SeguroTipo = null)
    {
        $this->n511SeguroTipo = $n511SeguroTipo;
    
        return $this;
    }

    /**
     * Get n511SeguroTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo 
     */
    public function getN511SeguroTipo()
    {
        return $this->n511SeguroTipo;
    }

    /**
     * Set n51AmbienteCieloFalTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n51AmbienteCieloFalTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbienteCieloFalTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n51AmbienteCieloFalTipo = null)
    {
        $this->n51AmbienteCieloFalTipo = $n51AmbienteCieloFalTipo;
    
        return $this;
    }

    /**
     * Get n51AmbienteCieloFalTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN51AmbienteCieloFalTipo()
    {
        return $this->n51AmbienteCieloFalTipo;
    }

    /**
     * Set n51TalleresEspTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenTalleresEspecializadosTipo $n51TalleresEspTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51TalleresEspTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenTalleresEspecializadosTipo $n51TalleresEspTipo = null)
    {
        $this->n51TalleresEspTipo = $n51TalleresEspTipo;
    
        return $this;
    }

    /**
     * Get n51TalleresEspTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenTalleresEspecializadosTipo 
     */
    public function getN51TalleresEspTipo()
    {
        return $this->n51TalleresEspTipo;
    }

    /**
     * Set n51AreaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenAreaTipo $n51AreaTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AreaTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenAreaTipo $n51AreaTipo = null)
    {
        $this->n51AreaTipo = $n51AreaTipo;
    
        return $this;
    }

    /**
     * Get n51AreaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenAreaTipo 
     */
    public function getN51AreaTipo()
    {
        return $this->n51AreaTipo;
    }

    /**
     * Set n15UsoOrgcurricularTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4OrgcurricularTipo $n15UsoOrgcurricularTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN15UsoOrgcurricularTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4OrgcurricularTipo $n15UsoOrgcurricularTipo = null)
    {
        $this->n15UsoOrgcurricularTipo = $n15UsoOrgcurricularTipo;
    
        return $this;
    }

    /**
     * Get n15UsoOrgcurricularTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4OrgcurricularTipo 
     */
    public function getN15UsoOrgcurricularTipo()
    {
        return $this->n15UsoOrgcurricularTipo;
    }

    /**
     * Set n13CielorasoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13CielorasoEstadogeneralTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN13CielorasoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13CielorasoEstadogeneralTipo = null)
    {
        $this->n13CielorasoEstadogeneralTipo = $n13CielorasoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13CielorasoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13CielorasoEstadogeneralTipo()
    {
        return $this->n13CielorasoEstadogeneralTipo;
    }

    /**
     * Set n13PinturaEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PinturaEstadogeneralTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN13PinturaEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PinturaEstadogeneralTipo = null)
    {
        $this->n13PinturaEstadogeneralTipo = $n13PinturaEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13PinturaEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13PinturaEstadogeneralTipo()
    {
        return $this->n13PinturaEstadogeneralTipo;
    }

    /**
     * Set n13PuertasEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PuertasEstadogeneralTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN13PuertasEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PuertasEstadogeneralTipo = null)
    {
        $this->n13PuertasEstadogeneralTipo = $n13PuertasEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13PuertasEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13PuertasEstadogeneralTipo()
    {
        return $this->n13PuertasEstadogeneralTipo;
    }

    /**
     * Set n13VentanasEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13VentanasEstadogeneralTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN13VentanasEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13VentanasEstadogeneralTipo = null)
    {
        $this->n13VentanasEstadogeneralTipo = $n13VentanasEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13VentanasEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13VentanasEstadogeneralTipo()
    {
        return $this->n13VentanasEstadogeneralTipo;
    }

    /**
     * Set n13TechoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13TechoEstadogeneralTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN13TechoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13TechoEstadogeneralTipo = null)
    {
        $this->n13TechoEstadogeneralTipo = $n13TechoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13TechoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13TechoEstadogeneralTipo()
    {
        return $this->n13TechoEstadogeneralTipo;
    }

    /**
     * Set n13ParedEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13ParedEstadogeneralTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN13ParedEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13ParedEstadogeneralTipo = null)
    {
        $this->n13ParedEstadogeneralTipo = $n13ParedEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13ParedEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13ParedEstadogeneralTipo()
    {
        return $this->n13ParedEstadogeneralTipo;
    }

    /**
     * Set n13PisoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PisoEstadogeneralTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN13PisoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13PisoEstadogeneralTipo = null)
    {
        $this->n13PisoEstadogeneralTipo = $n13PisoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13PisoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13PisoEstadogeneralTipo()
    {
        return $this->n13PisoEstadogeneralTipo;
    }

    /**
     * Set n13SeguridadEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13SeguridadEstadogeneralTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN13SeguridadEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13SeguridadEstadogeneralTipo = null)
    {
        $this->n13SeguridadEstadogeneralTipo = $n13SeguridadEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13SeguridadEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13SeguridadEstadogeneralTipo()
    {
        return $this->n13SeguridadEstadogeneralTipo;
    }

    /**
     * Set n13IluminacionelectricaEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13IluminacionelectricaEstadogeneralTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN13IluminacionelectricaEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13IluminacionelectricaEstadogeneralTipo = null)
    {
        $this->n13IluminacionelectricaEstadogeneralTipo = $n13IluminacionelectricaEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13IluminacionelectricaEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13IluminacionelectricaEstadogeneralTipo()
    {
        return $this->n13IluminacionelectricaEstadogeneralTipo;
    }

    /**
     * Set n13IluminacionnaturalEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13IluminacionnaturalEstadogeneralTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN13IluminacionnaturalEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13IluminacionnaturalEstadogeneralTipo = null)
    {
        $this->n13IluminacionnaturalEstadogeneralTipo = $n13IluminacionnaturalEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13IluminacionnaturalEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13IluminacionnaturalEstadogeneralTipo()
    {
        return $this->n13IluminacionnaturalEstadogeneralTipo;
    }

    /**
     * Set n13AmbienteEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13AmbienteEstadogeneralTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN13AmbienteEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n13AmbienteEstadogeneralTipo = null)
    {
        $this->n13AmbienteEstadogeneralTipo = $n13AmbienteEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n13AmbienteEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN13AmbienteEstadogeneralTipo()
    {
        return $this->n13AmbienteEstadogeneralTipo;
    }

    /**
     * Set n51AmbienteTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteTipo $n51AmbienteTipo
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setN51AmbienteTipo(\Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteTipo $n51AmbienteTipo = null)
    {
        $this->n51AmbienteTipo = $n51AmbienteTipo;
    
        return $this;
    }

    /**
     * Get n51AmbienteTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5AmbienteTipo 
     */
    public function getN51AmbienteTipo()
    {
        return $this->n51AmbienteTipo;
    }

    /**
     * Set infraestructuraJuridiccionGeografica
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica $infraestructuraJuridiccionGeografica
     * @return InfraestructuraH5Ambientepedagogico
     */
    public function setInfraestructuraJuridiccionGeografica(\Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica $infraestructuraJuridiccionGeografica = null)
    {
        $this->infraestructuraJuridiccionGeografica = $infraestructuraJuridiccionGeografica;
    
        return $this;
    }

    /**
     * Get infraestructuraJuridiccionGeografica
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica 
     */
    public function getInfraestructuraJuridiccionGeografica()
    {
        return $this->infraestructuraJuridiccionGeografica;
    }
}
