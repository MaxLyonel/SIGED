<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PlanillaPagoComparativo
 */
class PlanillaPagoComparativo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $gestion;

    /**
     * @var integer
     */
    private $mes;

    /**
     * @var string
     */
    private $servicio;

    /**
     * @var string
     */
    private $programa;

    /**
     * @var string
     */
    private $item;

    /**
     * @var integer
     */
    private $c31;

    /**
     * @var string
     */
    private $carnet;

    /**
     * @var string
     */
    private $paterno;

    /**
     * @var string
     */
    private $materno;

    /**
     * @var string
     */
    private $nombre1;

    /**
     * @var string
     */
    private $nombre2;

    /**
     * @var integer
     */
    private $cargo;

    /**
     * @var integer
     */
    private $porcate;

    /**
     * @var integer
     */
    private $horapr;

    /**
     * @var integer
     */
    private $horapa;

    /**
     * @var integer
     */
    private $horas;

    /**
     * @var integer
     */
    private $diastrab;

    /**
     * @var integer
     */
    private $afp;

    /**
     * @var integer
     */
    private $tipoProceso;

    /**
     * @var integer
     */
    private $pagado;

    /**
     * @var string
     */
    private $subsidio;

    /**
     * @var integer
     */
    private $acreedor;

    /**
     * @var string
     */
    private $reintegro;

    /**
     * @var string
     */
    private $saldoIva;

    /**
     * @var string
     */
    private $basico;

    /**
     * @var string
     */
    private $catego;

    /**
     * @var string
     */
    private $acumulo;

    /**
     * @var string
     */
    private $reintegros;

    /**
     * @var string
     */
    private $bonojer;

    /**
     * @var string
     */
    private $bonozona;

    /**
     * @var string
     */
    private $bonofron;

    /**
     * @var string
     */
    private $ingres3;

    /**
     * @var string
     */
    private $ingres5;

    /**
     * @var string
     */
    private $reinSub;

    /**
     * @var string
     */
    private $subsidios;

    /**
     * @var string
     */
    private $sumahab;

    /**
     * @var string
     */
    private $fonviInv;

    /**
     * @var string
     */
    private $renta;

    /**
     * @var string
     */
    private $adap;

    /**
     * @var string
     */
    private $multasFal;

    /**
     * @var string
     */
    private $descue10;

    /**
     * @var string
     */
    private $cob;

    /**
     * @var string
     */
    private $desSubsi;

    /**
     * @var string
     */
    private $otrosdesc;

    /**
     * @var string
     */
    private $afp10;

    /**
     * @var string
     */
    private $afp02;

    /**
     * @var string
     */
    private $afp005;

    /**
     * @var string
     */
    private $p13110;

    /**
     * @var string
     */
    private $p13120;

    /**
     * @var string
     */
    private $p13200;

    /**
     * @var string
     */
    private $sumades;

    /**
     * @var string
     */
    private $totcheque;

    /**
     * @var string
     */
    private $mmaachq;

    /**
     * @var string
     */
    private $nrochq;

    /**
     * @var integer
     */
    private $digchq;

    /**
     * @var integer
     */
    private $codDep;

    /**
     * @var integer
     */
    private $codObs;

    /**
     * @var string
     */
    private $codUe;

    /**
     * @var integer
     */
    private $codRda;

    /**
     * @var integer
     */
    private $codDis;

    /**
     * @var integer
     */
    private $niv;

    /**
     * @var integer
     */
    private $carnet2;

    /**
     * @var integer
     */
    private $carnetInteger;

    /**
     * @var string
     */
    private $ciPla;

    /**
     * @var string
     */
    private $compPla;

    /**
     * @var integer
     */
    private $estadoComparacionPlanillaTipoId;

    /**
     * @var integer
     */
    private $codUePlanilla;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var integer
     */
    private $codFunc;


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
     * Set gestion
     *
     * @param integer $gestion
     * @return PlanillaPagoComparativo
     */
    public function setGestion($gestion)
    {
        $this->gestion = $gestion;
    
        return $this;
    }

    /**
     * Get gestion
     *
     * @return integer 
     */
    public function getGestion()
    {
        return $this->gestion;
    }

    /**
     * Set mes
     *
     * @param integer $mes
     * @return PlanillaPagoComparativo
     */
    public function setMes($mes)
    {
        $this->mes = $mes;
    
        return $this;
    }

    /**
     * Get mes
     *
     * @return integer 
     */
    public function getMes()
    {
        return $this->mes;
    }

    /**
     * Set servicio
     *
     * @param string $servicio
     * @return PlanillaPagoComparativo
     */
    public function setServicio($servicio)
    {
        $this->servicio = $servicio;
    
        return $this;
    }

    /**
     * Get servicio
     *
     * @return string 
     */
    public function getServicio()
    {
        return $this->servicio;
    }

    /**
     * Set programa
     *
     * @param string $programa
     * @return PlanillaPagoComparativo
     */
    public function setPrograma($programa)
    {
        $this->programa = $programa;
    
        return $this;
    }

    /**
     * Get programa
     *
     * @return string 
     */
    public function getPrograma()
    {
        return $this->programa;
    }

    /**
     * Set item
     *
     * @param string $item
     * @return PlanillaPagoComparativo
     */
    public function setItem($item)
    {
        $this->item = $item;
    
        return $this;
    }

    /**
     * Get item
     *
     * @return string 
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set c31
     *
     * @param integer $c31
     * @return PlanillaPagoComparativo
     */
    public function setC31($c31)
    {
        $this->c31 = $c31;
    
        return $this;
    }

    /**
     * Get c31
     *
     * @return integer 
     */
    public function getC31()
    {
        return $this->c31;
    }

    /**
     * Set carnet
     *
     * @param string $carnet
     * @return PlanillaPagoComparativo
     */
    public function setCarnet($carnet)
    {
        $this->carnet = $carnet;
    
        return $this;
    }

    /**
     * Get carnet
     *
     * @return string 
     */
    public function getCarnet()
    {
        return $this->carnet;
    }

    /**
     * Set paterno
     *
     * @param string $paterno
     * @return PlanillaPagoComparativo
     */
    public function setPaterno($paterno)
    {
        $this->paterno = $paterno;
    
        return $this;
    }

    /**
     * Get paterno
     *
     * @return string 
     */
    public function getPaterno()
    {
        return $this->paterno;
    }

    /**
     * Set materno
     *
     * @param string $materno
     * @return PlanillaPagoComparativo
     */
    public function setMaterno($materno)
    {
        $this->materno = $materno;
    
        return $this;
    }

    /**
     * Get materno
     *
     * @return string 
     */
    public function getMaterno()
    {
        return $this->materno;
    }

    /**
     * Set nombre1
     *
     * @param string $nombre1
     * @return PlanillaPagoComparativo
     */
    public function setNombre1($nombre1)
    {
        $this->nombre1 = $nombre1;
    
        return $this;
    }

    /**
     * Get nombre1
     *
     * @return string 
     */
    public function getNombre1()
    {
        return $this->nombre1;
    }

    /**
     * Set nombre2
     *
     * @param string $nombre2
     * @return PlanillaPagoComparativo
     */
    public function setNombre2($nombre2)
    {
        $this->nombre2 = $nombre2;
    
        return $this;
    }

    /**
     * Get nombre2
     *
     * @return string 
     */
    public function getNombre2()
    {
        return $this->nombre2;
    }

    /**
     * Set cargo
     *
     * @param integer $cargo
     * @return PlanillaPagoComparativo
     */
    public function setCargo($cargo)
    {
        $this->cargo = $cargo;
    
        return $this;
    }

    /**
     * Get cargo
     *
     * @return integer 
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    /**
     * Set porcate
     *
     * @param integer $porcate
     * @return PlanillaPagoComparativo
     */
    public function setPorcate($porcate)
    {
        $this->porcate = $porcate;
    
        return $this;
    }

    /**
     * Get porcate
     *
     * @return integer 
     */
    public function getPorcate()
    {
        return $this->porcate;
    }

    /**
     * Set horapr
     *
     * @param integer $horapr
     * @return PlanillaPagoComparativo
     */
    public function setHorapr($horapr)
    {
        $this->horapr = $horapr;
    
        return $this;
    }

    /**
     * Get horapr
     *
     * @return integer 
     */
    public function getHorapr()
    {
        return $this->horapr;
    }

    /**
     * Set horapa
     *
     * @param integer $horapa
     * @return PlanillaPagoComparativo
     */
    public function setHorapa($horapa)
    {
        $this->horapa = $horapa;
    
        return $this;
    }

    /**
     * Get horapa
     *
     * @return integer 
     */
    public function getHorapa()
    {
        return $this->horapa;
    }

    /**
     * Set horas
     *
     * @param integer $horas
     * @return PlanillaPagoComparativo
     */
    public function setHoras($horas)
    {
        $this->horas = $horas;
    
        return $this;
    }

    /**
     * Get horas
     *
     * @return integer 
     */
    public function getHoras()
    {
        return $this->horas;
    }

    /**
     * Set diastrab
     *
     * @param integer $diastrab
     * @return PlanillaPagoComparativo
     */
    public function setDiastrab($diastrab)
    {
        $this->diastrab = $diastrab;
    
        return $this;
    }

    /**
     * Get diastrab
     *
     * @return integer 
     */
    public function getDiastrab()
    {
        return $this->diastrab;
    }

    /**
     * Set afp
     *
     * @param integer $afp
     * @return PlanillaPagoComparativo
     */
    public function setAfp($afp)
    {
        $this->afp = $afp;
    
        return $this;
    }

    /**
     * Get afp
     *
     * @return integer 
     */
    public function getAfp()
    {
        return $this->afp;
    }

    /**
     * Set tipoProceso
     *
     * @param integer $tipoProceso
     * @return PlanillaPagoComparativo
     */
    public function setTipoProceso($tipoProceso)
    {
        $this->tipoProceso = $tipoProceso;
    
        return $this;
    }

    /**
     * Get tipoProceso
     *
     * @return integer 
     */
    public function getTipoProceso()
    {
        return $this->tipoProceso;
    }

    /**
     * Set pagado
     *
     * @param integer $pagado
     * @return PlanillaPagoComparativo
     */
    public function setPagado($pagado)
    {
        $this->pagado = $pagado;
    
        return $this;
    }

    /**
     * Get pagado
     *
     * @return integer 
     */
    public function getPagado()
    {
        return $this->pagado;
    }

    /**
     * Set subsidio
     *
     * @param string $subsidio
     * @return PlanillaPagoComparativo
     */
    public function setSubsidio($subsidio)
    {
        $this->subsidio = $subsidio;
    
        return $this;
    }

    /**
     * Get subsidio
     *
     * @return string 
     */
    public function getSubsidio()
    {
        return $this->subsidio;
    }

    /**
     * Set acreedor
     *
     * @param integer $acreedor
     * @return PlanillaPagoComparativo
     */
    public function setAcreedor($acreedor)
    {
        $this->acreedor = $acreedor;
    
        return $this;
    }

    /**
     * Get acreedor
     *
     * @return integer 
     */
    public function getAcreedor()
    {
        return $this->acreedor;
    }

    /**
     * Set reintegro
     *
     * @param string $reintegro
     * @return PlanillaPagoComparativo
     */
    public function setReintegro($reintegro)
    {
        $this->reintegro = $reintegro;
    
        return $this;
    }

    /**
     * Get reintegro
     *
     * @return string 
     */
    public function getReintegro()
    {
        return $this->reintegro;
    }

    /**
     * Set saldoIva
     *
     * @param string $saldoIva
     * @return PlanillaPagoComparativo
     */
    public function setSaldoIva($saldoIva)
    {
        $this->saldoIva = $saldoIva;
    
        return $this;
    }

    /**
     * Get saldoIva
     *
     * @return string 
     */
    public function getSaldoIva()
    {
        return $this->saldoIva;
    }

    /**
     * Set basico
     *
     * @param string $basico
     * @return PlanillaPagoComparativo
     */
    public function setBasico($basico)
    {
        $this->basico = $basico;
    
        return $this;
    }

    /**
     * Get basico
     *
     * @return string 
     */
    public function getBasico()
    {
        return $this->basico;
    }

    /**
     * Set catego
     *
     * @param string $catego
     * @return PlanillaPagoComparativo
     */
    public function setCatego($catego)
    {
        $this->catego = $catego;
    
        return $this;
    }

    /**
     * Get catego
     *
     * @return string 
     */
    public function getCatego()
    {
        return $this->catego;
    }

    /**
     * Set acumulo
     *
     * @param string $acumulo
     * @return PlanillaPagoComparativo
     */
    public function setAcumulo($acumulo)
    {
        $this->acumulo = $acumulo;
    
        return $this;
    }

    /**
     * Get acumulo
     *
     * @return string 
     */
    public function getAcumulo()
    {
        return $this->acumulo;
    }

    /**
     * Set reintegros
     *
     * @param string $reintegros
     * @return PlanillaPagoComparativo
     */
    public function setReintegros($reintegros)
    {
        $this->reintegros = $reintegros;
    
        return $this;
    }

    /**
     * Get reintegros
     *
     * @return string 
     */
    public function getReintegros()
    {
        return $this->reintegros;
    }

    /**
     * Set bonojer
     *
     * @param string $bonojer
     * @return PlanillaPagoComparativo
     */
    public function setBonojer($bonojer)
    {
        $this->bonojer = $bonojer;
    
        return $this;
    }

    /**
     * Get bonojer
     *
     * @return string 
     */
    public function getBonojer()
    {
        return $this->bonojer;
    }

    /**
     * Set bonozona
     *
     * @param string $bonozona
     * @return PlanillaPagoComparativo
     */
    public function setBonozona($bonozona)
    {
        $this->bonozona = $bonozona;
    
        return $this;
    }

    /**
     * Get bonozona
     *
     * @return string 
     */
    public function getBonozona()
    {
        return $this->bonozona;
    }

    /**
     * Set bonofron
     *
     * @param string $bonofron
     * @return PlanillaPagoComparativo
     */
    public function setBonofron($bonofron)
    {
        $this->bonofron = $bonofron;
    
        return $this;
    }

    /**
     * Get bonofron
     *
     * @return string 
     */
    public function getBonofron()
    {
        return $this->bonofron;
    }

    /**
     * Set ingres3
     *
     * @param string $ingres3
     * @return PlanillaPagoComparativo
     */
    public function setIngres3($ingres3)
    {
        $this->ingres3 = $ingres3;
    
        return $this;
    }

    /**
     * Get ingres3
     *
     * @return string 
     */
    public function getIngres3()
    {
        return $this->ingres3;
    }

    /**
     * Set ingres5
     *
     * @param string $ingres5
     * @return PlanillaPagoComparativo
     */
    public function setIngres5($ingres5)
    {
        $this->ingres5 = $ingres5;
    
        return $this;
    }

    /**
     * Get ingres5
     *
     * @return string 
     */
    public function getIngres5()
    {
        return $this->ingres5;
    }

    /**
     * Set reinSub
     *
     * @param string $reinSub
     * @return PlanillaPagoComparativo
     */
    public function setReinSub($reinSub)
    {
        $this->reinSub = $reinSub;
    
        return $this;
    }

    /**
     * Get reinSub
     *
     * @return string 
     */
    public function getReinSub()
    {
        return $this->reinSub;
    }

    /**
     * Set subsidios
     *
     * @param string $subsidios
     * @return PlanillaPagoComparativo
     */
    public function setSubsidios($subsidios)
    {
        $this->subsidios = $subsidios;
    
        return $this;
    }

    /**
     * Get subsidios
     *
     * @return string 
     */
    public function getSubsidios()
    {
        return $this->subsidios;
    }

    /**
     * Set sumahab
     *
     * @param string $sumahab
     * @return PlanillaPagoComparativo
     */
    public function setSumahab($sumahab)
    {
        $this->sumahab = $sumahab;
    
        return $this;
    }

    /**
     * Get sumahab
     *
     * @return string 
     */
    public function getSumahab()
    {
        return $this->sumahab;
    }

    /**
     * Set fonviInv
     *
     * @param string $fonviInv
     * @return PlanillaPagoComparativo
     */
    public function setFonviInv($fonviInv)
    {
        $this->fonviInv = $fonviInv;
    
        return $this;
    }

    /**
     * Get fonviInv
     *
     * @return string 
     */
    public function getFonviInv()
    {
        return $this->fonviInv;
    }

    /**
     * Set renta
     *
     * @param string $renta
     * @return PlanillaPagoComparativo
     */
    public function setRenta($renta)
    {
        $this->renta = $renta;
    
        return $this;
    }

    /**
     * Get renta
     *
     * @return string 
     */
    public function getRenta()
    {
        return $this->renta;
    }

    /**
     * Set adap
     *
     * @param string $adap
     * @return PlanillaPagoComparativo
     */
    public function setAdap($adap)
    {
        $this->adap = $adap;
    
        return $this;
    }

    /**
     * Get adap
     *
     * @return string 
     */
    public function getAdap()
    {
        return $this->adap;
    }

    /**
     * Set multasFal
     *
     * @param string $multasFal
     * @return PlanillaPagoComparativo
     */
    public function setMultasFal($multasFal)
    {
        $this->multasFal = $multasFal;
    
        return $this;
    }

    /**
     * Get multasFal
     *
     * @return string 
     */
    public function getMultasFal()
    {
        return $this->multasFal;
    }

    /**
     * Set descue10
     *
     * @param string $descue10
     * @return PlanillaPagoComparativo
     */
    public function setDescue10($descue10)
    {
        $this->descue10 = $descue10;
    
        return $this;
    }

    /**
     * Get descue10
     *
     * @return string 
     */
    public function getDescue10()
    {
        return $this->descue10;
    }

    /**
     * Set cob
     *
     * @param string $cob
     * @return PlanillaPagoComparativo
     */
    public function setCob($cob)
    {
        $this->cob = $cob;
    
        return $this;
    }

    /**
     * Get cob
     *
     * @return string 
     */
    public function getCob()
    {
        return $this->cob;
    }

    /**
     * Set desSubsi
     *
     * @param string $desSubsi
     * @return PlanillaPagoComparativo
     */
    public function setDesSubsi($desSubsi)
    {
        $this->desSubsi = $desSubsi;
    
        return $this;
    }

    /**
     * Get desSubsi
     *
     * @return string 
     */
    public function getDesSubsi()
    {
        return $this->desSubsi;
    }

    /**
     * Set otrosdesc
     *
     * @param string $otrosdesc
     * @return PlanillaPagoComparativo
     */
    public function setOtrosdesc($otrosdesc)
    {
        $this->otrosdesc = $otrosdesc;
    
        return $this;
    }

    /**
     * Get otrosdesc
     *
     * @return string 
     */
    public function getOtrosdesc()
    {
        return $this->otrosdesc;
    }

    /**
     * Set afp10
     *
     * @param string $afp10
     * @return PlanillaPagoComparativo
     */
    public function setAfp10($afp10)
    {
        $this->afp10 = $afp10;
    
        return $this;
    }

    /**
     * Get afp10
     *
     * @return string 
     */
    public function getAfp10()
    {
        return $this->afp10;
    }

    /**
     * Set afp02
     *
     * @param string $afp02
     * @return PlanillaPagoComparativo
     */
    public function setAfp02($afp02)
    {
        $this->afp02 = $afp02;
    
        return $this;
    }

    /**
     * Get afp02
     *
     * @return string 
     */
    public function getAfp02()
    {
        return $this->afp02;
    }

    /**
     * Set afp005
     *
     * @param string $afp005
     * @return PlanillaPagoComparativo
     */
    public function setAfp005($afp005)
    {
        $this->afp005 = $afp005;
    
        return $this;
    }

    /**
     * Get afp005
     *
     * @return string 
     */
    public function getAfp005()
    {
        return $this->afp005;
    }

    /**
     * Set p13110
     *
     * @param string $p13110
     * @return PlanillaPagoComparativo
     */
    public function setP13110($p13110)
    {
        $this->p13110 = $p13110;
    
        return $this;
    }

    /**
     * Get p13110
     *
     * @return string 
     */
    public function getP13110()
    {
        return $this->p13110;
    }

    /**
     * Set p13120
     *
     * @param string $p13120
     * @return PlanillaPagoComparativo
     */
    public function setP13120($p13120)
    {
        $this->p13120 = $p13120;
    
        return $this;
    }

    /**
     * Get p13120
     *
     * @return string 
     */
    public function getP13120()
    {
        return $this->p13120;
    }

    /**
     * Set p13200
     *
     * @param string $p13200
     * @return PlanillaPagoComparativo
     */
    public function setP13200($p13200)
    {
        $this->p13200 = $p13200;
    
        return $this;
    }

    /**
     * Get p13200
     *
     * @return string 
     */
    public function getP13200()
    {
        return $this->p13200;
    }

    /**
     * Set sumades
     *
     * @param string $sumades
     * @return PlanillaPagoComparativo
     */
    public function setSumades($sumades)
    {
        $this->sumades = $sumades;
    
        return $this;
    }

    /**
     * Get sumades
     *
     * @return string 
     */
    public function getSumades()
    {
        return $this->sumades;
    }

    /**
     * Set totcheque
     *
     * @param string $totcheque
     * @return PlanillaPagoComparativo
     */
    public function setTotcheque($totcheque)
    {
        $this->totcheque = $totcheque;
    
        return $this;
    }

    /**
     * Get totcheque
     *
     * @return string 
     */
    public function getTotcheque()
    {
        return $this->totcheque;
    }

    /**
     * Set mmaachq
     *
     * @param string $mmaachq
     * @return PlanillaPagoComparativo
     */
    public function setMmaachq($mmaachq)
    {
        $this->mmaachq = $mmaachq;
    
        return $this;
    }

    /**
     * Get mmaachq
     *
     * @return string 
     */
    public function getMmaachq()
    {
        return $this->mmaachq;
    }

    /**
     * Set nrochq
     *
     * @param string $nrochq
     * @return PlanillaPagoComparativo
     */
    public function setNrochq($nrochq)
    {
        $this->nrochq = $nrochq;
    
        return $this;
    }

    /**
     * Get nrochq
     *
     * @return string 
     */
    public function getNrochq()
    {
        return $this->nrochq;
    }

    /**
     * Set digchq
     *
     * @param integer $digchq
     * @return PlanillaPagoComparativo
     */
    public function setDigchq($digchq)
    {
        $this->digchq = $digchq;
    
        return $this;
    }

    /**
     * Get digchq
     *
     * @return integer 
     */
    public function getDigchq()
    {
        return $this->digchq;
    }

    /**
     * Set codDep
     *
     * @param integer $codDep
     * @return PlanillaPagoComparativo
     */
    public function setCodDep($codDep)
    {
        $this->codDep = $codDep;
    
        return $this;
    }

    /**
     * Get codDep
     *
     * @return integer 
     */
    public function getCodDep()
    {
        return $this->codDep;
    }

    /**
     * Set codObs
     *
     * @param integer $codObs
     * @return PlanillaPagoComparativo
     */
    public function setCodObs($codObs)
    {
        $this->codObs = $codObs;
    
        return $this;
    }

    /**
     * Get codObs
     *
     * @return integer 
     */
    public function getCodObs()
    {
        return $this->codObs;
    }

    /**
     * Set codUe
     *
     * @param string $codUe
     * @return PlanillaPagoComparativo
     */
    public function setCodUe($codUe)
    {
        $this->codUe = $codUe;
    
        return $this;
    }

    /**
     * Get codUe
     *
     * @return string 
     */
    public function getCodUe()
    {
        return $this->codUe;
    }

    /**
     * Set codRda
     *
     * @param integer $codRda
     * @return PlanillaPagoComparativo
     */
    public function setCodRda($codRda)
    {
        $this->codRda = $codRda;
    
        return $this;
    }

    /**
     * Get codRda
     *
     * @return integer 
     */
    public function getCodRda()
    {
        return $this->codRda;
    }

    /**
     * Set codDis
     *
     * @param integer $codDis
     * @return PlanillaPagoComparativo
     */
    public function setCodDis($codDis)
    {
        $this->codDis = $codDis;
    
        return $this;
    }

    /**
     * Get codDis
     *
     * @return integer 
     */
    public function getCodDis()
    {
        return $this->codDis;
    }

    /**
     * Set niv
     *
     * @param integer $niv
     * @return PlanillaPagoComparativo
     */
    public function setNiv($niv)
    {
        $this->niv = $niv;
    
        return $this;
    }

    /**
     * Get niv
     *
     * @return integer 
     */
    public function getNiv()
    {
        return $this->niv;
    }

    /**
     * Set carnet2
     *
     * @param integer $carnet2
     * @return PlanillaPagoComparativo
     */
    public function setCarnet2($carnet2)
    {
        $this->carnet2 = $carnet2;
    
        return $this;
    }

    /**
     * Get carnet2
     *
     * @return integer 
     */
    public function getCarnet2()
    {
        return $this->carnet2;
    }

    /**
     * Set carnetInteger
     *
     * @param integer $carnetInteger
     * @return PlanillaPagoComparativo
     */
    public function setCarnetInteger($carnetInteger)
    {
        $this->carnetInteger = $carnetInteger;
    
        return $this;
    }

    /**
     * Get carnetInteger
     *
     * @return integer 
     */
    public function getCarnetInteger()
    {
        return $this->carnetInteger;
    }

    /**
     * Set ciPla
     *
     * @param string $ciPla
     * @return PlanillaPagoComparativo
     */
    public function setCiPla($ciPla)
    {
        $this->ciPla = $ciPla;
    
        return $this;
    }

    /**
     * Get ciPla
     *
     * @return string 
     */
    public function getCiPla()
    {
        return $this->ciPla;
    }

    /**
     * Set compPla
     *
     * @param string $compPla
     * @return PlanillaPagoComparativo
     */
    public function setCompPla($compPla)
    {
        $this->compPla = $compPla;
    
        return $this;
    }

    /**
     * Get compPla
     *
     * @return string 
     */
    public function getCompPla()
    {
        return $this->compPla;
    }

    /**
     * Set estadoComparacionPlanillaTipoId
     *
     * @param integer $estadoComparacionPlanillaTipoId
     * @return PlanillaPagoComparativo
     */
    public function setEstadoComparacionPlanillaTipoId($estadoComparacionPlanillaTipoId)
    {
        $this->estadoComparacionPlanillaTipoId = $estadoComparacionPlanillaTipoId;
    
        return $this;
    }

    /**
     * Get estadoComparacionPlanillaTipoId
     *
     * @return integer 
     */
    public function getEstadoComparacionPlanillaTipoId()
    {
        return $this->estadoComparacionPlanillaTipoId;
    }

    /**
     * Set codUePlanilla
     *
     * @param integer $codUePlanilla
     * @return PlanillaPagoComparativo
     */
    public function setCodUePlanilla($codUePlanilla)
    {
        $this->codUePlanilla = $codUePlanilla;
    
        return $this;
    }

    /**
     * Get codUePlanilla
     *
     * @return integer 
     */
    public function getCodUePlanilla()
    {
        return $this->codUePlanilla;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return PlanillaPagoComparativo
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set codFunc
     *
     * @param integer $codFunc
     * @return PlanillaPagoComparativo
     */
    public function setCodFunc($codFunc)
    {
        $this->codFunc = $codFunc;
    
        return $this;
    }

    /**
     * Get codFunc
     *
     * @return integer 
     */
    public function getCodFunc()
    {
        return $this->codFunc;
    }
}
