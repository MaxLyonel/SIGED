<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH5AmbientepedagogicoDeportivo
 */
class InfraestructuraH5AmbientepedagogicoDeportivo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $n53EsRecreativo;

    /**
     * @var boolean
     */
    private $n53EsDeportivo;

    /**
     * @var boolean
     */
    private $n53EsCultural;

    /**
     * @var boolean
     */
    private $n53EsUsoUniversal;

    /**
     * @var string
     */
    private $n53AmbienteAreaMts;

    /**
     * @var integer
     */
    private $n53AmbienteCapacidad;

    /**
     * @var boolean
     */
    private $n53EsTechado;

    /**
     * @var boolean
     */
    private $n53EsIluminacionElectrica;

    /**
     * @var boolean
     */
    private $n53EsIluminacionNatural;

    /**
     * @var boolean
     */
    private $n53EsGraderia;

    /**
     * @var boolean
     */
    private $n53EsAmbienteCieloFal;

    /**
     * @var boolean
     */
    private $n53EsAmbientePuerta;

    /**
     * @var boolean
     */
    private $n53EsAmbienteVentana;

    /**
     * @var boolean
     */
    private $n53EsAmbienteMuros;

    /**
     * @var boolean
     */
    private $n53EsAmbienteRevestimiento;

    /**
     * @var boolean
     */
    private $n53EsAmbientePiso;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral
     */
    private $estadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo
     */
    private $n512AbreTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo
     */
    private $n53AmbientePisoMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo
     */
    private $n53AmbienteRevestMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo
     */
    private $n53AmbienteMuroCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo
     */
    private $n53AmbienteMuroMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo
     */
    private $n53AmbienteVentanaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo
     */
    private $n511SeguroTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n53AmbientePisoCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n53AmbienteRevestCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n53AmbienteCieloFalTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n53AmbienteGraderiaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivoAmbienteTipo
     */
    private $n53AmbienteTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica
     */
    private $infraestructuraJuridiccionGeografica;

    public function __toString(){
        return $this->n53AmbienteAreaMts;
    }
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
     * Set n53EsRecreativo
     *
     * @param boolean $n53EsRecreativo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsRecreativo($n53EsRecreativo)
    {
        $this->n53EsRecreativo = $n53EsRecreativo;
    
        return $this;
    }

    /**
     * Get n53EsRecreativo
     *
     * @return boolean 
     */
    public function getN53EsRecreativo()
    {
        return $this->n53EsRecreativo;
    }

    /**
     * Set n53EsDeportivo
     *
     * @param boolean $n53EsDeportivo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsDeportivo($n53EsDeportivo)
    {
        $this->n53EsDeportivo = $n53EsDeportivo;
    
        return $this;
    }

    /**
     * Get n53EsDeportivo
     *
     * @return boolean 
     */
    public function getN53EsDeportivo()
    {
        return $this->n53EsDeportivo;
    }

    /**
     * Set n53EsCultural
     *
     * @param boolean $n53EsCultural
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsCultural($n53EsCultural)
    {
        $this->n53EsCultural = $n53EsCultural;
    
        return $this;
    }

    /**
     * Get n53EsCultural
     *
     * @return boolean 
     */
    public function getN53EsCultural()
    {
        return $this->n53EsCultural;
    }

    /**
     * Set n53EsUsoUniversal
     *
     * @param boolean $n53EsUsoUniversal
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsUsoUniversal($n53EsUsoUniversal)
    {
        $this->n53EsUsoUniversal = $n53EsUsoUniversal;
    
        return $this;
    }

    /**
     * Get n53EsUsoUniversal
     *
     * @return boolean 
     */
    public function getN53EsUsoUniversal()
    {
        return $this->n53EsUsoUniversal;
    }

    /**
     * Set n53AmbienteAreaMts
     *
     * @param string $n53AmbienteAreaMts
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbienteAreaMts($n53AmbienteAreaMts)
    {
        $this->n53AmbienteAreaMts = $n53AmbienteAreaMts;
    
        return $this;
    }

    /**
     * Get n53AmbienteAreaMts
     *
     * @return string 
     */
    public function getN53AmbienteAreaMts()
    {
        return $this->n53AmbienteAreaMts;
    }

    /**
     * Set n53AmbienteCapacidad
     *
     * @param integer $n53AmbienteCapacidad
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbienteCapacidad($n53AmbienteCapacidad)
    {
        $this->n53AmbienteCapacidad = $n53AmbienteCapacidad;
    
        return $this;
    }

    /**
     * Get n53AmbienteCapacidad
     *
     * @return integer 
     */
    public function getN53AmbienteCapacidad()
    {
        return $this->n53AmbienteCapacidad;
    }

    /**
     * Set n53EsTechado
     *
     * @param boolean $n53EsTechado
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsTechado($n53EsTechado)
    {
        $this->n53EsTechado = $n53EsTechado;
    
        return $this;
    }

    /**
     * Get n53EsTechado
     *
     * @return boolean 
     */
    public function getN53EsTechado()
    {
        return $this->n53EsTechado;
    }

    /**
     * Set n53EsIluminacionElectrica
     *
     * @param boolean $n53EsIluminacionElectrica
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsIluminacionElectrica($n53EsIluminacionElectrica)
    {
        $this->n53EsIluminacionElectrica = $n53EsIluminacionElectrica;
    
        return $this;
    }

    /**
     * Get n53EsIluminacionElectrica
     *
     * @return boolean 
     */
    public function getN53EsIluminacionElectrica()
    {
        return $this->n53EsIluminacionElectrica;
    }

    /**
     * Set n53EsIluminacionNatural
     *
     * @param boolean $n53EsIluminacionNatural
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsIluminacionNatural($n53EsIluminacionNatural)
    {
        $this->n53EsIluminacionNatural = $n53EsIluminacionNatural;
    
        return $this;
    }

    /**
     * Get n53EsIluminacionNatural
     *
     * @return boolean 
     */
    public function getN53EsIluminacionNatural()
    {
        return $this->n53EsIluminacionNatural;
    }

    /**
     * Set n53EsGraderia
     *
     * @param boolean $n53EsGraderia
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsGraderia($n53EsGraderia)
    {
        $this->n53EsGraderia = $n53EsGraderia;
    
        return $this;
    }

    /**
     * Get n53EsGraderia
     *
     * @return boolean 
     */
    public function getN53EsGraderia()
    {
        return $this->n53EsGraderia;
    }

    /**
     * Set n53EsAmbienteCieloFal
     *
     * @param boolean $n53EsAmbienteCieloFal
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsAmbienteCieloFal($n53EsAmbienteCieloFal)
    {
        $this->n53EsAmbienteCieloFal = $n53EsAmbienteCieloFal;
    
        return $this;
    }

    /**
     * Get n53EsAmbienteCieloFal
     *
     * @return boolean 
     */
    public function getN53EsAmbienteCieloFal()
    {
        return $this->n53EsAmbienteCieloFal;
    }

    /**
     * Set n53EsAmbientePuerta
     *
     * @param boolean $n53EsAmbientePuerta
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsAmbientePuerta($n53EsAmbientePuerta)
    {
        $this->n53EsAmbientePuerta = $n53EsAmbientePuerta;
    
        return $this;
    }

    /**
     * Get n53EsAmbientePuerta
     *
     * @return boolean 
     */
    public function getN53EsAmbientePuerta()
    {
        return $this->n53EsAmbientePuerta;
    }

    /**
     * Set n53EsAmbienteVentana
     *
     * @param boolean $n53EsAmbienteVentana
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsAmbienteVentana($n53EsAmbienteVentana)
    {
        $this->n53EsAmbienteVentana = $n53EsAmbienteVentana;
    
        return $this;
    }

    /**
     * Get n53EsAmbienteVentana
     *
     * @return boolean 
     */
    public function getN53EsAmbienteVentana()
    {
        return $this->n53EsAmbienteVentana;
    }

    /**
     * Set n53EsAmbienteMuros
     *
     * @param boolean $n53EsAmbienteMuros
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsAmbienteMuros($n53EsAmbienteMuros)
    {
        $this->n53EsAmbienteMuros = $n53EsAmbienteMuros;
    
        return $this;
    }

    /**
     * Get n53EsAmbienteMuros
     *
     * @return boolean 
     */
    public function getN53EsAmbienteMuros()
    {
        return $this->n53EsAmbienteMuros;
    }

    /**
     * Set n53EsAmbienteRevestimiento
     *
     * @param boolean $n53EsAmbienteRevestimiento
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsAmbienteRevestimiento($n53EsAmbienteRevestimiento)
    {
        $this->n53EsAmbienteRevestimiento = $n53EsAmbienteRevestimiento;
    
        return $this;
    }

    /**
     * Get n53EsAmbienteRevestimiento
     *
     * @return boolean 
     */
    public function getN53EsAmbienteRevestimiento()
    {
        return $this->n53EsAmbienteRevestimiento;
    }

    /**
     * Set n53EsAmbientePiso
     *
     * @param boolean $n53EsAmbientePiso
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53EsAmbientePiso($n53EsAmbientePiso)
    {
        $this->n53EsAmbientePiso = $n53EsAmbientePiso;
    
        return $this;
    }

    /**
     * Get n53EsAmbientePiso
     *
     * @return boolean 
     */
    public function getN53EsAmbientePiso()
    {
        return $this->n53EsAmbientePiso;
    }

    /**
     * Set estadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral $estadoTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
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
     * Set n512AbreTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n512AbreTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
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
     * Set n53AmbientePisoMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n53AmbientePisoMatTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbientePisoMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n53AmbientePisoMatTipo = null)
    {
        $this->n53AmbientePisoMatTipo = $n53AmbientePisoMatTipo;
    
        return $this;
    }

    /**
     * Get n53AmbientePisoMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo 
     */
    public function getN53AmbientePisoMatTipo()
    {
        return $this->n53AmbientePisoMatTipo;
    }

    /**
     * Set n53AmbienteRevestMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n53AmbienteRevestMatTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbienteRevestMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n53AmbienteRevestMatTipo = null)
    {
        $this->n53AmbienteRevestMatTipo = $n53AmbienteRevestMatTipo;
    
        return $this;
    }

    /**
     * Get n53AmbienteRevestMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo 
     */
    public function getN53AmbienteRevestMatTipo()
    {
        return $this->n53AmbienteRevestMatTipo;
    }

    /**
     * Set n53AmbienteMuroCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n53AmbienteMuroCaracTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbienteMuroCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n53AmbienteMuroCaracTipo = null)
    {
        $this->n53AmbienteMuroCaracTipo = $n53AmbienteMuroCaracTipo;
    
        return $this;
    }

    /**
     * Get n53AmbienteMuroCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo 
     */
    public function getN53AmbienteMuroCaracTipo()
    {
        return $this->n53AmbienteMuroCaracTipo;
    }

    /**
     * Set n53AmbienteMuroMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n53AmbienteMuroMatTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbienteMuroMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n53AmbienteMuroMatTipo = null)
    {
        $this->n53AmbienteMuroMatTipo = $n53AmbienteMuroMatTipo;
    
        return $this;
    }

    /**
     * Get n53AmbienteMuroMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo 
     */
    public function getN53AmbienteMuroMatTipo()
    {
        return $this->n53AmbienteMuroMatTipo;
    }

    /**
     * Set n53AmbienteVentanaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n53AmbienteVentanaTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbienteVentanaTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n53AmbienteVentanaTipo = null)
    {
        $this->n53AmbienteVentanaTipo = $n53AmbienteVentanaTipo;
    
        return $this;
    }

    /**
     * Get n53AmbienteVentanaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo 
     */
    public function getN53AmbienteVentanaTipo()
    {
        return $this->n53AmbienteVentanaTipo;
    }

    /**
     * Set n511SeguroTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n511SeguroTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
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
     * Set n53AmbientePisoCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n53AmbientePisoCaracTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbientePisoCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n53AmbientePisoCaracTipo = null)
    {
        $this->n53AmbientePisoCaracTipo = $n53AmbientePisoCaracTipo;
    
        return $this;
    }

    /**
     * Get n53AmbientePisoCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN53AmbientePisoCaracTipo()
    {
        return $this->n53AmbientePisoCaracTipo;
    }

    /**
     * Set n53AmbienteRevestCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n53AmbienteRevestCaracTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbienteRevestCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n53AmbienteRevestCaracTipo = null)
    {
        $this->n53AmbienteRevestCaracTipo = $n53AmbienteRevestCaracTipo;
    
        return $this;
    }

    /**
     * Get n53AmbienteRevestCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN53AmbienteRevestCaracTipo()
    {
        return $this->n53AmbienteRevestCaracTipo;
    }

    /**
     * Set n53AmbienteCieloFalTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n53AmbienteCieloFalTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbienteCieloFalTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n53AmbienteCieloFalTipo = null)
    {
        $this->n53AmbienteCieloFalTipo = $n53AmbienteCieloFalTipo;
    
        return $this;
    }

    /**
     * Get n53AmbienteCieloFalTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN53AmbienteCieloFalTipo()
    {
        return $this->n53AmbienteCieloFalTipo;
    }

    /**
     * Set n53AmbienteGraderiaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n53AmbienteGraderiaTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbienteGraderiaTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n53AmbienteGraderiaTipo = null)
    {
        $this->n53AmbienteGraderiaTipo = $n53AmbienteGraderiaTipo;
    
        return $this;
    }

    /**
     * Get n53AmbienteGraderiaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN53AmbienteGraderiaTipo()
    {
        return $this->n53AmbienteGraderiaTipo;
    }

    /**
     * Set n53AmbienteTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivoAmbienteTipo $n53AmbienteTipo
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
     */
    public function setN53AmbienteTipo(\Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivoAmbienteTipo $n53AmbienteTipo = null)
    {
        $this->n53AmbienteTipo = $n53AmbienteTipo;
    
        return $this;
    }

    /**
     * Get n53AmbienteTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH5AmbientepedagogicoDeportivoAmbienteTipo 
     */
    public function getN53AmbienteTipo()
    {
        return $this->n53AmbienteTipo;
    }

    /**
     * Set infraestructuraJuridiccionGeografica
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica $infraestructuraJuridiccionGeografica
     * @return InfraestructuraH5AmbientepedagogicoDeportivo
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
