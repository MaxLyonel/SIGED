<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH6AmbienteadministrativoServAlimentacion
 */
class InfraestructuraH6AmbienteadministrativoServAlimentacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n41NumeroAmbientes;

    /**
     * @var string
     */
    private $n41MetrosArea;

    /**
     * @var boolean
     */
    private $n41EsAmbienteTecho;

    /**
     * @var boolean
     */
    private $n41EsAmbienteCieloFal;

    /**
     * @var boolean
     */
    private $n41EsAmbientePuerta;

    /**
     * @var boolean
     */
    private $n41EsAmbienteVentana;

    /**
     * @var boolean
     */
    private $n41EsAmbienteMuros;

    /**
     * @var boolean
     */
    private $n41EsAmbienteRevestimiento;

    /**
     * @var boolean
     */
    private $n41EsAmbientePiso;

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
    private $n412AbreTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo
     */
    private $n41AmbientePisoMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo
     */
    private $n41AmbienteRevestMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo
     */
    private $n41AmbienteMuroCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo
     */
    private $n41AmbienteMuroMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo
     */
    private $n41AmbienteVentanaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo
     */
    private $n411SeguroTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n41AmbientePisoCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n41AmbienteRevestCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n41AmbienteCieloFalTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6ServicioAlimentacionTipo
     */
    private $n41AmbienteAlimentacionTipo;

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
     * Set n41NumeroAmbientes
     *
     * @param integer $n41NumeroAmbientes
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41NumeroAmbientes($n41NumeroAmbientes)
    {
        $this->n41NumeroAmbientes = $n41NumeroAmbientes;
    
        return $this;
    }

    /**
     * Get n41NumeroAmbientes
     *
     * @return integer 
     */
    public function getN41NumeroAmbientes()
    {
        return $this->n41NumeroAmbientes;
    }

    /**
     * Set n41MetrosArea
     *
     * @param string $n41MetrosArea
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41MetrosArea($n41MetrosArea)
    {
        $this->n41MetrosArea = $n41MetrosArea;
    
        return $this;
    }

    /**
     * Get n41MetrosArea
     *
     * @return string 
     */
    public function getN41MetrosArea()
    {
        return $this->n41MetrosArea;
    }

    /**
     * Set n41EsAmbienteTecho
     *
     * @param boolean $n41EsAmbienteTecho
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41EsAmbienteTecho($n41EsAmbienteTecho)
    {
        $this->n41EsAmbienteTecho = $n41EsAmbienteTecho;
    
        return $this;
    }

    /**
     * Get n41EsAmbienteTecho
     *
     * @return boolean 
     */
    public function getN41EsAmbienteTecho()
    {
        return $this->n41EsAmbienteTecho;
    }

    /**
     * Set n41EsAmbienteCieloFal
     *
     * @param boolean $n41EsAmbienteCieloFal
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41EsAmbienteCieloFal($n41EsAmbienteCieloFal)
    {
        $this->n41EsAmbienteCieloFal = $n41EsAmbienteCieloFal;
    
        return $this;
    }

    /**
     * Get n41EsAmbienteCieloFal
     *
     * @return boolean 
     */
    public function getN41EsAmbienteCieloFal()
    {
        return $this->n41EsAmbienteCieloFal;
    }

    /**
     * Set n41EsAmbientePuerta
     *
     * @param boolean $n41EsAmbientePuerta
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41EsAmbientePuerta($n41EsAmbientePuerta)
    {
        $this->n41EsAmbientePuerta = $n41EsAmbientePuerta;
    
        return $this;
    }

    /**
     * Get n41EsAmbientePuerta
     *
     * @return boolean 
     */
    public function getN41EsAmbientePuerta()
    {
        return $this->n41EsAmbientePuerta;
    }

    /**
     * Set n41EsAmbienteVentana
     *
     * @param boolean $n41EsAmbienteVentana
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41EsAmbienteVentana($n41EsAmbienteVentana)
    {
        $this->n41EsAmbienteVentana = $n41EsAmbienteVentana;
    
        return $this;
    }

    /**
     * Get n41EsAmbienteVentana
     *
     * @return boolean 
     */
    public function getN41EsAmbienteVentana()
    {
        return $this->n41EsAmbienteVentana;
    }

    /**
     * Set n41EsAmbienteMuros
     *
     * @param boolean $n41EsAmbienteMuros
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41EsAmbienteMuros($n41EsAmbienteMuros)
    {
        $this->n41EsAmbienteMuros = $n41EsAmbienteMuros;
    
        return $this;
    }

    /**
     * Get n41EsAmbienteMuros
     *
     * @return boolean 
     */
    public function getN41EsAmbienteMuros()
    {
        return $this->n41EsAmbienteMuros;
    }

    /**
     * Set n41EsAmbienteRevestimiento
     *
     * @param boolean $n41EsAmbienteRevestimiento
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41EsAmbienteRevestimiento($n41EsAmbienteRevestimiento)
    {
        $this->n41EsAmbienteRevestimiento = $n41EsAmbienteRevestimiento;
    
        return $this;
    }

    /**
     * Get n41EsAmbienteRevestimiento
     *
     * @return boolean 
     */
    public function getN41EsAmbienteRevestimiento()
    {
        return $this->n41EsAmbienteRevestimiento;
    }

    /**
     * Set n41EsAmbientePiso
     *
     * @param boolean $n41EsAmbientePiso
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41EsAmbientePiso($n41EsAmbientePiso)
    {
        $this->n41EsAmbientePiso = $n41EsAmbientePiso;
    
        return $this;
    }

    /**
     * Get n41EsAmbientePiso
     *
     * @return boolean 
     */
    public function getN41EsAmbientePiso()
    {
        return $this->n41EsAmbientePiso;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
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
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
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
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
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
     * Set n412AbreTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n412AbreTipo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN412AbreTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n412AbreTipo = null)
    {
        $this->n412AbreTipo = $n412AbreTipo;
    
        return $this;
    }

    /**
     * Get n412AbreTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo 
     */
    public function getN412AbreTipo()
    {
        return $this->n412AbreTipo;
    }

    /**
     * Set n41AmbientePisoMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n41AmbientePisoMatTipo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41AmbientePisoMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n41AmbientePisoMatTipo = null)
    {
        $this->n41AmbientePisoMatTipo = $n41AmbientePisoMatTipo;
    
        return $this;
    }

    /**
     * Get n41AmbientePisoMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo 
     */
    public function getN41AmbientePisoMatTipo()
    {
        return $this->n41AmbientePisoMatTipo;
    }

    /**
     * Set n41AmbienteRevestMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n41AmbienteRevestMatTipo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41AmbienteRevestMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n41AmbienteRevestMatTipo = null)
    {
        $this->n41AmbienteRevestMatTipo = $n41AmbienteRevestMatTipo;
    
        return $this;
    }

    /**
     * Get n41AmbienteRevestMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo 
     */
    public function getN41AmbienteRevestMatTipo()
    {
        return $this->n41AmbienteRevestMatTipo;
    }

    /**
     * Set n41AmbienteMuroCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n41AmbienteMuroCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41AmbienteMuroCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n41AmbienteMuroCaracTipo = null)
    {
        $this->n41AmbienteMuroCaracTipo = $n41AmbienteMuroCaracTipo;
    
        return $this;
    }

    /**
     * Get n41AmbienteMuroCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo 
     */
    public function getN41AmbienteMuroCaracTipo()
    {
        return $this->n41AmbienteMuroCaracTipo;
    }

    /**
     * Set n41AmbienteMuroMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n41AmbienteMuroMatTipo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41AmbienteMuroMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n41AmbienteMuroMatTipo = null)
    {
        $this->n41AmbienteMuroMatTipo = $n41AmbienteMuroMatTipo;
    
        return $this;
    }

    /**
     * Get n41AmbienteMuroMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo 
     */
    public function getN41AmbienteMuroMatTipo()
    {
        return $this->n41AmbienteMuroMatTipo;
    }

    /**
     * Set n41AmbienteVentanaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n41AmbienteVentanaTipo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41AmbienteVentanaTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n41AmbienteVentanaTipo = null)
    {
        $this->n41AmbienteVentanaTipo = $n41AmbienteVentanaTipo;
    
        return $this;
    }

    /**
     * Get n41AmbienteVentanaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo 
     */
    public function getN41AmbienteVentanaTipo()
    {
        return $this->n41AmbienteVentanaTipo;
    }

    /**
     * Set n411SeguroTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n411SeguroTipo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN411SeguroTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n411SeguroTipo = null)
    {
        $this->n411SeguroTipo = $n411SeguroTipo;
    
        return $this;
    }

    /**
     * Get n411SeguroTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo 
     */
    public function getN411SeguroTipo()
    {
        return $this->n411SeguroTipo;
    }

    /**
     * Set n41AmbientePisoCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n41AmbientePisoCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41AmbientePisoCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n41AmbientePisoCaracTipo = null)
    {
        $this->n41AmbientePisoCaracTipo = $n41AmbientePisoCaracTipo;
    
        return $this;
    }

    /**
     * Get n41AmbientePisoCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN41AmbientePisoCaracTipo()
    {
        return $this->n41AmbientePisoCaracTipo;
    }

    /**
     * Set n41AmbienteRevestCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n41AmbienteRevestCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41AmbienteRevestCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n41AmbienteRevestCaracTipo = null)
    {
        $this->n41AmbienteRevestCaracTipo = $n41AmbienteRevestCaracTipo;
    
        return $this;
    }

    /**
     * Get n41AmbienteRevestCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN41AmbienteRevestCaracTipo()
    {
        return $this->n41AmbienteRevestCaracTipo;
    }

    /**
     * Set n41AmbienteCieloFalTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n41AmbienteCieloFalTipo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41AmbienteCieloFalTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n41AmbienteCieloFalTipo = null)
    {
        $this->n41AmbienteCieloFalTipo = $n41AmbienteCieloFalTipo;
    
        return $this;
    }

    /**
     * Get n41AmbienteCieloFalTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN41AmbienteCieloFalTipo()
    {
        return $this->n41AmbienteCieloFalTipo;
    }

    /**
     * Set n41AmbienteAlimentacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6ServicioAlimentacionTipo $n41AmbienteAlimentacionTipo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
     */
    public function setN41AmbienteAlimentacionTipo(\Sie\AppWebBundle\Entity\InfraestructuraH6ServicioAlimentacionTipo $n41AmbienteAlimentacionTipo = null)
    {
        $this->n41AmbienteAlimentacionTipo = $n41AmbienteAlimentacionTipo;
    
        return $this;
    }

    /**
     * Get n41AmbienteAlimentacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6ServicioAlimentacionTipo 
     */
    public function getN41AmbienteAlimentacionTipo()
    {
        return $this->n41AmbienteAlimentacionTipo;
    }

    /**
     * Set infraestructuraH6Ambienteadministrativo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo $infraestructuraH6Ambienteadministrativo
     * @return InfraestructuraH6AmbienteadministrativoServAlimentacion
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
