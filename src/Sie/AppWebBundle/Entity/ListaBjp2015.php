<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ListaBjp2015
 */
class ListaBjp2015
{
    /**
     * @var string
     */
    private $codUe;

    /**
     * @var float
     */
    private $codDep;

    /**
     * @var string
     */
    private $depto;

    /**
     * @var string
     */
    private $codPro;

    /**
     * @var string
     */
    private $prov;

    /**
     * @var string
     */
    private $codMun;

    /**
     * @var string
     */
    private $municipio;

    /**
     * @var float
     */
    private $codCan;

    /**
     * @var string
     */
    private $canton;

    /**
     * @var string
     */
    private $codLoc;

    /**
     * @var string
     */
    private $localidad;

    /**
     * @var string
     */
    private $dependencia;

    /**
     * @var string
     */
    private $area;

    /**
     * @var string
     */
    private $codLe;

    /**
     * @var string
     */
    private $unidadEducativa;

    /**
     * @var float
     */
    private $codDis;

    /**
     * @var string
     */
    private $distrito;

    /**
     * @var string
     */
    private $zona;

    /**
     * @var string
     */
    private $direccion;

    /**
     * @var string
     */
    private $carnetDir;

    /**
     * @var string
     */
    private $director;

    /**
     * @var float
     */
    private $matpri1;

    /**
     * @var float
     */
    private $matpri2;

    /**
     * @var float
     */
    private $matpri3;

    /**
     * @var float
     */
    private $matpri4;

    /**
     * @var float
     */
    private $matpri5;

    /**
     * @var float
     */
    private $matpri6;

    /**
     * @var float
     */
    private $subTotmatpri;

    /**
     * @var float
     */
    private $matsec1;

    /**
     * @var float
     */
    private $matsec2;

    /**
     * @var float
     */
    private $matsec3;

    /**
     * @var float
     */
    private $matsec4;

    /**
     * @var float
     */
    private $matsec5;

    /**
     * @var float
     */
    private $matsec6;

    /**
     * @var float
     */
    private $subTotmatsec;

    /**
     * @var float
     */
    private $totalMat;


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
     * Set codDep
     *
     * @param float $codDep
     * @return ListaBjp2015
     */
    public function setCodDep($codDep)
    {
        $this->codDep = $codDep;
    
        return $this;
    }

    /**
     * Get codDep
     *
     * @return float 
     */
    public function getCodDep()
    {
        return $this->codDep;
    }

    /**
     * Set depto
     *
     * @param string $depto
     * @return ListaBjp2015
     */
    public function setDepto($depto)
    {
        $this->depto = $depto;
    
        return $this;
    }

    /**
     * Get depto
     *
     * @return string 
     */
    public function getDepto()
    {
        return $this->depto;
    }

    /**
     * Set codPro
     *
     * @param string $codPro
     * @return ListaBjp2015
     */
    public function setCodPro($codPro)
    {
        $this->codPro = $codPro;
    
        return $this;
    }

    /**
     * Get codPro
     *
     * @return string 
     */
    public function getCodPro()
    {
        return $this->codPro;
    }

    /**
     * Set prov
     *
     * @param string $prov
     * @return ListaBjp2015
     */
    public function setProv($prov)
    {
        $this->prov = $prov;
    
        return $this;
    }

    /**
     * Get prov
     *
     * @return string 
     */
    public function getProv()
    {
        return $this->prov;
    }

    /**
     * Set codMun
     *
     * @param string $codMun
     * @return ListaBjp2015
     */
    public function setCodMun($codMun)
    {
        $this->codMun = $codMun;
    
        return $this;
    }

    /**
     * Get codMun
     *
     * @return string 
     */
    public function getCodMun()
    {
        return $this->codMun;
    }

    /**
     * Set municipio
     *
     * @param string $municipio
     * @return ListaBjp2015
     */
    public function setMunicipio($municipio)
    {
        $this->municipio = $municipio;
    
        return $this;
    }

    /**
     * Get municipio
     *
     * @return string 
     */
    public function getMunicipio()
    {
        return $this->municipio;
    }

    /**
     * Set codCan
     *
     * @param float $codCan
     * @return ListaBjp2015
     */
    public function setCodCan($codCan)
    {
        $this->codCan = $codCan;
    
        return $this;
    }

    /**
     * Get codCan
     *
     * @return float 
     */
    public function getCodCan()
    {
        return $this->codCan;
    }

    /**
     * Set canton
     *
     * @param string $canton
     * @return ListaBjp2015
     */
    public function setCanton($canton)
    {
        $this->canton = $canton;
    
        return $this;
    }

    /**
     * Get canton
     *
     * @return string 
     */
    public function getCanton()
    {
        return $this->canton;
    }

