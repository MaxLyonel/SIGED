<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH6AmbienteadministrativoInterDormitorios
 */
class InfraestructuraH6AmbienteadministrativoInterDormitorios
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n34AmbienteCantidad;

    /**
     * @var string
     */
    private $n34AmbienteArea;

    /**
     * @var integer
     */
    private $n34AmbienteCamaLiteras;

    /**
     * @var integer
     */
    private $n34AmbienteCamaSimples;

    /**
     * @var integer
     */
    private $n34AmbienteCamaOtros;

    /**
     * @var boolean
     */
    private $n34EsAmbienteTecho;

    /**
     * @var boolean
     */
    private $n34EsAmbienteCieloFal;

    /**
     * @var boolean
     */
    private $n34EsAmbientePuerta;

    /**
     * @var boolean
     */
    private $n34EsAmbienteVentana;

    /**
     * @var boolean
     */
    private $n34EsAmbienteMuros;

    /**
     * @var boolean
     */
    private $n34EsAmbienteRevestimiento;

    /**
     * @var boolean
     */
    private $n34EsAmbientePiso;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral
     */
    private $estadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo
     */
    private $n342AbreTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo
     */
    private $n34AmbientePisoMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo
     */
    private $n34AmbienteRevestMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo
     */
    private $n34AmbienteMuroCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo
     */
    private $n34AmbienteMuroMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo
     */
    private $n34AmbienteVentanaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo
     */
    private $n341SeguroTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n34AmbientePisoCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n34AmbienteRevestCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n34AmbienteCieloFalTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteGeneroTipo
     */
    private $n34AmbienteTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoInternadoEst
     */
    private $infraestructuraH6AmbienteadministrativoInternadoEst;


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
     * Set n34AmbienteCantidad
     *
     * @param integer $n34AmbienteCantidad
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteCantidad($n34AmbienteCantidad)
    {
        $this->n34AmbienteCantidad = $n34AmbienteCantidad;
    
        return $this;
    }

    /**
     * Get n34AmbienteCantidad
     *
     * @return integer 
     */
    public function getN34AmbienteCantidad()
    {
        return $this->n34AmbienteCantidad;
    }

    /**
     * Set n34AmbienteArea
     *
     * @param string $n34AmbienteArea
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteArea($n34AmbienteArea)
    {
        $this->n34AmbienteArea = $n34AmbienteArea;
    
        return $this;
    }

    /**
     * Get n34AmbienteArea
     *
     * @return string 
     */
    public function getN34AmbienteArea()
    {
        return $this->n34AmbienteArea;
    }

    /**
     * Set n34AmbienteCamaLiteras
     *
     * @param integer $n34AmbienteCamaLiteras
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteCamaLiteras($n34AmbienteCamaLiteras)
    {
        $this->n34AmbienteCamaLiteras = $n34AmbienteCamaLiteras;
    
        return $this;
    }

    /**
     * Get n34AmbienteCamaLiteras
     *
     * @return integer 
     */
    public function getN34AmbienteCamaLiteras()
    {
        return $this->n34AmbienteCamaLiteras;
    }

    /**
     * Set n34AmbienteCamaSimples
     *
     * @param integer $n34AmbienteCamaSimples
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteCamaSimples($n34AmbienteCamaSimples)
    {
        $this->n34AmbienteCamaSimples = $n34AmbienteCamaSimples;
    
        return $this;
    }

    /**
     * Get n34AmbienteCamaSimples
     *
     * @return integer 
     */
    public function getN34AmbienteCamaSimples()
    {
        return $this->n34AmbienteCamaSimples;
    }

    /**
     * Set n34AmbienteCamaOtros
     *
     * @param integer $n34AmbienteCamaOtros
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteCamaOtros($n34AmbienteCamaOtros)
    {
        $this->n34AmbienteCamaOtros = $n34AmbienteCamaOtros;
    
        return $this;
    }

    /**
     * Get n34AmbienteCamaOtros
     *
     * @return integer 
     */
    public function getN34AmbienteCamaOtros()
    {
        return $this->n34AmbienteCamaOtros;
    }

    /**
     * Set n34EsAmbienteTecho
     *
     * @param boolean $n34EsAmbienteTecho
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34EsAmbienteTecho($n34EsAmbienteTecho)
    {
        $this->n34EsAmbienteTecho = $n34EsAmbienteTecho;
    
        return $this;
    }

    /**
     * Get n34EsAmbienteTecho
     *
     * @return boolean 
     */
    public function getN34EsAmbienteTecho()
    {
        return $this->n34EsAmbienteTecho;
    }

    /**
     * Set n34EsAmbienteCieloFal
     *
     * @param boolean $n34EsAmbienteCieloFal
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34EsAmbienteCieloFal($n34EsAmbienteCieloFal)
    {
        $this->n34EsAmbienteCieloFal = $n34EsAmbienteCieloFal;
    
        return $this;
    }

    /**
     * Get n34EsAmbienteCieloFal
     *
     * @return boolean 
     */
    public function getN34EsAmbienteCieloFal()
    {
        return $this->n34EsAmbienteCieloFal;
    }

    /**
     * Set n34EsAmbientePuerta
     *
     * @param boolean $n34EsAmbientePuerta
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34EsAmbientePuerta($n34EsAmbientePuerta)
    {
        $this->n34EsAmbientePuerta = $n34EsAmbientePuerta;
    
        return $this;
    }

    /**
     * Get n34EsAmbientePuerta
     *
     * @return boolean 
     */
    public function getN34EsAmbientePuerta()
    {
        return $this->n34EsAmbientePuerta;
    }

    /**
     * Set n34EsAmbienteVentana
     *
     * @param boolean $n34EsAmbienteVentana
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34EsAmbienteVentana($n34EsAmbienteVentana)
    {
        $this->n34EsAmbienteVentana = $n34EsAmbienteVentana;
    
        return $this;
    }

    /**
     * Get n34EsAmbienteVentana
     *
     * @return boolean 
     */
    public function getN34EsAmbienteVentana()
    {
        return $this->n34EsAmbienteVentana;
    }

    /**
     * Set n34EsAmbienteMuros
     *
     * @param boolean $n34EsAmbienteMuros
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34EsAmbienteMuros($n34EsAmbienteMuros)
    {
        $this->n34EsAmbienteMuros = $n34EsAmbienteMuros;
    
        return $this;
    }

    /**
     * Get n34EsAmbienteMuros
     *
     * @return boolean 
     */
    public function getN34EsAmbienteMuros()
    {
        return $this->n34EsAmbienteMuros;
    }

    /**
     * Set n34EsAmbienteRevestimiento
     *
     * @param boolean $n34EsAmbienteRevestimiento
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34EsAmbienteRevestimiento($n34EsAmbienteRevestimiento)
    {
        $this->n34EsAmbienteRevestimiento = $n34EsAmbienteRevestimiento;
    
        return $this;
    }

    /**
     * Get n34EsAmbienteRevestimiento
     *
     * @return boolean 
     */
    public function getN34EsAmbienteRevestimiento()
    {
        return $this->n34EsAmbienteRevestimiento;
    }

    /**
     * Set n34EsAmbientePiso
     *
     * @param boolean $n34EsAmbientePiso
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34EsAmbientePiso($n34EsAmbientePiso)
    {
        $this->n34EsAmbientePiso = $n34EsAmbientePiso;
    
        return $this;
    }

    /**
     * Get n34EsAmbientePiso
     *
     * @return boolean 
     */
    public function getN34EsAmbientePiso()
    {
        return $this->n34EsAmbientePiso;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
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
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
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
     * Set estadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral $estadoTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
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
     * Set n342AbreTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n342AbreTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN342AbreTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n342AbreTipo = null)
    {
        $this->n342AbreTipo = $n342AbreTipo;
    
        return $this;
    }

    /**
     * Get n342AbreTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo 
     */
    public function getN342AbreTipo()
    {
        return $this->n342AbreTipo;
    }

    /**
     * Set n34AmbientePisoMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n34AmbientePisoMatTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbientePisoMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n34AmbientePisoMatTipo = null)
    {
        $this->n34AmbientePisoMatTipo = $n34AmbientePisoMatTipo;
    
        return $this;
    }

    /**
     * Get n34AmbientePisoMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo 
     */
    public function getN34AmbientePisoMatTipo()
    {
        return $this->n34AmbientePisoMatTipo;
    }

    /**
     * Set n34AmbienteRevestMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n34AmbienteRevestMatTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteRevestMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n34AmbienteRevestMatTipo = null)
    {
        $this->n34AmbienteRevestMatTipo = $n34AmbienteRevestMatTipo;
    
        return $this;
    }

    /**
     * Get n34AmbienteRevestMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo 
     */
    public function getN34AmbienteRevestMatTipo()
    {
        return $this->n34AmbienteRevestMatTipo;
    }

    /**
     * Set n34AmbienteMuroCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n34AmbienteMuroCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteMuroCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n34AmbienteMuroCaracTipo = null)
    {
        $this->n34AmbienteMuroCaracTipo = $n34AmbienteMuroCaracTipo;
    
        return $this;
    }

    /**
     * Get n34AmbienteMuroCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo 
     */
    public function getN34AmbienteMuroCaracTipo()
    {
        return $this->n34AmbienteMuroCaracTipo;
    }

    /**
     * Set n34AmbienteMuroMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n34AmbienteMuroMatTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteMuroMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n34AmbienteMuroMatTipo = null)
    {
        $this->n34AmbienteMuroMatTipo = $n34AmbienteMuroMatTipo;
    
        return $this;
    }

    /**
     * Get n34AmbienteMuroMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo 
     */
    public function getN34AmbienteMuroMatTipo()
    {
        return $this->n34AmbienteMuroMatTipo;
    }

    /**
     * Set n34AmbienteVentanaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n34AmbienteVentanaTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteVentanaTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n34AmbienteVentanaTipo = null)
    {
        $this->n34AmbienteVentanaTipo = $n34AmbienteVentanaTipo;
    
        return $this;
    }

    /**
     * Get n34AmbienteVentanaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo 
     */
    public function getN34AmbienteVentanaTipo()
    {
        return $this->n34AmbienteVentanaTipo;
    }

    /**
     * Set n341SeguroTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n341SeguroTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN341SeguroTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n341SeguroTipo = null)
    {
        $this->n341SeguroTipo = $n341SeguroTipo;
    
        return $this;
    }

    /**
     * Get n341SeguroTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo 
     */
    public function getN341SeguroTipo()
    {
        return $this->n341SeguroTipo;
    }

    /**
     * Set n34AmbientePisoCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n34AmbientePisoCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbientePisoCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n34AmbientePisoCaracTipo = null)
    {
        $this->n34AmbientePisoCaracTipo = $n34AmbientePisoCaracTipo;
    
        return $this;
    }

    /**
     * Get n34AmbientePisoCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN34AmbientePisoCaracTipo()
    {
        return $this->n34AmbientePisoCaracTipo;
    }

    /**
     * Set n34AmbienteRevestCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n34AmbienteRevestCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteRevestCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n34AmbienteRevestCaracTipo = null)
    {
        $this->n34AmbienteRevestCaracTipo = $n34AmbienteRevestCaracTipo;
    
        return $this;
    }

    /**
     * Get n34AmbienteRevestCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN34AmbienteRevestCaracTipo()
    {
        return $this->n34AmbienteRevestCaracTipo;
    }

    /**
     * Set n34AmbienteCieloFalTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n34AmbienteCieloFalTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteCieloFalTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n34AmbienteCieloFalTipo = null)
    {
        $this->n34AmbienteCieloFalTipo = $n34AmbienteCieloFalTipo;
    
        return $this;
    }

    /**
     * Get n34AmbienteCieloFalTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN34AmbienteCieloFalTipo()
    {
        return $this->n34AmbienteCieloFalTipo;
    }

    /**
     * Set n34AmbienteTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteGeneroTipo $n34AmbienteTipo
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setN34AmbienteTipo(\Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteGeneroTipo $n34AmbienteTipo = null)
    {
        $this->n34AmbienteTipo = $n34AmbienteTipo;
    
        return $this;
    }

    /**
     * Get n34AmbienteTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteGeneroTipo 
     */
    public function getN34AmbienteTipo()
    {
        return $this->n34AmbienteTipo;
    }

    /**
     * Set infraestructuraH6AmbienteadministrativoInternadoEst
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoInternadoEst $infraestructuraH6AmbienteadministrativoInternadoEst
     * @return InfraestructuraH6AmbienteadministrativoInterDormitorios
     */
    public function setInfraestructuraH6AmbienteadministrativoInternadoEst(\Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoInternadoEst $infraestructuraH6AmbienteadministrativoInternadoEst = null)
    {
        $this->infraestructuraH6AmbienteadministrativoInternadoEst = $infraestructuraH6AmbienteadministrativoInternadoEst;
    
        return $this;
    }

    /**
     * Get infraestructuraH6AmbienteadministrativoInternadoEst
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6AmbienteadministrativoInternadoEst 
     */
    public function getInfraestructuraH6AmbienteadministrativoInternadoEst()
    {
        return $this->infraestructuraH6AmbienteadministrativoInternadoEst;
    }
}
