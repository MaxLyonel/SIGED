<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH3RiesgosDelitos
 */
class InfraestructuraH3RiesgosDelitos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n21RoboCantidad;

    /**
     * @var boolean
     */
    private $n21EsHuboAsalto;

    /**
     * @var boolean
     */
    private $n11EsInundacion;

    /**
     * @var \DateTime
     */
    private $n11InundacionFechainicial;

    /**
     * @var \DateTime
     */
    private $n11InundacionFechafinal;

    /**
     * @var boolean
     */
    private $n11EsIncendio;

    /**
     * @var \DateTime
     */
    private $n11IncendioFechainicial;

    /**
     * @var \DateTime
     */
    private $n11IncendioFechafinal;

    /**
     * @var boolean
     */
    private $n11EsSequia;

    /**
     * @var \DateTime
     */
    private $n11SequiaFechainicial;

    /**
     * @var \DateTime
     */
    private $n11SequiaFechafinal;

    /**
     * @var boolean
     */
    private $n11EsDeslizamiento;

    /**
     * @var \DateTime
     */
    private $n11DeslizamientoFechainicial;

    /**
     * @var \DateTime
     */
    private $n11DeslizamientoFechafinal;

    /**
     * @var boolean
     */
    private $n11EsRiada;

    /**
     * @var \DateTime
     */
    private $n11RiadaFechainicial;

    /**
     * @var \DateTime
     */
    private $n11RiadaFechafinal;

    /**
     * @var boolean
     */
    private $n11EsSismo;

    /**
     * @var \DateTime
     */
    private $n11SismoFechainicial;

    /**
     * @var \DateTime
     */
    private $n11SismoFechafinal;

    /**
     * @var boolean
     */
    private $n11EsViento;

    /**
     * @var \DateTime
     */
    private $n11VientoFechainicial;

    /**
     * @var \DateTime
     */
    private $n11VientoFechafinal;

    /**
     * @var boolean
     */
    private $n11EsGranizada;

    /**
     * @var \DateTime
     */
    private $n11GranizadaFechainicial;

    /**
     * @var \DateTime
     */
    private $n11GranizadaFechafinal;

    /**
     * @var boolean
     */
    private $n11EsHelada;

    /**
     * @var \DateTime
     */
    private $n11HeladaFechainicial;

    /**
     * @var \DateTime
     */
    private $n11HeladaFechafinal;

    /**
     * @var boolean
     */
    private $n11EsHundimiento;

    /**
     * @var boolean
     */
    private $n12EsBarranco;

    /**
     * @var boolean
     */
    private $n12EsBosque;

    /**
     * @var boolean
     */
    private $n12EsRio;

    /**
     * @var boolean
     */
    private $n12EsCerro;

    /**
     * @var boolean
     */
    private $n12EsCentrominero;

    /**
     * @var boolean
     */
    private $n12EsBotadero;

    /**
     * @var boolean
     */
    private $n12EsFabrica;

    /**
     * @var boolean
     */
    private $n12EsPasofrontera;

    /**
     * @var boolean
     */
    private $n12EsBar;

    /**
     * @var boolean
     */
    private $n12EsEstacionelectrica;

    /**
     * @var boolean
     */
    private $n12EsZonariezgo;

    /**
     * @var boolean
     */
    private $n13SiSuspendieronclases;

    /**
     * @var boolean
     */
    private $n14SiAlbergue;

    /**
     * @var boolean
     */
    private $n15EsTimbrepanico;

    /**
     * @var boolean
     */
    private $n15EsExtintor;

    /**
     * @var boolean
     */
    private $n15EsSalidaemergencia;

    /**
     * @var boolean
     */
    private $n15EsCamaraseguridad;

    /**
     * @var boolean
     */
    private $n15SiFuncionaCamaraseguridad;

    /**
     * @var boolean
     */
    private $n15EsSenaleticaevac;

    /**
     * @var boolean
     */
    private $n15EsDepositoagua;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH3SenaleticaEvacTipo
     */
    private $n15InfraestructuraH3SenaleticaEvacTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH3SuspencionClasesTiempoTipo
     */
    private $n13InfraestructuraH3SuspencionClasesTiempoTipo;

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
     * Set n21RoboCantidad
     *
     * @param integer $n21RoboCantidad
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN21RoboCantidad($n21RoboCantidad)
    {
        $this->n21RoboCantidad = $n21RoboCantidad;
    
        return $this;
    }

    /**
     * Get n21RoboCantidad
     *
     * @return integer 
     */
    public function getN21RoboCantidad()
    {
        return $this->n21RoboCantidad;
    }

    /**
     * Set n21EsHuboAsalto
     *
     * @param boolean $n21EsHuboAsalto
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN21EsHuboAsalto($n21EsHuboAsalto)
    {
        $this->n21EsHuboAsalto = $n21EsHuboAsalto;
    
        return $this;
    }

    /**
     * Get n21EsHuboAsalto
     *
     * @return boolean 
     */
    public function getN21EsHuboAsalto()
    {
        return $this->n21EsHuboAsalto;
    }

    /**
     * Set n11EsInundacion
     *
     * @param boolean $n11EsInundacion
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11EsInundacion($n11EsInundacion)
    {
        $this->n11EsInundacion = $n11EsInundacion;
    
        return $this;
    }

    /**
     * Get n11EsInundacion
     *
     * @return boolean 
     */
    public function getN11EsInundacion()
    {
        return $this->n11EsInundacion;
    }

    /**
     * Set n11InundacionFechainicial
     *
     * @param \DateTime $n11InundacionFechainicial
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11InundacionFechainicial($n11InundacionFechainicial)
    {
        $this->n11InundacionFechainicial = $n11InundacionFechainicial;
    
        return $this;
    }

    /**
     * Get n11InundacionFechainicial
     *
     * @return \DateTime 
     */
    public function getN11InundacionFechainicial()
    {
        return $this->n11InundacionFechainicial;
    }

    /**
     * Set n11InundacionFechafinal
     *
     * @param \DateTime $n11InundacionFechafinal
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11InundacionFechafinal($n11InundacionFechafinal)
    {
        $this->n11InundacionFechafinal = $n11InundacionFechafinal;
    
        return $this;
    }

    /**
     * Get n11InundacionFechafinal
     *
     * @return \DateTime 
     */
    public function getN11InundacionFechafinal()
    {
        return $this->n11InundacionFechafinal;
    }

    /**
     * Set n11EsIncendio
     *
     * @param boolean $n11EsIncendio
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11EsIncendio($n11EsIncendio)
    {
        $this->n11EsIncendio = $n11EsIncendio;
    
        return $this;
    }

    /**
     * Get n11EsIncendio
     *
     * @return boolean 
     */
    public function getN11EsIncendio()
    {
        return $this->n11EsIncendio;
    }

    /**
     * Set n11IncendioFechainicial
     *
     * @param \DateTime $n11IncendioFechainicial
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11IncendioFechainicial($n11IncendioFechainicial)
    {
        $this->n11IncendioFechainicial = $n11IncendioFechainicial;
    
        return $this;
    }

    /**
     * Get n11IncendioFechainicial
     *
     * @return \DateTime 
     */
    public function getN11IncendioFechainicial()
    {
        return $this->n11IncendioFechainicial;
    }

    /**
     * Set n11IncendioFechafinal
     *
     * @param \DateTime $n11IncendioFechafinal
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11IncendioFechafinal($n11IncendioFechafinal)
    {
        $this->n11IncendioFechafinal = $n11IncendioFechafinal;
    
        return $this;
    }

    /**
     * Get n11IncendioFechafinal
     *
     * @return \DateTime 
     */
    public function getN11IncendioFechafinal()
    {
        return $this->n11IncendioFechafinal;
    }

    /**
     * Set n11EsSequia
     *
     * @param boolean $n11EsSequia
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11EsSequia($n11EsSequia)
    {
        $this->n11EsSequia = $n11EsSequia;
    
        return $this;
    }

    /**
     * Get n11EsSequia
     *
     * @return boolean 
     */
    public function getN11EsSequia()
    {
        return $this->n11EsSequia;
    }

    /**
     * Set n11SequiaFechainicial
     *
     * @param \DateTime $n11SequiaFechainicial
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11SequiaFechainicial($n11SequiaFechainicial)
    {
        $this->n11SequiaFechainicial = $n11SequiaFechainicial;
    
        return $this;
    }

    /**
     * Get n11SequiaFechainicial
     *
     * @return \DateTime 
     */
    public function getN11SequiaFechainicial()
    {
        return $this->n11SequiaFechainicial;
    }

    /**
     * Set n11SequiaFechafinal
     *
     * @param \DateTime $n11SequiaFechafinal
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11SequiaFechafinal($n11SequiaFechafinal)
    {
        $this->n11SequiaFechafinal = $n11SequiaFechafinal;
    
        return $this;
    }

    /**
     * Get n11SequiaFechafinal
     *
     * @return \DateTime 
     */
    public function getN11SequiaFechafinal()
    {
        return $this->n11SequiaFechafinal;
    }

    /**
     * Set n11EsDeslizamiento
     *
     * @param boolean $n11EsDeslizamiento
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11EsDeslizamiento($n11EsDeslizamiento)
    {
        $this->n11EsDeslizamiento = $n11EsDeslizamiento;
    
        return $this;
    }

    /**
     * Get n11EsDeslizamiento
     *
     * @return boolean 
     */
    public function getN11EsDeslizamiento()
    {
        return $this->n11EsDeslizamiento;
    }

    /**
     * Set n11DeslizamientoFechainicial
     *
     * @param \DateTime $n11DeslizamientoFechainicial
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11DeslizamientoFechainicial($n11DeslizamientoFechainicial)
    {
        $this->n11DeslizamientoFechainicial = $n11DeslizamientoFechainicial;
    
        return $this;
    }

    /**
     * Get n11DeslizamientoFechainicial
     *
     * @return \DateTime 
     */
    public function getN11DeslizamientoFechainicial()
    {
        return $this->n11DeslizamientoFechainicial;
    }

    /**
     * Set n11DeslizamientoFechafinal
     *
     * @param \DateTime $n11DeslizamientoFechafinal
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11DeslizamientoFechafinal($n11DeslizamientoFechafinal)
    {
        $this->n11DeslizamientoFechafinal = $n11DeslizamientoFechafinal;
    
        return $this;
    }

    /**
     * Get n11DeslizamientoFechafinal
     *
     * @return \DateTime 
     */
    public function getN11DeslizamientoFechafinal()
    {
        return $this->n11DeslizamientoFechafinal;
    }

    /**
     * Set n11EsRiada
     *
     * @param boolean $n11EsRiada
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11EsRiada($n11EsRiada)
    {
        $this->n11EsRiada = $n11EsRiada;
    
        return $this;
    }

    /**
     * Get n11EsRiada
     *
     * @return boolean 
     */
    public function getN11EsRiada()
    {
        return $this->n11EsRiada;
    }

    /**
     * Set n11RiadaFechainicial
     *
     * @param \DateTime $n11RiadaFechainicial
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11RiadaFechainicial($n11RiadaFechainicial)
    {
        $this->n11RiadaFechainicial = $n11RiadaFechainicial;
    
        return $this;
    }

    /**
     * Get n11RiadaFechainicial
     *
     * @return \DateTime 
     */
    public function getN11RiadaFechainicial()
    {
        return $this->n11RiadaFechainicial;
    }

    /**
     * Set n11RiadaFechafinal
     *
     * @param \DateTime $n11RiadaFechafinal
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11RiadaFechafinal($n11RiadaFechafinal)
    {
        $this->n11RiadaFechafinal = $n11RiadaFechafinal;
    
        return $this;
    }

    /**
     * Get n11RiadaFechafinal
     *
     * @return \DateTime 
     */
    public function getN11RiadaFechafinal()
    {
        return $this->n11RiadaFechafinal;
    }

    /**
     * Set n11EsSismo
     *
     * @param boolean $n11EsSismo
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11EsSismo($n11EsSismo)
    {
        $this->n11EsSismo = $n11EsSismo;
    
        return $this;
    }

    /**
     * Get n11EsSismo
     *
     * @return boolean 
     */
    public function getN11EsSismo()
    {
        return $this->n11EsSismo;
    }

    /**
     * Set n11SismoFechainicial
     *
     * @param \DateTime $n11SismoFechainicial
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11SismoFechainicial($n11SismoFechainicial)
    {
        $this->n11SismoFechainicial = $n11SismoFechainicial;
    
        return $this;
    }

    /**
     * Get n11SismoFechainicial
     *
     * @return \DateTime 
     */
    public function getN11SismoFechainicial()
    {
        return $this->n11SismoFechainicial;
    }

    /**
     * Set n11SismoFechafinal
     *
     * @param \DateTime $n11SismoFechafinal
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11SismoFechafinal($n11SismoFechafinal)
    {
        $this->n11SismoFechafinal = $n11SismoFechafinal;
    
        return $this;
    }

    /**
     * Get n11SismoFechafinal
     *
     * @return \DateTime 
     */
    public function getN11SismoFechafinal()
    {
        return $this->n11SismoFechafinal;
    }

    /**
     * Set n11EsViento
     *
     * @param boolean $n11EsViento
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11EsViento($n11EsViento)
    {
        $this->n11EsViento = $n11EsViento;
    
        return $this;
    }

    /**
     * Get n11EsViento
     *
     * @return boolean 
     */
    public function getN11EsViento()
    {
        return $this->n11EsViento;
    }

    /**
     * Set n11VientoFechainicial
     *
     * @param \DateTime $n11VientoFechainicial
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11VientoFechainicial($n11VientoFechainicial)
    {
        $this->n11VientoFechainicial = $n11VientoFechainicial;
    
        return $this;
    }

    /**
     * Get n11VientoFechainicial
     *
     * @return \DateTime 
     */
    public function getN11VientoFechainicial()
    {
        return $this->n11VientoFechainicial;
    }

    /**
     * Set n11VientoFechafinal
     *
     * @param \DateTime $n11VientoFechafinal
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11VientoFechafinal($n11VientoFechafinal)
    {
        $this->n11VientoFechafinal = $n11VientoFechafinal;
    
        return $this;
    }

    /**
     * Get n11VientoFechafinal
     *
     * @return \DateTime 
     */
    public function getN11VientoFechafinal()
    {
        return $this->n11VientoFechafinal;
    }

    /**
     * Set n11EsGranizada
     *
     * @param boolean $n11EsGranizada
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11EsGranizada($n11EsGranizada)
    {
        $this->n11EsGranizada = $n11EsGranizada;
    
        return $this;
    }

    /**
     * Get n11EsGranizada
     *
     * @return boolean 
     */
    public function getN11EsGranizada()
    {
        return $this->n11EsGranizada;
    }

    /**
     * Set n11GranizadaFechainicial
     *
     * @param \DateTime $n11GranizadaFechainicial
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11GranizadaFechainicial($n11GranizadaFechainicial)
    {
        $this->n11GranizadaFechainicial = $n11GranizadaFechainicial;
    
        return $this;
    }

    /**
     * Get n11GranizadaFechainicial
     *
     * @return \DateTime 
     */
    public function getN11GranizadaFechainicial()
    {
        return $this->n11GranizadaFechainicial;
    }

    /**
     * Set n11GranizadaFechafinal
     *
     * @param \DateTime $n11GranizadaFechafinal
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11GranizadaFechafinal($n11GranizadaFechafinal)
    {
        $this->n11GranizadaFechafinal = $n11GranizadaFechafinal;
    
        return $this;
    }

    /**
     * Get n11GranizadaFechafinal
     *
     * @return \DateTime 
     */
    public function getN11GranizadaFechafinal()
    {
        return $this->n11GranizadaFechafinal;
    }

    /**
     * Set n11EsHelada
     *
     * @param boolean $n11EsHelada
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11EsHelada($n11EsHelada)
    {
        $this->n11EsHelada = $n11EsHelada;
    
        return $this;
    }

    /**
     * Get n11EsHelada
     *
     * @return boolean 
     */
    public function getN11EsHelada()
    {
        return $this->n11EsHelada;
    }

    /**
     * Set n11HeladaFechainicial
     *
     * @param \DateTime $n11HeladaFechainicial
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11HeladaFechainicial($n11HeladaFechainicial)
    {
        $this->n11HeladaFechainicial = $n11HeladaFechainicial;
    
        return $this;
    }

    /**
     * Get n11HeladaFechainicial
     *
     * @return \DateTime 
     */
    public function getN11HeladaFechainicial()
    {
        return $this->n11HeladaFechainicial;
    }

    /**
     * Set n11HeladaFechafinal
     *
     * @param \DateTime $n11HeladaFechafinal
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11HeladaFechafinal($n11HeladaFechafinal)
    {
        $this->n11HeladaFechafinal = $n11HeladaFechafinal;
    
        return $this;
    }

    /**
     * Get n11HeladaFechafinal
     *
     * @return \DateTime 
     */
    public function getN11HeladaFechafinal()
    {
        return $this->n11HeladaFechafinal;
    }

    /**
     * Set n11EsHundimiento
     *
     * @param boolean $n11EsHundimiento
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN11EsHundimiento($n11EsHundimiento)
    {
        $this->n11EsHundimiento = $n11EsHundimiento;
    
        return $this;
    }

    /**
     * Get n11EsHundimiento
     *
     * @return boolean 
     */
    public function getN11EsHundimiento()
    {
        return $this->n11EsHundimiento;
    }

    /**
     * Set n12EsBarranco
     *
     * @param boolean $n12EsBarranco
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN12EsBarranco($n12EsBarranco)
    {
        $this->n12EsBarranco = $n12EsBarranco;
    
        return $this;
    }

    /**
     * Get n12EsBarranco
     *
     * @return boolean 
     */
    public function getN12EsBarranco()
    {
        return $this->n12EsBarranco;
    }

    /**
     * Set n12EsBosque
     *
     * @param boolean $n12EsBosque
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN12EsBosque($n12EsBosque)
    {
        $this->n12EsBosque = $n12EsBosque;
    
        return $this;
    }

    /**
     * Get n12EsBosque
     *
     * @return boolean 
     */
    public function getN12EsBosque()
    {
        return $this->n12EsBosque;
    }

    /**
     * Set n12EsRio
     *
     * @param boolean $n12EsRio
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN12EsRio($n12EsRio)
    {
        $this->n12EsRio = $n12EsRio;
    
        return $this;
    }

    /**
     * Get n12EsRio
     *
     * @return boolean 
     */
    public function getN12EsRio()
    {
        return $this->n12EsRio;
    }

    /**
     * Set n12EsCerro
     *
     * @param boolean $n12EsCerro
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN12EsCerro($n12EsCerro)
    {
        $this->n12EsCerro = $n12EsCerro;
    
        return $this;
    }

    /**
     * Get n12EsCerro
     *
     * @return boolean 
     */
    public function getN12EsCerro()
    {
        return $this->n12EsCerro;
    }

    /**
     * Set n12EsCentrominero
     *
     * @param boolean $n12EsCentrominero
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN12EsCentrominero($n12EsCentrominero)
    {
        $this->n12EsCentrominero = $n12EsCentrominero;
    
        return $this;
    }

    /**
     * Get n12EsCentrominero
     *
     * @return boolean 
     */
    public function getN12EsCentrominero()
    {
        return $this->n12EsCentrominero;
    }

    /**
     * Set n12EsBotadero
     *
     * @param boolean $n12EsBotadero
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN12EsBotadero($n12EsBotadero)
    {
        $this->n12EsBotadero = $n12EsBotadero;
    
        return $this;
    }

    /**
     * Get n12EsBotadero
     *
     * @return boolean 
     */
    public function getN12EsBotadero()
    {
        return $this->n12EsBotadero;
    }

    /**
     * Set n12EsFabrica
     *
     * @param boolean $n12EsFabrica
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN12EsFabrica($n12EsFabrica)
    {
        $this->n12EsFabrica = $n12EsFabrica;
    
        return $this;
    }

    /**
     * Get n12EsFabrica
     *
     * @return boolean 
     */
    public function getN12EsFabrica()
    {
        return $this->n12EsFabrica;
    }

    /**
     * Set n12EsPasofrontera
     *
     * @param boolean $n12EsPasofrontera
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN12EsPasofrontera($n12EsPasofrontera)
    {
        $this->n12EsPasofrontera = $n12EsPasofrontera;
    
        return $this;
    }

    /**
     * Get n12EsPasofrontera
     *
     * @return boolean 
     */
    public function getN12EsPasofrontera()
    {
        return $this->n12EsPasofrontera;
    }

    /**
     * Set n12EsBar
     *
     * @param boolean $n12EsBar
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN12EsBar($n12EsBar)
    {
        $this->n12EsBar = $n12EsBar;
    
        return $this;
    }

    /**
     * Get n12EsBar
     *
     * @return boolean 
     */
    public function getN12EsBar()
    {
        return $this->n12EsBar;
    }

    /**
     * Set n12EsEstacionelectrica
     *
     * @param boolean $n12EsEstacionelectrica
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN12EsEstacionelectrica($n12EsEstacionelectrica)
    {
        $this->n12EsEstacionelectrica = $n12EsEstacionelectrica;
    
        return $this;
    }

    /**
     * Get n12EsEstacionelectrica
     *
     * @return boolean 
     */
    public function getN12EsEstacionelectrica()
    {
        return $this->n12EsEstacionelectrica;
    }

    /**
     * Set n12EsZonariezgo
     *
     * @param boolean $n12EsZonariezgo
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN12EsZonariezgo($n12EsZonariezgo)
    {
        $this->n12EsZonariezgo = $n12EsZonariezgo;
    
        return $this;
    }

    /**
     * Get n12EsZonariezgo
     *
     * @return boolean 
     */
    public function getN12EsZonariezgo()
    {
        return $this->n12EsZonariezgo;
    }

    /**
     * Set n13SiSuspendieronclases
     *
     * @param boolean $n13SiSuspendieronclases
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN13SiSuspendieronclases($n13SiSuspendieronclases)
    {
        $this->n13SiSuspendieronclases = $n13SiSuspendieronclases;
    
        return $this;
    }

    /**
     * Get n13SiSuspendieronclases
     *
     * @return boolean 
     */
    public function getN13SiSuspendieronclases()
    {
        return $this->n13SiSuspendieronclases;
    }

    /**
     * Set n14SiAlbergue
     *
     * @param boolean $n14SiAlbergue
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN14SiAlbergue($n14SiAlbergue)
    {
        $this->n14SiAlbergue = $n14SiAlbergue;
    
        return $this;
    }

    /**
     * Get n14SiAlbergue
     *
     * @return boolean 
     */
    public function getN14SiAlbergue()
    {
        return $this->n14SiAlbergue;
    }

    /**
     * Set n15EsTimbrepanico
     *
     * @param boolean $n15EsTimbrepanico
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN15EsTimbrepanico($n15EsTimbrepanico)
    {
        $this->n15EsTimbrepanico = $n15EsTimbrepanico;
    
        return $this;
    }

    /**
     * Get n15EsTimbrepanico
     *
     * @return boolean 
     */
    public function getN15EsTimbrepanico()
    {
        return $this->n15EsTimbrepanico;
    }

    /**
     * Set n15EsExtintor
     *
     * @param boolean $n15EsExtintor
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN15EsExtintor($n15EsExtintor)
    {
        $this->n15EsExtintor = $n15EsExtintor;
    
        return $this;
    }

    /**
     * Get n15EsExtintor
     *
     * @return boolean 
     */
    public function getN15EsExtintor()
    {
        return $this->n15EsExtintor;
    }

    /**
     * Set n15EsSalidaemergencia
     *
     * @param boolean $n15EsSalidaemergencia
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN15EsSalidaemergencia($n15EsSalidaemergencia)
    {
        $this->n15EsSalidaemergencia = $n15EsSalidaemergencia;
    
        return $this;
    }

    /**
     * Get n15EsSalidaemergencia
     *
     * @return boolean 
     */
    public function getN15EsSalidaemergencia()
    {
        return $this->n15EsSalidaemergencia;
    }

    /**
     * Set n15EsCamaraseguridad
     *
     * @param boolean $n15EsCamaraseguridad
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN15EsCamaraseguridad($n15EsCamaraseguridad)
    {
        $this->n15EsCamaraseguridad = $n15EsCamaraseguridad;
    
        return $this;
    }

    /**
     * Get n15EsCamaraseguridad
     *
     * @return boolean 
     */
    public function getN15EsCamaraseguridad()
    {
        return $this->n15EsCamaraseguridad;
    }

    /**
     * Set n15SiFuncionaCamaraseguridad
     *
     * @param boolean $n15SiFuncionaCamaraseguridad
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN15SiFuncionaCamaraseguridad($n15SiFuncionaCamaraseguridad)
    {
        $this->n15SiFuncionaCamaraseguridad = $n15SiFuncionaCamaraseguridad;
    
        return $this;
    }

    /**
     * Get n15SiFuncionaCamaraseguridad
     *
     * @return boolean 
     */
    public function getN15SiFuncionaCamaraseguridad()
    {
        return $this->n15SiFuncionaCamaraseguridad;
    }

    /**
     * Set n15EsSenaleticaevac
     *
     * @param boolean $n15EsSenaleticaevac
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN15EsSenaleticaevac($n15EsSenaleticaevac)
    {
        $this->n15EsSenaleticaevac = $n15EsSenaleticaevac;
    
        return $this;
    }

    /**
     * Get n15EsSenaleticaevac
     *
     * @return boolean 
     */
    public function getN15EsSenaleticaevac()
    {
        return $this->n15EsSenaleticaevac;
    }

    /**
     * Set n15EsDepositoagua
     *
     * @param boolean $n15EsDepositoagua
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN15EsDepositoagua($n15EsDepositoagua)
    {
        $this->n15EsDepositoagua = $n15EsDepositoagua;
    
        return $this;
    }

    /**
     * Get n15EsDepositoagua
     *
     * @return boolean 
     */
    public function getN15EsDepositoagua()
    {
        return $this->n15EsDepositoagua;
    }

    /**
     * Set n15InfraestructuraH3SenaleticaEvacTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH3SenaleticaEvacTipo $n15InfraestructuraH3SenaleticaEvacTipo
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN15InfraestructuraH3SenaleticaEvacTipo(\Sie\AppWebBundle\Entity\InfraestructuraH3SenaleticaEvacTipo $n15InfraestructuraH3SenaleticaEvacTipo = null)
    {
        $this->n15InfraestructuraH3SenaleticaEvacTipo = $n15InfraestructuraH3SenaleticaEvacTipo;
    
        return $this;
    }

    /**
     * Get n15InfraestructuraH3SenaleticaEvacTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH3SenaleticaEvacTipo 
     */
    public function getN15InfraestructuraH3SenaleticaEvacTipo()
    {
        return $this->n15InfraestructuraH3SenaleticaEvacTipo;
    }

    /**
     * Set n13InfraestructuraH3SuspencionClasesTiempoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH3SuspencionClasesTiempoTipo $n13InfraestructuraH3SuspencionClasesTiempoTipo
     * @return InfraestructuraH3RiesgosDelitos
     */
    public function setN13InfraestructuraH3SuspencionClasesTiempoTipo(\Sie\AppWebBundle\Entity\InfraestructuraH3SuspencionClasesTiempoTipo $n13InfraestructuraH3SuspencionClasesTiempoTipo = null)
    {
        $this->n13InfraestructuraH3SuspencionClasesTiempoTipo = $n13InfraestructuraH3SuspencionClasesTiempoTipo;
    
        return $this;
    }

    /**
     * Get n13InfraestructuraH3SuspencionClasesTiempoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH3SuspencionClasesTiempoTipo 
     */
    public function getN13InfraestructuraH3SuspencionClasesTiempoTipo()
    {
        return $this->n13InfraestructuraH3SuspencionClasesTiempoTipo;
    }

    /**
     * Set infraestructuraJuridiccionGeografica
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica $infraestructuraJuridiccionGeografica
     * @return InfraestructuraH3RiesgosDelitos
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
