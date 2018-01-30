<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH6AmbienteadministrativoVivMaestros
 */
class InfraestructuraH6AmbienteadministrativoVivMaestros
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n21NumeroAmbientes;

    /**
     * @var integer
     */
    private $n21NumeroHabitantes;

    /**
     * @var integer
     */
    private $n21NumeroBanios;

    /**
     * @var integer
     */
    private $n21NumeroDuchas;

    /**
     * @var integer
     */
    private $n21NumeroCocinas;

    /**
     * @var string
     */
    private $n21MetrosArea;

    /**
     * @var boolean
     */
    private $n21EsAmbienteTecho;

    /**
     * @var boolean
     */
    private $n21EsAmbienteCieloFal;

    /**
     * @var boolean
     */
    private $n21EsAmbientePuerta;

    /**
     * @var boolean
     */
    private $n21EsAmbienteVentana;

    /**
     * @var boolean
     */
    private $n21EsAmbienteMuros;

    /**
     * @var boolean
     */
    private $n21EsAmbienteRevestimiento;

    /**
     * @var boolean
     */
    private $n21EsAmbientePiso;

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
    private $n212AbreTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo
     */
    private $infraestructuraH6Ambienteadministrativo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo
     */
    private $n21AmbientePisoMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo
     */
    private $n21AmbienteRevestMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo
     */
    private $n21AmbienteMuroCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo
     */
    private $n21AmbienteMuroMatTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo
     */
    private $n21AmbienteVentanaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo
     */
    private $n211SeguroTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n21AmbientePisoCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n21AmbienteRevestCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n21AmbienteCieloFalTipo;


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
     * Set n21NumeroAmbientes
     *
     * @param integer $n21NumeroAmbientes
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21NumeroAmbientes($n21NumeroAmbientes)
    {
        $this->n21NumeroAmbientes = $n21NumeroAmbientes;
    
        return $this;
    }

    /**
     * Get n21NumeroAmbientes
     *
     * @return integer 
     */
    public function getN21NumeroAmbientes()
    {
        return $this->n21NumeroAmbientes;
    }

    /**
     * Set n21NumeroHabitantes
     *
     * @param integer $n21NumeroHabitantes
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21NumeroHabitantes($n21NumeroHabitantes)
    {
        $this->n21NumeroHabitantes = $n21NumeroHabitantes;
    
        return $this;
    }

    /**
     * Get n21NumeroHabitantes
     *
     * @return integer 
     */
    public function getN21NumeroHabitantes()
    {
        return $this->n21NumeroHabitantes;
    }

    /**
     * Set n21NumeroBanios
     *
     * @param integer $n21NumeroBanios
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21NumeroBanios($n21NumeroBanios)
    {
        $this->n21NumeroBanios = $n21NumeroBanios;
    
        return $this;
    }

    /**
     * Get n21NumeroBanios
     *
     * @return integer 
     */
    public function getN21NumeroBanios()
    {
        return $this->n21NumeroBanios;
    }

    /**
     * Set n21NumeroDuchas
     *
     * @param integer $n21NumeroDuchas
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21NumeroDuchas($n21NumeroDuchas)
    {
        $this->n21NumeroDuchas = $n21NumeroDuchas;
    
        return $this;
    }

    /**
     * Get n21NumeroDuchas
     *
     * @return integer 
     */
    public function getN21NumeroDuchas()
    {
        return $this->n21NumeroDuchas;
    }

    /**
     * Set n21NumeroCocinas
     *
     * @param integer $n21NumeroCocinas
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21NumeroCocinas($n21NumeroCocinas)
    {
        $this->n21NumeroCocinas = $n21NumeroCocinas;
    
        return $this;
    }

    /**
     * Get n21NumeroCocinas
     *
     * @return integer 
     */
    public function getN21NumeroCocinas()
    {
        return $this->n21NumeroCocinas;
    }

    /**
     * Set n21MetrosArea
     *
     * @param string $n21MetrosArea
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21MetrosArea($n21MetrosArea)
    {
        $this->n21MetrosArea = $n21MetrosArea;
    
        return $this;
    }

    /**
     * Get n21MetrosArea
     *
     * @return string 
     */
    public function getN21MetrosArea()
    {
        return $this->n21MetrosArea;
    }

    /**
     * Set n21EsAmbienteTecho
     *
     * @param boolean $n21EsAmbienteTecho
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21EsAmbienteTecho($n21EsAmbienteTecho)
    {
        $this->n21EsAmbienteTecho = $n21EsAmbienteTecho;
    
        return $this;
    }

    /**
     * Get n21EsAmbienteTecho
     *
     * @return boolean 
     */
    public function getN21EsAmbienteTecho()
    {
        return $this->n21EsAmbienteTecho;
    }

    /**
     * Set n21EsAmbienteCieloFal
     *
     * @param boolean $n21EsAmbienteCieloFal
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21EsAmbienteCieloFal($n21EsAmbienteCieloFal)
    {
        $this->n21EsAmbienteCieloFal = $n21EsAmbienteCieloFal;
    
        return $this;
    }

    /**
     * Get n21EsAmbienteCieloFal
     *
     * @return boolean 
     */
    public function getN21EsAmbienteCieloFal()
    {
        return $this->n21EsAmbienteCieloFal;
    }

    /**
     * Set n21EsAmbientePuerta
     *
     * @param boolean $n21EsAmbientePuerta
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21EsAmbientePuerta($n21EsAmbientePuerta)
    {
        $this->n21EsAmbientePuerta = $n21EsAmbientePuerta;
    
        return $this;
    }

    /**
     * Get n21EsAmbientePuerta
     *
     * @return boolean 
     */
    public function getN21EsAmbientePuerta()
    {
        return $this->n21EsAmbientePuerta;
    }

    /**
     * Set n21EsAmbienteVentana
     *
     * @param boolean $n21EsAmbienteVentana
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21EsAmbienteVentana($n21EsAmbienteVentana)
    {
        $this->n21EsAmbienteVentana = $n21EsAmbienteVentana;
    
        return $this;
    }

    /**
     * Get n21EsAmbienteVentana
     *
     * @return boolean 
     */
    public function getN21EsAmbienteVentana()
    {
        return $this->n21EsAmbienteVentana;
    }

    /**
     * Set n21EsAmbienteMuros
     *
     * @param boolean $n21EsAmbienteMuros
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21EsAmbienteMuros($n21EsAmbienteMuros)
    {
        $this->n21EsAmbienteMuros = $n21EsAmbienteMuros;
    
        return $this;
    }

    /**
     * Get n21EsAmbienteMuros
     *
     * @return boolean 
     */
    public function getN21EsAmbienteMuros()
    {
        return $this->n21EsAmbienteMuros;
    }

    /**
     * Set n21EsAmbienteRevestimiento
     *
     * @param boolean $n21EsAmbienteRevestimiento
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21EsAmbienteRevestimiento($n21EsAmbienteRevestimiento)
    {
        $this->n21EsAmbienteRevestimiento = $n21EsAmbienteRevestimiento;
    
        return $this;
    }

    /**
     * Get n21EsAmbienteRevestimiento
     *
     * @return boolean 
     */
    public function getN21EsAmbienteRevestimiento()
    {
        return $this->n21EsAmbienteRevestimiento;
    }

    /**
     * Set n21EsAmbientePiso
     *
     * @param boolean $n21EsAmbientePiso
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21EsAmbientePiso($n21EsAmbientePiso)
    {
        $this->n21EsAmbientePiso = $n21EsAmbientePiso;
    
        return $this;
    }

    /**
     * Get n21EsAmbientePiso
     *
     * @return boolean 
     */
    public function getN21EsAmbientePiso()
    {
        return $this->n21EsAmbientePiso;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
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
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
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
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
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
     * Set n212AbreTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n212AbreTipo
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN212AbreTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n212AbreTipo = null)
    {
        $this->n212AbreTipo = $n212AbreTipo;
    
        return $this;
    }

    /**
     * Get n212AbreTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo 
     */
    public function getN212AbreTipo()
    {
        return $this->n212AbreTipo;
    }

    /**
     * Set infraestructuraH6Ambienteadministrativo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6Ambienteadministrativo $infraestructuraH6Ambienteadministrativo
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
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

    /**
     * Set n21AmbientePisoMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n21AmbientePisoMatTipo
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21AmbientePisoMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n21AmbientePisoMatTipo = null)
    {
        $this->n21AmbientePisoMatTipo = $n21AmbientePisoMatTipo;
    
        return $this;
    }

    /**
     * Get n21AmbientePisoMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo 
     */
    public function getN21AmbientePisoMatTipo()
    {
        return $this->n21AmbientePisoMatTipo;
    }

    /**
     * Set n21AmbienteRevestMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n21AmbienteRevestMatTipo
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21AmbienteRevestMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n21AmbienteRevestMatTipo = null)
    {
        $this->n21AmbienteRevestMatTipo = $n21AmbienteRevestMatTipo;
    
        return $this;
    }

    /**
     * Get n21AmbienteRevestMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo 
     */
    public function getN21AmbienteRevestMatTipo()
    {
        return $this->n21AmbienteRevestMatTipo;
    }

    /**
     * Set n21AmbienteMuroCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n21AmbienteMuroCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21AmbienteMuroCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n21AmbienteMuroCaracTipo = null)
    {
        $this->n21AmbienteMuroCaracTipo = $n21AmbienteMuroCaracTipo;
    
        return $this;
    }

    /**
     * Get n21AmbienteMuroCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo 
     */
    public function getN21AmbienteMuroCaracTipo()
    {
        return $this->n21AmbienteMuroCaracTipo;
    }

    /**
     * Set n21AmbienteMuroMatTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n21AmbienteMuroMatTipo
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21AmbienteMuroMatTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n21AmbienteMuroMatTipo = null)
    {
        $this->n21AmbienteMuroMatTipo = $n21AmbienteMuroMatTipo;
    
        return $this;
    }

    /**
     * Get n21AmbienteMuroMatTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo 
     */
    public function getN21AmbienteMuroMatTipo()
    {
        return $this->n21AmbienteMuroMatTipo;
    }

    /**
     * Set n21AmbienteVentanaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n21AmbienteVentanaTipo
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21AmbienteVentanaTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n21AmbienteVentanaTipo = null)
    {
        $this->n21AmbienteVentanaTipo = $n21AmbienteVentanaTipo;
    
        return $this;
    }

    /**
     * Get n21AmbienteVentanaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo 
     */
    public function getN21AmbienteVentanaTipo()
    {
        return $this->n21AmbienteVentanaTipo;
    }

    /**
     * Set n211SeguroTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n211SeguroTipo
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN211SeguroTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n211SeguroTipo = null)
    {
        $this->n211SeguroTipo = $n211SeguroTipo;
    
        return $this;
    }

    /**
     * Get n211SeguroTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo 
     */
    public function getN211SeguroTipo()
    {
        return $this->n211SeguroTipo;
    }

    /**
     * Set n21AmbientePisoCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n21AmbientePisoCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21AmbientePisoCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n21AmbientePisoCaracTipo = null)
    {
        $this->n21AmbientePisoCaracTipo = $n21AmbientePisoCaracTipo;
    
        return $this;
    }

    /**
     * Get n21AmbientePisoCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN21AmbientePisoCaracTipo()
    {
        return $this->n21AmbientePisoCaracTipo;
    }

    /**
     * Set n21AmbienteRevestCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n21AmbienteRevestCaracTipo
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21AmbienteRevestCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n21AmbienteRevestCaracTipo = null)
    {
        $this->n21AmbienteRevestCaracTipo = $n21AmbienteRevestCaracTipo;
    
        return $this;
    }

    /**
     * Get n21AmbienteRevestCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN21AmbienteRevestCaracTipo()
    {
        return $this->n21AmbienteRevestCaracTipo;
    }

    /**
     * Set n21AmbienteCieloFalTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n21AmbienteCieloFalTipo
     * @return InfraestructuraH6AmbienteadministrativoVivMaestros
     */
    public function setN21AmbienteCieloFalTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n21AmbienteCieloFalTipo = null)
    {
        $this->n21AmbienteCieloFalTipo = $n21AmbienteCieloFalTipo;
    
        return $this;
    }

    /**
     * Get n21AmbienteCieloFalTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN21AmbienteCieloFalTipo()
    {
        return $this->n21AmbienteCieloFalTipo;
    }
}