    /**
     * Set codLoc
     *
     * @param string $codLoc
     * @return ListaBjp2015
     */
    public function setCodLoc($codLoc)
    {
        $this->codLoc = $codLoc;
    
        return $this;
    }

    /**
     * Get codLoc
     *
     * @return string 
     */
    public function getCodLoc()
    {
        return $this->codLoc;
    }

    /**
     * Set localidad
     *
     * @param string $localidad
     * @return ListaBjp2015
     */
    public function setLocalidad($localidad)
    {
        $this->localidad = $localidad;
    
        return $this;
    }

    /**
     * Get localidad
     *
     * @return string 
     */
    public function getLocalidad()
    {
        return $this->localidad;
    }

    /**
     * Set dependencia
     *
     * @param string $dependencia
     * @return ListaBjp2015
     */
    public function setDependencia($dependencia)
    {
        $this->dependencia = $dependencia;
    
        return $this;
    }

    /**
     * Get dependencia
     *
     * @return string 
     */
    public function getDependencia()
    {
        return $this->dependencia;
    }

    /**
     * Set area
     *
     * @param string $area
     * @return ListaBjp2015
     */
    public function setArea($area)
    {
        $this->area = $area;
    
        return $this;
    }

    /**
     * Get area
     *
     * @return string 
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * Set codLe
     *
     * @param string $codLe
     * @return ListaBjp2015
     */
    public function setCodLe($codLe)
    {
        $this->codLe = $codLe;
    
        return $this;
    }

    /**
     * Get codLe
     *
     * @return string 
     */
    public function getCodLe()
    {
        return $this->codLe;
    }

    /**
     * Set unidadEducativa
     *
     * @param string $unidadEducativa
     * @return ListaBjp2015
     */
    public function setUnidadEducativa($unidadEducativa)
    {
        $this->unidadEducativa = $unidadEducativa;
    
        return $this;
    }

    /**
     * Get unidadEducativa
     *
     * @return string 
     */
    public function getUnidadEducativa()
    {
        return $this->unidadEducativa;
    }

    /**
     * Set codDis
     *
     * @param float $codDis
     * @return ListaBjp2015
     */
    public function setCodDis($codDis)
    {
        $this->codDis = $codDis;
    
        return $this;
    }

    /**
     * Get codDis
     *
     * @return float 
     */
    public function getCodDis()
    {
        return $this->codDis;
    }

    /**
     * Set distrito
     *
     * @param string $distrito
     * @return ListaBjp2015
     */
    public function setDistrito($distrito)
    {
        $this->distrito = $distrito;
    
        return $this;
    }

    /**
     * Get distrito
     *
     * @return string 
     */
    public function getDistrito()
    {
        return $this->distrito;
    }

    /**
     * Set zona
     *
     * @param string $zona
     * @return ListaBjp2015
     */
    public function setZona($zona)
    {
        $this->zona = $zona;
    
        return $this;
    }

