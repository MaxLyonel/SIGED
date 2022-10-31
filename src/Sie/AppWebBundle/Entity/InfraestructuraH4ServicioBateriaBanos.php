<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH4ServicioBateriaBanos
 */
class InfraestructuraH4ServicioBateriaBanos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $n5Areatotalm2;

    /**
     * @var integer
     */
    private $n5BanioConagua;

    /**
     * @var integer
     */
    private $n5BanioSinagua;

    /**
     * @var integer
     */
    private $n5LetrinaFunciona;

    /**
     * @var integer
     */
    private $n5LetrinaNofunciona;

    /**
     * @var integer
     */
    private $n5InodoroFunciona;

    /**
     * @var integer
     */
    private $n5InodoroNofunciona;

    /**
     * @var integer
     */
    private $n5UrinarioFunciona;

    /**
     * @var integer
     */
    private $n5UrinarioNofunciona;

    /**
     * @var integer
     */
    private $n5LavamanoFunciona;

    /**
     * @var integer
     */
    private $n5LavamanoNofunciona;

    /**
     * @var integer
     */
    private $n5DuchaFunciona;

    /**
     * @var integer
     */
    private $n5DuchaNofunciona;

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
    private $n5EsFuncionaAmbiente;

    /**
     * @var boolean
     */
    private $n52EsTieneTecho;

    /**
     * @var boolean
     */
    private $n52EsTieneCieloFalso;

    /**
     * @var boolean
     */
    private $n52EsTienePuerta;

    /**
     * @var boolean
     */
    private $n52EsTieneVentana;

    /**
     * @var boolean
     */
    private $n52EsTieneMuros;

    /**
     * @var boolean
     */
    private $n52EsTieneRevest;

    /**
     * @var boolean
     */
    private $n52EsTienePiso;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral
     */
    private $estadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo
     */
    private $n522AbreTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo
     */
    private $n52TienePisoMaterTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo
     */
    private $n52TieneRevestMaterTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo
     */
    private $n52TieneMurosCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo
     */
    private $n52TieneMurosMaterTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo
     */
    private $n52TieneVentanaCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo
     */
    private $n521SeguroTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n52TienePisoCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n52TieneRevestCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo
     */
    private $n52TieneCieloFalsoCaracTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n5CieloEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n5PisoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n5TechoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n5ParedEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH4BanoTipo
     */
    private $n5AmbienteBanoTipo;

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
     * Set n5Areatotalm2
     *
     * @param string $n5Areatotalm2
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5Areatotalm2($n5Areatotalm2)
    {
        $this->n5Areatotalm2 = $n5Areatotalm2;
    
        return $this;
    }

    /**
     * Get n5Areatotalm2
     *
     * @return string 
     */
    public function getN5Areatotalm2()
    {
        return $this->n5Areatotalm2;
    }

    /**
     * Set n5BanioConagua
     *
     * @param integer $n5BanioConagua
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5BanioConagua($n5BanioConagua)
    {
        $this->n5BanioConagua = $n5BanioConagua;
    
        return $this;
    }

    /**
     * Get n5BanioConagua
     *
     * @return integer 
     */
    public function getN5BanioConagua()
    {
        return $this->n5BanioConagua;
    }

    /**
     * Set n5BanioSinagua
     *
     * @param integer $n5BanioSinagua
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5BanioSinagua($n5BanioSinagua)
    {
        $this->n5BanioSinagua = $n5BanioSinagua;
    
        return $this;
    }

    /**
     * Get n5BanioSinagua
     *
     * @return integer 
     */
    public function getN5BanioSinagua()
    {
        return $this->n5BanioSinagua;
    }

    /**
     * Set n5LetrinaFunciona
     *
     * @param integer $n5LetrinaFunciona
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5LetrinaFunciona($n5LetrinaFunciona)
    {
        $this->n5LetrinaFunciona = $n5LetrinaFunciona;
    
        return $this;
    }

    /**
     * Get n5LetrinaFunciona
     *
     * @return integer 
     */
    public function getN5LetrinaFunciona()
    {
        return $this->n5LetrinaFunciona;
    }

    /**
     * Set n5LetrinaNofunciona
     *
     * @param integer $n5LetrinaNofunciona
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5LetrinaNofunciona($n5LetrinaNofunciona)
    {
        $this->n5LetrinaNofunciona = $n5LetrinaNofunciona;
    
        return $this;
    }

    /**
     * Get n5LetrinaNofunciona
     *
     * @return integer 
     */
    public function getN5LetrinaNofunciona()
    {
        return $this->n5LetrinaNofunciona;
    }

    /**
     * Set n5InodoroFunciona
     *
     * @param integer $n5InodoroFunciona
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5InodoroFunciona($n5InodoroFunciona)
    {
        $this->n5InodoroFunciona = $n5InodoroFunciona;
    
        return $this;
    }

    /**
     * Get n5InodoroFunciona
     *
     * @return integer 
     */
    public function getN5InodoroFunciona()
    {
        return $this->n5InodoroFunciona;
    }

    /**
     * Set n5InodoroNofunciona
     *
     * @param integer $n5InodoroNofunciona
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5InodoroNofunciona($n5InodoroNofunciona)
    {
        $this->n5InodoroNofunciona = $n5InodoroNofunciona;
    
        return $this;
    }

    /**
     * Get n5InodoroNofunciona
     *
     * @return integer 
     */
    public function getN5InodoroNofunciona()
    {
        return $this->n5InodoroNofunciona;
    }

    /**
     * Set n5UrinarioFunciona
     *
     * @param integer $n5UrinarioFunciona
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5UrinarioFunciona($n5UrinarioFunciona)
    {
        $this->n5UrinarioFunciona = $n5UrinarioFunciona;
    
        return $this;
    }

    /**
     * Get n5UrinarioFunciona
     *
     * @return integer 
     */
    public function getN5UrinarioFunciona()
    {
        return $this->n5UrinarioFunciona;
    }

    /**
     * Set n5UrinarioNofunciona
     *
     * @param integer $n5UrinarioNofunciona
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5UrinarioNofunciona($n5UrinarioNofunciona)
    {
        $this->n5UrinarioNofunciona = $n5UrinarioNofunciona;
    
        return $this;
    }

    /**
     * Get n5UrinarioNofunciona
     *
     * @return integer 
     */
    public function getN5UrinarioNofunciona()
    {
        return $this->n5UrinarioNofunciona;
    }

    /**
     * Set n5LavamanoFunciona
     *
     * @param integer $n5LavamanoFunciona
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5LavamanoFunciona($n5LavamanoFunciona)
    {
        $this->n5LavamanoFunciona = $n5LavamanoFunciona;
    
        return $this;
    }

    /**
     * Get n5LavamanoFunciona
     *
     * @return integer 
     */
    public function getN5LavamanoFunciona()
    {
        return $this->n5LavamanoFunciona;
    }

    /**
     * Set n5LavamanoNofunciona
     *
     * @param integer $n5LavamanoNofunciona
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5LavamanoNofunciona($n5LavamanoNofunciona)
    {
        $this->n5LavamanoNofunciona = $n5LavamanoNofunciona;
    
        return $this;
    }

    /**
     * Get n5LavamanoNofunciona
     *
     * @return integer 
     */
    public function getN5LavamanoNofunciona()
    {
        return $this->n5LavamanoNofunciona;
    }

    /**
     * Set n5DuchaFunciona
     *
     * @param integer $n5DuchaFunciona
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5DuchaFunciona($n5DuchaFunciona)
    {
        $this->n5DuchaFunciona = $n5DuchaFunciona;
    
        return $this;
    }

    /**
     * Get n5DuchaFunciona
     *
     * @return integer 
     */
    public function getN5DuchaFunciona()
    {
        return $this->n5DuchaFunciona;
    }

    /**
     * Set n5DuchaNofunciona
     *
     * @param integer $n5DuchaNofunciona
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5DuchaNofunciona($n5DuchaNofunciona)
    {
        $this->n5DuchaNofunciona = $n5DuchaNofunciona;
    
        return $this;
    }

    /**
     * Get n5DuchaNofunciona
     *
     * @return integer 
     */
    public function getN5DuchaNofunciona()
    {
        return $this->n5DuchaNofunciona;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH4ServicioBateriaBanos
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
     * @return InfraestructuraH4ServicioBateriaBanos
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
     * Set n5EsFuncionaAmbiente
     *
     * @param boolean $n5EsFuncionaAmbiente
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5EsFuncionaAmbiente($n5EsFuncionaAmbiente)
    {
        $this->n5EsFuncionaAmbiente = $n5EsFuncionaAmbiente;
    
        return $this;
    }

    /**
     * Get n5EsFuncionaAmbiente
     *
     * @return boolean 
     */
    public function getN5EsFuncionaAmbiente()
    {
        return $this->n5EsFuncionaAmbiente;
    }

    /**
     * Set n52EsTieneTecho
     *
     * @param boolean $n52EsTieneTecho
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52EsTieneTecho($n52EsTieneTecho)
    {
        $this->n52EsTieneTecho = $n52EsTieneTecho;
    
        return $this;
    }

    /**
     * Get n52EsTieneTecho
     *
     * @return boolean 
     */
    public function getN52EsTieneTecho()
    {
        return $this->n52EsTieneTecho;
    }

    /**
     * Set n52EsTieneCieloFalso
     *
     * @param boolean $n52EsTieneCieloFalso
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52EsTieneCieloFalso($n52EsTieneCieloFalso)
    {
        $this->n52EsTieneCieloFalso = $n52EsTieneCieloFalso;
    
        return $this;
    }

    /**
     * Get n52EsTieneCieloFalso
     *
     * @return boolean 
     */
    public function getN52EsTieneCieloFalso()
    {
        return $this->n52EsTieneCieloFalso;
    }

    /**
     * Set n52EsTienePuerta
     *
     * @param boolean $n52EsTienePuerta
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52EsTienePuerta($n52EsTienePuerta)
    {
        $this->n52EsTienePuerta = $n52EsTienePuerta;
    
        return $this;
    }

    /**
     * Get n52EsTienePuerta
     *
     * @return boolean 
     */
    public function getN52EsTienePuerta()
    {
        return $this->n52EsTienePuerta;
    }

    /**
     * Set n52EsTieneVentana
     *
     * @param boolean $n52EsTieneVentana
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52EsTieneVentana($n52EsTieneVentana)
    {
        $this->n52EsTieneVentana = $n52EsTieneVentana;
    
        return $this;
    }

    /**
     * Get n52EsTieneVentana
     *
     * @return boolean 
     */
    public function getN52EsTieneVentana()
    {
        return $this->n52EsTieneVentana;
    }

    /**
     * Set n52EsTieneMuros
     *
     * @param boolean $n52EsTieneMuros
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52EsTieneMuros($n52EsTieneMuros)
    {
        $this->n52EsTieneMuros = $n52EsTieneMuros;
    
        return $this;
    }

    /**
     * Get n52EsTieneMuros
     *
     * @return boolean 
     */
    public function getN52EsTieneMuros()
    {
        return $this->n52EsTieneMuros;
    }

    /**
     * Set n52EsTieneRevest
     *
     * @param boolean $n52EsTieneRevest
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52EsTieneRevest($n52EsTieneRevest)
    {
        $this->n52EsTieneRevest = $n52EsTieneRevest;
    
        return $this;
    }

    /**
     * Get n52EsTieneRevest
     *
     * @return boolean 
     */
    public function getN52EsTieneRevest()
    {
        return $this->n52EsTieneRevest;
    }

    /**
     * Set n52EsTienePiso
     *
     * @param boolean $n52EsTienePiso
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52EsTienePiso($n52EsTienePiso)
    {
        $this->n52EsTienePiso = $n52EsTienePiso;
    
        return $this;
    }

    /**
     * Get n52EsTienePiso
     *
     * @return boolean 
     */
    public function getN52EsTienePiso()
    {
        return $this->n52EsTienePiso;
    }

    /**
     * Set estadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadoGeneral $estadoTipo
     * @return InfraestructuraH4ServicioBateriaBanos
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
     * Set n522AbreTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n522AbreTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN522AbreTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo $n522AbreTipo = null)
    {
        $this->n522AbreTipo = $n522AbreTipo;
    
        return $this;
    }

    /**
     * Get n522AbreTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasAbreTipo 
     */
    public function getN522AbreTipo()
    {
        return $this->n522AbreTipo;
    }

    /**
     * Set n52TienePisoMaterTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n52TienePisoMaterTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52TienePisoMaterTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo $n52TienePisoMaterTipo = null)
    {
        $this->n52TienePisoMaterTipo = $n52TienePisoMaterTipo;
    
        return $this;
    }

    /**
     * Get n52TienePisoMaterTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPisosMaterialTipo 
     */
    public function getN52TienePisoMaterTipo()
    {
        return $this->n52TienePisoMaterTipo;
    }

    /**
     * Set n52TieneRevestMaterTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n52TieneRevestMaterTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52TieneRevestMaterTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo $n52TieneRevestMaterTipo = null)
    {
        $this->n52TieneRevestMaterTipo = $n52TieneRevestMaterTipo;
    
        return $this;
    }

    /**
     * Get n52TieneRevestMaterTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenRevestimientoMaterialTipo 
     */
    public function getN52TieneRevestMaterTipo()
    {
        return $this->n52TieneRevestMaterTipo;
    }

    /**
     * Set n52TieneMurosCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n52TieneMurosCaracTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52TieneMurosCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo $n52TieneMurosCaracTipo = null)
    {
        $this->n52TieneMurosCaracTipo = $n52TieneMurosCaracTipo;
    
        return $this;
    }

    /**
     * Get n52TieneMurosCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosCaracTipo 
     */
    public function getN52TieneMurosCaracTipo()
    {
        return $this->n52TieneMurosCaracTipo;
    }

    /**
     * Set n52TieneMurosMaterTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n52TieneMurosMaterTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52TieneMurosMaterTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo $n52TieneMurosMaterTipo = null)
    {
        $this->n52TieneMurosMaterTipo = $n52TieneMurosMaterTipo;
    
        return $this;
    }

    /**
     * Get n52TieneMurosMaterTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenMurosMaterialTipo 
     */
    public function getN52TieneMurosMaterTipo()
    {
        return $this->n52TieneMurosMaterTipo;
    }

    /**
     * Set n52TieneVentanaCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n52TieneVentanaCaracTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52TieneVentanaCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo $n52TieneVentanaCaracTipo = null)
    {
        $this->n52TieneVentanaCaracTipo = $n52TieneVentanaCaracTipo;
    
        return $this;
    }

    /**
     * Get n52TieneVentanaCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenVentanasCaracTipo 
     */
    public function getN52TieneVentanaCaracTipo()
    {
        return $this->n52TieneVentanaCaracTipo;
    }

    /**
     * Set n521SeguroTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n521SeguroTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN521SeguroTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo $n521SeguroTipo = null)
    {
        $this->n521SeguroTipo = $n521SeguroTipo;
    
        return $this;
    }

    /**
     * Get n521SeguroTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenPuertasSeguroTipo 
     */
    public function getN521SeguroTipo()
    {
        return $this->n521SeguroTipo;
    }

    /**
     * Set n52TienePisoCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n52TienePisoCaracTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52TienePisoCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n52TienePisoCaracTipo = null)
    {
        $this->n52TienePisoCaracTipo = $n52TienePisoCaracTipo;
    
        return $this;
    }

    /**
     * Get n52TienePisoCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN52TienePisoCaracTipo()
    {
        return $this->n52TienePisoCaracTipo;
    }

    /**
     * Set n52TieneRevestCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n52TieneRevestCaracTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52TieneRevestCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n52TieneRevestCaracTipo = null)
    {
        $this->n52TieneRevestCaracTipo = $n52TieneRevestCaracTipo;
    
        return $this;
    }

    /**
     * Get n52TieneRevestCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN52TieneRevestCaracTipo()
    {
        return $this->n52TieneRevestCaracTipo;
    }

    /**
     * Set n52TieneCieloFalsoCaracTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n52TieneCieloFalsoCaracTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN52TieneCieloFalsoCaracTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo $n52TieneCieloFalsoCaracTipo = null)
    {
        $this->n52TieneCieloFalsoCaracTipo = $n52TieneCieloFalsoCaracTipo;
    
        return $this;
    }

    /**
     * Get n52TieneCieloFalsoCaracTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenCaracteristicasInfraTipo 
     */
    public function getN52TieneCieloFalsoCaracTipo()
    {
        return $this->n52TieneCieloFalsoCaracTipo;
    }

    /**
     * Set n5CieloEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n5CieloEstadogeneralTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5CieloEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n5CieloEstadogeneralTipo = null)
    {
        $this->n5CieloEstadogeneralTipo = $n5CieloEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n5CieloEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN5CieloEstadogeneralTipo()
    {
        return $this->n5CieloEstadogeneralTipo;
    }

    /**
     * Set n5PisoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n5PisoEstadogeneralTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5PisoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n5PisoEstadogeneralTipo = null)
    {
        $this->n5PisoEstadogeneralTipo = $n5PisoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n5PisoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN5PisoEstadogeneralTipo()
    {
        return $this->n5PisoEstadogeneralTipo;
    }

    /**
     * Set n5TechoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n5TechoEstadogeneralTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5TechoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n5TechoEstadogeneralTipo = null)
    {
        $this->n5TechoEstadogeneralTipo = $n5TechoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n5TechoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN5TechoEstadogeneralTipo()
    {
        return $this->n5TechoEstadogeneralTipo;
    }

    /**
     * Set n5ParedEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n5ParedEstadogeneralTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5ParedEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n5ParedEstadogeneralTipo = null)
    {
        $this->n5ParedEstadogeneralTipo = $n5ParedEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n5ParedEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN5ParedEstadogeneralTipo()
    {
        return $this->n5ParedEstadogeneralTipo;
    }

    /**
     * Set n5AmbienteBanoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4BanoTipo $n5AmbienteBanoTipo
     * @return InfraestructuraH4ServicioBateriaBanos
     */
    public function setN5AmbienteBanoTipo(\Sie\AppWebBundle\Entity\InfraestructuraH4BanoTipo $n5AmbienteBanoTipo = null)
    {
        $this->n5AmbienteBanoTipo = $n5AmbienteBanoTipo;
    
        return $this;
    }

    /**
     * Get n5AmbienteBanoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH4BanoTipo 
     */
    public function getN5AmbienteBanoTipo()
    {
        return $this->n5AmbienteBanoTipo;
    }

    /**
     * Set infraestructuraH4Servicio
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH4Servicio $infraestructuraH4Servicio
     * @return InfraestructuraH4ServicioBateriaBanos
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
