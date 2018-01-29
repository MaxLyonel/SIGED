<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2CaracteristicaEdificadosPisos
 */
class InfraestructuraH2CaracteristicaEdificadosPisos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var float
     */
    private $n12AreaM2;

    /**
     * @var integer
     */
    private $n13NroAmbPedagogicos;

    /**
     * @var integer
     */
    private $n14NroAmbNoPedagogicos;

    /**
     * @var integer
     */
    private $n15TotalBanios;

    /**
     * @var integer
     */
    private $n16TotalAmbientes;

    /**
     * @var boolean
     */
    private $n21SiCieloFalso;

    /**
     * @var boolean
     */
    private $n22SiPuertas;

    /**
     * @var boolean
     */
    private $n23SiVentanas;

    /**
     * @var boolean
     */
    private $n24SiTecho;

    /**
     * @var boolean
     */
    private $n25SiMuros;

    /**
     * @var boolean
     */
    private $n26SiRevestimiento;

    /**
     * @var boolean
     */
    private $n27SiPiso;

    /**
     * @var boolean
     */
    private $n31SiGradas;

    /**
     * @var boolean
     */
    private $n33SiRampas;

    /**
     * @var boolean
     */
    private $n35SiSenaletica;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo
     */
    private $n31SenalesTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral
     */
    private $estadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo
     */
    private $n35SenalesiomaTipo2;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo
     */
    private $n35SenalesiomaTipo1;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo
     */
    private $n222AbreTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo
     */
    private $n271PisoMaterialTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo
     */
    private $n261RevestMaterialTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo
     */
    private $n252MurosCaracteristicasTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo
     */
    private $n251MurosMaterialTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo
     */
    private $n231VidriosTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo
     */
    private $n221SeguroTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n272PisoCaracteristicasTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n262RevestCaracteristicasTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n211CaracteristicasTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2PisosGradasTipo
     */
    private $n32GradasTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2PisoNroPisoTipo
     */
    private $n11NroPisoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificados
     */
    private $infraestructuraH2CaracteristicaEdificados;


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
     * Set n12AreaM2
     *
     * @param float $n12AreaM2
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN12AreaM2($n12AreaM2)
    {
        $this->n12AreaM2 = $n12AreaM2;
    
        return $this;
    }

    /**
     * Get n12AreaM2
     *
     * @return float 
     */
    public function getN12AreaM2()
    {
        return $this->n12AreaM2;
    }

    /**
     * Set n13NroAmbPedagogicos
     *
     * @param integer $n13NroAmbPedagogicos
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN13NroAmbPedagogicos($n13NroAmbPedagogicos)
    {
        $this->n13NroAmbPedagogicos = $n13NroAmbPedagogicos;
    
        return $this;
    }

    /**
     * Get n13NroAmbPedagogicos
     *
     * @return integer 
     */
    public function getN13NroAmbPedagogicos()
    {
        return $this->n13NroAmbPedagogicos;
    }

    /**
     * Set n14NroAmbNoPedagogicos
     *
     * @param integer $n14NroAmbNoPedagogicos
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN14NroAmbNoPedagogicos($n14NroAmbNoPedagogicos)
    {
        $this->n14NroAmbNoPedagogicos = $n14NroAmbNoPedagogicos;
    
        return $this;
    }

    /**
     * Get n14NroAmbNoPedagogicos
     *
     * @return integer 
     */
    public function getN14NroAmbNoPedagogicos()
    {
        return $this->n14NroAmbNoPedagogicos;
    }

    /**
     * Set n15TotalBanios
     *
     * @param integer $n15TotalBanios
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN15TotalBanios($n15TotalBanios)
    {
        $this->n15TotalBanios = $n15TotalBanios;
    
        return $this;
    }

    /**
     * Get n15TotalBanios
     *
     * @return integer 
     */
    public function getN15TotalBanios()
    {
        return $this->n15TotalBanios;
    }

    /**
     * Set n16TotalAmbientes
     *
     * @param integer $n16TotalAmbientes
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN16TotalAmbientes($n16TotalAmbientes)
    {
        $this->n16TotalAmbientes = $n16TotalAmbientes;
    
        return $this;
    }

    /**
     * Get n16TotalAmbientes
     *
     * @return integer 
     */
    public function getN16TotalAmbientes()
    {
        return $this->n16TotalAmbientes;
    }

    /**
     * Set n21SiCieloFalso
     *
     * @param boolean $n21SiCieloFalso
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN21SiCieloFalso($n21SiCieloFalso)
    {
        $this->n21SiCieloFalso = $n21SiCieloFalso;
    
        return $this;
    }

    /**
     * Get n21SiCieloFalso
     *
     * @return boolean 
     */
    public function getN21SiCieloFalso()
    {
        return $this->n21SiCieloFalso;
    }

    /**
     * Set n22SiPuertas
     *
     * @param boolean $n22SiPuertas
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN22SiPuertas($n22SiPuertas)
    {
        $this->n22SiPuertas = $n22SiPuertas;
    
        return $this;
    }

    /**
     * Get n22SiPuertas
     *
     * @return boolean 
     */
    public function getN22SiPuertas()
    {
        return $this->n22SiPuertas;
    }

    /**
     * Set n23SiVentanas
     *
     * @param boolean $n23SiVentanas
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN23SiVentanas($n23SiVentanas)
    {
        $this->n23SiVentanas = $n23SiVentanas;
    
        return $this;
    }

    /**
     * Get n23SiVentanas
     *
     * @return boolean 
     */
    public function getN23SiVentanas()
    {
        return $this->n23SiVentanas;
    }

    /**
     * Set n24SiTecho
     *
     * @param boolean $n24SiTecho
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN24SiTecho($n24SiTecho)
    {
        $this->n24SiTecho = $n24SiTecho;
    
        return $this;
    }

    /**
     * Get n24SiTecho
     *
     * @return boolean 
     */
    public function getN24SiTecho()
    {
        return $this->n24SiTecho;
    }

    /**
     * Set n25SiMuros
     *
     * @param boolean $n25SiMuros
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN25SiMuros($n25SiMuros)
    {
        $this->n25SiMuros = $n25SiMuros;
    
        return $this;
    }

    /**
     * Get n25SiMuros
     *
     * @return boolean 
     */
    public function getN25SiMuros()
    {
        return $this->n25SiMuros;
    }

    /**
     * Set n26SiRevestimiento
     *
     * @param boolean $n26SiRevestimiento
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN26SiRevestimiento($n26SiRevestimiento)
    {
        $this->n26SiRevestimiento = $n26SiRevestimiento;
    
        return $this;
    }

    /**
     * Get n26SiRevestimiento
     *
     * @return boolean 
     */
    public function getN26SiRevestimiento()
    {
        return $this->n26SiRevestimiento;
    }

    /**
     * Set n27SiPiso
     *
     * @param boolean $n27SiPiso
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN27SiPiso($n27SiPiso)
    {
        $this->n27SiPiso = $n27SiPiso;
    
        return $this;
    }

    /**
     * Get n27SiPiso
     *
     * @return boolean 
     */
    public function getN27SiPiso()
    {
        return $this->n27SiPiso;
    }

    /**
     * Set n31SiGradas
     *
     * @param boolean $n31SiGradas
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN31SiGradas($n31SiGradas)
    {
        $this->n31SiGradas = $n31SiGradas;
    
        return $this;
    }

    /**
     * Get n31SiGradas
     *
     * @return boolean 
     */
    public function getN31SiGradas()
    {
        return $this->n31SiGradas;
    }

    /**
     * Set n33SiRampas
     *
     * @param boolean $n33SiRampas
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN33SiRampas($n33SiRampas)
    {
        $this->n33SiRampas = $n33SiRampas;
    
        return $this;
    }

    /**
     * Get n33SiRampas
     *
     * @return boolean 
     */
    public function getN33SiRampas()
    {
        return $this->n33SiRampas;
    }

    /**
     * Set n35SiSenaletica
     *
     * @param boolean $n35SiSenaletica
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN35SiSenaletica($n35SiSenaletica)
    {
        $this->n35SiSenaletica = $n35SiSenaletica;
    
        return $this;
    }

    /**
     * Get n35SiSenaletica
     *
     * @return boolean 
     */
    public function getN35SiSenaletica()
    {
        return $this->n35SiSenaletica;
    }

    /**
     * Set n31SenalesTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo $n31SenalesTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN31SenalesTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo $n31SenalesTipo = null)
    {
        $this->n31SenalesTipo = $n31SenalesTipo;
    
        return $this;
    }

    /**
     * Get n31SenalesTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesTipo 
     */
    public function getN31SenalesTipo()
    {
        return $this->n31SenalesTipo;
    }

    /**
     * Set estadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral $estadoTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
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
     * Set n35SenalesiomaTipo2
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n35SenalesiomaTipo2
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN35SenalesiomaTipo2(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n35SenalesiomaTipo2 = null)
    {
        $this->n35SenalesiomaTipo2 = $n35SenalesiomaTipo2;
    
        return $this;
    }

    /**
     * Get n35SenalesiomaTipo2
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo 
     */
    public function getN35SenalesiomaTipo2()
    {
        return $this->n35SenalesiomaTipo2;
    }

    /**
     * Set n35SenalesiomaTipo1
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n35SenalesiomaTipo1
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN35SenalesiomaTipo1(\Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo $n35SenalesiomaTipo1 = null)
    {
        $this->n35SenalesiomaTipo1 = $n35SenalesiomaTipo1;
    
        return $this;
    }

    /**
     * Get n35SenalesiomaTipo1
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2SenalesIdiomaTipo 
     */
    public function getN35SenalesiomaTipo1()
    {
        return $this->n35SenalesiomaTipo1;
    }

    /**
     * Set n222AbreTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n222AbreTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN222AbreTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n222AbreTipo = null)
    {
        $this->n222AbreTipo = $n222AbreTipo;
    
        return $this;
    }

    /**
     * Get n222AbreTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo 
     */
    public function getN222AbreTipo()
    {
        return $this->n222AbreTipo;
    }

    /**
     * Set n271PisoMaterialTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n271PisoMaterialTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN271PisoMaterialTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n271PisoMaterialTipo = null)
    {
        $this->n271PisoMaterialTipo = $n271PisoMaterialTipo;
    
        return $this;
    }

    /**
     * Get n271PisoMaterialTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo 
     */
    public function getN271PisoMaterialTipo()
    {
        return $this->n271PisoMaterialTipo;
    }

    /**
     * Set n261RevestMaterialTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n261RevestMaterialTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN261RevestMaterialTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n261RevestMaterialTipo = null)
    {
        $this->n261RevestMaterialTipo = $n261RevestMaterialTipo;
    
        return $this;
    }

    /**
     * Get n261RevestMaterialTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo 
     */
    public function getN261RevestMaterialTipo()
    {
        return $this->n261RevestMaterialTipo;
    }

    /**
     * Set n252MurosCaracteristicasTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n252MurosCaracteristicasTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN252MurosCaracteristicasTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n252MurosCaracteristicasTipo = null)
    {
        $this->n252MurosCaracteristicasTipo = $n252MurosCaracteristicasTipo;
    
        return $this;
    }

    /**
     * Get n252MurosCaracteristicasTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo 
     */
    public function getN252MurosCaracteristicasTipo()
    {
        return $this->n252MurosCaracteristicasTipo;
    }

    /**
     * Set n251MurosMaterialTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n251MurosMaterialTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN251MurosMaterialTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n251MurosMaterialTipo = null)
    {
        $this->n251MurosMaterialTipo = $n251MurosMaterialTipo;
    
        return $this;
    }

    /**
     * Get n251MurosMaterialTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo 
     */
    public function getN251MurosMaterialTipo()
    {
        return $this->n251MurosMaterialTipo;
    }

    /**
     * Set n231VidriosTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n231VidriosTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN231VidriosTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n231VidriosTipo = null)
    {
        $this->n231VidriosTipo = $n231VidriosTipo;
    
        return $this;
    }

    /**
     * Get n231VidriosTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo 
     */
    public function getN231VidriosTipo()
    {
        return $this->n231VidriosTipo;
    }

    /**
     * Set n221SeguroTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n221SeguroTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN221SeguroTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n221SeguroTipo = null)
    {
        $this->n221SeguroTipo = $n221SeguroTipo;
    
        return $this;
    }

    /**
     * Get n221SeguroTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo 
     */
    public function getN221SeguroTipo()
    {
        return $this->n221SeguroTipo;
    }

    /**
     * Set n272PisoCaracteristicasTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n272PisoCaracteristicasTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN272PisoCaracteristicasTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n272PisoCaracteristicasTipo = null)
    {
        $this->n272PisoCaracteristicasTipo = $n272PisoCaracteristicasTipo;
    
        return $this;
    }

    /**
     * Get n272PisoCaracteristicasTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN272PisoCaracteristicasTipo()
    {
        return $this->n272PisoCaracteristicasTipo;
    }

    /**
     * Set n262RevestCaracteristicasTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n262RevestCaracteristicasTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN262RevestCaracteristicasTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n262RevestCaracteristicasTipo = null)
    {
        $this->n262RevestCaracteristicasTipo = $n262RevestCaracteristicasTipo;
    
        return $this;
    }

    /**
     * Get n262RevestCaracteristicasTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN262RevestCaracteristicasTipo()
    {
        return $this->n262RevestCaracteristicasTipo;
    }

    /**
     * Set n211CaracteristicasTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n211CaracteristicasTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN211CaracteristicasTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n211CaracteristicasTipo = null)
    {
        $this->n211CaracteristicasTipo = $n211CaracteristicasTipo;
    
        return $this;
    }

    /**
     * Get n211CaracteristicasTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN211CaracteristicasTipo()
    {
        return $this->n211CaracteristicasTipo;
    }

    /**
     * Set n32GradasTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2PisosGradasTipo $n32GradasTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN32GradasTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2PisosGradasTipo $n32GradasTipo = null)
    {
        $this->n32GradasTipo = $n32GradasTipo;
    
        return $this;
    }

    /**
     * Get n32GradasTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2PisosGradasTipo 
     */
    public function getN32GradasTipo()
    {
        return $this->n32GradasTipo;
    }

    /**
     * Set n11NroPisoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2PisoNroPisoTipo $n11NroPisoTipo
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setN11NroPisoTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2PisoNroPisoTipo $n11NroPisoTipo = null)
    {
        $this->n11NroPisoTipo = $n11NroPisoTipo;
    
        return $this;
    }

    /**
     * Get n11NroPisoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2PisoNroPisoTipo 
     */
    public function getN11NroPisoTipo()
    {
        return $this->n11NroPisoTipo;
    }

    /**
     * Set infraestructuraH2CaracteristicaEdificados
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificados $infraestructuraH2CaracteristicaEdificados
     * @return InfraestructuraH2CaracteristicaEdificadosPisos
     */
    public function setInfraestructuraH2CaracteristicaEdificados(\Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificados $infraestructuraH2CaracteristicaEdificados = null)
    {
        $this->infraestructuraH2CaracteristicaEdificados = $infraestructuraH2CaracteristicaEdificados;
    
        return $this;
    }

    /**
     * Get infraestructuraH2CaracteristicaEdificados
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2CaracteristicaEdificados 
     */
    public function getInfraestructuraH2CaracteristicaEdificados()
    {
        return $this->infraestructuraH2CaracteristicaEdificados;
    }
}
