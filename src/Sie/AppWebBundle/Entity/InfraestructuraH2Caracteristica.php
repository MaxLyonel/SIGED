<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2Caracteristica
 */
class InfraestructuraH2Caracteristica
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $n11Areaconstruida;

    /**
     * @var string
     */
    private $n11Arearecreacion;

    /**
     * @var string
     */
    private $n11Areanoconstruida;

    /**
     * @var string
     */
    private $n11Areacultivo;

    /**
     * @var string
     */
    private $n11Areacriaanimales;

    /**
     * @var integer
     */
    private $n13MuroperimetralArea;

    /**
     * @var string
     */
    private $n13MuroperimetralActual;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoInundacion;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoRiada;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoTormenta;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoIncendio;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoGranizo;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoHumedad;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoBarranco;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoContaminacion;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoDeslizamiento;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoSifonamiento;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoTerremoto;

    /**
     * @var boolean
     */
    private $n14ConEsRiesgoNoexiste;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoInundacion;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoRiada;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoTormenta;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoIncendio;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoGranizo;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoHumedad;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoBarranco;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoContaminacion;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoDeslizamiento;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoSifonamiento;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoTerremoto;

    /**
     * @var boolean
     */
    private $n15CulEsRiesgoNoexiste;

    /**
     * @var boolean
     */
    private $n21EsConstruidaEducativo;

    /**
     * @var integer
     */
    private $n22AnioConstruccion;

    /**
     * @var integer
     */
    private $n23AnioRefaccion;

    /**
     * @var integer
     */
    private $n24AnioAmpliacion;

    /**
     * @var string
     */
    private $n26Razonsocial;

    /**
     * @var string
     */
    private $n29DocumentoObs;

    /**
     * @var string
     */
    private $n29DocumentoNroPartida;

    /**
     * @var boolean
     */
    private $n29EsPlanoAprobado;

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
    private $n11Areatotal;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2DocumentoTipo
     */
    private $n29DocumentoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n28CieloEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n28PisoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n28ParedEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n28TechoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n27EstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2PropiedadTipo
     */
    private $n25PropiedadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenAvanceTipo
     */
    private $n13MuroperimetralAvanceTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2TopografiaTipo
     */
    private $n12TopografiaTipo;

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
     * Set n11Areaconstruida
     *
     * @param string $n11Areaconstruida
     * @return InfraestructuraH2Caracteristica
     */
    public function setN11Areaconstruida($n11Areaconstruida)
    {
        $this->n11Areaconstruida = $n11Areaconstruida;
    
        return $this;
    }

    /**
     * Get n11Areaconstruida
     *
     * @return string 
     */
    public function getN11Areaconstruida()
    {
        return $this->n11Areaconstruida;
    }

    /**
     * Set n11Arearecreacion
     *
     * @param string $n11Arearecreacion
     * @return InfraestructuraH2Caracteristica
     */
    public function setN11Arearecreacion($n11Arearecreacion)
    {
        $this->n11Arearecreacion = $n11Arearecreacion;
    
        return $this;
    }

    /**
     * Get n11Arearecreacion
     *
     * @return string 
     */
    public function getN11Arearecreacion()
    {
        return $this->n11Arearecreacion;
    }

    /**
     * Set n11Areanoconstruida
     *
     * @param string $n11Areanoconstruida
     * @return InfraestructuraH2Caracteristica
     */
    public function setN11Areanoconstruida($n11Areanoconstruida)
    {
        $this->n11Areanoconstruida = $n11Areanoconstruida;
    
        return $this;
    }

    /**
     * Get n11Areanoconstruida
     *
     * @return string 
     */
    public function getN11Areanoconstruida()
    {
        return $this->n11Areanoconstruida;
    }

    /**
     * Set n11Areacultivo
     *
     * @param string $n11Areacultivo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN11Areacultivo($n11Areacultivo)
    {
        $this->n11Areacultivo = $n11Areacultivo;
    
        return $this;
    }

    /**
     * Get n11Areacultivo
     *
     * @return string 
     */
    public function getN11Areacultivo()
    {
        return $this->n11Areacultivo;
    }

    /**
     * Set n11Areacriaanimales
     *
     * @param string $n11Areacriaanimales
     * @return InfraestructuraH2Caracteristica
     */
    public function setN11Areacriaanimales($n11Areacriaanimales)
    {
        $this->n11Areacriaanimales = $n11Areacriaanimales;
    
        return $this;
    }

    /**
     * Get n11Areacriaanimales
     *
     * @return string 
     */
    public function getN11Areacriaanimales()
    {
        return $this->n11Areacriaanimales;
    }

    /**
     * Set n13MuroperimetralArea
     *
     * @param integer $n13MuroperimetralArea
     * @return InfraestructuraH2Caracteristica
     */
    public function setN13MuroperimetralArea($n13MuroperimetralArea)
    {
        $this->n13MuroperimetralArea = $n13MuroperimetralArea;
    
        return $this;
    }

    /**
     * Get n13MuroperimetralArea
     *
     * @return integer 
     */
    public function getN13MuroperimetralArea()
    {
        return $this->n13MuroperimetralArea;
    }

    /**
     * Set n13MuroperimetralActual
     *
     * @param string $n13MuroperimetralActual
     * @return InfraestructuraH2Caracteristica
     */
    public function setN13MuroperimetralActual($n13MuroperimetralActual)
    {
        $this->n13MuroperimetralActual = $n13MuroperimetralActual;
    
        return $this;
    }

    /**
     * Get n13MuroperimetralActual
     *
     * @return string 
     */
    public function getN13MuroperimetralActual()
    {
        return $this->n13MuroperimetralActual;
    }

    /**
     * Set n14ConEsRiesgoInundacion
     *
     * @param boolean $n14ConEsRiesgoInundacion
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoInundacion($n14ConEsRiesgoInundacion)
    {
        $this->n14ConEsRiesgoInundacion = $n14ConEsRiesgoInundacion;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoInundacion
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoInundacion()
    {
        return $this->n14ConEsRiesgoInundacion;
    }

    /**
     * Set n14ConEsRiesgoRiada
     *
     * @param boolean $n14ConEsRiesgoRiada
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoRiada($n14ConEsRiesgoRiada)
    {
        $this->n14ConEsRiesgoRiada = $n14ConEsRiesgoRiada;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoRiada
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoRiada()
    {
        return $this->n14ConEsRiesgoRiada;
    }

    /**
     * Set n14ConEsRiesgoTormenta
     *
     * @param boolean $n14ConEsRiesgoTormenta
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoTormenta($n14ConEsRiesgoTormenta)
    {
        $this->n14ConEsRiesgoTormenta = $n14ConEsRiesgoTormenta;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoTormenta
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoTormenta()
    {
        return $this->n14ConEsRiesgoTormenta;
    }

    /**
     * Set n14ConEsRiesgoIncendio
     *
     * @param boolean $n14ConEsRiesgoIncendio
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoIncendio($n14ConEsRiesgoIncendio)
    {
        $this->n14ConEsRiesgoIncendio = $n14ConEsRiesgoIncendio;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoIncendio
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoIncendio()
    {
        return $this->n14ConEsRiesgoIncendio;
    }

    /**
     * Set n14ConEsRiesgoGranizo
     *
     * @param boolean $n14ConEsRiesgoGranizo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoGranizo($n14ConEsRiesgoGranizo)
    {
        $this->n14ConEsRiesgoGranizo = $n14ConEsRiesgoGranizo;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoGranizo
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoGranizo()
    {
        return $this->n14ConEsRiesgoGranizo;
    }

    /**
     * Set n14ConEsRiesgoHumedad
     *
     * @param boolean $n14ConEsRiesgoHumedad
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoHumedad($n14ConEsRiesgoHumedad)
    {
        $this->n14ConEsRiesgoHumedad = $n14ConEsRiesgoHumedad;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoHumedad
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoHumedad()
    {
        return $this->n14ConEsRiesgoHumedad;
    }

    /**
     * Set n14ConEsRiesgoBarranco
     *
     * @param boolean $n14ConEsRiesgoBarranco
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoBarranco($n14ConEsRiesgoBarranco)
    {
        $this->n14ConEsRiesgoBarranco = $n14ConEsRiesgoBarranco;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoBarranco
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoBarranco()
    {
        return $this->n14ConEsRiesgoBarranco;
    }

    /**
     * Set n14ConEsRiesgoContaminacion
     *
     * @param boolean $n14ConEsRiesgoContaminacion
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoContaminacion($n14ConEsRiesgoContaminacion)
    {
        $this->n14ConEsRiesgoContaminacion = $n14ConEsRiesgoContaminacion;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoContaminacion
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoContaminacion()
    {
        return $this->n14ConEsRiesgoContaminacion;
    }

    /**
     * Set n14ConEsRiesgoDeslizamiento
     *
     * @param boolean $n14ConEsRiesgoDeslizamiento
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoDeslizamiento($n14ConEsRiesgoDeslizamiento)
    {
        $this->n14ConEsRiesgoDeslizamiento = $n14ConEsRiesgoDeslizamiento;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoDeslizamiento
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoDeslizamiento()
    {
        return $this->n14ConEsRiesgoDeslizamiento;
    }

    /**
     * Set n14ConEsRiesgoSifonamiento
     *
     * @param boolean $n14ConEsRiesgoSifonamiento
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoSifonamiento($n14ConEsRiesgoSifonamiento)
    {
        $this->n14ConEsRiesgoSifonamiento = $n14ConEsRiesgoSifonamiento;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoSifonamiento
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoSifonamiento()
    {
        return $this->n14ConEsRiesgoSifonamiento;
    }

    /**
     * Set n14ConEsRiesgoTerremoto
     *
     * @param boolean $n14ConEsRiesgoTerremoto
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoTerremoto($n14ConEsRiesgoTerremoto)
    {
        $this->n14ConEsRiesgoTerremoto = $n14ConEsRiesgoTerremoto;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoTerremoto
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoTerremoto()
    {
        return $this->n14ConEsRiesgoTerremoto;
    }

    /**
     * Set n14ConEsRiesgoNoexiste
     *
     * @param boolean $n14ConEsRiesgoNoexiste
     * @return InfraestructuraH2Caracteristica
     */
    public function setN14ConEsRiesgoNoexiste($n14ConEsRiesgoNoexiste)
    {
        $this->n14ConEsRiesgoNoexiste = $n14ConEsRiesgoNoexiste;
    
        return $this;
    }

    /**
     * Get n14ConEsRiesgoNoexiste
     *
     * @return boolean 
     */
    public function getN14ConEsRiesgoNoexiste()
    {
        return $this->n14ConEsRiesgoNoexiste;
    }

    /**
     * Set n15CulEsRiesgoInundacion
     *
     * @param boolean $n15CulEsRiesgoInundacion
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoInundacion($n15CulEsRiesgoInundacion)
    {
        $this->n15CulEsRiesgoInundacion = $n15CulEsRiesgoInundacion;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoInundacion
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoInundacion()
    {
        return $this->n15CulEsRiesgoInundacion;
    }

    /**
     * Set n15CulEsRiesgoRiada
     *
     * @param boolean $n15CulEsRiesgoRiada
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoRiada($n15CulEsRiesgoRiada)
    {
        $this->n15CulEsRiesgoRiada = $n15CulEsRiesgoRiada;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoRiada
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoRiada()
    {
        return $this->n15CulEsRiesgoRiada;
    }

    /**
     * Set n15CulEsRiesgoTormenta
     *
     * @param boolean $n15CulEsRiesgoTormenta
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoTormenta($n15CulEsRiesgoTormenta)
    {
        $this->n15CulEsRiesgoTormenta = $n15CulEsRiesgoTormenta;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoTormenta
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoTormenta()
    {
        return $this->n15CulEsRiesgoTormenta;
    }

    /**
     * Set n15CulEsRiesgoIncendio
     *
     * @param boolean $n15CulEsRiesgoIncendio
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoIncendio($n15CulEsRiesgoIncendio)
    {
        $this->n15CulEsRiesgoIncendio = $n15CulEsRiesgoIncendio;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoIncendio
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoIncendio()
    {
        return $this->n15CulEsRiesgoIncendio;
    }

    /**
     * Set n15CulEsRiesgoGranizo
     *
     * @param boolean $n15CulEsRiesgoGranizo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoGranizo($n15CulEsRiesgoGranizo)
    {
        $this->n15CulEsRiesgoGranizo = $n15CulEsRiesgoGranizo;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoGranizo
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoGranizo()
    {
        return $this->n15CulEsRiesgoGranizo;
    }

    /**
     * Set n15CulEsRiesgoHumedad
     *
     * @param boolean $n15CulEsRiesgoHumedad
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoHumedad($n15CulEsRiesgoHumedad)
    {
        $this->n15CulEsRiesgoHumedad = $n15CulEsRiesgoHumedad;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoHumedad
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoHumedad()
    {
        return $this->n15CulEsRiesgoHumedad;
    }

    /**
     * Set n15CulEsRiesgoBarranco
     *
     * @param boolean $n15CulEsRiesgoBarranco
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoBarranco($n15CulEsRiesgoBarranco)
    {
        $this->n15CulEsRiesgoBarranco = $n15CulEsRiesgoBarranco;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoBarranco
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoBarranco()
    {
        return $this->n15CulEsRiesgoBarranco;
    }

    /**
     * Set n15CulEsRiesgoContaminacion
     *
     * @param boolean $n15CulEsRiesgoContaminacion
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoContaminacion($n15CulEsRiesgoContaminacion)
    {
        $this->n15CulEsRiesgoContaminacion = $n15CulEsRiesgoContaminacion;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoContaminacion
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoContaminacion()
    {
        return $this->n15CulEsRiesgoContaminacion;
    }

    /**
     * Set n15CulEsRiesgoDeslizamiento
     *
     * @param boolean $n15CulEsRiesgoDeslizamiento
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoDeslizamiento($n15CulEsRiesgoDeslizamiento)
    {
        $this->n15CulEsRiesgoDeslizamiento = $n15CulEsRiesgoDeslizamiento;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoDeslizamiento
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoDeslizamiento()
    {
        return $this->n15CulEsRiesgoDeslizamiento;
    }

    /**
     * Set n15CulEsRiesgoSifonamiento
     *
     * @param boolean $n15CulEsRiesgoSifonamiento
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoSifonamiento($n15CulEsRiesgoSifonamiento)
    {
        $this->n15CulEsRiesgoSifonamiento = $n15CulEsRiesgoSifonamiento;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoSifonamiento
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoSifonamiento()
    {
        return $this->n15CulEsRiesgoSifonamiento;
    }

    /**
     * Set n15CulEsRiesgoTerremoto
     *
     * @param boolean $n15CulEsRiesgoTerremoto
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoTerremoto($n15CulEsRiesgoTerremoto)
    {
        $this->n15CulEsRiesgoTerremoto = $n15CulEsRiesgoTerremoto;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoTerremoto
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoTerremoto()
    {
        return $this->n15CulEsRiesgoTerremoto;
    }

    /**
     * Set n15CulEsRiesgoNoexiste
     *
     * @param boolean $n15CulEsRiesgoNoexiste
     * @return InfraestructuraH2Caracteristica
     */
    public function setN15CulEsRiesgoNoexiste($n15CulEsRiesgoNoexiste)
    {
        $this->n15CulEsRiesgoNoexiste = $n15CulEsRiesgoNoexiste;
    
        return $this;
    }

    /**
     * Get n15CulEsRiesgoNoexiste
     *
     * @return boolean 
     */
    public function getN15CulEsRiesgoNoexiste()
    {
        return $this->n15CulEsRiesgoNoexiste;
    }

    /**
     * Set n21EsConstruidaEducativo
     *
     * @param boolean $n21EsConstruidaEducativo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN21EsConstruidaEducativo($n21EsConstruidaEducativo)
    {
        $this->n21EsConstruidaEducativo = $n21EsConstruidaEducativo;
    
        return $this;
    }

    /**
     * Get n21EsConstruidaEducativo
     *
     * @return boolean 
     */
    public function getN21EsConstruidaEducativo()
    {
        return $this->n21EsConstruidaEducativo;
    }

    /**
     * Set n22AnioConstruccion
     *
     * @param integer $n22AnioConstruccion
     * @return InfraestructuraH2Caracteristica
     */
    public function setN22AnioConstruccion($n22AnioConstruccion)
    {
        $this->n22AnioConstruccion = $n22AnioConstruccion;
    
        return $this;
    }

    /**
     * Get n22AnioConstruccion
     *
     * @return integer 
     */
    public function getN22AnioConstruccion()
    {
        return $this->n22AnioConstruccion;
    }

    /**
     * Set n23AnioRefaccion
     *
     * @param integer $n23AnioRefaccion
     * @return InfraestructuraH2Caracteristica
     */
    public function setN23AnioRefaccion($n23AnioRefaccion)
    {
        $this->n23AnioRefaccion = $n23AnioRefaccion;
    
        return $this;
    }

    /**
     * Get n23AnioRefaccion
     *
     * @return integer 
     */
    public function getN23AnioRefaccion()
    {
        return $this->n23AnioRefaccion;
    }

    /**
     * Set n24AnioAmpliacion
     *
     * @param integer $n24AnioAmpliacion
     * @return InfraestructuraH2Caracteristica
     */
    public function setN24AnioAmpliacion($n24AnioAmpliacion)
    {
        $this->n24AnioAmpliacion = $n24AnioAmpliacion;
    
        return $this;
    }

    /**
     * Get n24AnioAmpliacion
     *
     * @return integer 
     */
    public function getN24AnioAmpliacion()
    {
        return $this->n24AnioAmpliacion;
    }

    /**
     * Set n26Razonsocial
     *
     * @param string $n26Razonsocial
     * @return InfraestructuraH2Caracteristica
     */
    public function setN26Razonsocial($n26Razonsocial)
    {
        $this->n26Razonsocial = mb_strtoupper($n26Razonsocial,'utf-8');
    
        return $this;
    }

    /**
     * Get n26Razonsocial
     *
     * @return string 
     */
    public function getN26Razonsocial()
    {
        return $this->n26Razonsocial;
    }

    /**
     * Set n29DocumentoObs
     *
     * @param string $n29DocumentoObs
     * @return InfraestructuraH2Caracteristica
     */
    public function setN29DocumentoObs($n29DocumentoObs)
    {
        $this->n29DocumentoObs = mb_strtoupper($n29DocumentoObs,'utf-8');
    
        return $this;
    }

    /**
     * Get n29DocumentoObs
     *
     * @return string 
     */
    public function getN29DocumentoObs()
    {
        return $this->n29DocumentoObs;
    }

    /**
     * Set n29DocumentoNroPartida
     *
     * @param string $n29DocumentoNroPartida
     * @return InfraestructuraH2Caracteristica
     */
    public function setN29DocumentoNroPartida($n29DocumentoNroPartida)
    {
        $this->n29DocumentoNroPartida = $n29DocumentoNroPartida;
    
        return $this;
    }

    /**
     * Get n29DocumentoNroPartida
     *
     * @return string 
     */
    public function getN29DocumentoNroPartida()
    {
        return $this->n29DocumentoNroPartida;
    }

    /**
     * Set n29EsPlanoAprobado
     *
     * @param boolean $n29EsPlanoAprobado
     * @return InfraestructuraH2Caracteristica
     */
    public function setN29EsPlanoAprobado($n29EsPlanoAprobado)
    {
        $this->n29EsPlanoAprobado = $n29EsPlanoAprobado;
    
        return $this;
    }

    /**
     * Get n29EsPlanoAprobado
     *
     * @return boolean 
     */
    public function getN29EsPlanoAprobado()
    {
        return $this->n29EsPlanoAprobado;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH2Caracteristica
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
     * @return InfraestructuraH2Caracteristica
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
     * Set n11Areatotal
     *
     * @param string $n11Areatotal
     * @return InfraestructuraH2Caracteristica
     */
    public function setN11Areatotal($n11Areatotal)
    {
        $this->n11Areatotal = $n11Areatotal;
    
        return $this;
    }

    /**
     * Get n11Areatotal
     *
     * @return string 
     */
    public function getN11Areatotal()
    {
        return $this->n11Areatotal;
    }

    /**
     * Set n29DocumentoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2DocumentoTipo $n29DocumentoTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN29DocumentoTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2DocumentoTipo $n29DocumentoTipo = null)
    {
        $this->n29DocumentoTipo = $n29DocumentoTipo;
    
        return $this;
    }

    /**
     * Get n29DocumentoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2DocumentoTipo 
     */
    public function getN29DocumentoTipo()
    {
        return $this->n29DocumentoTipo;
    }

    /**
     * Set n28CieloEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n28CieloEstadogeneralTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN28CieloEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n28CieloEstadogeneralTipo = null)
    {
        $this->n28CieloEstadogeneralTipo = $n28CieloEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n28CieloEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN28CieloEstadogeneralTipo()
    {
        return $this->n28CieloEstadogeneralTipo;
    }

    /**
     * Set n28PisoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n28PisoEstadogeneralTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN28PisoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n28PisoEstadogeneralTipo = null)
    {
        $this->n28PisoEstadogeneralTipo = $n28PisoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n28PisoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN28PisoEstadogeneralTipo()
    {
        return $this->n28PisoEstadogeneralTipo;
    }

    /**
     * Set n28ParedEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n28ParedEstadogeneralTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN28ParedEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n28ParedEstadogeneralTipo = null)
    {
        $this->n28ParedEstadogeneralTipo = $n28ParedEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n28ParedEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN28ParedEstadogeneralTipo()
    {
        return $this->n28ParedEstadogeneralTipo;
    }

    /**
     * Set n28TechoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n28TechoEstadogeneralTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN28TechoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n28TechoEstadogeneralTipo = null)
    {
        $this->n28TechoEstadogeneralTipo = $n28TechoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n28TechoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN28TechoEstadogeneralTipo()
    {
        return $this->n28TechoEstadogeneralTipo;
    }

    /**
     * Set n27EstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n27EstadogeneralTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN27EstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n27EstadogeneralTipo = null)
    {
        $this->n27EstadogeneralTipo = $n27EstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n27EstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN27EstadogeneralTipo()
    {
        return $this->n27EstadogeneralTipo;
    }

    /**
     * Set n25PropiedadTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2PropiedadTipo $n25PropiedadTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN25PropiedadTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2PropiedadTipo $n25PropiedadTipo = null)
    {
        $this->n25PropiedadTipo = $n25PropiedadTipo;
    
        return $this;
    }

    /**
     * Get n25PropiedadTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2PropiedadTipo 
     */
    public function getN25PropiedadTipo()
    {
        return $this->n25PropiedadTipo;
    }

    /**
     * Set n13MuroperimetralAvanceTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenAvanceTipo $n13MuroperimetralAvanceTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN13MuroperimetralAvanceTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenAvanceTipo $n13MuroperimetralAvanceTipo = null)
    {
        $this->n13MuroperimetralAvanceTipo = $n13MuroperimetralAvanceTipo;
    
        return $this;
    }

    /**
     * Get n13MuroperimetralAvanceTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenAvanceTipo 
     */
    public function getN13MuroperimetralAvanceTipo()
    {
        return $this->n13MuroperimetralAvanceTipo;
    }

    /**
     * Set n12TopografiaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2TopografiaTipo $n12TopografiaTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN12TopografiaTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2TopografiaTipo $n12TopografiaTipo = null)
    {
        $this->n12TopografiaTipo = $n12TopografiaTipo;
    
        return $this;
    }

    /**
     * Get n12TopografiaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2TopografiaTipo 
     */
    public function getN12TopografiaTipo()
    {
        return $this->n12TopografiaTipo;
    }

    /**
     * Set infraestructuraJuridiccionGeografica
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica $infraestructuraJuridiccionGeografica
     * @return InfraestructuraH2Caracteristica
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
    /**
     * @var string
     */
    private $n13Muroperimetral;

    /**
     * @var boolean
     */
    private $n311ConEsRiesgoInundacion;

    /**
     * @var boolean
     */
    private $n317ConEsRiesgoRiada;

    /**
     * @var boolean
     */
    private $n318ConEsRiesgoTormenta;

    /**
     * @var boolean
     */
    private $n312ConEsRiesgoIncendio;

    /**
     * @var boolean
     */
    private $n319ConEsRiesgoGranizo;

    /**
     * @var boolean
     */
    private $n314ConEsRiesgoDeslizamiento;

    /**
     * @var boolean
     */
    private $n315ConEsRiesgoSifonamiento;

    /**
     * @var boolean
     */
    private $n316ConEsRiesgoTerremoto;

    /**
     * @var boolean
     */
    private $n210EsPlanoAprobado;

    /**
     * @var float
     */
    private $n11AreaHuerto;

    /**
     * @var float
     */
    private $n11AreaInvernadero;

    /**
     * @var float
     */
    private $n11AreaGranjaEscolar;

    /**
     * @var boolean
     */
    private $n22AnioConstruccionEsDiscap;

    /**
     * @var boolean
     */
    private $n23AnioRefaccionEsDiscap;

    /**
     * @var boolean
     */
    private $n24AnioAmpliacionEsDiscap;

    /**
     * @var string
     */
    private $n27TipoDocumentacion;

    /**
     * @var string
     */
    private $n28FolioRealTarjetaObs;

    /**
     * @var string
     */
    private $n211AdjDocumentacionFolio;

    /**
     * @var boolean
     */
    private $n212SiRampas;

    /**
     * @var boolean
     */
    private $n212SiTieneRampa;

    /**
     * @var boolean
     */
    private $n313ConEsRiesgoSequia;

    /**
     * @var \DateTime
     */
    private $n313ConEsRiesgoSequiaMesi;

    /**
     * @var \DateTime
     */
    private $n313ConEsRiesgoSequiaMesf;

    /**
     * @var \DateTime
     */
    private $n311ConEsRiesgoInundacionMesi;

    /**
     * @var \DateTime
     */
    private $n311ConEsRiesgoInundacionMesf;

    /**
     * @var \DateTime
     */
    private $n312ConEsRiesgoIncendioMesi;

    /**
     * @var \DateTime
     */
    private $n312ConEsRiesgoIncendioMesf;

    /**
     * @var \DateTime
     */
    private $n314ConEsRiesgoDeslizamientoMesi;

    /**
     * @var \DateTime
     */
    private $n314ConEsRiesgoDeslizamientoMesf;

    /**
     * @var \DateTime
     */
    private $n315ConEsRiesgoSifonamientoMesi;

    /**
     * @var \DateTime
     */
    private $n315ConEsRiesgoSifonamientoMesf;

    /**
     * @var \DateTime
     */
    private $n316ConEsRiesgoTerremotoMesi;

    /**
     * @var \DateTime
     */
    private $n316ConEsRiesgoTerremotoMesf;

    /**
     * @var \DateTime
     */
    private $n317ConEsRiesgoRiadaMesi;

    /**
     * @var \DateTime
     */
    private $n317ConEsRiesgoRiadaMesf;

    /**
     * @var \DateTime
     */
    private $n318ConEsRiesgoTormentaMesi;

    /**
     * @var \DateTime
     */
    private $n318ConEsRiesgoTormentaMesf;

    /**
     * @var \DateTime
     */
    private $n319ConEsRiesgoGranizoMesi;

    /**
     * @var \DateTime
     */
    private $n319ConEsRiesgoGranizoMesf;

    /**
     * @var boolean
     */
    private $n3120ConEsRiesgoHelada;

    /**
     * @var \DateTime
     */
    private $n3120ConEsRiesgoHeladaMesi;

    /**
     * @var \DateTime
     */
    private $n3120ConEsRiesgoHeladaMesf;

    /**
     * @var boolean
     */
    private $n321EsEdifProxBarrancos;

    /**
     * @var boolean
     */
    private $n321EsEdifProxRiosLagQue;

    /**
     * @var boolean
     */
    private $n321EsEdifProxEstaElectricas;

    /**
     * @var boolean
     */
    private $n321EsEdifProxEstaGasolineras;

    /**
     * @var boolean
     */
    private $n321EsEdifProxFabrMatContami;

    /**
     * @var boolean
     */
    private $n321EsEdifProxBotaderosMuni;

    /**
     * @var boolean
     */
    private $n321EsEdifProxCentroMinero;

    /**
     * @var boolean
     */
    private $n321EsEdifProxPasoFrontera;

    /**
     * @var boolean
     */
    private $n321EsEdifProxBares;

    /**
     * @var boolean
     */
    private $n321EsEdifProxBosques;

    /**
     * @var boolean
     */
    private $n331EsSuspencionClases;

    /**
     * @var boolean
     */
    private $n341EsAlbergue;

    /**
     * @var boolean
     */
    private $n354EsSenaleticaEvac;

    /**
     * @var boolean
     */
    private $n355EsDepositoAgua;

    /**
     * @var boolean
     */
    private $n321EsEdifProxCerroLadera;

    /**
     * @var boolean
     */
    private $n321EsEdifProxRiesgoDelito;

    /**
     * @var boolean
     */
    private $n351EsTimbrePanico;

    /**
     * @var integer
     */
    private $n351EsTimbrePanicoCant;

    /**
     * @var boolean
     */
    private $n352EsExtintores;

    /**
     * @var integer
     */
    private $n352ExtintoresCant;

    /**
     * @var boolean
     */
    private $n353EsCamSeg;

    /**
     * @var integer
     */
    private $n353CamSegCant;

    /**
     * @var boolean
     */
    private $n353EsCamSegFuncionamiento;

    /**
     * @var boolean
     */
    private $n212SiSenaletica;

    /**
     * @var integer
     */
    private $n31NroBloque;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo
     */
    private $n213SenalesTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo
     */
    private $n212SenalesiomaTipo2;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo
     */
    private $n212SenalesiomaTipo1;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo
     */
    private $n215TipoRampas;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2ConstruidaEducativoTipo
     */
    private $n21InfraestructuraH2ConstruidaEducativoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2PropiedadTipo
     */
    private $n25InfraestructuraH2PropiedadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenAvanceTipo
     */
    private $n13EdificacionAmurallada;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2TopografiaTipo
     */
    private $n12InfraestructuraH2TopografiaTipo;


    /**
     * Set n13Muroperimetral
     *
     * @param string $n13Muroperimetral
     * @return InfraestructuraH2Caracteristica
     */
    public function setN13Muroperimetral($n13Muroperimetral)
    {
        $this->n13Muroperimetral = $n13Muroperimetral;
    
        return $this;
    }

    /**
     * Get n13Muroperimetral
     *
     * @return string 
     */
    public function getN13Muroperimetral()
    {
        return $this->n13Muroperimetral;
    }

    /**
     * Set n311ConEsRiesgoInundacion
     *
     * @param boolean $n311ConEsRiesgoInundacion
     * @return InfraestructuraH2Caracteristica
     */
    public function setN311ConEsRiesgoInundacion($n311ConEsRiesgoInundacion)
    {
        $this->n311ConEsRiesgoInundacion = $n311ConEsRiesgoInundacion;
    
        return $this;
    }

    /**
     * Get n311ConEsRiesgoInundacion
     *
     * @return boolean 
     */
    public function getN311ConEsRiesgoInundacion()
    {
        return $this->n311ConEsRiesgoInundacion;
    }

    /**
     * Set n317ConEsRiesgoRiada
     *
     * @param boolean $n317ConEsRiesgoRiada
     * @return InfraestructuraH2Caracteristica
     */
    public function setN317ConEsRiesgoRiada($n317ConEsRiesgoRiada)
    {
        $this->n317ConEsRiesgoRiada = $n317ConEsRiesgoRiada;
    
        return $this;
    }

    /**
     * Get n317ConEsRiesgoRiada
     *
     * @return boolean 
     */
    public function getN317ConEsRiesgoRiada()
    {
        return $this->n317ConEsRiesgoRiada;
    }

    /**
     * Set n318ConEsRiesgoTormenta
     *
     * @param boolean $n318ConEsRiesgoTormenta
     * @return InfraestructuraH2Caracteristica
     */
    public function setN318ConEsRiesgoTormenta($n318ConEsRiesgoTormenta)
    {
        $this->n318ConEsRiesgoTormenta = $n318ConEsRiesgoTormenta;
    
        return $this;
    }

    /**
     * Get n318ConEsRiesgoTormenta
     *
     * @return boolean 
     */
    public function getN318ConEsRiesgoTormenta()
    {
        return $this->n318ConEsRiesgoTormenta;
    }

    /**
     * Set n312ConEsRiesgoIncendio
     *
     * @param boolean $n312ConEsRiesgoIncendio
     * @return InfraestructuraH2Caracteristica
     */
    public function setN312ConEsRiesgoIncendio($n312ConEsRiesgoIncendio)
    {
        $this->n312ConEsRiesgoIncendio = $n312ConEsRiesgoIncendio;
    
        return $this;
    }

    /**
     * Get n312ConEsRiesgoIncendio
     *
     * @return boolean 
     */
    public function getN312ConEsRiesgoIncendio()
    {
        return $this->n312ConEsRiesgoIncendio;
    }

    /**
     * Set n319ConEsRiesgoGranizo
     *
     * @param boolean $n319ConEsRiesgoGranizo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN319ConEsRiesgoGranizo($n319ConEsRiesgoGranizo)
    {
        $this->n319ConEsRiesgoGranizo = $n319ConEsRiesgoGranizo;
    
        return $this;
    }

    /**
     * Get n319ConEsRiesgoGranizo
     *
     * @return boolean 
     */
    public function getN319ConEsRiesgoGranizo()
    {
        return $this->n319ConEsRiesgoGranizo;
    }

    /**
     * Set n314ConEsRiesgoDeslizamiento
     *
     * @param boolean $n314ConEsRiesgoDeslizamiento
     * @return InfraestructuraH2Caracteristica
     */
    public function setN314ConEsRiesgoDeslizamiento($n314ConEsRiesgoDeslizamiento)
    {
        $this->n314ConEsRiesgoDeslizamiento = $n314ConEsRiesgoDeslizamiento;
    
        return $this;
    }

    /**
     * Get n314ConEsRiesgoDeslizamiento
     *
     * @return boolean 
     */
    public function getN314ConEsRiesgoDeslizamiento()
    {
        return $this->n314ConEsRiesgoDeslizamiento;
    }

    /**
     * Set n315ConEsRiesgoSifonamiento
     *
     * @param boolean $n315ConEsRiesgoSifonamiento
     * @return InfraestructuraH2Caracteristica
     */
    public function setN315ConEsRiesgoSifonamiento($n315ConEsRiesgoSifonamiento)
    {
        $this->n315ConEsRiesgoSifonamiento = $n315ConEsRiesgoSifonamiento;
    
        return $this;
    }

    /**
     * Get n315ConEsRiesgoSifonamiento
     *
     * @return boolean 
     */
    public function getN315ConEsRiesgoSifonamiento()
    {
        return $this->n315ConEsRiesgoSifonamiento;
    }

    /**
     * Set n316ConEsRiesgoTerremoto
     *
     * @param boolean $n316ConEsRiesgoTerremoto
     * @return InfraestructuraH2Caracteristica
     */
    public function setN316ConEsRiesgoTerremoto($n316ConEsRiesgoTerremoto)
    {
        $this->n316ConEsRiesgoTerremoto = $n316ConEsRiesgoTerremoto;
    
        return $this;
    }

    /**
     * Get n316ConEsRiesgoTerremoto
     *
     * @return boolean 
     */
    public function getN316ConEsRiesgoTerremoto()
    {
        return $this->n316ConEsRiesgoTerremoto;
    }

    /**
     * Set n210EsPlanoAprobado
     *
     * @param boolean $n210EsPlanoAprobado
     * @return InfraestructuraH2Caracteristica
     */
    public function setN210EsPlanoAprobado($n210EsPlanoAprobado)
    {
        $this->n210EsPlanoAprobado = $n210EsPlanoAprobado;
    
        return $this;
    }

    /**
     * Get n210EsPlanoAprobado
     *
     * @return boolean 
     */
    public function getN210EsPlanoAprobado()
    {
        return $this->n210EsPlanoAprobado;
    }

    /**
     * Set n11AreaHuerto
     *
     * @param float $n11AreaHuerto
     * @return InfraestructuraH2Caracteristica
     */
    public function setN11AreaHuerto($n11AreaHuerto)
    {
        $this->n11AreaHuerto = $n11AreaHuerto;
    
        return $this;
    }

    /**
     * Get n11AreaHuerto
     *
     * @return float 
     */
    public function getN11AreaHuerto()
    {
        return $this->n11AreaHuerto;
    }

    /**
     * Set n11AreaInvernadero
     *
     * @param float $n11AreaInvernadero
     * @return InfraestructuraH2Caracteristica
     */
    public function setN11AreaInvernadero($n11AreaInvernadero)
    {
        $this->n11AreaInvernadero = $n11AreaInvernadero;
    
        return $this;
    }

    /**
     * Get n11AreaInvernadero
     *
     * @return float 
     */
    public function getN11AreaInvernadero()
    {
        return $this->n11AreaInvernadero;
    }

    /**
     * Set n11AreaGranjaEscolar
     *
     * @param float $n11AreaGranjaEscolar
     * @return InfraestructuraH2Caracteristica
     */
    public function setN11AreaGranjaEscolar($n11AreaGranjaEscolar)
    {
        $this->n11AreaGranjaEscolar = $n11AreaGranjaEscolar;
    
        return $this;
    }

    /**
     * Get n11AreaGranjaEscolar
     *
     * @return float 
     */
    public function getN11AreaGranjaEscolar()
    {
        return $this->n11AreaGranjaEscolar;
    }

    /**
     * Set n22AnioConstruccionEsDiscap
     *
     * @param boolean $n22AnioConstruccionEsDiscap
     * @return InfraestructuraH2Caracteristica
     */
    public function setN22AnioConstruccionEsDiscap($n22AnioConstruccionEsDiscap)
    {
        $this->n22AnioConstruccionEsDiscap = $n22AnioConstruccionEsDiscap;
    
        return $this;
    }

    /**
     * Get n22AnioConstruccionEsDiscap
     *
     * @return boolean 
     */
    public function getN22AnioConstruccionEsDiscap()
    {
        return $this->n22AnioConstruccionEsDiscap;
    }

    /**
     * Set n23AnioRefaccionEsDiscap
     *
     * @param boolean $n23AnioRefaccionEsDiscap
     * @return InfraestructuraH2Caracteristica
     */
    public function setN23AnioRefaccionEsDiscap($n23AnioRefaccionEsDiscap)
    {
        $this->n23AnioRefaccionEsDiscap = $n23AnioRefaccionEsDiscap;
    
        return $this;
    }

    /**
     * Get n23AnioRefaccionEsDiscap
     *
     * @return boolean 
     */
    public function getN23AnioRefaccionEsDiscap()
    {
        return $this->n23AnioRefaccionEsDiscap;
    }

    /**
     * Set n24AnioAmpliacionEsDiscap
     *
     * @param boolean $n24AnioAmpliacionEsDiscap
     * @return InfraestructuraH2Caracteristica
     */
    public function setN24AnioAmpliacionEsDiscap($n24AnioAmpliacionEsDiscap)
    {
        $this->n24AnioAmpliacionEsDiscap = $n24AnioAmpliacionEsDiscap;
    
        return $this;
    }

    /**
     * Get n24AnioAmpliacionEsDiscap
     *
     * @return boolean 
     */
    public function getN24AnioAmpliacionEsDiscap()
    {
        return $this->n24AnioAmpliacionEsDiscap;
    }

    /**
     * Set n27TipoDocumentacion
     *
     * @param string $n27TipoDocumentacion
     * @return InfraestructuraH2Caracteristica
     */
    public function setN27TipoDocumentacion($n27TipoDocumentacion)
    {
        $this->n27TipoDocumentacion = $n27TipoDocumentacion;
    
        return $this;
    }

    /**
     * Get n27TipoDocumentacion
     *
     * @return string 
     */
    public function getN27TipoDocumentacion()
    {
        return $this->n27TipoDocumentacion;
    }

    /**
     * Set n28FolioRealTarjetaObs
     *
     * @param string $n28FolioRealTarjetaObs
     * @return InfraestructuraH2Caracteristica
     */
    public function setN28FolioRealTarjetaObs($n28FolioRealTarjetaObs)
    {
        $this->n28FolioRealTarjetaObs = $n28FolioRealTarjetaObs;
    
        return $this;
    }

    /**
     * Get n28FolioRealTarjetaObs
     *
     * @return string 
     */
    public function getN28FolioRealTarjetaObs()
    {
        return $this->n28FolioRealTarjetaObs;
    }

    /**
     * Set n211AdjDocumentacionFolio
     *
     * @param string $n211AdjDocumentacionFolio
     * @return InfraestructuraH2Caracteristica
     */
    public function setN211AdjDocumentacionFolio($n211AdjDocumentacionFolio)
    {
        $this->n211AdjDocumentacionFolio = $n211AdjDocumentacionFolio;
    
        return $this;
    }

    /**
     * Get n211AdjDocumentacionFolio
     *
     * @return string 
     */
    public function getN211AdjDocumentacionFolio()
    {
        return $this->n211AdjDocumentacionFolio;
    }

    /**
     * Set n212SiRampas
     *
     * @param boolean $n212SiRampas
     * @return InfraestructuraH2Caracteristica
     */
    public function setN212SiRampas($n212SiRampas)
    {
        $this->n212SiRampas = $n212SiRampas;
    
        return $this;
    }

    /**
     * Get n212SiRampas
     *
     * @return boolean 
     */
    public function getN212SiRampas()
    {
        return $this->n212SiRampas;
    }

    /**
     * Set n212SiTieneRampa
     *
     * @param boolean $n212SiTieneRampa
     * @return InfraestructuraH2Caracteristica
     */
    public function setN212SiTieneRampa($n212SiTieneRampa)
    {
        $this->n212SiTieneRampa = $n212SiTieneRampa;
    
        return $this;
    }

    /**
     * Get n212SiTieneRampa
     *
     * @return boolean 
     */
    public function getN212SiTieneRampa()
    {
        return $this->n212SiTieneRampa;
    }

    /**
     * Set n313ConEsRiesgoSequia
     *
     * @param boolean $n313ConEsRiesgoSequia
     * @return InfraestructuraH2Caracteristica
     */
    public function setN313ConEsRiesgoSequia($n313ConEsRiesgoSequia)
    {
        $this->n313ConEsRiesgoSequia = $n313ConEsRiesgoSequia;
    
        return $this;
    }

    /**
     * Get n313ConEsRiesgoSequia
     *
     * @return boolean 
     */
    public function getN313ConEsRiesgoSequia()
    {
        return $this->n313ConEsRiesgoSequia;
    }

    /**
     * Set n313ConEsRiesgoSequiaMesi
     *
     * @param \DateTime $n313ConEsRiesgoSequiaMesi
     * @return InfraestructuraH2Caracteristica
     */
    public function setN313ConEsRiesgoSequiaMesi($n313ConEsRiesgoSequiaMesi)
    {
        $this->n313ConEsRiesgoSequiaMesi = $n313ConEsRiesgoSequiaMesi;
    
        return $this;
    }

    /**
     * Get n313ConEsRiesgoSequiaMesi
     *
     * @return \DateTime 
     */
    public function getN313ConEsRiesgoSequiaMesi()
    {
        return $this->n313ConEsRiesgoSequiaMesi;
    }

    /**
     * Set n313ConEsRiesgoSequiaMesf
     *
     * @param \DateTime $n313ConEsRiesgoSequiaMesf
     * @return InfraestructuraH2Caracteristica
     */
    public function setN313ConEsRiesgoSequiaMesf($n313ConEsRiesgoSequiaMesf)
    {
        $this->n313ConEsRiesgoSequiaMesf = $n313ConEsRiesgoSequiaMesf;
    
        return $this;
    }

    /**
     * Get n313ConEsRiesgoSequiaMesf
     *
     * @return \DateTime 
     */
    public function getN313ConEsRiesgoSequiaMesf()
    {
        return $this->n313ConEsRiesgoSequiaMesf;
    }

    /**
     * Set n311ConEsRiesgoInundacionMesi
     *
     * @param \DateTime $n311ConEsRiesgoInundacionMesi
     * @return InfraestructuraH2Caracteristica
     */
    public function setN311ConEsRiesgoInundacionMesi($n311ConEsRiesgoInundacionMesi)
    {
        $this->n311ConEsRiesgoInundacionMesi = $n311ConEsRiesgoInundacionMesi;
    
        return $this;
    }

    /**
     * Get n311ConEsRiesgoInundacionMesi
     *
     * @return \DateTime 
     */
    public function getN311ConEsRiesgoInundacionMesi()
    {
        return $this->n311ConEsRiesgoInundacionMesi;
    }

    /**
     * Set n311ConEsRiesgoInundacionMesf
     *
     * @param \DateTime $n311ConEsRiesgoInundacionMesf
     * @return InfraestructuraH2Caracteristica
     */
    public function setN311ConEsRiesgoInundacionMesf($n311ConEsRiesgoInundacionMesf)
    {
        $this->n311ConEsRiesgoInundacionMesf = $n311ConEsRiesgoInundacionMesf;
    
        return $this;
    }

    /**
     * Get n311ConEsRiesgoInundacionMesf
     *
     * @return \DateTime 
     */
    public function getN311ConEsRiesgoInundacionMesf()
    {
        return $this->n311ConEsRiesgoInundacionMesf;
    }

    /**
     * Set n312ConEsRiesgoIncendioMesi
     *
     * @param \DateTime $n312ConEsRiesgoIncendioMesi
     * @return InfraestructuraH2Caracteristica
     */
    public function setN312ConEsRiesgoIncendioMesi($n312ConEsRiesgoIncendioMesi)
    {
        $this->n312ConEsRiesgoIncendioMesi = $n312ConEsRiesgoIncendioMesi;
    
        return $this;
    }

    /**
     * Get n312ConEsRiesgoIncendioMesi
     *
     * @return \DateTime 
     */
    public function getN312ConEsRiesgoIncendioMesi()
    {
        return $this->n312ConEsRiesgoIncendioMesi;
    }

    /**
     * Set n312ConEsRiesgoIncendioMesf
     *
     * @param \DateTime $n312ConEsRiesgoIncendioMesf
     * @return InfraestructuraH2Caracteristica
     */
    public function setN312ConEsRiesgoIncendioMesf($n312ConEsRiesgoIncendioMesf)
    {
        $this->n312ConEsRiesgoIncendioMesf = $n312ConEsRiesgoIncendioMesf;
    
        return $this;
    }

    /**
     * Get n312ConEsRiesgoIncendioMesf
     *
     * @return \DateTime 
     */
    public function getN312ConEsRiesgoIncendioMesf()
    {
        return $this->n312ConEsRiesgoIncendioMesf;
    }

    /**
     * Set n314ConEsRiesgoDeslizamientoMesi
     *
     * @param \DateTime $n314ConEsRiesgoDeslizamientoMesi
     * @return InfraestructuraH2Caracteristica
     */
    public function setN314ConEsRiesgoDeslizamientoMesi($n314ConEsRiesgoDeslizamientoMesi)
    {
        $this->n314ConEsRiesgoDeslizamientoMesi = $n314ConEsRiesgoDeslizamientoMesi;
    
        return $this;
    }

    /**
     * Get n314ConEsRiesgoDeslizamientoMesi
     *
     * @return \DateTime 
     */
    public function getN314ConEsRiesgoDeslizamientoMesi()
    {
        return $this->n314ConEsRiesgoDeslizamientoMesi;
    }

    /**
     * Set n314ConEsRiesgoDeslizamientoMesf
     *
     * @param \DateTime $n314ConEsRiesgoDeslizamientoMesf
     * @return InfraestructuraH2Caracteristica
     */
    public function setN314ConEsRiesgoDeslizamientoMesf($n314ConEsRiesgoDeslizamientoMesf)
    {
        $this->n314ConEsRiesgoDeslizamientoMesf = $n314ConEsRiesgoDeslizamientoMesf;
    
        return $this;
    }

    /**
     * Get n314ConEsRiesgoDeslizamientoMesf
     *
     * @return \DateTime 
     */
    public function getN314ConEsRiesgoDeslizamientoMesf()
    {
        return $this->n314ConEsRiesgoDeslizamientoMesf;
    }

    /**
     * Set n315ConEsRiesgoSifonamientoMesi
     *
     * @param \DateTime $n315ConEsRiesgoSifonamientoMesi
     * @return InfraestructuraH2Caracteristica
     */
    public function setN315ConEsRiesgoSifonamientoMesi($n315ConEsRiesgoSifonamientoMesi)
    {
        $this->n315ConEsRiesgoSifonamientoMesi = $n315ConEsRiesgoSifonamientoMesi;
    
        return $this;
    }

    /**
     * Get n315ConEsRiesgoSifonamientoMesi
     *
     * @return \DateTime 
     */
    public function getN315ConEsRiesgoSifonamientoMesi()
    {
        return $this->n315ConEsRiesgoSifonamientoMesi;
    }

    /**
     * Set n315ConEsRiesgoSifonamientoMesf
     *
     * @param \DateTime $n315ConEsRiesgoSifonamientoMesf
     * @return InfraestructuraH2Caracteristica
     */
    public function setN315ConEsRiesgoSifonamientoMesf($n315ConEsRiesgoSifonamientoMesf)
    {
        $this->n315ConEsRiesgoSifonamientoMesf = $n315ConEsRiesgoSifonamientoMesf;
    
        return $this;
    }

    /**
     * Get n315ConEsRiesgoSifonamientoMesf
     *
     * @return \DateTime 
     */
    public function getN315ConEsRiesgoSifonamientoMesf()
    {
        return $this->n315ConEsRiesgoSifonamientoMesf;
    }

    /**
     * Set n316ConEsRiesgoTerremotoMesi
     *
     * @param \DateTime $n316ConEsRiesgoTerremotoMesi
     * @return InfraestructuraH2Caracteristica
     */
    public function setN316ConEsRiesgoTerremotoMesi($n316ConEsRiesgoTerremotoMesi)
    {
        $this->n316ConEsRiesgoTerremotoMesi = $n316ConEsRiesgoTerremotoMesi;
    
        return $this;
    }

    /**
     * Get n316ConEsRiesgoTerremotoMesi
     *
     * @return \DateTime 
     */
    public function getN316ConEsRiesgoTerremotoMesi()
    {
        return $this->n316ConEsRiesgoTerremotoMesi;
    }

    /**
     * Set n316ConEsRiesgoTerremotoMesf
     *
     * @param \DateTime $n316ConEsRiesgoTerremotoMesf
     * @return InfraestructuraH2Caracteristica
     */
    public function setN316ConEsRiesgoTerremotoMesf($n316ConEsRiesgoTerremotoMesf)
    {
        $this->n316ConEsRiesgoTerremotoMesf = $n316ConEsRiesgoTerremotoMesf;
    
        return $this;
    }

    /**
     * Get n316ConEsRiesgoTerremotoMesf
     *
     * @return \DateTime 
     */
    public function getN316ConEsRiesgoTerremotoMesf()
    {
        return $this->n316ConEsRiesgoTerremotoMesf;
    }

    /**
     * Set n317ConEsRiesgoRiadaMesi
     *
     * @param \DateTime $n317ConEsRiesgoRiadaMesi
     * @return InfraestructuraH2Caracteristica
     */
    public function setN317ConEsRiesgoRiadaMesi($n317ConEsRiesgoRiadaMesi)
    {
        $this->n317ConEsRiesgoRiadaMesi = $n317ConEsRiesgoRiadaMesi;
    
        return $this;
    }

    /**
     * Get n317ConEsRiesgoRiadaMesi
     *
     * @return \DateTime 
     */
    public function getN317ConEsRiesgoRiadaMesi()
    {
        return $this->n317ConEsRiesgoRiadaMesi;
    }

    /**
     * Set n317ConEsRiesgoRiadaMesf
     *
     * @param \DateTime $n317ConEsRiesgoRiadaMesf
     * @return InfraestructuraH2Caracteristica
     */
    public function setN317ConEsRiesgoRiadaMesf($n317ConEsRiesgoRiadaMesf)
    {
        $this->n317ConEsRiesgoRiadaMesf = $n317ConEsRiesgoRiadaMesf;
    
        return $this;
    }

    /**
     * Get n317ConEsRiesgoRiadaMesf
     *
     * @return \DateTime 
     */
    public function getN317ConEsRiesgoRiadaMesf()
    {
        return $this->n317ConEsRiesgoRiadaMesf;
    }

    /**
     * Set n318ConEsRiesgoTormentaMesi
     *
     * @param \DateTime $n318ConEsRiesgoTormentaMesi
     * @return InfraestructuraH2Caracteristica
     */
    public function setN318ConEsRiesgoTormentaMesi($n318ConEsRiesgoTormentaMesi)
    {
        $this->n318ConEsRiesgoTormentaMesi = $n318ConEsRiesgoTormentaMesi;
    
        return $this;
    }

    /**
     * Get n318ConEsRiesgoTormentaMesi
     *
     * @return \DateTime 
     */
    public function getN318ConEsRiesgoTormentaMesi()
    {
        return $this->n318ConEsRiesgoTormentaMesi;
    }

    /**
     * Set n318ConEsRiesgoTormentaMesf
     *
     * @param \DateTime $n318ConEsRiesgoTormentaMesf
     * @return InfraestructuraH2Caracteristica
     */
    public function setN318ConEsRiesgoTormentaMesf($n318ConEsRiesgoTormentaMesf)
    {
        $this->n318ConEsRiesgoTormentaMesf = $n318ConEsRiesgoTormentaMesf;
    
        return $this;
    }

    /**
     * Get n318ConEsRiesgoTormentaMesf
     *
     * @return \DateTime 
     */
    public function getN318ConEsRiesgoTormentaMesf()
    {
        return $this->n318ConEsRiesgoTormentaMesf;
    }

    /**
     * Set n319ConEsRiesgoGranizoMesi
     *
     * @param \DateTime $n319ConEsRiesgoGranizoMesi
     * @return InfraestructuraH2Caracteristica
     */
    public function setN319ConEsRiesgoGranizoMesi($n319ConEsRiesgoGranizoMesi)
    {
        $this->n319ConEsRiesgoGranizoMesi = $n319ConEsRiesgoGranizoMesi;
    
        return $this;
    }

    /**
     * Get n319ConEsRiesgoGranizoMesi
     *
     * @return \DateTime 
     */
    public function getN319ConEsRiesgoGranizoMesi()
    {
        return $this->n319ConEsRiesgoGranizoMesi;
    }

    /**
     * Set n319ConEsRiesgoGranizoMesf
     *
     * @param \DateTime $n319ConEsRiesgoGranizoMesf
     * @return InfraestructuraH2Caracteristica
     */
    public function setN319ConEsRiesgoGranizoMesf($n319ConEsRiesgoGranizoMesf)
    {
        $this->n319ConEsRiesgoGranizoMesf = $n319ConEsRiesgoGranizoMesf;
    
        return $this;
    }

    /**
     * Get n319ConEsRiesgoGranizoMesf
     *
     * @return \DateTime 
     */
    public function getN319ConEsRiesgoGranizoMesf()
    {
        return $this->n319ConEsRiesgoGranizoMesf;
    }

    /**
     * Set n3120ConEsRiesgoHelada
     *
     * @param boolean $n3120ConEsRiesgoHelada
     * @return InfraestructuraH2Caracteristica
     */
    public function setN3120ConEsRiesgoHelada($n3120ConEsRiesgoHelada)
    {
        $this->n3120ConEsRiesgoHelada = $n3120ConEsRiesgoHelada;
    
        return $this;
    }

    /**
     * Get n3120ConEsRiesgoHelada
     *
     * @return boolean 
     */
    public function getN3120ConEsRiesgoHelada()
    {
        return $this->n3120ConEsRiesgoHelada;
    }

    /**
     * Set n3120ConEsRiesgoHeladaMesi
     *
     * @param \DateTime $n3120ConEsRiesgoHeladaMesi
     * @return InfraestructuraH2Caracteristica
     */
    public function setN3120ConEsRiesgoHeladaMesi($n3120ConEsRiesgoHeladaMesi)
    {
        $this->n3120ConEsRiesgoHeladaMesi = $n3120ConEsRiesgoHeladaMesi;
    
        return $this;
    }

    /**
     * Get n3120ConEsRiesgoHeladaMesi
     *
     * @return \DateTime 
     */
    public function getN3120ConEsRiesgoHeladaMesi()
    {
        return $this->n3120ConEsRiesgoHeladaMesi;
    }

    /**
     * Set n3120ConEsRiesgoHeladaMesf
     *
     * @param \DateTime $n3120ConEsRiesgoHeladaMesf
     * @return InfraestructuraH2Caracteristica
     */
    public function setN3120ConEsRiesgoHeladaMesf($n3120ConEsRiesgoHeladaMesf)
    {
        $this->n3120ConEsRiesgoHeladaMesf = $n3120ConEsRiesgoHeladaMesf;
    
        return $this;
    }

    /**
     * Get n3120ConEsRiesgoHeladaMesf
     *
     * @return \DateTime 
     */
    public function getN3120ConEsRiesgoHeladaMesf()
    {
        return $this->n3120ConEsRiesgoHeladaMesf;
    }

    /**
     * Set n321EsEdifProxBarrancos
     *
     * @param boolean $n321EsEdifProxBarrancos
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxBarrancos($n321EsEdifProxBarrancos)
    {
        $this->n321EsEdifProxBarrancos = $n321EsEdifProxBarrancos;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxBarrancos
     *
     * @return boolean 
     */
    public function getN321EsEdifProxBarrancos()
    {
        return $this->n321EsEdifProxBarrancos;
    }

    /**
     * Set n321EsEdifProxRiosLagQue
     *
     * @param boolean $n321EsEdifProxRiosLagQue
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxRiosLagQue($n321EsEdifProxRiosLagQue)
    {
        $this->n321EsEdifProxRiosLagQue = $n321EsEdifProxRiosLagQue;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxRiosLagQue
     *
     * @return boolean 
     */
    public function getN321EsEdifProxRiosLagQue()
    {
        return $this->n321EsEdifProxRiosLagQue;
    }

    /**
     * Set n321EsEdifProxEstaElectricas
     *
     * @param boolean $n321EsEdifProxEstaElectricas
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxEstaElectricas($n321EsEdifProxEstaElectricas)
    {
        $this->n321EsEdifProxEstaElectricas = $n321EsEdifProxEstaElectricas;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxEstaElectricas
     *
     * @return boolean 
     */
    public function getN321EsEdifProxEstaElectricas()
    {
        return $this->n321EsEdifProxEstaElectricas;
    }

    /**
     * Set n321EsEdifProxEstaGasolineras
     *
     * @param boolean $n321EsEdifProxEstaGasolineras
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxEstaGasolineras($n321EsEdifProxEstaGasolineras)
    {
        $this->n321EsEdifProxEstaGasolineras = $n321EsEdifProxEstaGasolineras;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxEstaGasolineras
     *
     * @return boolean 
     */
    public function getN321EsEdifProxEstaGasolineras()
    {
        return $this->n321EsEdifProxEstaGasolineras;
    }

    /**
     * Set n321EsEdifProxFabrMatContami
     *
     * @param boolean $n321EsEdifProxFabrMatContami
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxFabrMatContami($n321EsEdifProxFabrMatContami)
    {
        $this->n321EsEdifProxFabrMatContami = $n321EsEdifProxFabrMatContami;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxFabrMatContami
     *
     * @return boolean 
     */
    public function getN321EsEdifProxFabrMatContami()
    {
        return $this->n321EsEdifProxFabrMatContami;
    }

    /**
     * Set n321EsEdifProxBotaderosMuni
     *
     * @param boolean $n321EsEdifProxBotaderosMuni
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxBotaderosMuni($n321EsEdifProxBotaderosMuni)
    {
        $this->n321EsEdifProxBotaderosMuni = $n321EsEdifProxBotaderosMuni;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxBotaderosMuni
     *
     * @return boolean 
     */
    public function getN321EsEdifProxBotaderosMuni()
    {
        return $this->n321EsEdifProxBotaderosMuni;
    }

    /**
     * Set n321EsEdifProxCentroMinero
     *
     * @param boolean $n321EsEdifProxCentroMinero
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxCentroMinero($n321EsEdifProxCentroMinero)
    {
        $this->n321EsEdifProxCentroMinero = $n321EsEdifProxCentroMinero;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxCentroMinero
     *
     * @return boolean 
     */
    public function getN321EsEdifProxCentroMinero()
    {
        return $this->n321EsEdifProxCentroMinero;
    }

    /**
     * Set n321EsEdifProxPasoFrontera
     *
     * @param boolean $n321EsEdifProxPasoFrontera
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxPasoFrontera($n321EsEdifProxPasoFrontera)
    {
        $this->n321EsEdifProxPasoFrontera = $n321EsEdifProxPasoFrontera;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxPasoFrontera
     *
     * @return boolean 
     */
    public function getN321EsEdifProxPasoFrontera()
    {
        return $this->n321EsEdifProxPasoFrontera;
    }

    /**
     * Set n321EsEdifProxBares
     *
     * @param boolean $n321EsEdifProxBares
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxBares($n321EsEdifProxBares)
    {
        $this->n321EsEdifProxBares = $n321EsEdifProxBares;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxBares
     *
     * @return boolean 
     */
    public function getN321EsEdifProxBares()
    {
        return $this->n321EsEdifProxBares;
    }

    /**
     * Set n321EsEdifProxBosques
     *
     * @param boolean $n321EsEdifProxBosques
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxBosques($n321EsEdifProxBosques)
    {
        $this->n321EsEdifProxBosques = $n321EsEdifProxBosques;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxBosques
     *
     * @return boolean 
     */
    public function getN321EsEdifProxBosques()
    {
        return $this->n321EsEdifProxBosques;
    }

    /**
     * Set n331EsSuspencionClases
     *
     * @param boolean $n331EsSuspencionClases
     * @return InfraestructuraH2Caracteristica
     */
    public function setN331EsSuspencionClases($n331EsSuspencionClases)
    {
        $this->n331EsSuspencionClases = $n331EsSuspencionClases;
    
        return $this;
    }

    /**
     * Get n331EsSuspencionClases
     *
     * @return boolean 
     */
    public function getN331EsSuspencionClases()
    {
        return $this->n331EsSuspencionClases;
    }

    /**
     * Set n341EsAlbergue
     *
     * @param boolean $n341EsAlbergue
     * @return InfraestructuraH2Caracteristica
     */
    public function setN341EsAlbergue($n341EsAlbergue)
    {
        $this->n341EsAlbergue = $n341EsAlbergue;
    
        return $this;
    }

    /**
     * Get n341EsAlbergue
     *
     * @return boolean 
     */
    public function getN341EsAlbergue()
    {
        return $this->n341EsAlbergue;
    }

    /**
     * Set n354EsSenaleticaEvac
     *
     * @param boolean $n354EsSenaleticaEvac
     * @return InfraestructuraH2Caracteristica
     */
    public function setN354EsSenaleticaEvac($n354EsSenaleticaEvac)
    {
        $this->n354EsSenaleticaEvac = $n354EsSenaleticaEvac;
    
        return $this;
    }

    /**
     * Get n354EsSenaleticaEvac
     *
     * @return boolean 
     */
    public function getN354EsSenaleticaEvac()
    {
        return $this->n354EsSenaleticaEvac;
    }

    /**
     * Set n355EsDepositoAgua
     *
     * @param boolean $n355EsDepositoAgua
     * @return InfraestructuraH2Caracteristica
     */
    public function setN355EsDepositoAgua($n355EsDepositoAgua)
    {
        $this->n355EsDepositoAgua = $n355EsDepositoAgua;
    
        return $this;
    }

    /**
     * Get n355EsDepositoAgua
     *
     * @return boolean 
     */
    public function getN355EsDepositoAgua()
    {
        return $this->n355EsDepositoAgua;
    }

    /**
     * Set n321EsEdifProxCerroLadera
     *
     * @param boolean $n321EsEdifProxCerroLadera
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxCerroLadera($n321EsEdifProxCerroLadera)
    {
        $this->n321EsEdifProxCerroLadera = $n321EsEdifProxCerroLadera;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxCerroLadera
     *
     * @return boolean 
     */
    public function getN321EsEdifProxCerroLadera()
    {
        return $this->n321EsEdifProxCerroLadera;
    }

    /**
     * Set n321EsEdifProxRiesgoDelito
     *
     * @param boolean $n321EsEdifProxRiesgoDelito
     * @return InfraestructuraH2Caracteristica
     */
    public function setN321EsEdifProxRiesgoDelito($n321EsEdifProxRiesgoDelito)
    {
        $this->n321EsEdifProxRiesgoDelito = $n321EsEdifProxRiesgoDelito;
    
        return $this;
    }

    /**
     * Get n321EsEdifProxRiesgoDelito
     *
     * @return boolean 
     */
    public function getN321EsEdifProxRiesgoDelito()
    {
        return $this->n321EsEdifProxRiesgoDelito;
    }

    /**
     * Set n351EsTimbrePanico
     *
     * @param boolean $n351EsTimbrePanico
     * @return InfraestructuraH2Caracteristica
     */
    public function setN351EsTimbrePanico($n351EsTimbrePanico)
    {
        $this->n351EsTimbrePanico = $n351EsTimbrePanico;
    
        return $this;
    }

    /**
     * Get n351EsTimbrePanico
     *
     * @return boolean 
     */
    public function getN351EsTimbrePanico()
    {
        return $this->n351EsTimbrePanico;
    }

    /**
     * Set n351EsTimbrePanicoCant
     *
     * @param integer $n351EsTimbrePanicoCant
     * @return InfraestructuraH2Caracteristica
     */
    public function setN351EsTimbrePanicoCant($n351EsTimbrePanicoCant)
    {
        $this->n351EsTimbrePanicoCant = $n351EsTimbrePanicoCant;
    
        return $this;
    }

    /**
     * Get n351EsTimbrePanicoCant
     *
     * @return integer 
     */
    public function getN351EsTimbrePanicoCant()
    {
        return $this->n351EsTimbrePanicoCant;
    }

    /**
     * Set n352EsExtintores
     *
     * @param boolean $n352EsExtintores
     * @return InfraestructuraH2Caracteristica
     */
    public function setN352EsExtintores($n352EsExtintores)
    {
        $this->n352EsExtintores = $n352EsExtintores;
    
        return $this;
    }

    /**
     * Get n352EsExtintores
     *
     * @return boolean 
     */
    public function getN352EsExtintores()
    {
        return $this->n352EsExtintores;
    }

    /**
     * Set n352ExtintoresCant
     *
     * @param integer $n352ExtintoresCant
     * @return InfraestructuraH2Caracteristica
     */
    public function setN352ExtintoresCant($n352ExtintoresCant)
    {
        $this->n352ExtintoresCant = $n352ExtintoresCant;
    
        return $this;
    }

    /**
     * Get n352ExtintoresCant
     *
     * @return integer 
     */
    public function getN352ExtintoresCant()
    {
        return $this->n352ExtintoresCant;
    }

    /**
     * Set n353EsCamSeg
     *
     * @param boolean $n353EsCamSeg
     * @return InfraestructuraH2Caracteristica
     */
    public function setN353EsCamSeg($n353EsCamSeg)
    {
        $this->n353EsCamSeg = $n353EsCamSeg;
    
        return $this;
    }

    /**
     * Get n353EsCamSeg
     *
     * @return boolean 
     */
    public function getN353EsCamSeg()
    {
        return $this->n353EsCamSeg;
    }

    /**
     * Set n353CamSegCant
     *
     * @param integer $n353CamSegCant
     * @return InfraestructuraH2Caracteristica
     */
    public function setN353CamSegCant($n353CamSegCant)
    {
        $this->n353CamSegCant = $n353CamSegCant;
    
        return $this;
    }

    /**
     * Get n353CamSegCant
     *
     * @return integer 
     */
    public function getN353CamSegCant()
    {
        return $this->n353CamSegCant;
    }

    /**
     * Set n353EsCamSegFuncionamiento
     *
     * @param boolean $n353EsCamSegFuncionamiento
     * @return InfraestructuraH2Caracteristica
     */
    public function setN353EsCamSegFuncionamiento($n353EsCamSegFuncionamiento)
    {
        $this->n353EsCamSegFuncionamiento = $n353EsCamSegFuncionamiento;
    
        return $this;
    }

    /**
     * Get n353EsCamSegFuncionamiento
     *
     * @return boolean 
     */
    public function getN353EsCamSegFuncionamiento()
    {
        return $this->n353EsCamSegFuncionamiento;
    }

    /**
     * Set n212SiSenaletica
     *
     * @param boolean $n212SiSenaletica
     * @return InfraestructuraH2Caracteristica
     */
    public function setN212SiSenaletica($n212SiSenaletica)
    {
        $this->n212SiSenaletica = $n212SiSenaletica;
    
        return $this;
    }

    /**
     * Get n212SiSenaletica
     *
     * @return boolean 
     */
    public function getN212SiSenaletica()
    {
        return $this->n212SiSenaletica;
    }

    /**
     * Set n31NroBloque
     *
     * @param integer $n31NroBloque
     * @return InfraestructuraH2Caracteristica
     */
    public function setN31NroBloque($n31NroBloque)
    {
        $this->n31NroBloque = $n31NroBloque;
    
        return $this;
    }

    /**
     * Get n31NroBloque
     *
     * @return integer 
     */
    public function getN31NroBloque()
    {
        return $this->n31NroBloque;
    }

    /**
     * Set n213SenalesTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo $n213SenalesTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN213SenalesTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo $n213SenalesTipo = null)
    {
        $this->n213SenalesTipo = $n213SenalesTipo;
    
        return $this;
    }

    /**
     * Get n213SenalesTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo 
     */
    public function getN213SenalesTipo()
    {
        return $this->n213SenalesTipo;
    }

    /**
     * Set n212SenalesiomaTipo2
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n212SenalesiomaTipo2
     * @return InfraestructuraH2Caracteristica
     */
    public function setN212SenalesiomaTipo2(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n212SenalesiomaTipo2 = null)
    {
        $this->n212SenalesiomaTipo2 = $n212SenalesiomaTipo2;
    
        return $this;
    }

    /**
     * Get n212SenalesiomaTipo2
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo 
     */
    public function getN212SenalesiomaTipo2()
    {
        return $this->n212SenalesiomaTipo2;
    }

    /**
     * Set n212SenalesiomaTipo1
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n212SenalesiomaTipo1
     * @return InfraestructuraH2Caracteristica
     */
    public function setN212SenalesiomaTipo1(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n212SenalesiomaTipo1 = null)
    {
        $this->n212SenalesiomaTipo1 = $n212SenalesiomaTipo1;
    
        return $this;
    }

    /**
     * Get n212SenalesiomaTipo1
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo 
     */
    public function getN212SenalesiomaTipo1()
    {
        return $this->n212SenalesiomaTipo1;
    }

    /**
     * Set n215TipoRampas
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo $n215TipoRampas
     * @return InfraestructuraH2Caracteristica
     */
    public function setN215TipoRampas(\Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo $n215TipoRampas = null)
    {
        $this->n215TipoRampas = $n215TipoRampas;
    
        return $this;
    }

    /**
     * Get n215TipoRampas
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2RampaTipo 
     */
    public function getN215TipoRampas()
    {
        return $this->n215TipoRampas;
    }

    /**
     * Set n21InfraestructuraH2ConstruidaEducativoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2ConstruidaEducativoTipo $n21InfraestructuraH2ConstruidaEducativoTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN21InfraestructuraH2ConstruidaEducativoTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2ConstruidaEducativoTipo $n21InfraestructuraH2ConstruidaEducativoTipo = null)
    {
        $this->n21InfraestructuraH2ConstruidaEducativoTipo = $n21InfraestructuraH2ConstruidaEducativoTipo;
    
        return $this;
    }

    /**
     * Get n21InfraestructuraH2ConstruidaEducativoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2ConstruidaEducativoTipo 
     */
    public function getN21InfraestructuraH2ConstruidaEducativoTipo()
    {
        return $this->n21InfraestructuraH2ConstruidaEducativoTipo;
    }

    /**
     * Set n25InfraestructuraH2PropiedadTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2PropiedadTipo $n25InfraestructuraH2PropiedadTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN25InfraestructuraH2PropiedadTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2PropiedadTipo $n25InfraestructuraH2PropiedadTipo = null)
    {
        $this->n25InfraestructuraH2PropiedadTipo = $n25InfraestructuraH2PropiedadTipo;
    
        return $this;
    }

    /**
     * Get n25InfraestructuraH2PropiedadTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2PropiedadTipo 
     */
    public function getN25InfraestructuraH2PropiedadTipo()
    {
        return $this->n25InfraestructuraH2PropiedadTipo;
    }

    /**
     * Set n13EdificacionAmurallada
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenAvanceTipo $n13EdificacionAmurallada
     * @return InfraestructuraH2Caracteristica
     */
    public function setN13EdificacionAmurallada(\Sie\AppWebBundle\Entity\InfraestructuraGenAvanceTipo $n13EdificacionAmurallada = null)
    {
        $this->n13EdificacionAmurallada = $n13EdificacionAmurallada;
    
        return $this;
    }

    /**
     * Get n13EdificacionAmurallada
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenAvanceTipo 
     */
    public function getN13EdificacionAmurallada()
    {
        return $this->n13EdificacionAmurallada;
    }

    /**
     * Set n12InfraestructuraH2TopografiaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2TopografiaTipo $n12InfraestructuraH2TopografiaTipo
     * @return InfraestructuraH2Caracteristica
     */
    public function setN12InfraestructuraH2TopografiaTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2TopografiaTipo $n12InfraestructuraH2TopografiaTipo = null)
    {
        $this->n12InfraestructuraH2TopografiaTipo = $n12InfraestructuraH2TopografiaTipo;
    
        return $this;
    }

    /**
     * Get n12InfraestructuraH2TopografiaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2TopografiaTipo 
     */
    public function getN12InfraestructuraH2TopografiaTipo()
    {
        return $this->n12InfraestructuraH2TopografiaTipo;
    }
}