    /**
     * Get zona
     *
     * @return string 
     */
    public function getZona()
    {
        return $this->zona;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return ListaBjp2015
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    
        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set carnetDir
     *
     * @param string $carnetDir
     * @return ListaBjp2015
     */
    public function setCarnetDir($carnetDir)
    {
        $this->carnetDir = $carnetDir;
    
        return $this;
    }

    /**
     * Get carnetDir
     *
     * @return string 
     */
    public function getCarnetDir()
    {
        return $this->carnetDir;
    }

    /**
     * Set director
     *
     * @param string $director
     * @return ListaBjp2015
     */
    public function setDirector($director)
    {
        $this->director = $director;
    
        return $this;
    }

    /**
     * Get director
     *
     * @return string 
     */
    public function getDirector()
    {
        return $this->director;
    }

    /**
     * Set matpri1
     *
     * @param float $matpri1
     * @return ListaBjp2015
     */
    public function setMatpri1($matpri1)
    {
        $this->matpri1 = $matpri1;
    
        return $this;
    }

    /**
     * Get matpri1
     *
     * @return float 
     */
    public function getMatpri1()
    {
        return $this->matpri1;
    }

    /**
     * Set matpri2
     *
     * @param float $matpri2
     * @return ListaBjp2015
     */
    public function setMatpri2($matpri2)
    {
        $this->matpri2 = $matpri2;
    
        return $this;
    }

    /**
     * Get matpri2
     *
     * @return float 
     */
    public function getMatpri2()
    {
        return $this->matpri2;
    }

    /**
     * Set matpri3
     *
     * @param float $matpri3
     * @return ListaBjp2015
     */
    public function setMatpri3($matpri3)
    {
        $this->matpri3 = $matpri3;
    
        return $this;
    }

    /**
     * Get matpri3
     *
     * @return float 
     */
    public function getMatpri3()
    {
        return $this->matpri3;
    }

    /**
     * Set matpri4
     *
     * @param float $matpri4
     * @return ListaBjp2015
     */
    public function setMatpri4($matpri4)
    {
        $this->matpri4 = $matpri4;
    
        return $this;
    }

    /**
     * Get matpri4
     *
     * @return float 
     */
    public function getMatpri4()
    {
        return $this->matpri4;
    }

    /**
     * Set matpri5
     *
     * @param float $matpri5
     * @return ListaBjp2015
     */
    public function setMatpri5($matpri5)
    {
        $this->matpri5 = $matpri5;
    
        return $this;
    }

    /**
     * Get matpri5
     *
     * @return float 
     */
    public function getMatpri5()
    {
        return $this->matpri5;
    }

    /**
     * Set matpri6
     *
     * @param float $matpri6
     * @return ListaBjp2015
     */
    public function setMatpri6($matpri6)
    {
        $this->matpri6 = $matpri6;
    
        return $this;
    }

    /**
     * Get matpri6
     *
     * @return float 
     */
    public function getMatpri6()
    {
        return $this->matpri6;
    }

    /**
     * Set subTotmatpri
     *
     * @param float $subTotmatpri
     * @return ListaBjp2015
     */
    public function setSubTotmatpri($subTotmatpri)
    {
        $this->subTotmatpri = $subTotmatpri;
    
        return $this;
    }

    /**
     * Get subTotmatpri
     *
     * @return float 
     */
    public function getSubTotmatpri()
    {
        return $this->subTotmatpri;
    }

    /**
     * Set matsec1
     *
     * @param float $matsec1
     * @return ListaBjp2015
     */
    public function setMatsec1($matsec1)
    {
        $this->matsec1 = $matsec1;
    
        return $this;
    }

    /**
     * Get matsec1
     *
     * @return float 
     */
    public function getMatsec1()
    {
        return $this->matsec1;
    }

    /**
     * Set matsec2
     *
     * @param float $matsec2
     * @return ListaBjp2015
     */
    public function setMatsec2($matsec2)
    {
        $this->matsec2 = $matsec2;
    
        return $this;
    }

    /**
     * Get matsec2
     *
     * @return float 
     */
    public function getMatsec2()
    {
        return $this->matsec2;
    }

    /**
     * Set matsec3
     *
     * @param float $matsec3
     * @return ListaBjp2015
     */
    public function setMatsec3($matsec3)
    {
        $this->matsec3 = $matsec3;
    
        return $this;
    }

    /**
     * Get matsec3
     *
     * @return float 
     */
    public function getMatsec3()
    {
        return $this->matsec3;
    }

    /**
     * Set matsec4
     *
     * @param float $matsec4
     * @return ListaBjp2015
     */
    public function setMatsec4($matsec4)
    {
        $this->matsec4 = $matsec4;
    
        return $this;
    }

    /**
     * Get matsec4
     *
     * @return float 
     */
    public function getMatsec4()
    {
        return $this->matsec4;
    }

    /**
     * Set matsec5
     *
     * @param float $matsec5
     * @return ListaBjp2015
     */
    public function setMatsec5($matsec5)
    {
        $this->matsec5 = $matsec5;
    
        return $this;
    }

    /**
     * Get matsec5
     *
     * @return float 
     */
    public function getMatsec5()
    {
        return $this->matsec5;
    }

    /**
     * Set matsec6
     *
     * @param float $matsec6
     * @return ListaBjp2015
     */
    public function setMatsec6($matsec6)
    {
        $this->matsec6 = $matsec6;
    
        return $this;
    }

    /**
     * Get matsec6
     *
     * @return float 
     */
    public function getMatsec6()
    {
        return $this->matsec6;
    }

    /**
     * Set subTotmatsec
     *
     * @param float $subTotmatsec
     * @return ListaBjp2015
     */
    public function setSubTotmatsec($subTotmatsec)
    {
        $this->subTotmatsec = $subTotmatsec;
    
        return $this;
    }

    /**
     * Get subTotmatsec
     *
     * @return float 
     */
    public function getSubTotmatsec()
    {
        return $this->subTotmatsec;
    }

    /**
     * Set totalMat
     *
     * @param float $totalMat
     * @return ListaBjp2015
     */
    public function setTotalMat($totalMat)
    {
        $this->totalMat = $totalMat;
    
        return $this;
    }

    /**
     * Get totalMat
     *
     * @return float 
     */
    public function getTotalMat()
    {
        return $this->totalMat;
    }
}
