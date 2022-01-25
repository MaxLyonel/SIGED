<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4ServicioVestuarios
 */
class InfraestructuraH4ServicioVestuarios
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $n6Areatotalm2;

    /**
     * @var boolean
     */
    private $n6EsFuncionaAmbiente;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var boolean
     */
    private $n62EsTieneTecho;

    /**
     * @var boolean
     */
    private $n62EsTieneCieloFalso;

    /**
     * @var boolean
     */
    private $n62EsTienePuerta;

    /**
     * @var boolean
     */
    private $n62EsTieneVentana;

    /**
     * @var boolean
     */
    private $n62EsTieneMuros;

    /**
     * @var boolean
     */
    private $n62EsTieneRevest;

    /**
     * @var boolean
     */
    private $n62EsTienePiso;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral
     */
    private $estadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo
     */
    private $n622AbreTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo
     */
    private $n62TienePisoMaterTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo
     */
    private $n62TieneRevestMaterTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo
     */
    private $n62TieneMurosCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo
     */
    private $n62TieneMurosMaterTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo
     */
    private $n62TieneVentanaCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n621SeguroTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n62TienePisoCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n62TieneRevestCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n62TieneCieloFalsoCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4BanoTipo
     */
    private $n6ServicioAmbienteTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4Servicio
     */
    private $infraestructuraH4Servicio;


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
     * Set n6Areatotalm2
     *
     * @param string $n6Areatotalm2
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN6Areatotalm2($n6Areatotalm2)
    {
        $this->n6Areatotalm2 = $n6Areatotalm2;
    
        return $this;
    }

    /**
     * Get n6Areatotalm2
     *
     * @return string 
     */
    public function getN6Areatotalm2()
    {
        return $this->n6Areatotalm2;
    }

    /**
     * Set n6EsFuncionaAmbiente
     *
     * @param boolean $n6EsFuncionaAmbiente
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN6EsFuncionaAmbiente($n6EsFuncionaAmbiente)
    {
        $this->n6EsFuncionaAmbiente = $n6EsFuncionaAmbiente;
    
        return $this;
    }

    /**
     * Get n6EsFuncionaAmbiente
     *
     * @return boolean 
     */
    public function getN6EsFuncionaAmbiente()
    {
        return $this->n6EsFuncionaAmbiente;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH4ServicioVestuarios
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
     * Set n62EsTieneTecho
     *
     * @param boolean $n62EsTieneTecho
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62EsTieneTecho($n62EsTieneTecho)
    {
        $this->n62EsTieneTecho = $n62EsTieneTecho;
    
        return $this;
    }

    /**
     * Get n62EsTieneTecho
     *
     * @return boolean 
     */
    public function getN62EsTieneTecho()
    {
        return $this->n62EsTieneTecho;
    }

    /**
     * Set n62EsTieneCieloFalso
     *
     * @param boolean $n62EsTieneCieloFalso
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62EsTieneCieloFalso($n62EsTieneCieloFalso)
    {
        $this->n62EsTieneCieloFalso = $n62EsTieneCieloFalso;
    
        return $this;
    }

    /**
     * Get n62EsTieneCieloFalso
     *
     * @return boolean 
     */
    public function getN62EsTieneCieloFalso()
    {
        return $this->n62EsTieneCieloFalso;
    }

    /**
     * Set n62EsTienePuerta
     *
     * @param boolean $n62EsTienePuerta
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62EsTienePuerta($n62EsTienePuerta)
    {
        $this->n62EsTienePuerta = $n62EsTienePuerta;
    
        return $this;
    }

    /**
     * Get n62EsTienePuerta
     *
     * @return boolean 
     */
    public function getN62EsTienePuerta()
    {
        return $this->n62EsTienePuerta;
    }

    /**
     * Set n62EsTieneVentana
     *
     * @param boolean $n62EsTieneVentana
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62EsTieneVentana($n62EsTieneVentana)
    {
        $this->n62EsTieneVentana = $n62EsTieneVentana;
    
        return $this;
    }

    /**
     * Get n62EsTieneVentana
     *
     * @return boolean 
     */
    public function getN62EsTieneVentana()
    {
        return $this->n62EsTieneVentana;
    }

    /**
     * Set n62EsTieneMuros
     *
     * @param boolean $n62EsTieneMuros
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62EsTieneMuros($n62EsTieneMuros)
    {
        $this->n62EsTieneMuros = $n62EsTieneMuros;
    
        return $this;
    }

    /**
     * Get n62EsTieneMuros
     *
     * @return boolean 
     */
    public function getN62EsTieneMuros()
    {
        return $this->n62EsTieneMuros;
    }

    /**
     * Set n62EsTieneRevest
     *
     * @param boolean $n62EsTieneRevest
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62EsTieneRevest($n62EsTieneRevest)
    {
        $this->n62EsTieneRevest = $n62EsTieneRevest;
    
        return $this;
    }

    /**
     * Get n62EsTieneRevest
     *
     * @return boolean 
     */
    public function getN62EsTieneRevest()
    {
        return $this->n62EsTieneRevest;
    }

    /**
     * Set n62EsTienePiso
     *
     * @param boolean $n62EsTienePiso
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62EsTienePiso($n62EsTienePiso)
    {
        $this->n62EsTienePiso = $n62EsTienePiso;
    
        return $this;
    }

    /**
     * Get n62EsTienePiso
     *
     * @return boolean 
     */
    public function getN62EsTienePiso()
    {
        return $this->n62EsTienePiso;
    }

    /**
     * Set estadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral $estadoTipo
     * @return InfraestructuraH4ServicioVestuarios
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
     * Set n622AbreTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n622AbreTipo
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN622AbreTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n622AbreTipo = null)
    {
        $this->n622AbreTipo = $n622AbreTipo;
    
        return $this;
    }

    /**
     * Get n622AbreTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo 
     */
    public function getN622AbreTipo()
    {
        return $this->n622AbreTipo;
    }

    /**
     * Set n62TienePisoMaterTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n62TienePisoMaterTipo
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62TienePisoMaterTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n62TienePisoMaterTipo = null)
    {
        $this->n62TienePisoMaterTipo = $n62TienePisoMaterTipo;
    
        return $this;
    }

    /**
     * Get n62TienePisoMaterTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo 
     */
    public function getN62TienePisoMaterTipo()
    {
        return $this->n62TienePisoMaterTipo;
    }

    /**
     * Set n62TieneRevestMaterTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n62TieneRevestMaterTipo
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62TieneRevestMaterTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n62TieneRevestMaterTipo = null)
    {
        $this->n62TieneRevestMaterTipo = $n62TieneRevestMaterTipo;
    
        return $this;
    }

    /**
     * Get n62TieneRevestMaterTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo 
     */
    public function getN62TieneRevestMaterTipo()
    {
        return $this->n62TieneRevestMaterTipo;
    }

    /**
     * Set n62TieneMurosCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n62TieneMurosCaracTipo
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62TieneMurosCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n62TieneMurosCaracTipo = null)
    {
        $this->n62TieneMurosCaracTipo = $n62TieneMurosCaracTipo;
    
        return $this;
    }

    /**
     * Get n62TieneMurosCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo 
     */
    public function getN62TieneMurosCaracTipo()
    {
        return $this->n62TieneMurosCaracTipo;
    }

    /**
     * Set n62TieneMurosMaterTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n62TieneMurosMaterTipo
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62TieneMurosMaterTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n62TieneMurosMaterTipo = null)
    {
        $this->n62TieneMurosMaterTipo = $n62TieneMurosMaterTipo;
    
        return $this;
    }

    /**
     * Get n62TieneMurosMaterTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo 
     */
    public function getN62TieneMurosMaterTipo()
    {
        return $this->n62TieneMurosMaterTipo;
    }

    /**
     * Set n62TieneVentanaCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n62TieneVentanaCaracTipo
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62TieneVentanaCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n62TieneVentanaCaracTipo = null)
    {
        $this->n62TieneVentanaCaracTipo = $n62TieneVentanaCaracTipo;
    
        return $this;
    }

    /**
     * Get n62TieneVentanaCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo 
     */
    public function getN62TieneVentanaCaracTipo()
    {
        return $this->n62TieneVentanaCaracTipo;
    }

    /**
     * Set n621SeguroTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n621SeguroTipo
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN621SeguroTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n621SeguroTipo = null)
    {
        $this->n621SeguroTipo = $n621SeguroTipo;
    
        return $this;
    }

    /**
     * Get n621SeguroTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN621SeguroTipo()
    {
        return $this->n621SeguroTipo;
    }

    /**
     * Set n62TienePisoCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n62TienePisoCaracTipo
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62TienePisoCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n62TienePisoCaracTipo = null)
    {
        $this->n62TienePisoCaracTipo = $n62TienePisoCaracTipo;
    
        return $this;
    }

    /**
     * Get n62TienePisoCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN62TienePisoCaracTipo()
    {
        return $this->n62TienePisoCaracTipo;
    }

    /**
     * Set n62TieneRevestCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n62TieneRevestCaracTipo
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62TieneRevestCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n62TieneRevestCaracTipo = null)
    {
        $this->n62TieneRevestCaracTipo = $n62TieneRevestCaracTipo;
    
        return $this;
    }

    /**
     * Get n62TieneRevestCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN62TieneRevestCaracTipo()
    {
        return $this->n62TieneRevestCaracTipo;
    }

    /**
     * Set n62TieneCieloFalsoCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n62TieneCieloFalsoCaracTipo
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN62TieneCieloFalsoCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n62TieneCieloFalsoCaracTipo = null)
    {
        $this->n62TieneCieloFalsoCaracTipo = $n62TieneCieloFalsoCaracTipo;
    
        return $this;
    }

    /**
     * Get n62TieneCieloFalsoCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN62TieneCieloFalsoCaracTipo()
    {
        return $this->n62TieneCieloFalsoCaracTipo;
    }

    /**
     * Set n6ServicioAmbienteTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4BanoTipo $n6ServicioAmbienteTipo
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setN6ServicioAmbienteTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4BanoTipo $n6ServicioAmbienteTipo = null)
    {
        $this->n6ServicioAmbienteTipo = $n6ServicioAmbienteTipo;
    
        return $this;
    }

    /**
     * Get n6ServicioAmbienteTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4BanoTipo 
     */
    public function getN6ServicioAmbienteTipo()
    {
        return $this->n6ServicioAmbienteTipo;
    }

    /**
     * Set infraestructuraH4Servicio
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4Servicio $infraestructuraH4Servicio
     * @return InfraestructuraH4ServicioVestuarios
     */
    public function setInfraestructuraH4Servicio(\Sie\AppWebBundle\Entity\InfraestructuraH4Servicio $infraestructuraH4Servicio = null)
    {
        $this->infraestructuraH4Servicio = $infraestructuraH4Servicio;
    
        return $this;
    }

    /**
     * Get infraestructuraH4Servicio
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4Servicio 
     */
    public function getInfraestructuraH4Servicio()
    {
        return $this->infraestructuraH4Servicio;
    }
}
