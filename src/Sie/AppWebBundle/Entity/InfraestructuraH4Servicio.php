<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4Servicio
 */
class InfraestructuraH4Servicio
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $n11EsEnergiaelectrica;

    /**
     * @var string
     */
    private $n12FuenteElectricaOtro;

    /**
     * @var integer
     */
    private $n15NumeroAmbientesPedagogicos;

    /**
     * @var integer
     */
    private $n15NumeroAmbientesNoPedagogicos;

    /**
     * @var integer
     */
    private $n15NumeroBanios;

    /**
     * @var integer
     */
    private $n15NumeroMedidores;

    /**
     * @var integer
     */
    private $n16NumeroMedidoresFuncionan;

    /**
     * @var integer
     */
    private $n16NumeroMedidoresNoFuncionan;

    /**
     * @var boolean
     */
    private $n21EsDiponeAgua;

    /**
     * @var string
     */
    private $n22MedioAguaOtro;

    /**
     * @var boolean
     */
    private $n23EsCuentaTanqueAgua;

    /**
     * @var boolean
     */
    private $n24EsCuentaBombaAgua;

    /**
     * @var boolean
     */
    private $n25EsCuentaRedAgua;

    /**
     * @var integer
     */
    private $n25NumeroAmbientesAgua;

    /**
     * @var boolean
     */
    private $n31EsInstalacionSaneamiento;

    /**
     * @var boolean
     */
    private $n32ExcretasEsAlcantarillado;

    /**
     * @var boolean
     */
    private $n32ExcretasEsSeptica;

    /**
     * @var boolean
     */
    private $n32ExcretasEsPozo;

    /**
     * @var boolean
     */
    private $n32ExcretasEsCieloAbierto;

    /**
     * @var boolean
     */
    private $n32ExcretasEsOtro;

    /**
     * @var boolean
     */
    private $n33ExcretasEsNosabe;

    /**
     * @var boolean
     */
    private $n34EsBuenascondiciones;

    /**
     * @var boolean
     */
    private $n34EsBuenasventilacion;

    /**
     * @var boolean
     */
    private $n34EsPrivacidad;

    /**
     * @var string
     */
    private $n41EliminacionBasuraOtro;

    /**
     * @var boolean
     */
    private $n41EsCentroSalud;

    /**
     * @var string
     */
    private $n41MetrosCentroSalud;

    /**
     * @var boolean
     */
    private $n42EsCentroPolicial;

    /**
     * @var string
     */
    private $n42MetrosCentroPolicial;

    /**
     * @var boolean
     */
    private $n53EsCentroEsparcimiento;

    /**
     * @var string
     */
    private $n53MetrosCentroEsparcimiento;

    /**
     * @var boolean
     */
    private $n54EsCentroCultural;

    /**
     * @var string
     */
    private $n54MetrosCentroCultural;

    /**
     * @var boolean
     */
    private $n55EsCentroIglesia;

    /**
     * @var string
     */
    private $n55MetrosCentroIglesia;

    /**
     * @var boolean
     */
    private $n56EsCentroInternet;

    /**
     * @var string
     */
    private $n56MetrosCentroInternet;

    /**
     * @var boolean
     */
    private $n57EsCentroCorreo;

    /**
     * @var string
     */
    private $n57MetrosCentroCorreo;

    /**
     * @var boolean
     */
    private $n58EsCentroTelefono;

    /**
     * @var string
     */
    private $n58MetrosCentroTelefono;

    /**
     * @var boolean
     */
    private $n59EsCentroNucleoEducativo;

    /**
     * @var string
     */
    private $n59MetrosCentroNucleoEducativo;

    /**
     * @var boolean
     */
    private $n510EsCentroRadiocomunicacion;

    /**
     * @var string
     */
    private $n510MetrosCentroRadiocomunicacion;

    /**
     * @var boolean
     */
    private $n511EsServicioEnfermeria;

    /**
     * @var boolean
     */
    private $n512EsServicioInternet;

    /**
     * @var boolean
     */
    private $n43EsServicioTelecentro;

    /**
     * @var boolean
     */
    private $n514EsServicioGas;

    /**
     * @var boolean
     */
    private $n515EsSenalDiscapacidad;

    /**
     * @var boolean
     */
    private $n516EsFuncionaCee;

    /**
     * @var boolean
     */
    private $n517EsRampasacceso;

    /**
     * @var boolean
     */
    private $n518EsGuiasDiscapicidadVisual;

    /**
     * @var boolean
     */
    private $n519EsAmbientesPedagogicosDiscapacidad;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var boolean
     */
    private $n13EsTipoInstalacion;

    /**
     * @var string
     */
    private $n43MetrosServicioTelecentro;

    /**
     * @var boolean
     */
    private $n43EsDnaSlim;

    /**
     * @var string
     */
    private $n43MetrosDnaSlim;

    /**
     * @var boolean
     */
    private $n43EsUesProx;

    /**
     * @var string
     */
    private $n43MetrosUesProx;

    /**
     * @var boolean
     */
    private $n43EsEstBomberos;

    /**
     * @var string
     */
    private $n43MetrosEstBomberos;

    /**
     * @var boolean
     */
    private $n43EsMercadoProxim;

    /**
     * @var string
     */
    private $n43MetrosMercadoProxim;

    /**
     * @var boolean
     */
    private $n43EsComunitariaProxim;

    /**
     * @var string
     */
    private $n43MetrosComunitariaProxim;

    /**
     * @var boolean
     */
    private $n43EsUniversidadProxim;

    /**
     * @var string
     */
    private $n43MetrosUniversidadProxim;

    /**
     * @var boolean
     */
    private $n43EsTecnologicoProxim;

    /**
     * @var string
     */
    private $n43MetrosTecnologicoProxim;

    /**
     * @var integer
     */
    private $n29AmbientesConAgua;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4PurificadorAguaTipo
     */
    private $n28PurificadorAguaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4UsoAguaTipo
     */
    private $n27UsoAguaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH3EnlugarTipo
     */
    private $n518GuiasDiscapicidadEnlugarTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH3EnlugarTipo
     */
    private $n517RampasaccesoEnlugarTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH3IdiomaTipo
     */
    private $n515EsSenaliomaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH3PeriodicidadTipo
     */
    private $n34PeriodicidadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4EliminacionBasuraTipo
     */
    private $n33ElminacionBasuraTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4AccesoAguaTipo
     */
    private $n26AccesoAguaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4MedioAguaTipo
     */
    private $n22MedioAguaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4DisponibilidadTipo
     */
    private $n14DisponibilidadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4FuenteElectricaTipo
     */
    private $n12FuenteElectricaTipo;

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
     * Set n11EsEnergiaelectrica
     *
     * @param boolean $n11EsEnergiaelectrica
     * @return InfraestructuraH4Servicio
     */
    public function setN11EsEnergiaelectrica($n11EsEnergiaelectrica)
    {
        $this->n11EsEnergiaelectrica = $n11EsEnergiaelectrica;
    
        return $this;
    }

    /**
     * Get n11EsEnergiaelectrica
     *
     * @return boolean 
     */
    public function getN11EsEnergiaelectrica()
    {
        return $this->n11EsEnergiaelectrica;
    }

    /**
     * Set n12FuenteElectricaOtro
     *
     * @param string $n12FuenteElectricaOtro
     * @return InfraestructuraH4Servicio
     */
    public function setN12FuenteElectricaOtro($n12FuenteElectricaOtro)
    {
        $this->n12FuenteElectricaOtro = $n12FuenteElectricaOtro;
    
        return $this;
    }

    /**
     * Get n12FuenteElectricaOtro
     *
     * @return string 
     */
    public function getN12FuenteElectricaOtro()
    {
        return $this->n12FuenteElectricaOtro;
    }

    /**
     * Set n15NumeroAmbientesPedagogicos
     *
     * @param integer $n15NumeroAmbientesPedagogicos
     * @return InfraestructuraH4Servicio
     */
    public function setN15NumeroAmbientesPedagogicos($n15NumeroAmbientesPedagogicos)
    {
        $this->n15NumeroAmbientesPedagogicos = $n15NumeroAmbientesPedagogicos;
    
        return $this;
    }

    /**
     * Get n15NumeroAmbientesPedagogicos
     *
     * @return integer 
     */
    public function getN15NumeroAmbientesPedagogicos()
    {
        return $this->n15NumeroAmbientesPedagogicos;
    }

    /**
     * Set n15NumeroAmbientesNoPedagogicos
     *
     * @param integer $n15NumeroAmbientesNoPedagogicos
     * @return InfraestructuraH4Servicio
     */
    public function setN15NumeroAmbientesNoPedagogicos($n15NumeroAmbientesNoPedagogicos)
    {
        $this->n15NumeroAmbientesNoPedagogicos = $n15NumeroAmbientesNoPedagogicos;
    
        return $this;
    }

    /**
     * Get n15NumeroAmbientesNoPedagogicos
     *
     * @return integer 
     */
    public function getN15NumeroAmbientesNoPedagogicos()
    {
        return $this->n15NumeroAmbientesNoPedagogicos;
    }

    /**
     * Set n15NumeroBanios
     *
     * @param integer $n15NumeroBanios
     * @return InfraestructuraH4Servicio
     */
    public function setN15NumeroBanios($n15NumeroBanios)
    {
        $this->n15NumeroBanios = $n15NumeroBanios;
    
        return $this;
    }

    /**
     * Get n15NumeroBanios
     *
     * @return integer 
     */
    public function getN15NumeroBanios()
    {
        return $this->n15NumeroBanios;
    }

    /**
     * Set n15NumeroMedidores
     *
     * @param integer $n15NumeroMedidores
     * @return InfraestructuraH4Servicio
     */
    public function setN15NumeroMedidores($n15NumeroMedidores)
    {
        $this->n15NumeroMedidores = $n15NumeroMedidores;
    
        return $this;
    }

    /**
     * Get n15NumeroMedidores
     *
     * @return integer 
     */
    public function getN15NumeroMedidores()
    {
        return $this->n15NumeroMedidores;
    }

    /**
     * Set n16NumeroMedidoresFuncionan
     *
     * @param integer $n16NumeroMedidoresFuncionan
     * @return InfraestructuraH4Servicio
     */
    public function setN16NumeroMedidoresFuncionan($n16NumeroMedidoresFuncionan)
    {
        $this->n16NumeroMedidoresFuncionan = $n16NumeroMedidoresFuncionan;
    
        return $this;
    }

    /**
     * Get n16NumeroMedidoresFuncionan
     *
     * @return integer 
     */
    public function getN16NumeroMedidoresFuncionan()
    {
        return $this->n16NumeroMedidoresFuncionan;
    }

    /**
     * Set n16NumeroMedidoresNoFuncionan
     *
     * @param integer $n16NumeroMedidoresNoFuncionan
     * @return InfraestructuraH4Servicio
     */
    public function setN16NumeroMedidoresNoFuncionan($n16NumeroMedidoresNoFuncionan)
    {
        $this->n16NumeroMedidoresNoFuncionan = $n16NumeroMedidoresNoFuncionan;
    
        return $this;
    }

    /**
     * Get n16NumeroMedidoresNoFuncionan
     *
     * @return integer 
     */
    public function getN16NumeroMedidoresNoFuncionan()
    {
        return $this->n16NumeroMedidoresNoFuncionan;
    }

    /**
     * Set n21EsDiponeAgua
     *
     * @param boolean $n21EsDiponeAgua
     * @return InfraestructuraH4Servicio
     */
    public function setN21EsDiponeAgua($n21EsDiponeAgua)
    {
        $this->n21EsDiponeAgua = $n21EsDiponeAgua;
    
        return $this;
    }

    /**
     * Get n21EsDiponeAgua
     *
     * @return boolean 
     */
    public function getN21EsDiponeAgua()
    {
        return $this->n21EsDiponeAgua;
    }

    /**
     * Set n22MedioAguaOtro
     *
     * @param string $n22MedioAguaOtro
     * @return InfraestructuraH4Servicio
     */
    public function setN22MedioAguaOtro($n22MedioAguaOtro)
    {
        $this->n22MedioAguaOtro = $n22MedioAguaOtro;
    
        return $this;
    }

    /**
     * Get n22MedioAguaOtro
     *
     * @return string 
     */
    public function getN22MedioAguaOtro()
    {
        return $this->n22MedioAguaOtro;
    }

    /**
     * Set n23EsCuentaTanqueAgua
     *
     * @param boolean $n23EsCuentaTanqueAgua
     * @return InfraestructuraH4Servicio
     */
    public function setN23EsCuentaTanqueAgua($n23EsCuentaTanqueAgua)
    {
        $this->n23EsCuentaTanqueAgua = $n23EsCuentaTanqueAgua;
    
        return $this;
    }

    /**
     * Get n23EsCuentaTanqueAgua
     *
     * @return boolean 
     */
    public function getN23EsCuentaTanqueAgua()
    {
        return $this->n23EsCuentaTanqueAgua;
    }

    /**
     * Set n24EsCuentaBombaAgua
     *
     * @param boolean $n24EsCuentaBombaAgua
     * @return InfraestructuraH4Servicio
     */
    public function setN24EsCuentaBombaAgua($n24EsCuentaBombaAgua)
    {
        $this->n24EsCuentaBombaAgua = $n24EsCuentaBombaAgua;
    
        return $this;
    }

    /**
     * Get n24EsCuentaBombaAgua
     *
     * @return boolean 
     */
    public function getN24EsCuentaBombaAgua()
    {
        return $this->n24EsCuentaBombaAgua;
    }

    /**
     * Set n25EsCuentaRedAgua
     *
     * @param boolean $n25EsCuentaRedAgua
     * @return InfraestructuraH4Servicio
     */
    public function setN25EsCuentaRedAgua($n25EsCuentaRedAgua)
    {
        $this->n25EsCuentaRedAgua = $n25EsCuentaRedAgua;
    
        return $this;
    }

    /**
     * Get n25EsCuentaRedAgua
     *
     * @return boolean 
     */
    public function getN25EsCuentaRedAgua()
    {
        return $this->n25EsCuentaRedAgua;
    }

    /**
     * Set n25NumeroAmbientesAgua
     *
     * @param integer $n25NumeroAmbientesAgua
     * @return InfraestructuraH4Servicio
     */
    public function setN25NumeroAmbientesAgua($n25NumeroAmbientesAgua)
    {
        $this->n25NumeroAmbientesAgua = $n25NumeroAmbientesAgua;
    
        return $this;
    }

    /**
     * Get n25NumeroAmbientesAgua
     *
     * @return integer 
     */
    public function getN25NumeroAmbientesAgua()
    {
        return $this->n25NumeroAmbientesAgua;
    }

    /**
     * Set n31EsInstalacionSaneamiento
     *
     * @param boolean $n31EsInstalacionSaneamiento
     * @return InfraestructuraH4Servicio
     */
    public function setN31EsInstalacionSaneamiento($n31EsInstalacionSaneamiento)
    {
        $this->n31EsInstalacionSaneamiento = $n31EsInstalacionSaneamiento;
    
        return $this;
    }

    /**
     * Get n31EsInstalacionSaneamiento
     *
     * @return boolean 
     */
    public function getN31EsInstalacionSaneamiento()
    {
        return $this->n31EsInstalacionSaneamiento;
    }

    /**
     * Set n32ExcretasEsAlcantarillado
     *
     * @param boolean $n32ExcretasEsAlcantarillado
     * @return InfraestructuraH4Servicio
     */
    public function setN32ExcretasEsAlcantarillado($n32ExcretasEsAlcantarillado)
    {
        $this->n32ExcretasEsAlcantarillado = $n32ExcretasEsAlcantarillado;
    
        return $this;
    }

    /**
     * Get n32ExcretasEsAlcantarillado
     *
     * @return boolean 
     */
    public function getN32ExcretasEsAlcantarillado()
    {
        return $this->n32ExcretasEsAlcantarillado;
    }

    /**
     * Set n32ExcretasEsSeptica
     *
     * @param boolean $n32ExcretasEsSeptica
     * @return InfraestructuraH4Servicio
     */
    public function setN32ExcretasEsSeptica($n32ExcretasEsSeptica)
    {
        $this->n32ExcretasEsSeptica = $n32ExcretasEsSeptica;
    
        return $this;
    }

    /**
     * Get n32ExcretasEsSeptica
     *
     * @return boolean 
     */
    public function getN32ExcretasEsSeptica()
    {
        return $this->n32ExcretasEsSeptica;
    }

    /**
     * Set n32ExcretasEsPozo
     *
     * @param boolean $n32ExcretasEsPozo
     * @return InfraestructuraH4Servicio
     */
    public function setN32ExcretasEsPozo($n32ExcretasEsPozo)
    {
        $this->n32ExcretasEsPozo = $n32ExcretasEsPozo;
    
        return $this;
    }

    /**
     * Get n32ExcretasEsPozo
     *
     * @return boolean 
     */
    public function getN32ExcretasEsPozo()
    {
        return $this->n32ExcretasEsPozo;
    }

    /**
     * Set n32ExcretasEsCieloAbierto
     *
     * @param boolean $n32ExcretasEsCieloAbierto
     * @return InfraestructuraH4Servicio
     */
    public function setN32ExcretasEsCieloAbierto($n32ExcretasEsCieloAbierto)
    {
        $this->n32ExcretasEsCieloAbierto = $n32ExcretasEsCieloAbierto;
    
        return $this;
    }

    /**
     * Get n32ExcretasEsCieloAbierto
     *
     * @return boolean 
     */
    public function getN32ExcretasEsCieloAbierto()
    {
        return $this->n32ExcretasEsCieloAbierto;
    }

    /**
     * Set n32ExcretasEsOtro
     *
     * @param boolean $n32ExcretasEsOtro
     * @return InfraestructuraH4Servicio
     */
    public function setN32ExcretasEsOtro($n32ExcretasEsOtro)
    {
        $this->n32ExcretasEsOtro = $n32ExcretasEsOtro;
    
        return $this;
    }

    /**
     * Get n32ExcretasEsOtro
     *
     * @return boolean 
     */
    public function getN32ExcretasEsOtro()
    {
        return $this->n32ExcretasEsOtro;
    }

    /**
     * Set n33ExcretasEsNosabe
     *
     * @param boolean $n33ExcretasEsNosabe
     * @return InfraestructuraH4Servicio
     */
    public function setN33ExcretasEsNosabe($n33ExcretasEsNosabe)
    {
        $this->n33ExcretasEsNosabe = $n33ExcretasEsNosabe;
    
        return $this;
    }

    /**
     * Get n33ExcretasEsNosabe
     *
     * @return boolean 
     */
    public function getN33ExcretasEsNosabe()
    {
        return $this->n33ExcretasEsNosabe;
    }

    /**
     * Set n34EsBuenascondiciones
     *
     * @param boolean $n34EsBuenascondiciones
     * @return InfraestructuraH4Servicio
     */
    public function setN34EsBuenascondiciones($n34EsBuenascondiciones)
    {
        $this->n34EsBuenascondiciones = $n34EsBuenascondiciones;
    
        return $this;
    }

    /**
     * Get n34EsBuenascondiciones
     *
     * @return boolean 
     */
    public function getN34EsBuenascondiciones()
    {
        return $this->n34EsBuenascondiciones;
    }

    /**
     * Set n34EsBuenasventilacion
     *
     * @param boolean $n34EsBuenasventilacion
     * @return InfraestructuraH4Servicio
     */
    public function setN34EsBuenasventilacion($n34EsBuenasventilacion)
    {
        $this->n34EsBuenasventilacion = $n34EsBuenasventilacion;
    
        return $this;
    }

    /**
     * Get n34EsBuenasventilacion
     *
     * @return boolean 
     */
    public function getN34EsBuenasventilacion()
    {
        return $this->n34EsBuenasventilacion;
    }

    /**
     * Set n34EsPrivacidad
     *
     * @param boolean $n34EsPrivacidad
     * @return InfraestructuraH4Servicio
     */
    public function setN34EsPrivacidad($n34EsPrivacidad)
    {
        $this->n34EsPrivacidad = $n34EsPrivacidad;
    
        return $this;
    }

    /**
     * Get n34EsPrivacidad
     *
     * @return boolean 
     */
    public function getN34EsPrivacidad()
    {
        return $this->n34EsPrivacidad;
    }

    /**
     * Set n41EliminacionBasuraOtro
     *
     * @param string $n41EliminacionBasuraOtro
     * @return InfraestructuraH4Servicio
     */
    public function setN41EliminacionBasuraOtro($n41EliminacionBasuraOtro)
    {
        $this->n41EliminacionBasuraOtro = $n41EliminacionBasuraOtro;
    
        return $this;
    }

    /**
     * Get n41EliminacionBasuraOtro
     *
     * @return string 
     */
    public function getN41EliminacionBasuraOtro()
    {
        return $this->n41EliminacionBasuraOtro;
    }

    /**
     * Set n41EsCentroSalud
     *
     * @param boolean $n41EsCentroSalud
     * @return InfraestructuraH4Servicio
     */
    public function setN41EsCentroSalud($n41EsCentroSalud)
    {
        $this->n41EsCentroSalud = $n41EsCentroSalud;
    
        return $this;
    }

    /**
     * Get n41EsCentroSalud
     *
     * @return boolean 
     */
    public function getN41EsCentroSalud()
    {
        return $this->n41EsCentroSalud;
    }

    /**
     * Set n41MetrosCentroSalud
     *
     * @param string $n41MetrosCentroSalud
     * @return InfraestructuraH4Servicio
     */
    public function setN41MetrosCentroSalud($n41MetrosCentroSalud)
    {
        $this->n41MetrosCentroSalud = $n41MetrosCentroSalud;
    
        return $this;
    }

    /**
     * Get n41MetrosCentroSalud
     *
     * @return string 
     */
    public function getN41MetrosCentroSalud()
    {
        return $this->n41MetrosCentroSalud;
    }

    /**
     * Set n42EsCentroPolicial
     *
     * @param boolean $n42EsCentroPolicial
     * @return InfraestructuraH4Servicio
     */
    public function setN42EsCentroPolicial($n42EsCentroPolicial)
    {
        $this->n42EsCentroPolicial = $n42EsCentroPolicial;
    
        return $this;
    }

    /**
     * Get n42EsCentroPolicial
     *
     * @return boolean 
     */
    public function getN42EsCentroPolicial()
    {
        return $this->n42EsCentroPolicial;
    }

    /**
     * Set n42MetrosCentroPolicial
     *
     * @param string $n42MetrosCentroPolicial
     * @return InfraestructuraH4Servicio
     */
    public function setN42MetrosCentroPolicial($n42MetrosCentroPolicial)
    {
        $this->n42MetrosCentroPolicial = $n42MetrosCentroPolicial;
    
        return $this;
    }

    /**
     * Get n42MetrosCentroPolicial
     *
     * @return string 
     */
    public function getN42MetrosCentroPolicial()
    {
        return $this->n42MetrosCentroPolicial;
    }

    /**
     * Set n53EsCentroEsparcimiento
     *
     * @param boolean $n53EsCentroEsparcimiento
     * @return InfraestructuraH4Servicio
     */
    public function setN53EsCentroEsparcimiento($n53EsCentroEsparcimiento)
    {
        $this->n53EsCentroEsparcimiento = $n53EsCentroEsparcimiento;
    
        return $this;
    }

    /**
     * Get n53EsCentroEsparcimiento
     *
     * @return boolean 
     */
    public function getN53EsCentroEsparcimiento()
    {
        return $this->n53EsCentroEsparcimiento;
    }

    /**
     * Set n53MetrosCentroEsparcimiento
     *
     * @param string $n53MetrosCentroEsparcimiento
     * @return InfraestructuraH4Servicio
     */
    public function setN53MetrosCentroEsparcimiento($n53MetrosCentroEsparcimiento)
    {
        $this->n53MetrosCentroEsparcimiento = $n53MetrosCentroEsparcimiento;
    
        return $this;
    }

    /**
     * Get n53MetrosCentroEsparcimiento
     *
     * @return string 
     */
    public function getN53MetrosCentroEsparcimiento()
    {
        return $this->n53MetrosCentroEsparcimiento;
    }

    /**
     * Set n54EsCentroCultural
     *
     * @param boolean $n54EsCentroCultural
     * @return InfraestructuraH4Servicio
     */
    public function setN54EsCentroCultural($n54EsCentroCultural)
    {
        $this->n54EsCentroCultural = $n54EsCentroCultural;
    
        return $this;
    }

    /**
     * Get n54EsCentroCultural
     *
     * @return boolean 
     */
    public function getN54EsCentroCultural()
    {
        return $this->n54EsCentroCultural;
    }

    /**
     * Set n54MetrosCentroCultural
     *
     * @param string $n54MetrosCentroCultural
     * @return InfraestructuraH4Servicio
     */
    public function setN54MetrosCentroCultural($n54MetrosCentroCultural)
    {
        $this->n54MetrosCentroCultural = $n54MetrosCentroCultural;
    
        return $this;
    }

    /**
     * Get n54MetrosCentroCultural
     *
     * @return string 
     */
    public function getN54MetrosCentroCultural()
    {
        return $this->n54MetrosCentroCultural;
    }

    /**
     * Set n55EsCentroIglesia
     *
     * @param boolean $n55EsCentroIglesia
     * @return InfraestructuraH4Servicio
     */
    public function setN55EsCentroIglesia($n55EsCentroIglesia)
    {
        $this->n55EsCentroIglesia = $n55EsCentroIglesia;
    
        return $this;
    }

    /**
     * Get n55EsCentroIglesia
     *
     * @return boolean 
     */
    public function getN55EsCentroIglesia()
    {
        return $this->n55EsCentroIglesia;
    }

    /**
     * Set n55MetrosCentroIglesia
     *
     * @param string $n55MetrosCentroIglesia
     * @return InfraestructuraH4Servicio
     */
    public function setN55MetrosCentroIglesia($n55MetrosCentroIglesia)
    {
        $this->n55MetrosCentroIglesia = $n55MetrosCentroIglesia;
    
        return $this;
    }

    /**
     * Get n55MetrosCentroIglesia
     *
     * @return string 
     */
    public function getN55MetrosCentroIglesia()
    {
        return $this->n55MetrosCentroIglesia;
    }

    /**
     * Set n56EsCentroInternet
     *
     * @param boolean $n56EsCentroInternet
     * @return InfraestructuraH4Servicio
     */
    public function setN56EsCentroInternet($n56EsCentroInternet)
    {
        $this->n56EsCentroInternet = $n56EsCentroInternet;
    
        return $this;
    }

    /**
     * Get n56EsCentroInternet
     *
     * @return boolean 
     */
    public function getN56EsCentroInternet()
    {
        return $this->n56EsCentroInternet;
    }

    /**
     * Set n56MetrosCentroInternet
     *
     * @param string $n56MetrosCentroInternet
     * @return InfraestructuraH4Servicio
     */
    public function setN56MetrosCentroInternet($n56MetrosCentroInternet)
    {
        $this->n56MetrosCentroInternet = $n56MetrosCentroInternet;
    
        return $this;
    }

    /**
     * Get n56MetrosCentroInternet
     *
     * @return string 
     */
    public function getN56MetrosCentroInternet()
    {
        return $this->n56MetrosCentroInternet;
    }

    /**
     * Set n57EsCentroCorreo
     *
     * @param boolean $n57EsCentroCorreo
     * @return InfraestructuraH4Servicio
     */
    public function setN57EsCentroCorreo($n57EsCentroCorreo)
    {
        $this->n57EsCentroCorreo = $n57EsCentroCorreo;
    
        return $this;
    }

    /**
     * Get n57EsCentroCorreo
     *
     * @return boolean 
     */
    public function getN57EsCentroCorreo()
    {
        return $this->n57EsCentroCorreo;
    }

    /**
     * Set n57MetrosCentroCorreo
     *
     * @param string $n57MetrosCentroCorreo
     * @return InfraestructuraH4Servicio
     */
    public function setN57MetrosCentroCorreo($n57MetrosCentroCorreo)
    {
        $this->n57MetrosCentroCorreo = $n57MetrosCentroCorreo;
    
        return $this;
    }

    /**
     * Get n57MetrosCentroCorreo
     *
     * @return string 
     */
    public function getN57MetrosCentroCorreo()
    {
        return $this->n57MetrosCentroCorreo;
    }

    /**
     * Set n58EsCentroTelefono
     *
     * @param boolean $n58EsCentroTelefono
     * @return InfraestructuraH4Servicio
     */
    public function setN58EsCentroTelefono($n58EsCentroTelefono)
    {
        $this->n58EsCentroTelefono = $n58EsCentroTelefono;
    
        return $this;
    }

    /**
     * Get n58EsCentroTelefono
     *
     * @return boolean 
     */
    public function getN58EsCentroTelefono()
    {
        return $this->n58EsCentroTelefono;
    }

    /**
     * Set n58MetrosCentroTelefono
     *
     * @param string $n58MetrosCentroTelefono
     * @return InfraestructuraH4Servicio
     */
    public function setN58MetrosCentroTelefono($n58MetrosCentroTelefono)
    {
        $this->n58MetrosCentroTelefono = $n58MetrosCentroTelefono;
    
        return $this;
    }

    /**
     * Get n58MetrosCentroTelefono
     *
     * @return string 
     */
    public function getN58MetrosCentroTelefono()
    {
        return $this->n58MetrosCentroTelefono;
    }

    /**
     * Set n59EsCentroNucleoEducativo
     *
     * @param boolean $n59EsCentroNucleoEducativo
     * @return InfraestructuraH4Servicio
     */
    public function setN59EsCentroNucleoEducativo($n59EsCentroNucleoEducativo)
    {
        $this->n59EsCentroNucleoEducativo = $n59EsCentroNucleoEducativo;
    
        return $this;
    }

    /**
     * Get n59EsCentroNucleoEducativo
     *
     * @return boolean 
     */
    public function getN59EsCentroNucleoEducativo()
    {
        return $this->n59EsCentroNucleoEducativo;
    }

    /**
     * Set n59MetrosCentroNucleoEducativo
     *
     * @param string $n59MetrosCentroNucleoEducativo
     * @return InfraestructuraH4Servicio
     */
    public function setN59MetrosCentroNucleoEducativo($n59MetrosCentroNucleoEducativo)
    {
        $this->n59MetrosCentroNucleoEducativo = $n59MetrosCentroNucleoEducativo;
    
        return $this;
    }

    /**
     * Get n59MetrosCentroNucleoEducativo
     *
     * @return string 
     */
    public function getN59MetrosCentroNucleoEducativo()
    {
        return $this->n59MetrosCentroNucleoEducativo;
    }

    /**
     * Set n510EsCentroRadiocomunicacion
     *
     * @param boolean $n510EsCentroRadiocomunicacion
     * @return InfraestructuraH4Servicio
     */
    public function setN510EsCentroRadiocomunicacion($n510EsCentroRadiocomunicacion)
    {
        $this->n510EsCentroRadiocomunicacion = $n510EsCentroRadiocomunicacion;
    
        return $this;
    }

    /**
     * Get n510EsCentroRadiocomunicacion
     *
     * @return boolean 
     */
    public function getN510EsCentroRadiocomunicacion()
    {
        return $this->n510EsCentroRadiocomunicacion;
    }

    /**
     * Set n510MetrosCentroRadiocomunicacion
     *
     * @param string $n510MetrosCentroRadiocomunicacion
     * @return InfraestructuraH4Servicio
     */
    public function setN510MetrosCentroRadiocomunicacion($n510MetrosCentroRadiocomunicacion)
    {
        $this->n510MetrosCentroRadiocomunicacion = $n510MetrosCentroRadiocomunicacion;
    
        return $this;
    }

    /**
     * Get n510MetrosCentroRadiocomunicacion
     *
     * @return string 
     */
    public function getN510MetrosCentroRadiocomunicacion()
    {
        return $this->n510MetrosCentroRadiocomunicacion;
    }

    /**
     * Set n511EsServicioEnfermeria
     *
     * @param boolean $n511EsServicioEnfermeria
     * @return InfraestructuraH4Servicio
     */
    public function setN511EsServicioEnfermeria($n511EsServicioEnfermeria)
    {
        $this->n511EsServicioEnfermeria = $n511EsServicioEnfermeria;
    
        return $this;
    }

    /**
     * Get n511EsServicioEnfermeria
     *
     * @return boolean 
     */
    public function getN511EsServicioEnfermeria()
    {
        return $this->n511EsServicioEnfermeria;
    }

    /**
     * Set n512EsServicioInternet
     *
     * @param boolean $n512EsServicioInternet
     * @return InfraestructuraH4Servicio
     */
    public function setN512EsServicioInternet($n512EsServicioInternet)
    {
        $this->n512EsServicioInternet = $n512EsServicioInternet;
    
        return $this;
    }

    /**
     * Get n512EsServicioInternet
     *
     * @return boolean 
     */
    public function getN512EsServicioInternet()
    {
        return $this->n512EsServicioInternet;
    }

    /**
     * Set n43EsServicioTelecentro
     *
     * @param boolean $n43EsServicioTelecentro
     * @return InfraestructuraH4Servicio
     */
    public function setN43EsServicioTelecentro($n43EsServicioTelecentro)
    {
        $this->n43EsServicioTelecentro = $n43EsServicioTelecentro;
    
        return $this;
    }

    /**
     * Get n43EsServicioTelecentro
     *
     * @return boolean 
     */
    public function getN43EsServicioTelecentro()
    {
        return $this->n43EsServicioTelecentro;
    }

    /**
     * Set n514EsServicioGas
     *
     * @param boolean $n514EsServicioGas
     * @return InfraestructuraH4Servicio
     */
    public function setN514EsServicioGas($n514EsServicioGas)
    {
        $this->n514EsServicioGas = $n514EsServicioGas;
    
        return $this;
    }

    /**
     * Get n514EsServicioGas
     *
     * @return boolean 
     */
    public function getN514EsServicioGas()
    {
        return $this->n514EsServicioGas;
    }

    /**
     * Set n515EsSenalDiscapacidad
     *
     * @param boolean $n515EsSenalDiscapacidad
     * @return InfraestructuraH4Servicio
     */
    public function setN515EsSenalDiscapacidad($n515EsSenalDiscapacidad)
    {
        $this->n515EsSenalDiscapacidad = $n515EsSenalDiscapacidad;
    
        return $this;
    }

    /**
     * Get n515EsSenalDiscapacidad
     *
     * @return boolean 
     */
    public function getN515EsSenalDiscapacidad()
    {
        return $this->n515EsSenalDiscapacidad;
    }

    /**
     * Set n516EsFuncionaCee
     *
     * @param boolean $n516EsFuncionaCee
     * @return InfraestructuraH4Servicio
     */
    public function setN516EsFuncionaCee($n516EsFuncionaCee)
    {
        $this->n516EsFuncionaCee = $n516EsFuncionaCee;
    
        return $this;
    }

    /**
     * Get n516EsFuncionaCee
     *
     * @return boolean 
     */
    public function getN516EsFuncionaCee()
    {
        return $this->n516EsFuncionaCee;
    }

    /**
     * Set n517EsRampasacceso
     *
     * @param boolean $n517EsRampasacceso
     * @return InfraestructuraH4Servicio
     */
    public function setN517EsRampasacceso($n517EsRampasacceso)
    {
        $this->n517EsRampasacceso = $n517EsRampasacceso;
    
        return $this;
    }

    /**
     * Get n517EsRampasacceso
     *
     * @return boolean 
     */
    public function getN517EsRampasacceso()
    {
        return $this->n517EsRampasacceso;
    }

    /**
     * Set n518EsGuiasDiscapicidadVisual
     *
     * @param boolean $n518EsGuiasDiscapicidadVisual
     * @return InfraestructuraH4Servicio
     */
    public function setN518EsGuiasDiscapicidadVisual($n518EsGuiasDiscapicidadVisual)
    {
        $this->n518EsGuiasDiscapicidadVisual = $n518EsGuiasDiscapicidadVisual;
    
        return $this;
    }

    /**
     * Get n518EsGuiasDiscapicidadVisual
     *
     * @return boolean 
     */
    public function getN518EsGuiasDiscapicidadVisual()
    {
        return $this->n518EsGuiasDiscapicidadVisual;
    }

    /**
     * Set n519EsAmbientesPedagogicosDiscapacidad
     *
     * @param boolean $n519EsAmbientesPedagogicosDiscapacidad
     * @return InfraestructuraH4Servicio
     */
    public function setN519EsAmbientesPedagogicosDiscapacidad($n519EsAmbientesPedagogicosDiscapacidad)
    {
        $this->n519EsAmbientesPedagogicosDiscapacidad = $n519EsAmbientesPedagogicosDiscapacidad;
    
        return $this;
    }

    /**
     * Get n519EsAmbientesPedagogicosDiscapacidad
     *
     * @return boolean 
     */
    public function getN519EsAmbientesPedagogicosDiscapacidad()
    {
        return $this->n519EsAmbientesPedagogicosDiscapacidad;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH4Servicio
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
     * @return InfraestructuraH4Servicio
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
     * Set n13EsTipoInstalacion
     *
     * @param boolean $n13EsTipoInstalacion
     * @return InfraestructuraH4Servicio
     */
    public function setN13EsTipoInstalacion($n13EsTipoInstalacion)
    {
        $this->n13EsTipoInstalacion = $n13EsTipoInstalacion;
    
        return $this;
    }

    /**
     * Get n13EsTipoInstalacion
     *
     * @return boolean 
     */
    public function getN13EsTipoInstalacion()
    {
        return $this->n13EsTipoInstalacion;
    }

    /**
     * Set n43MetrosServicioTelecentro
     *
     * @param string $n43MetrosServicioTelecentro
     * @return InfraestructuraH4Servicio
     */
    public function setN43MetrosServicioTelecentro($n43MetrosServicioTelecentro)
    {
        $this->n43MetrosServicioTelecentro = $n43MetrosServicioTelecentro;
    
        return $this;
    }

    /**
     * Get n43MetrosServicioTelecentro
     *
     * @return string 
     */
    public function getN43MetrosServicioTelecentro()
    {
        return $this->n43MetrosServicioTelecentro;
    }

    /**
     * Set n43EsDnaSlim
     *
     * @param boolean $n43EsDnaSlim
     * @return InfraestructuraH4Servicio
     */
    public function setN43EsDnaSlim($n43EsDnaSlim)
    {
        $this->n43EsDnaSlim = $n43EsDnaSlim;
    
        return $this;
    }

    /**
     * Get n43EsDnaSlim
     *
     * @return boolean 
     */
    public function getN43EsDnaSlim()
    {
        return $this->n43EsDnaSlim;
    }

    /**
     * Set n43MetrosDnaSlim
     *
     * @param string $n43MetrosDnaSlim
     * @return InfraestructuraH4Servicio
     */
    public function setN43MetrosDnaSlim($n43MetrosDnaSlim)
    {
        $this->n43MetrosDnaSlim = $n43MetrosDnaSlim;
    
        return $this;
    }

    /**
     * Get n43MetrosDnaSlim
     *
     * @return string 
     */
    public function getN43MetrosDnaSlim()
    {
        return $this->n43MetrosDnaSlim;
    }

    /**
     * Set n43EsUesProx
     *
     * @param boolean $n43EsUesProx
     * @return InfraestructuraH4Servicio
     */
    public function setN43EsUesProx($n43EsUesProx)
    {
        $this->n43EsUesProx = $n43EsUesProx;
    
        return $this;
    }

    /**
     * Get n43EsUesProx
     *
     * @return boolean 
     */
    public function getN43EsUesProx()
    {
        return $this->n43EsUesProx;
    }

    /**
     * Set n43MetrosUesProx
     *
     * @param string $n43MetrosUesProx
     * @return InfraestructuraH4Servicio
     */
    public function setN43MetrosUesProx($n43MetrosUesProx)
    {
        $this->n43MetrosUesProx = $n43MetrosUesProx;
    
        return $this;
    }

    /**
     * Get n43MetrosUesProx
     *
     * @return string 
     */
    public function getN43MetrosUesProx()
    {
        return $this->n43MetrosUesProx;
    }

    /**
     * Set n43EsEstBomberos
     *
     * @param boolean $n43EsEstBomberos
     * @return InfraestructuraH4Servicio
     */
    public function setN43EsEstBomberos($n43EsEstBomberos)
    {
        $this->n43EsEstBomberos = $n43EsEstBomberos;
    
        return $this;
    }

    /**
     * Get n43EsEstBomberos
     *
     * @return boolean 
     */
    public function getN43EsEstBomberos()
    {
        return $this->n43EsEstBomberos;
    }

    /**
     * Set n43MetrosEstBomberos
     *
     * @param string $n43MetrosEstBomberos
     * @return InfraestructuraH4Servicio
     */
    public function setN43MetrosEstBomberos($n43MetrosEstBomberos)
    {
        $this->n43MetrosEstBomberos = $n43MetrosEstBomberos;
    
        return $this;
    }

    /**
     * Get n43MetrosEstBomberos
     *
     * @return string 
     */
    public function getN43MetrosEstBomberos()
    {
        return $this->n43MetrosEstBomberos;
    }

    /**
     * Set n43EsMercadoProxim
     *
     * @param boolean $n43EsMercadoProxim
     * @return InfraestructuraH4Servicio
     */
    public function setN43EsMercadoProxim($n43EsMercadoProxim)
    {
        $this->n43EsMercadoProxim = $n43EsMercadoProxim;
    
        return $this;
    }

    /**
     * Get n43EsMercadoProxim
     *
     * @return boolean 
     */
    public function getN43EsMercadoProxim()
    {
        return $this->n43EsMercadoProxim;
    }

    /**
     * Set n43MetrosMercadoProxim
     *
     * @param string $n43MetrosMercadoProxim
     * @return InfraestructuraH4Servicio
     */
    public function setN43MetrosMercadoProxim($n43MetrosMercadoProxim)
    {
        $this->n43MetrosMercadoProxim = $n43MetrosMercadoProxim;
    
        return $this;
    }

    /**
     * Get n43MetrosMercadoProxim
     *
     * @return string 
     */
    public function getN43MetrosMercadoProxim()
    {
        return $this->n43MetrosMercadoProxim;
    }

    /**
     * Set n43EsComunitariaProxim
     *
     * @param boolean $n43EsComunitariaProxim
     * @return InfraestructuraH4Servicio
     */
    public function setN43EsComunitariaProxim($n43EsComunitariaProxim)
    {
        $this->n43EsComunitariaProxim = $n43EsComunitariaProxim;
    
        return $this;
    }

    /**
     * Get n43EsComunitariaProxim
     *
     * @return boolean 
     */
    public function getN43EsComunitariaProxim()
    {
        return $this->n43EsComunitariaProxim;
    }

    /**
     * Set n43MetrosComunitariaProxim
     *
     * @param string $n43MetrosComunitariaProxim
     * @return InfraestructuraH4Servicio
     */
    public function setN43MetrosComunitariaProxim($n43MetrosComunitariaProxim)
    {
        $this->n43MetrosComunitariaProxim = $n43MetrosComunitariaProxim;
    
        return $this;
    }

    /**
     * Get n43MetrosComunitariaProxim
     *
     * @return string 
     */
    public function getN43MetrosComunitariaProxim()
    {
        return $this->n43MetrosComunitariaProxim;
    }

    /**
     * Set n43EsUniversidadProxim
     *
     * @param boolean $n43EsUniversidadProxim
     * @return InfraestructuraH4Servicio
     */
    public function setN43EsUniversidadProxim($n43EsUniversidadProxim)
    {
        $this->n43EsUniversidadProxim = $n43EsUniversidadProxim;
    
        return $this;
    }

    /**
     * Get n43EsUniversidadProxim
     *
     * @return boolean 
     */
    public function getN43EsUniversidadProxim()
    {
        return $this->n43EsUniversidadProxim;
    }

    /**
     * Set n43MetrosUniversidadProxim
     *
     * @param string $n43MetrosUniversidadProxim
     * @return InfraestructuraH4Servicio
     */
    public function setN43MetrosUniversidadProxim($n43MetrosUniversidadProxim)
    {
        $this->n43MetrosUniversidadProxim = $n43MetrosUniversidadProxim;
    
        return $this;
    }

    /**
     * Get n43MetrosUniversidadProxim
     *
     * @return string 
     */
    public function getN43MetrosUniversidadProxim()
    {
        return $this->n43MetrosUniversidadProxim;
    }

    /**
     * Set n43EsTecnologicoProxim
     *
     * @param boolean $n43EsTecnologicoProxim
     * @return InfraestructuraH4Servicio
     */
    public function setN43EsTecnologicoProxim($n43EsTecnologicoProxim)
    {
        $this->n43EsTecnologicoProxim = $n43EsTecnologicoProxim;
    
        return $this;
    }

    /**
     * Get n43EsTecnologicoProxim
     *
     * @return boolean 
     */
    public function getN43EsTecnologicoProxim()
    {
        return $this->n43EsTecnologicoProxim;
    }

    /**
     * Set n43MetrosTecnologicoProxim
     *
     * @param string $n43MetrosTecnologicoProxim
     * @return InfraestructuraH4Servicio
     */
    public function setN43MetrosTecnologicoProxim($n43MetrosTecnologicoProxim)
    {
        $this->n43MetrosTecnologicoProxim = $n43MetrosTecnologicoProxim;
    
        return $this;
    }

    /**
     * Get n43MetrosTecnologicoProxim
     *
     * @return string 
     */
    public function getN43MetrosTecnologicoProxim()
    {
        return $this->n43MetrosTecnologicoProxim;
    }

    /**
     * Set n29AmbientesConAgua
     *
     * @param integer $n29AmbientesConAgua
     * @return InfraestructuraH4Servicio
     */
    public function setN29AmbientesConAgua($n29AmbientesConAgua)
    {
        $this->n29AmbientesConAgua = $n29AmbientesConAgua;
    
        return $this;
    }

    /**
     * Get n29AmbientesConAgua
     *
     * @return integer 
     */
    public function getN29AmbientesConAgua()
    {
        return $this->n29AmbientesConAgua;
    }

    /**
     * Set n28PurificadorAguaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4PurificadorAguaTipo $n28PurificadorAguaTipo
     * @return InfraestructuraH4Servicio
     */
    public function setN28PurificadorAguaTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4PurificadorAguaTipo $n28PurificadorAguaTipo = null)
    {
        $this->n28PurificadorAguaTipo = $n28PurificadorAguaTipo;
    
        return $this;
    }

    /**
     * Get n28PurificadorAguaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4PurificadorAguaTipo 
     */
    public function getN28PurificadorAguaTipo()
    {
        return $this->n28PurificadorAguaTipo;
    }

    /**
     * Set n27UsoAguaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4UsoAguaTipo $n27UsoAguaTipo
     * @return InfraestructuraH4Servicio
     */
    public function setN27UsoAguaTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4UsoAguaTipo $n27UsoAguaTipo = null)
    {
        $this->n27UsoAguaTipo = $n27UsoAguaTipo;
    
        return $this;
    }

    /**
     * Get n27UsoAguaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4UsoAguaTipo 
     */
    public function getN27UsoAguaTipo()
    {
        return $this->n27UsoAguaTipo;
    }

    /**
     * Set n518GuiasDiscapicidadEnlugarTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH3EnlugarTipo $n518GuiasDiscapicidadEnlugarTipo
     * @return InfraestructuraH4Servicio
     */
    public function setN518GuiasDiscapicidadEnlugarTipo(\Sie\AppWebBundle\Entity\InfraestructuraH3EnlugarTipo $n518GuiasDiscapicidadEnlugarTipo = null)
    {
        $this->n518GuiasDiscapicidadEnlugarTipo = $n518GuiasDiscapicidadEnlugarTipo;
    
        return $this;
    }

    /**
     * Get n518GuiasDiscapicidadEnlugarTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH3EnlugarTipo 
     */
    public function getN518GuiasDiscapicidadEnlugarTipo()
    {
        return $this->n518GuiasDiscapicidadEnlugarTipo;
    }

    /**
     * Set n517RampasaccesoEnlugarTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH3EnlugarTipo $n517RampasaccesoEnlugarTipo
     * @return InfraestructuraH4Servicio
     */
    public function setN517RampasaccesoEnlugarTipo(\Sie\AppWebBundle\Entity\InfraestructuraH3EnlugarTipo $n517RampasaccesoEnlugarTipo = null)
    {
        $this->n517RampasaccesoEnlugarTipo = $n517RampasaccesoEnlugarTipo;
    
        return $this;
    }

    /**
     * Get n517RampasaccesoEnlugarTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH3EnlugarTipo 
     */
    public function getN517RampasaccesoEnlugarTipo()
    {
        return $this->n517RampasaccesoEnlugarTipo;
    }

    /**
     * Set n515EsSenaliomaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH3IdiomaTipo $n515EsSenaliomaTipo
     * @return InfraestructuraH4Servicio
     */
    public function setN515EsSenaliomaTipo(\Sie\AppWebBundle\Entity\InfraestructuraH3IdiomaTipo $n515EsSenaliomaTipo = null)
    {
        $this->n515EsSenaliomaTipo = $n515EsSenaliomaTipo;
    
        return $this;
    }

    /**
     * Get n515EsSenaliomaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH3IdiomaTipo 
     */
    public function getN515EsSenaliomaTipo()
    {
        return $this->n515EsSenaliomaTipo;
    }

    /**
     * Set n34PeriodicidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH3PeriodicidadTipo $n34PeriodicidadTipo
     * @return InfraestructuraH4Servicio
     */
    public function setN34PeriodicidadTipo(\Sie\AppWebBundle\Entity\InfraestructuraH3PeriodicidadTipo $n34PeriodicidadTipo = null)
    {
        $this->n34PeriodicidadTipo = $n34PeriodicidadTipo;
    
        return $this;
    }

    /**
     * Get n34PeriodicidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH3PeriodicidadTipo 
     */
    public function getN34PeriodicidadTipo()
    {
        return $this->n34PeriodicidadTipo;
    }

    /**
     * Set n33ElminacionBasuraTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4EliminacionBasuraTipo $n33ElminacionBasuraTipo
     * @return InfraestructuraH4Servicio
     */
    public function setN33ElminacionBasuraTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4EliminacionBasuraTipo $n33ElminacionBasuraTipo = null)
    {
        $this->n33ElminacionBasuraTipo = $n33ElminacionBasuraTipo;
    
        return $this;
    }

    /**
     * Get n33ElminacionBasuraTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4EliminacionBasuraTipo 
     */
    public function getN33ElminacionBasuraTipo()
    {
        return $this->n33ElminacionBasuraTipo;
    }

    /**
     * Set n26AccesoAguaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4AccesoAguaTipo $n26AccesoAguaTipo
     * @return InfraestructuraH4Servicio
     */
    public function setN26AccesoAguaTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4AccesoAguaTipo $n26AccesoAguaTipo = null)
    {
        $this->n26AccesoAguaTipo = $n26AccesoAguaTipo;
    
        return $this;
    }

    /**
     * Get n26AccesoAguaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4AccesoAguaTipo 
     */
    public function getN26AccesoAguaTipo()
    {
        return $this->n26AccesoAguaTipo;
    }

    /**
     * Set n22MedioAguaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4MedioAguaTipo $n22MedioAguaTipo
     * @return InfraestructuraH4Servicio
     */
    public function setN22MedioAguaTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4MedioAguaTipo $n22MedioAguaTipo = null)
    {
        $this->n22MedioAguaTipo = $n22MedioAguaTipo;
    
        return $this;
    }

    /**
     * Get n22MedioAguaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4MedioAguaTipo 
     */
    public function getN22MedioAguaTipo()
    {
        return $this->n22MedioAguaTipo;
    }

    /**
     * Set n14DisponibilidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4DisponibilidadTipo $n14DisponibilidadTipo
     * @return InfraestructuraH4Servicio
     */
    public function setN14DisponibilidadTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4DisponibilidadTipo $n14DisponibilidadTipo = null)
    {
        $this->n14DisponibilidadTipo = $n14DisponibilidadTipo;
    
        return $this;
    }

    /**
     * Get n14DisponibilidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4DisponibilidadTipo 
     */
    public function getN14DisponibilidadTipo()
    {
        return $this->n14DisponibilidadTipo;
    }

    /**
     * Set n12FuenteElectricaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4FuenteElectricaTipo $n12FuenteElectricaTipo
     * @return InfraestructuraH4Servicio
     */
    public function setN12FuenteElectricaTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4FuenteElectricaTipo $n12FuenteElectricaTipo = null)
    {
        $this->n12FuenteElectricaTipo = $n12FuenteElectricaTipo;
    
        return $this;
    }

    /**
     * Get n12FuenteElectricaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4FuenteElectricaTipo 
     */
    public function getN12FuenteElectricaTipo()
    {
        return $this->n12FuenteElectricaTipo;
    }

    /**
     * Set infraestructuraJuridiccionGeografica
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica $infraestructuraJuridiccionGeografica
     * @return InfraestructuraH4Servicio
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
