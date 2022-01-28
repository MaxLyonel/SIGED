<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH6AmbienteadministrativoAmbiente
 */
class InfraestructuraH6AmbienteadministrativoAmbiente
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n11NumeroBueno;

    /**
     * @var integer
     */
    private $n11NumeroRegular;

    /**
     * @var integer
     */
    private $n11NumeroMalo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var string
     */
    private $n61AmbienteAreaAdm;

    /**
     * @var boolean
     */
    private $n61EsAmbienteTecho;

    /**
     * @var boolean
     */
    private $n61EsAmbienteCieloFal;

    /**
     * @var boolean
     */
    private $n61EsAmbientePuerta;

    /**
     * @var boolean
     */
    private $n61EsAmbienteVentana;

    /**
     * @var boolean
     */
    private $n61EsAmbienteMuros;

    /**
     * @var boolean
     */
    private $n61EsAmbienteRevestimiento;

    /**
     * @var boolean
     */
    private $n61EsAmbientePiso;

    /**
     * @var integer
     */
    private $n612AbreTipoId;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral
     */
    private $estadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo
     */
    private $n61AmbientePisoMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo
     */
    private $n61AmbienteRevestMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo
     */
    private $n61AmbienteMuroCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo
     */
    private $n61AmbienteMuroMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo
     */
    private $n61AmbienteVentanaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo
     */
    private $n611SeguroTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n61AmbientePisoCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n61AmbienteRevestCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n61AmbienteCieloFalTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoTipo
     */
    private $n11AmbienteadministrativoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo
     */
    private $infraestructuraH6Ambienteadministrativo;


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
     * Set n11NumeroBueno
     *
     * @param integer $n11NumeroBueno
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN11NumeroBueno($n11NumeroBueno)
    {
        $this->n11NumeroBueno = $n11NumeroBueno;
    
        return $this;
    }

    /**
     * Get n11NumeroBueno
     *
     * @return integer 
     */
    public function getN11NumeroBueno()
    {
        return $this->n11NumeroBueno;
    }

    /**
     * Set n11NumeroRegular
     *
     * @param integer $n11NumeroRegular
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN11NumeroRegular($n11NumeroRegular)
    {
        $this->n11NumeroRegular = $n11NumeroRegular;
    
        return $this;
    }

    /**
     * Get n11NumeroRegular
     *
     * @return integer 
     */
    public function getN11NumeroRegular()
    {
        return $this->n11NumeroRegular;
    }

    /**
     * Set n11NumeroMalo
     *
     * @param integer $n11NumeroMalo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN11NumeroMalo($n11NumeroMalo)
    {
        $this->n11NumeroMalo = $n11NumeroMalo;
    
        return $this;
    }

    /**
     * Get n11NumeroMalo
     *
     * @return integer 
     */
    public function getN11NumeroMalo()
    {
        return $this->n11NumeroMalo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
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
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
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
     * Set n61AmbienteAreaAdm
     *
     * @param string $n61AmbienteAreaAdm
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61AmbienteAreaAdm($n61AmbienteAreaAdm)
    {
        $this->n61AmbienteAreaAdm = $n61AmbienteAreaAdm;
    
        return $this;
    }

    /**
     * Get n61AmbienteAreaAdm
     *
     * @return string 
     */
    public function getN61AmbienteAreaAdm()
    {
        return $this->n61AmbienteAreaAdm;
    }

    /**
     * Set n61EsAmbienteTecho
     *
     * @param boolean $n61EsAmbienteTecho
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61EsAmbienteTecho($n61EsAmbienteTecho)
    {
        $this->n61EsAmbienteTecho = $n61EsAmbienteTecho;
    
        return $this;
    }

    /**
     * Get n61EsAmbienteTecho
     *
     * @return boolean 
     */
    public function getN61EsAmbienteTecho()
    {
        return $this->n61EsAmbienteTecho;
    }

    /**
     * Set n61EsAmbienteCieloFal
     *
     * @param boolean $n61EsAmbienteCieloFal
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61EsAmbienteCieloFal($n61EsAmbienteCieloFal)
    {
        $this->n61EsAmbienteCieloFal = $n61EsAmbienteCieloFal;
    
        return $this;
    }

    /**
     * Get n61EsAmbienteCieloFal
     *
     * @return boolean 
     */
    public function getN61EsAmbienteCieloFal()
    {
        return $this->n61EsAmbienteCieloFal;
    }

    /**
     * Set n61EsAmbientePuerta
     *
     * @param boolean $n61EsAmbientePuerta
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61EsAmbientePuerta($n61EsAmbientePuerta)
    {
        $this->n61EsAmbientePuerta = $n61EsAmbientePuerta;
    
        return $this;
    }

    /**
     * Get n61EsAmbientePuerta
     *
     * @return boolean 
     */
    public function getN61EsAmbientePuerta()
    {
        return $this->n61EsAmbientePuerta;
    }

    /**
     * Set n61EsAmbienteVentana
     *
     * @param boolean $n61EsAmbienteVentana
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61EsAmbienteVentana($n61EsAmbienteVentana)
    {
        $this->n61EsAmbienteVentana = $n61EsAmbienteVentana;
    
        return $this;
    }

    /**
     * Get n61EsAmbienteVentana
     *
     * @return boolean 
     */
    public function getN61EsAmbienteVentana()
    {
        return $this->n61EsAmbienteVentana;
    }

    /**
     * Set n61EsAmbienteMuros
     *
     * @param boolean $n61EsAmbienteMuros
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61EsAmbienteMuros($n61EsAmbienteMuros)
    {
        $this->n61EsAmbienteMuros = $n61EsAmbienteMuros;
    
        return $this;
    }

    /**
     * Get n61EsAmbienteMuros
     *
     * @return boolean 
     */
    public function getN61EsAmbienteMuros()
    {
        return $this->n61EsAmbienteMuros;
    }

    /**
     * Set n61EsAmbienteRevestimiento
     *
     * @param boolean $n61EsAmbienteRevestimiento
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61EsAmbienteRevestimiento($n61EsAmbienteRevestimiento)
    {
        $this->n61EsAmbienteRevestimiento = $n61EsAmbienteRevestimiento;
    
        return $this;
    }

    /**
     * Get n61EsAmbienteRevestimiento
     *
     * @return boolean 
     */
    public function getN61EsAmbienteRevestimiento()
    {
        return $this->n61EsAmbienteRevestimiento;
    }

    /**
     * Set n61EsAmbientePiso
     *
     * @param boolean $n61EsAmbientePiso
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61EsAmbientePiso($n61EsAmbientePiso)
    {
        $this->n61EsAmbientePiso = $n61EsAmbientePiso;
    
        return $this;
    }

    /**
     * Get n61EsAmbientePiso
     *
     * @return boolean 
     */
    public function getN61EsAmbientePiso()
    {
        return $this->n61EsAmbientePiso;
    }

    /**
     * Set n612AbreTipoId
     *
     * @param integer $n612AbreTipoId
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN612AbreTipoId($n612AbreTipoId)
    {
        $this->n612AbreTipoId = $n612AbreTipoId;
    
        return $this;
    }

    /**
     * Get n612AbreTipoId
     *
     * @return integer 
     */
    public function getN612AbreTipoId()
    {
        return $this->n612AbreTipoId;
    }

    /**
     * Set estadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral $estadoTipo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
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
     * Set n61AmbientePisoMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n61AmbientePisoMatTipo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61AmbientePisoMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n61AmbientePisoMatTipo = null)
    {
        $this->n61AmbientePisoMatTipo = $n61AmbientePisoMatTipo;
    
        return $this;
    }

    /**
     * Get n61AmbientePisoMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo 
     */
    public function getN61AmbientePisoMatTipo()
    {
        return $this->n61AmbientePisoMatTipo;
    }

    /**
     * Set n61AmbienteRevestMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n61AmbienteRevestMatTipo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61AmbienteRevestMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n61AmbienteRevestMatTipo = null)
    {
        $this->n61AmbienteRevestMatTipo = $n61AmbienteRevestMatTipo;
    
        return $this;
    }

    /**
     * Get n61AmbienteRevestMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo 
     */
    public function getN61AmbienteRevestMatTipo()
    {
        return $this->n61AmbienteRevestMatTipo;
    }

    /**
     * Set n61AmbienteMuroCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n61AmbienteMuroCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61AmbienteMuroCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n61AmbienteMuroCaracTipo = null)
    {
        $this->n61AmbienteMuroCaracTipo = $n61AmbienteMuroCaracTipo;
    
        return $this;
    }

    /**
     * Get n61AmbienteMuroCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo 
     */
    public function getN61AmbienteMuroCaracTipo()
    {
        return $this->n61AmbienteMuroCaracTipo;
    }

    /**
     * Set n61AmbienteMuroMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n61AmbienteMuroMatTipo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61AmbienteMuroMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n61AmbienteMuroMatTipo = null)
    {
        $this->n61AmbienteMuroMatTipo = $n61AmbienteMuroMatTipo;
    
        return $this;
    }

    /**
     * Get n61AmbienteMuroMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo 
     */
    public function getN61AmbienteMuroMatTipo()
    {
        return $this->n61AmbienteMuroMatTipo;
    }

    /**
     * Set n61AmbienteVentanaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n61AmbienteVentanaTipo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61AmbienteVentanaTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n61AmbienteVentanaTipo = null)
    {
        $this->n61AmbienteVentanaTipo = $n61AmbienteVentanaTipo;
    
        return $this;
    }

    /**
     * Get n61AmbienteVentanaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo 
     */
    public function getN61AmbienteVentanaTipo()
    {
        return $this->n61AmbienteVentanaTipo;
    }

    /**
     * Set n611SeguroTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n611SeguroTipo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN611SeguroTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n611SeguroTipo = null)
    {
        $this->n611SeguroTipo = $n611SeguroTipo;
    
        return $this;
    }

    /**
     * Get n611SeguroTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo 
     */
    public function getN611SeguroTipo()
    {
        return $this->n611SeguroTipo;
    }

    /**
     * Set n61AmbientePisoCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n61AmbientePisoCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61AmbientePisoCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n61AmbientePisoCaracTipo = null)
    {
        $this->n61AmbientePisoCaracTipo = $n61AmbientePisoCaracTipo;
    
        return $this;
    }

    /**
     * Get n61AmbientePisoCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN61AmbientePisoCaracTipo()
    {
        return $this->n61AmbientePisoCaracTipo;
    }

    /**
     * Set n61AmbienteRevestCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n61AmbienteRevestCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61AmbienteRevestCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n61AmbienteRevestCaracTipo = null)
    {
        $this->n61AmbienteRevestCaracTipo = $n61AmbienteRevestCaracTipo;
    
        return $this;
    }

    /**
     * Get n61AmbienteRevestCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN61AmbienteRevestCaracTipo()
    {
        return $this->n61AmbienteRevestCaracTipo;
    }

    /**
     * Set n61AmbienteCieloFalTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n61AmbienteCieloFalTipo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN61AmbienteCieloFalTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n61AmbienteCieloFalTipo = null)
    {
        $this->n61AmbienteCieloFalTipo = $n61AmbienteCieloFalTipo;
    
        return $this;
    }

    /**
     * Get n61AmbienteCieloFalTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN61AmbienteCieloFalTipo()
    {
        return $this->n61AmbienteCieloFalTipo;
    }

    /**
     * Set n11AmbienteadministrativoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoTipo $n11AmbienteadministrativoTipo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setN11AmbienteadministrativoTipo(\Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoTipo $n11AmbienteadministrativoTipo = null)
    {
        $this->n11AmbienteadministrativoTipo = $n11AmbienteadministrativoTipo;
    
        return $this;
    }

    /**
     * Get n11AmbienteadministrativoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoTipo 
     */
    public function getN11AmbienteadministrativoTipo()
    {
        return $this->n11AmbienteadministrativoTipo;
    }

    /**
     * Set infraestructuraH6Ambienteadministrativo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo $infraestructuraH6Ambienteadministrativo
     * @return InfraestructuraH6AmbienteadministrativoAmbiente
     */
    public function setInfraestructuraH6Ambienteadministrativo(\Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo $infraestructuraH6Ambienteadministrativo = null)
    {
        $this->infraestructuraH6Ambienteadministrativo = $infraestructuraH6Ambienteadministrativo;
    
        return $this;
    }

    /**
     * Get infraestructuraH6Ambienteadministrativo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo 
     */
    public function getInfraestructuraH6Ambienteadministrativo()
    {
        return $this->infraestructuraH6Ambienteadministrativo;
    }
}
