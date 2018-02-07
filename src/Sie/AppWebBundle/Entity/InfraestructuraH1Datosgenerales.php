<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH1Datosgenerales
 */
class InfraestructuraH1Datosgenerales
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $n110Zonabarrio;

    /**
     * @var string
     */
    private $n111Macrodistrito;

    /**
     * @var string
     */
    private $n112Distritomun;

    /**
     * @var string
     */
    private $n21Calleavenida;

    /**
     * @var string
     */
    private $n22Descripcionacceso;

    /**
     * @var string
     */
    private $n31Tramotroncal;

    /**
     * @var string
     */
    private $n32Tramocomplementaria;

    /**
     * @var string
     */
    private $n33Tramovecinal;

    /**
     * @var integer
     */
    private $n34TerrestreDias;

    /**
     * @var integer
     */
    private $n34TerrestreHrs;

    /**
     * @var integer
     */
    private $n34TerrestreMin;

    /**
     * @var integer
     */
    private $n34TerrestreCosto;

    /**
     * @var string
     */
    private $n34TerrestreDescripcion;

    /**
     * @var integer
     */
    private $n34FluvialDias;

    /**
     * @var integer
     */
    private $n34FluvialHrs;

    /**
     * @var integer
     */
    private $n34FluvialMin;

    /**
     * @var integer
     */
    private $n34FluvialCosto;

    /**
     * @var string
     */
    private $n34FluvialDescripcion;

    /**
     * @var integer
     */
    private $n34AereoDias;

    /**
     * @var integer
     */
    private $n34AereoHrs;

    /**
     * @var integer
     */
    private $n34AereoMin;

    /**
     * @var integer
     */
    private $n34AereoCosto;

    /**
     * @var string
     */
    private $n34AereoDescripcion;

    /**
     * @var integer
     */
    private $n34CombinacionDias;

    /**
     * @var integer
     */
    private $n34CombinacionHrs;

    /**
     * @var integer
     */
    private $n34CombinacionMin;

    /**
     * @var integer
     */
    private $n34CombinacionCosto;

    /**
     * @var string
     */
    private $n34CombinacionDescripcion;

    /**
     * @var string
     */
    private $n5Obs;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

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
     * Set n110Zonabarrio
     *
     * @param string $n110Zonabarrio
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN110Zonabarrio($n110Zonabarrio)
    {
        $this->n110Zonabarrio = mb_strtoupper($n110Zonabarrio,'utf-8');
    
        return $this;
    }

    /**
     * Get n110Zonabarrio
     *
     * @return string 
     */
    public function getN110Zonabarrio()
    {
        return $this->n110Zonabarrio;
    }

    /**
     * Set n111Macrodistrito
     *
     * @param string $n111Macrodistrito
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN111Macrodistrito($n111Macrodistrito)
    {
        $this->n111Macrodistrito = mb_strtoupper($n111Macrodistrito,'utf-8');
    
        return $this;
    }

    /**
     * Get n111Macrodistrito
     *
     * @return string 
     */
    public function getN111Macrodistrito()
    {
        return $this->n111Macrodistrito;
    }

    /**
     * Set n112Distritomun
     *
     * @param string $n112Distritomun
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN112Distritomun($n112Distritomun)
    {
        $this->n112Distritomun = mb_strtoupper($n112Distritomun,'utf-8');
    
        return $this;
    }

    /**
     * Get n112Distritomun
     *
     * @return string 
     */
    public function getN112Distritomun()
    {
        return $this->n112Distritomun;
    }

    /**
     * Set n21Calleavenida
     *
     * @param string $n21Calleavenida
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN21Calleavenida($n21Calleavenida)
    {
        $this->n21Calleavenida = mb_strtoupper($n21Calleavenida,'utf-8');
    
        return $this;
    }

    /**
     * Get n21Calleavenida
     *
     * @return string 
     */
    public function getN21Calleavenida()
    {
        return $this->n21Calleavenida;
    }

    /**
     * Set n22Descripcionacceso
     *
     * @param string $n22Descripcionacceso
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN22Descripcionacceso($n22Descripcionacceso)
    {
        $this->n22Descripcionacceso = mb_strtoupper($n22Descripcionacceso,'utf-8');
    
        return $this;
    }

    /**
     * Get n22Descripcionacceso
     *
     * @return string 
     */
    public function getN22Descripcionacceso()
    {
        return $this->n22Descripcionacceso;
    }

    /**
     * Set n31Tramotroncal
     *
     * @param string $n31Tramotroncal
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN31Tramotroncal($n31Tramotroncal)
    {
        $this->n31Tramotroncal = mb_strtoupper($n31Tramotroncal,'utf-8');
    
        return $this;
    }

    /**
     * Get n31Tramotroncal
     *
     * @return string 
     */
    public function getN31Tramotroncal()
    {
        return $this->n31Tramotroncal;
    }

    /**
     * Set n32Tramocomplementaria
     *
     * @param string $n32Tramocomplementaria
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN32Tramocomplementaria($n32Tramocomplementaria)
    {
        $this->n32Tramocomplementaria = mb_strtoupper($n32Tramocomplementaria,'utf-8');
    
        return $this;
    }

    /**
     * Get n32Tramocomplementaria
     *
     * @return string 
     */
    public function getN32Tramocomplementaria()
    {
        return $this->n32Tramocomplementaria;
    }

    /**
     * Set n33Tramovecinal
     *
     * @param string $n33Tramovecinal
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN33Tramovecinal($n33Tramovecinal)
    {
        $this->n33Tramovecinal = mb_strtoupper($n33Tramovecinal,'utf-8');
    
        return $this;
    }

    /**
     * Get n33Tramovecinal
     *
     * @return string 
     */
    public function getN33Tramovecinal()
    {
        return $this->n33Tramovecinal;
    }

    /**
     * Set n34TerrestreDias
     *
     * @param integer $n34TerrestreDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34TerrestreDias($n34TerrestreDias)
    {
        $this->n34TerrestreDias = $n34TerrestreDias;
    
        return $this;
    }

    /**
     * Get n34TerrestreDias
     *
     * @return integer 
     */
    public function getN34TerrestreDias()
    {
        return $this->n34TerrestreDias;
    }

    /**
     * Set n34TerrestreHrs
     *
     * @param integer $n34TerrestreHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34TerrestreHrs($n34TerrestreHrs)
    {
        $this->n34TerrestreHrs = $n34TerrestreHrs;
    
        return $this;
    }

    /**
     * Get n34TerrestreHrs
     *
     * @return integer 
     */
    public function getN34TerrestreHrs()
    {
        return $this->n34TerrestreHrs;
    }

    /**
     * Set n34TerrestreMin
     *
     * @param integer $n34TerrestreMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34TerrestreMin($n34TerrestreMin)
    {
        $this->n34TerrestreMin = $n34TerrestreMin;
    
        return $this;
    }

    /**
     * Get n34TerrestreMin
     *
     * @return integer 
     */
    public function getN34TerrestreMin()
    {
        return $this->n34TerrestreMin;
    }

    /**
     * Set n34TerrestreCosto
     *
     * @param integer $n34TerrestreCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34TerrestreCosto($n34TerrestreCosto)
    {
        $this->n34TerrestreCosto = $n34TerrestreCosto;
    
        return $this;
    }

    /**
     * Get n34TerrestreCosto
     *
     * @return integer 
     */
    public function getN34TerrestreCosto()
    {
        return $this->n34TerrestreCosto;
    }

    /**
     * Set n34TerrestreDescripcion
     *
     * @param string $n34TerrestreDescripcion
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34TerrestreDescripcion($n34TerrestreDescripcion)
    {
        $this->n34TerrestreDescripcion = mb_strtoupper($n34TerrestreDescripcion,'utf-8');
    
        return $this;
    }

    /**
     * Get n34TerrestreDescripcion
     *
     * @return string 
     */
    public function getN34TerrestreDescripcion()
    {
        return $this->n34TerrestreDescripcion;
    }

    /**
     * Set n34FluvialDias
     *
     * @param integer $n34FluvialDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialDias($n34FluvialDias)
    {
        $this->n34FluvialDias = $n34FluvialDias;
    
        return $this;
    }

    /**
     * Get n34FluvialDias
     *
     * @return integer 
     */
    public function getN34FluvialDias()
    {
        return $this->n34FluvialDias;
    }

    /**
     * Set n34FluvialHrs
     *
     * @param integer $n34FluvialHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialHrs($n34FluvialHrs)
    {
        $this->n34FluvialHrs = $n34FluvialHrs;
    
        return $this;
    }

    /**
     * Get n34FluvialHrs
     *
     * @return integer 
     */
    public function getN34FluvialHrs()
    {
        return $this->n34FluvialHrs;
    }

    /**
     * Set n34FluvialMin
     *
     * @param integer $n34FluvialMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialMin($n34FluvialMin)
    {
        $this->n34FluvialMin = $n34FluvialMin;
    
        return $this;
    }

    /**
     * Get n34FluvialMin
     *
     * @return integer 
     */
    public function getN34FluvialMin()
    {
        return $this->n34FluvialMin;
    }

    /**
     * Set n34FluvialCosto
     *
     * @param integer $n34FluvialCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialCosto($n34FluvialCosto)
    {
        $this->n34FluvialCosto = $n34FluvialCosto;
    
        return $this;
    }

    /**
     * Get n34FluvialCosto
     *
     * @return integer 
     */
    public function getN34FluvialCosto()
    {
        return $this->n34FluvialCosto;
    }

    /**
     * Set n34FluvialDescripcion
     *
     * @param string $n34FluvialDescripcion
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialDescripcion($n34FluvialDescripcion)
    {
        $this->n34FluvialDescripcion = mb_strtoupper($n34FluvialDescripcion,'utf-8');
    
        return $this;
    }

    /**
     * Get n34FluvialDescripcion
     *
     * @return string 
     */
    public function getN34FluvialDescripcion()
    {
        return $this->n34FluvialDescripcion;
    }

    /**
     * Set n34AereoDias
     *
     * @param integer $n34AereoDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoDias($n34AereoDias)
    {
        $this->n34AereoDias = $n34AereoDias;
    
        return $this;
    }

    /**
     * Get n34AereoDias
     *
     * @return integer 
     */
    public function getN34AereoDias()
    {
        return $this->n34AereoDias;
    }

    /**
     * Set n34AereoHrs
     *
     * @param integer $n34AereoHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoHrs($n34AereoHrs)
    {
        $this->n34AereoHrs = $n34AereoHrs;
    
        return $this;
    }

    /**
     * Get n34AereoHrs
     *
     * @return integer 
     */
    public function getN34AereoHrs()
    {
        return $this->n34AereoHrs;
    }

    /**
     * Set n34AereoMin
     *
     * @param integer $n34AereoMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoMin($n34AereoMin)
    {
        $this->n34AereoMin = $n34AereoMin;
    
        return $this;
    }

    /**
     * Get n34AereoMin
     *
     * @return integer 
     */
    public function getN34AereoMin()
    {
        return $this->n34AereoMin;
    }

    /**
     * Set n34AereoCosto
     *
     * @param integer $n34AereoCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoCosto($n34AereoCosto)
    {
        $this->n34AereoCosto = $n34AereoCosto;
    
        return $this;
    }

    /**
     * Get n34AereoCosto
     *
     * @return integer 
     */
    public function getN34AereoCosto()
    {
        return $this->n34AereoCosto;
    }

    /**
     * Set n34AereoDescripcion
     *
     * @param string $n34AereoDescripcion
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoDescripcion($n34AereoDescripcion)
    {
        $this->n34AereoDescripcion = mb_strtoupper($n34AereoDescripcion,'utf-8');
    
        return $this;
    }

    /**
     * Get n34AereoDescripcion
     *
     * @return string 
     */
    public function getN34AereoDescripcion()
    {
        return $this->n34AereoDescripcion;
    }

    /**
     * Set n34CombinacionDias
     *
     * @param integer $n34CombinacionDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34CombinacionDias($n34CombinacionDias)
    {
        $this->n34CombinacionDias = $n34CombinacionDias;
    
        return $this;
    }

    /**
     * Get n34CombinacionDias
     *
     * @return integer 
     */
    public function getN34CombinacionDias()
    {
        return $this->n34CombinacionDias;
    }

    /**
     * Set n34CombinacionHrs
     *
     * @param integer $n34CombinacionHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34CombinacionHrs($n34CombinacionHrs)
    {
        $this->n34CombinacionHrs = $n34CombinacionHrs;
    
        return $this;
    }

    /**
     * Get n34CombinacionHrs
     *
     * @return integer 
     */
    public function getN34CombinacionHrs()
    {
        return $this->n34CombinacionHrs;
    }

    /**
     * Set n34CombinacionMin
     *
     * @param integer $n34CombinacionMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34CombinacionMin($n34CombinacionMin)
    {
        $this->n34CombinacionMin = $n34CombinacionMin;
    
        return $this;
    }

    /**
     * Get n34CombinacionMin
     *
     * @return integer 
     */
    public function getN34CombinacionMin()
    {
        return $this->n34CombinacionMin;
    }

    /**
     * Set n34CombinacionCosto
     *
     * @param integer $n34CombinacionCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34CombinacionCosto($n34CombinacionCosto)
    {
        $this->n34CombinacionCosto = $n34CombinacionCosto;
    
        return $this;
    }

    /**
     * Get n34CombinacionCosto
     *
     * @return integer 
     */
    public function getN34CombinacionCosto()
    {
        return $this->n34CombinacionCosto;
    }

    /**
     * Set n34CombinacionDescripcion
     *
     * @param string $n34CombinacionDescripcion
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34CombinacionDescripcion($n34CombinacionDescripcion)
    {
        $this->n34CombinacionDescripcion = mb_strtoupper($n34CombinacionDescripcion,'utf-8');
    
        return $this;
    }

    /**
     * Get n34CombinacionDescripcion
     *
     * @return string 
     */
    public function getN34CombinacionDescripcion()
    {
        return $this->n34CombinacionDescripcion;
    }

    /**
     * Set n5Obs
     *
     * @param string $n5Obs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN5Obs($n5Obs)
    {
        $this->n5Obs = mb_strtoupper($n5Obs,'utf-8');
    
        return $this;
    }

    /**
     * Get n5Obs
     *
     * @return string 
     */
    public function getN5Obs()
    {
        return $this->n5Obs;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH1Datosgenerales
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
     * Set infraestructuraJuridiccionGeografica
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraJuridiccionGeografica $infraestructuraJuridiccionGeografica
     * @return InfraestructuraH1Datosgenerales
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
    private $n19Zonabarrio;

    /**
     * @var string
     */
    private $n110Direccion;

    /**
     * @var integer
     */
    private $n34VehicularDistDias;

    /**
     * @var integer
     */
    private $n34VehicularDistHrs;

    /**
     * @var integer
     */
    private $n34VehicularDistMin;

    /**
     * @var float
     */
    private $n34VehicularDistCosto;

    /**
     * @var integer
     */
    private $n34FluvialDistDias;

    /**
     * @var integer
     */
    private $n34FluvialDistHrs;

    /**
     * @var integer
     */
    private $n34FluvialDistMin;

    /**
     * @var float
     */
    private $n34FluvialDistCosto;

    /**
     * @var integer
     */
    private $n34AereoDistDias;

    /**
     * @var integer
     */
    private $n34AereoDistHrs;

    /**
     * @var integer
     */
    private $n34AereoDistMin;

    /**
     * @var float
     */
    private $n34AereoDistCosto;

    /**
     * @var integer
     */
    private $n34PeatonalDistDias;

    /**
     * @var integer
     */
    private $n34PeatonalDistHrs;

    /**
     * @var integer
     */
    private $n34PeatonalDistMin;

    /**
     * @var float
     */
    private $n34PeatonalDistCosto;

    /**
     * @var integer
     */
    private $n34PeatonalMunDias;

    /**
     * @var integer
     */
    private $n34PeatonalMunHrs;

    /**
     * @var integer
     */
    private $n34PeatonalMunMin;

    /**
     * @var float
     */
    private $n34PeatonalMunCosto;

    /**
     * @var integer
     */
    private $n34VehicularMunDias;

    /**
     * @var integer
     */
    private $n34VehicularMunHrs;

    /**
     * @var integer
     */
    private $n34VehicularMunMin;

    /**
     * @var float
     */
    private $n34VehicularMunCosto;

    /**
     * @var integer
     */
    private $n34FluvialMunDias;

    /**
     * @var integer
     */
    private $n34FluvialMunHrs;

    /**
     * @var integer
     */
    private $n34FluvialMunMin;

    /**
     * @var float
     */
    private $n34FluvialMunCosto;

    /**
     * @var integer
     */
    private $n34AereoMunDias;

    /**
     * @var integer
     */
    private $n34AereoMunHrs;

    /**
     * @var integer
     */
    private $n34AereoMunMin;

    /**
     * @var float
     */
    private $n34AereoMunCosto;

    /**
     * @var string
     */
    private $n21FotografiaPrincipal;

    /**
     * @var string
     */
    private $n21FotografiaFrontal;

    /**
     * @var string
     */
    private $n21FotografiaLateral;

    /**
     * @var string
     */
    private $n21FotografiaPanoramica;

    /**
     * @var boolean
     */
    private $n34PeatonalEsDist;

    /**
     * @var boolean
     */
    private $n34VehicularEsDist;

    /**
     * @var boolean
     */
    private $n34FluvialEsDist;

    /**
     * @var boolean
     */
    private $n34AereoEsDist;

    /**
     * @var boolean
     */
    private $n34PeatonalEsMun;

    /**
     * @var boolean
     */
    private $n34VehicularEsMun;

    /**
     * @var boolean
     */
    private $n34FluvialEsMun;

    /**
     * @var boolean
     */
    private $n34AereoEsMun;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoAereoTipo
     */
    private $n34InfraestructuraH1AccesoAereoTipoMun;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo
     */
    private $n34InfraestructuraH1AccesoFluvialTipoMun2;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo
     */
    private $n34InfraestructuraH1AccesoFluvialTipoMun1;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo
     */
    private $n34InfraestructuraH1AccesoVehicularTipoMun3;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo
     */
    private $n34InfraestructuraH1AccesoVehicularTipoMun2;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo
     */
    private $n34InfraestructuraH1AccesoVehicularTipoMun1;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoAereoTipo
     */
    private $n34InfraestructuraH1AccesoAereoTipoDist;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo
     */
    private $n34InfraestructuraH1AccesoFluvialTipoDist2;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo
     */
    private $n34InfraestructuraH1AccesoFluvialTipoDist1;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo
     */
    private $n34InfraestructuraH1AccesoVehicularTipoDist3;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo
     */
    private $n34InfraestructuraH1AccesoVehicularTipoDist2;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo
     */
    private $n34InfraestructuraH1AccesoVehicularTipoDist1;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH1DireccionTipo
     */
    private $n110InfraestructuraH1DireccionTipo;


    /**
     * Set n19Zonabarrio
     *
     * @param string $n19Zonabarrio
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN19Zonabarrio($n19Zonabarrio)
    {
        $this->n19Zonabarrio = $n19Zonabarrio;
    
        return $this;
    }

    /**
     * Get n19Zonabarrio
     *
     * @return string 
     */
    public function getN19Zonabarrio()
    {
        return $this->n19Zonabarrio;
    }

    /**
     * Set n110Direccion
     *
     * @param string $n110Direccion
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN110Direccion($n110Direccion)
    {
        $this->n110Direccion = $n110Direccion;
    
        return $this;
    }

    /**
     * Get n110Direccion
     *
     * @return string 
     */
    public function getN110Direccion()
    {
        return $this->n110Direccion;
    }

    /**
     * Set n34VehicularDistDias
     *
     * @param integer $n34VehicularDistDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34VehicularDistDias($n34VehicularDistDias)
    {
        $this->n34VehicularDistDias = $n34VehicularDistDias;
    
        return $this;
    }

    /**
     * Get n34VehicularDistDias
     *
     * @return integer 
     */
    public function getN34VehicularDistDias()
    {
        return $this->n34VehicularDistDias;
    }

    /**
     * Set n34VehicularDistHrs
     *
     * @param integer $n34VehicularDistHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34VehicularDistHrs($n34VehicularDistHrs)
    {
        $this->n34VehicularDistHrs = $n34VehicularDistHrs;
    
        return $this;
    }

    /**
     * Get n34VehicularDistHrs
     *
     * @return integer 
     */
    public function getN34VehicularDistHrs()
    {
        return $this->n34VehicularDistHrs;
    }

    /**
     * Set n34VehicularDistMin
     *
     * @param integer $n34VehicularDistMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34VehicularDistMin($n34VehicularDistMin)
    {
        $this->n34VehicularDistMin = $n34VehicularDistMin;
    
        return $this;
    }

    /**
     * Get n34VehicularDistMin
     *
     * @return integer 
     */
    public function getN34VehicularDistMin()
    {
        return $this->n34VehicularDistMin;
    }

    /**
     * Set n34VehicularDistCosto
     *
     * @param float $n34VehicularDistCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34VehicularDistCosto($n34VehicularDistCosto)
    {
        $this->n34VehicularDistCosto = $n34VehicularDistCosto;
    
        return $this;
    }

    /**
     * Get n34VehicularDistCosto
     *
     * @return float 
     */
    public function getN34VehicularDistCosto()
    {
        return $this->n34VehicularDistCosto;
    }

    /**
     * Set n34FluvialDistDias
     *
     * @param integer $n34FluvialDistDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialDistDias($n34FluvialDistDias)
    {
        $this->n34FluvialDistDias = $n34FluvialDistDias;
    
        return $this;
    }

    /**
     * Get n34FluvialDistDias
     *
     * @return integer 
     */
    public function getN34FluvialDistDias()
    {
        return $this->n34FluvialDistDias;
    }

    /**
     * Set n34FluvialDistHrs
     *
     * @param integer $n34FluvialDistHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialDistHrs($n34FluvialDistHrs)
    {
        $this->n34FluvialDistHrs = $n34FluvialDistHrs;
    
        return $this;
    }

    /**
     * Get n34FluvialDistHrs
     *
     * @return integer 
     */
    public function getN34FluvialDistHrs()
    {
        return $this->n34FluvialDistHrs;
    }

    /**
     * Set n34FluvialDistMin
     *
     * @param integer $n34FluvialDistMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialDistMin($n34FluvialDistMin)
    {
        $this->n34FluvialDistMin = $n34FluvialDistMin;
    
        return $this;
    }

    /**
     * Get n34FluvialDistMin
     *
     * @return integer 
     */
    public function getN34FluvialDistMin()
    {
        return $this->n34FluvialDistMin;
    }

    /**
     * Set n34FluvialDistCosto
     *
     * @param float $n34FluvialDistCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialDistCosto($n34FluvialDistCosto)
    {
        $this->n34FluvialDistCosto = $n34FluvialDistCosto;
    
        return $this;
    }

    /**
     * Get n34FluvialDistCosto
     *
     * @return float 
     */
    public function getN34FluvialDistCosto()
    {
        return $this->n34FluvialDistCosto;
    }

    /**
     * Set n34AereoDistDias
     *
     * @param integer $n34AereoDistDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoDistDias($n34AereoDistDias)
    {
        $this->n34AereoDistDias = $n34AereoDistDias;
    
        return $this;
    }

    /**
     * Get n34AereoDistDias
     *
     * @return integer 
     */
    public function getN34AereoDistDias()
    {
        return $this->n34AereoDistDias;
    }

    /**
     * Set n34AereoDistHrs
     *
     * @param integer $n34AereoDistHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoDistHrs($n34AereoDistHrs)
    {
        $this->n34AereoDistHrs = $n34AereoDistHrs;
    
        return $this;
    }

    /**
     * Get n34AereoDistHrs
     *
     * @return integer 
     */
    public function getN34AereoDistHrs()
    {
        return $this->n34AereoDistHrs;
    }

    /**
     * Set n34AereoDistMin
     *
     * @param integer $n34AereoDistMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoDistMin($n34AereoDistMin)
    {
        $this->n34AereoDistMin = $n34AereoDistMin;
    
        return $this;
    }

    /**
     * Get n34AereoDistMin
     *
     * @return integer 
     */
    public function getN34AereoDistMin()
    {
        return $this->n34AereoDistMin;
    }

    /**
     * Set n34AereoDistCosto
     *
     * @param float $n34AereoDistCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoDistCosto($n34AereoDistCosto)
    {
        $this->n34AereoDistCosto = $n34AereoDistCosto;
    
        return $this;
    }

    /**
     * Get n34AereoDistCosto
     *
     * @return float 
     */
    public function getN34AereoDistCosto()
    {
        return $this->n34AereoDistCosto;
    }

    /**
     * Set n34PeatonalDistDias
     *
     * @param integer $n34PeatonalDistDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34PeatonalDistDias($n34PeatonalDistDias)
    {
        $this->n34PeatonalDistDias = $n34PeatonalDistDias;
    
        return $this;
    }

    /**
     * Get n34PeatonalDistDias
     *
     * @return integer 
     */
    public function getN34PeatonalDistDias()
    {
        return $this->n34PeatonalDistDias;
    }

    /**
     * Set n34PeatonalDistHrs
     *
     * @param integer $n34PeatonalDistHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34PeatonalDistHrs($n34PeatonalDistHrs)
    {
        $this->n34PeatonalDistHrs = $n34PeatonalDistHrs;
    
        return $this;
    }

    /**
     * Get n34PeatonalDistHrs
     *
     * @return integer 
     */
    public function getN34PeatonalDistHrs()
    {
        return $this->n34PeatonalDistHrs;
    }

    /**
     * Set n34PeatonalDistMin
     *
     * @param integer $n34PeatonalDistMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34PeatonalDistMin($n34PeatonalDistMin)
    {
        $this->n34PeatonalDistMin = $n34PeatonalDistMin;
    
        return $this;
    }

    /**
     * Get n34PeatonalDistMin
     *
     * @return integer 
     */
    public function getN34PeatonalDistMin()
    {
        return $this->n34PeatonalDistMin;
    }

    /**
     * Set n34PeatonalDistCosto
     *
     * @param float $n34PeatonalDistCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34PeatonalDistCosto($n34PeatonalDistCosto)
    {
        $this->n34PeatonalDistCosto = $n34PeatonalDistCosto;
    
        return $this;
    }

    /**
     * Get n34PeatonalDistCosto
     *
     * @return float 
     */
    public function getN34PeatonalDistCosto()
    {
        return $this->n34PeatonalDistCosto;
    }

    /**
     * Set n34PeatonalMunDias
     *
     * @param integer $n34PeatonalMunDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34PeatonalMunDias($n34PeatonalMunDias)
    {
        $this->n34PeatonalMunDias = $n34PeatonalMunDias;
    
        return $this;
    }

    /**
     * Get n34PeatonalMunDias
     *
     * @return integer 
     */
    public function getN34PeatonalMunDias()
    {
        return $this->n34PeatonalMunDias;
    }

    /**
     * Set n34PeatonalMunHrs
     *
     * @param integer $n34PeatonalMunHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34PeatonalMunHrs($n34PeatonalMunHrs)
    {
        $this->n34PeatonalMunHrs = $n34PeatonalMunHrs;
    
        return $this;
    }

    /**
     * Get n34PeatonalMunHrs
     *
     * @return integer 
     */
    public function getN34PeatonalMunHrs()
    {
        return $this->n34PeatonalMunHrs;
    }

    /**
     * Set n34PeatonalMunMin
     *
     * @param integer $n34PeatonalMunMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34PeatonalMunMin($n34PeatonalMunMin)
    {
        $this->n34PeatonalMunMin = $n34PeatonalMunMin;
    
        return $this;
    }

    /**
     * Get n34PeatonalMunMin
     *
     * @return integer 
     */
    public function getN34PeatonalMunMin()
    {
        return $this->n34PeatonalMunMin;
    }

    /**
     * Set n34PeatonalMunCosto
     *
     * @param float $n34PeatonalMunCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34PeatonalMunCosto($n34PeatonalMunCosto)
    {
        $this->n34PeatonalMunCosto = $n34PeatonalMunCosto;
    
        return $this;
    }

    /**
     * Get n34PeatonalMunCosto
     *
     * @return float 
     */
    public function getN34PeatonalMunCosto()
    {
        return $this->n34PeatonalMunCosto;
    }

    /**
     * Set n34VehicularMunDias
     *
     * @param integer $n34VehicularMunDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34VehicularMunDias($n34VehicularMunDias)
    {
        $this->n34VehicularMunDias = $n34VehicularMunDias;
    
        return $this;
    }

    /**
     * Get n34VehicularMunDias
     *
     * @return integer 
     */
    public function getN34VehicularMunDias()
    {
        return $this->n34VehicularMunDias;
    }

    /**
     * Set n34VehicularMunHrs
     *
     * @param integer $n34VehicularMunHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34VehicularMunHrs($n34VehicularMunHrs)
    {
        $this->n34VehicularMunHrs = $n34VehicularMunHrs;
    
        return $this;
    }

    /**
     * Get n34VehicularMunHrs
     *
     * @return integer 
     */
    public function getN34VehicularMunHrs()
    {
        return $this->n34VehicularMunHrs;
    }

    /**
     * Set n34VehicularMunMin
     *
     * @param integer $n34VehicularMunMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34VehicularMunMin($n34VehicularMunMin)
    {
        $this->n34VehicularMunMin = $n34VehicularMunMin;
    
        return $this;
    }

    /**
     * Get n34VehicularMunMin
     *
     * @return integer 
     */
    public function getN34VehicularMunMin()
    {
        return $this->n34VehicularMunMin;
    }

    /**
     * Set n34VehicularMunCosto
     *
     * @param float $n34VehicularMunCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34VehicularMunCosto($n34VehicularMunCosto)
    {
        $this->n34VehicularMunCosto = $n34VehicularMunCosto;
    
        return $this;
    }

    /**
     * Get n34VehicularMunCosto
     *
     * @return float 
     */
    public function getN34VehicularMunCosto()
    {
        return $this->n34VehicularMunCosto;
    }

    /**
     * Set n34FluvialMunDias
     *
     * @param integer $n34FluvialMunDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialMunDias($n34FluvialMunDias)
    {
        $this->n34FluvialMunDias = $n34FluvialMunDias;
    
        return $this;
    }

    /**
     * Get n34FluvialMunDias
     *
     * @return integer 
     */
    public function getN34FluvialMunDias()
    {
        return $this->n34FluvialMunDias;
    }

    /**
     * Set n34FluvialMunHrs
     *
     * @param integer $n34FluvialMunHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialMunHrs($n34FluvialMunHrs)
    {
        $this->n34FluvialMunHrs = $n34FluvialMunHrs;
    
        return $this;
    }

    /**
     * Get n34FluvialMunHrs
     *
     * @return integer 
     */
    public function getN34FluvialMunHrs()
    {
        return $this->n34FluvialMunHrs;
    }

    /**
     * Set n34FluvialMunMin
     *
     * @param integer $n34FluvialMunMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialMunMin($n34FluvialMunMin)
    {
        $this->n34FluvialMunMin = $n34FluvialMunMin;
    
        return $this;
    }

    /**
     * Get n34FluvialMunMin
     *
     * @return integer 
     */
    public function getN34FluvialMunMin()
    {
        return $this->n34FluvialMunMin;
    }

    /**
     * Set n34FluvialMunCosto
     *
     * @param float $n34FluvialMunCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialMunCosto($n34FluvialMunCosto)
    {
        $this->n34FluvialMunCosto = $n34FluvialMunCosto;
    
        return $this;
    }

    /**
     * Get n34FluvialMunCosto
     *
     * @return float 
     */
    public function getN34FluvialMunCosto()
    {
        return $this->n34FluvialMunCosto;
    }

    /**
     * Set n34AereoMunDias
     *
     * @param integer $n34AereoMunDias
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoMunDias($n34AereoMunDias)
    {
        $this->n34AereoMunDias = $n34AereoMunDias;
    
        return $this;
    }

    /**
     * Get n34AereoMunDias
     *
     * @return integer 
     */
    public function getN34AereoMunDias()
    {
        return $this->n34AereoMunDias;
    }

    /**
     * Set n34AereoMunHrs
     *
     * @param integer $n34AereoMunHrs
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoMunHrs($n34AereoMunHrs)
    {
        $this->n34AereoMunHrs = $n34AereoMunHrs;
    
        return $this;
    }

    /**
     * Get n34AereoMunHrs
     *
     * @return integer 
     */
    public function getN34AereoMunHrs()
    {
        return $this->n34AereoMunHrs;
    }

    /**
     * Set n34AereoMunMin
     *
     * @param integer $n34AereoMunMin
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoMunMin($n34AereoMunMin)
    {
        $this->n34AereoMunMin = $n34AereoMunMin;
    
        return $this;
    }

    /**
     * Get n34AereoMunMin
     *
     * @return integer 
     */
    public function getN34AereoMunMin()
    {
        return $this->n34AereoMunMin;
    }

    /**
     * Set n34AereoMunCosto
     *
     * @param float $n34AereoMunCosto
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoMunCosto($n34AereoMunCosto)
    {
        $this->n34AereoMunCosto = $n34AereoMunCosto;
    
        return $this;
    }

    /**
     * Get n34AereoMunCosto
     *
     * @return float 
     */
    public function getN34AereoMunCosto()
    {
        return $this->n34AereoMunCosto;
    }

    /**
     * Set n21FotografiaPrincipal
     *
     * @param string $n21FotografiaPrincipal
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN21FotografiaPrincipal($n21FotografiaPrincipal)
    {
        $this->n21FotografiaPrincipal = $n21FotografiaPrincipal;
    
        return $this;
    }

    /**
     * Get n21FotografiaPrincipal
     *
     * @return string 
     */
    public function getN21FotografiaPrincipal()
    {
        return $this->n21FotografiaPrincipal;
    }

    /**
     * Set n21FotografiaFrontal
     *
     * @param string $n21FotografiaFrontal
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN21FotografiaFrontal($n21FotografiaFrontal)
    {
        $this->n21FotografiaFrontal = $n21FotografiaFrontal;
    
        return $this;
    }

    /**
     * Get n21FotografiaFrontal
     *
     * @return string 
     */
    public function getN21FotografiaFrontal()
    {
        return $this->n21FotografiaFrontal;
    }

    /**
     * Set n21FotografiaLateral
     *
     * @param string $n21FotografiaLateral
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN21FotografiaLateral($n21FotografiaLateral)
    {
        $this->n21FotografiaLateral = $n21FotografiaLateral;
    
        return $this;
    }

    /**
     * Get n21FotografiaLateral
     *
     * @return string 
     */
    public function getN21FotografiaLateral()
    {
        return $this->n21FotografiaLateral;
    }

    /**
     * Set n21FotografiaPanoramica
     *
     * @param string $n21FotografiaPanoramica
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN21FotografiaPanoramica($n21FotografiaPanoramica)
    {
        $this->n21FotografiaPanoramica = $n21FotografiaPanoramica;
    
        return $this;
    }

    /**
     * Get n21FotografiaPanoramica
     *
     * @return string 
     */
    public function getN21FotografiaPanoramica()
    {
        return $this->n21FotografiaPanoramica;
    }

    /**
     * Set n34PeatonalEsDist
     *
     * @param boolean $n34PeatonalEsDist
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34PeatonalEsDist($n34PeatonalEsDist)
    {
        $this->n34PeatonalEsDist = $n34PeatonalEsDist;
    
        return $this;
    }

    /**
     * Get n34PeatonalEsDist
     *
     * @return boolean 
     */
    public function getN34PeatonalEsDist()
    {
        return $this->n34PeatonalEsDist;
    }

    /**
     * Set n34VehicularEsDist
     *
     * @param boolean $n34VehicularEsDist
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34VehicularEsDist($n34VehicularEsDist)
    {
        $this->n34VehicularEsDist = $n34VehicularEsDist;
    
        return $this;
    }

    /**
     * Get n34VehicularEsDist
     *
     * @return boolean 
     */
    public function getN34VehicularEsDist()
    {
        return $this->n34VehicularEsDist;
    }

    /**
     * Set n34FluvialEsDist
     *
     * @param boolean $n34FluvialEsDist
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialEsDist($n34FluvialEsDist)
    {
        $this->n34FluvialEsDist = $n34FluvialEsDist;
    
        return $this;
    }

    /**
     * Get n34FluvialEsDist
     *
     * @return boolean 
     */
    public function getN34FluvialEsDist()
    {
        return $this->n34FluvialEsDist;
    }

    /**
     * Set n34AereoEsDist
     *
     * @param boolean $n34AereoEsDist
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoEsDist($n34AereoEsDist)
    {
        $this->n34AereoEsDist = $n34AereoEsDist;
    
        return $this;
    }

    /**
     * Get n34AereoEsDist
     *
     * @return boolean 
     */
    public function getN34AereoEsDist()
    {
        return $this->n34AereoEsDist;
    }

    /**
     * Set n34PeatonalEsMun
     *
     * @param boolean $n34PeatonalEsMun
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34PeatonalEsMun($n34PeatonalEsMun)
    {
        $this->n34PeatonalEsMun = $n34PeatonalEsMun;
    
        return $this;
    }

    /**
     * Get n34PeatonalEsMun
     *
     * @return boolean 
     */
    public function getN34PeatonalEsMun()
    {
        return $this->n34PeatonalEsMun;
    }

    /**
     * Set n34VehicularEsMun
     *
     * @param boolean $n34VehicularEsMun
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34VehicularEsMun($n34VehicularEsMun)
    {
        $this->n34VehicularEsMun = $n34VehicularEsMun;
    
        return $this;
    }

    /**
     * Get n34VehicularEsMun
     *
     * @return boolean 
     */
    public function getN34VehicularEsMun()
    {
        return $this->n34VehicularEsMun;
    }

    /**
     * Set n34FluvialEsMun
     *
     * @param boolean $n34FluvialEsMun
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34FluvialEsMun($n34FluvialEsMun)
    {
        $this->n34FluvialEsMun = $n34FluvialEsMun;
    
        return $this;
    }

    /**
     * Get n34FluvialEsMun
     *
     * @return boolean 
     */
    public function getN34FluvialEsMun()
    {
        return $this->n34FluvialEsMun;
    }

    /**
     * Set n34AereoEsMun
     *
     * @param boolean $n34AereoEsMun
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34AereoEsMun($n34AereoEsMun)
    {
        $this->n34AereoEsMun = $n34AereoEsMun;
    
        return $this;
    }

    /**
     * Get n34AereoEsMun
     *
     * @return boolean 
     */
    public function getN34AereoEsMun()
    {
        return $this->n34AereoEsMun;
    }

    /**
     * Set n34InfraestructuraH1AccesoAereoTipoMun
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoAereoTipo $n34InfraestructuraH1AccesoAereoTipoMun
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoAereoTipoMun(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoAereoTipo $n34InfraestructuraH1AccesoAereoTipoMun = null)
    {
        $this->n34InfraestructuraH1AccesoAereoTipoMun = $n34InfraestructuraH1AccesoAereoTipoMun;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoAereoTipoMun
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoAereoTipo 
     */
    public function getN34InfraestructuraH1AccesoAereoTipoMun()
    {
        return $this->n34InfraestructuraH1AccesoAereoTipoMun;
    }

    /**
     * Set n34InfraestructuraH1AccesoFluvialTipoMun2
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo $n34InfraestructuraH1AccesoFluvialTipoMun2
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoFluvialTipoMun2(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo $n34InfraestructuraH1AccesoFluvialTipoMun2 = null)
    {
        $this->n34InfraestructuraH1AccesoFluvialTipoMun2 = $n34InfraestructuraH1AccesoFluvialTipoMun2;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoFluvialTipoMun2
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo 
     */
    public function getN34InfraestructuraH1AccesoFluvialTipoMun2()
    {
        return $this->n34InfraestructuraH1AccesoFluvialTipoMun2;
    }

    /**
     * Set n34InfraestructuraH1AccesoFluvialTipoMun1
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo $n34InfraestructuraH1AccesoFluvialTipoMun1
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoFluvialTipoMun1(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo $n34InfraestructuraH1AccesoFluvialTipoMun1 = null)
    {
        $this->n34InfraestructuraH1AccesoFluvialTipoMun1 = $n34InfraestructuraH1AccesoFluvialTipoMun1;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoFluvialTipoMun1
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo 
     */
    public function getN34InfraestructuraH1AccesoFluvialTipoMun1()
    {
        return $this->n34InfraestructuraH1AccesoFluvialTipoMun1;
    }

    /**
     * Set n34InfraestructuraH1AccesoVehicularTipoMun3
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoMun3
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoVehicularTipoMun3(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoMun3 = null)
    {
        $this->n34InfraestructuraH1AccesoVehicularTipoMun3 = $n34InfraestructuraH1AccesoVehicularTipoMun3;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoVehicularTipoMun3
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo 
     */
    public function getN34InfraestructuraH1AccesoVehicularTipoMun3()
    {
        return $this->n34InfraestructuraH1AccesoVehicularTipoMun3;
    }

    /**
     * Set n34InfraestructuraH1AccesoVehicularTipoMun2
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoMun2
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoVehicularTipoMun2(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoMun2 = null)
    {
        $this->n34InfraestructuraH1AccesoVehicularTipoMun2 = $n34InfraestructuraH1AccesoVehicularTipoMun2;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoVehicularTipoMun2
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo 
     */
    public function getN34InfraestructuraH1AccesoVehicularTipoMun2()
    {
        return $this->n34InfraestructuraH1AccesoVehicularTipoMun2;
    }

    /**
     * Set n34InfraestructuraH1AccesoVehicularTipoMun1
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoMun1
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoVehicularTipoMun1(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoMun1 = null)
    {
        $this->n34InfraestructuraH1AccesoVehicularTipoMun1 = $n34InfraestructuraH1AccesoVehicularTipoMun1;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoVehicularTipoMun1
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo 
     */
    public function getN34InfraestructuraH1AccesoVehicularTipoMun1()
    {
        return $this->n34InfraestructuraH1AccesoVehicularTipoMun1;
    }

    /**
     * Set n34InfraestructuraH1AccesoAereoTipoDist
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoAereoTipo $n34InfraestructuraH1AccesoAereoTipoDist
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoAereoTipoDist(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoAereoTipo $n34InfraestructuraH1AccesoAereoTipoDist = null)
    {
        $this->n34InfraestructuraH1AccesoAereoTipoDist = $n34InfraestructuraH1AccesoAereoTipoDist;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoAereoTipoDist
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoAereoTipo 
     */
    public function getN34InfraestructuraH1AccesoAereoTipoDist()
    {
        return $this->n34InfraestructuraH1AccesoAereoTipoDist;
    }

    /**
     * Set n34InfraestructuraH1AccesoFluvialTipoDist2
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo $n34InfraestructuraH1AccesoFluvialTipoDist2
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoFluvialTipoDist2(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo $n34InfraestructuraH1AccesoFluvialTipoDist2 = null)
    {
        $this->n34InfraestructuraH1AccesoFluvialTipoDist2 = $n34InfraestructuraH1AccesoFluvialTipoDist2;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoFluvialTipoDist2
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo 
     */
    public function getN34InfraestructuraH1AccesoFluvialTipoDist2()
    {
        return $this->n34InfraestructuraH1AccesoFluvialTipoDist2;
    }

    /**
     * Set n34InfraestructuraH1AccesoFluvialTipoDist1
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo $n34InfraestructuraH1AccesoFluvialTipoDist1
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoFluvialTipoDist1(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo $n34InfraestructuraH1AccesoFluvialTipoDist1 = null)
    {
        $this->n34InfraestructuraH1AccesoFluvialTipoDist1 = $n34InfraestructuraH1AccesoFluvialTipoDist1;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoFluvialTipoDist1
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoFluvialTipo 
     */
    public function getN34InfraestructuraH1AccesoFluvialTipoDist1()
    {
        return $this->n34InfraestructuraH1AccesoFluvialTipoDist1;
    }

    /**
     * Set n34InfraestructuraH1AccesoVehicularTipoDist3
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoDist3
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoVehicularTipoDist3(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoDist3 = null)
    {
        $this->n34InfraestructuraH1AccesoVehicularTipoDist3 = $n34InfraestructuraH1AccesoVehicularTipoDist3;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoVehicularTipoDist3
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo 
     */
    public function getN34InfraestructuraH1AccesoVehicularTipoDist3()
    {
        return $this->n34InfraestructuraH1AccesoVehicularTipoDist3;
    }

    /**
     * Set n34InfraestructuraH1AccesoVehicularTipoDist2
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoDist2
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoVehicularTipoDist2(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoDist2 = null)
    {
        $this->n34InfraestructuraH1AccesoVehicularTipoDist2 = $n34InfraestructuraH1AccesoVehicularTipoDist2;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoVehicularTipoDist2
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo 
     */
    public function getN34InfraestructuraH1AccesoVehicularTipoDist2()
    {
        return $this->n34InfraestructuraH1AccesoVehicularTipoDist2;
    }

    /**
     * Set n34InfraestructuraH1AccesoVehicularTipoDist1
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoDist1
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN34InfraestructuraH1AccesoVehicularTipoDist1(\Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo $n34InfraestructuraH1AccesoVehicularTipoDist1 = null)
    {
        $this->n34InfraestructuraH1AccesoVehicularTipoDist1 = $n34InfraestructuraH1AccesoVehicularTipoDist1;
    
        return $this;
    }

    /**
     * Get n34InfraestructuraH1AccesoVehicularTipoDist1
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1AccesoVehicularTipo 
     */
    public function getN34InfraestructuraH1AccesoVehicularTipoDist1()
    {
        return $this->n34InfraestructuraH1AccesoVehicularTipoDist1;
    }

    /**
     * Set n110InfraestructuraH1DireccionTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH1DireccionTipo $n110InfraestructuraH1DireccionTipo
     * @return InfraestructuraH1Datosgenerales
     */
    public function setN110InfraestructuraH1DireccionTipo(\Sie\AppWebBundle\Entity\InfraestructuraH1DireccionTipo $n110InfraestructuraH1DireccionTipo = null)
    {
        $this->n110InfraestructuraH1DireccionTipo = $n110InfraestructuraH1DireccionTipo;
    
        return $this;
    }

    /**
     * Get n110InfraestructuraH1DireccionTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH1DireccionTipo 
     */
    public function getN110InfraestructuraH1DireccionTipo()
    {
        return $this->n110InfraestructuraH1DireccionTipo;
    }
}
