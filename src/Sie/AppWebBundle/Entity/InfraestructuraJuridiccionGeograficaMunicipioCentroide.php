<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraJuridiccionGeograficaMunicipioCentroide
 */
class InfraestructuraJuridiccionGeograficaMunicipioCentroide
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $depto;

    /**
     * @var string
     */
    private $prov;

    /**
     * @var string
     */
    private $mun;

    /**
     * @var integer
     */
    private $pob;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var float
     */
    private $latitudX;

    /**
     * @var float
     */
    private $longitudY;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $lugarTipoMunicipio2012;


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
     * Set depto
     *
     * @param string $depto
     * @return InfraestructuraJuridiccionGeograficaMunicipioCentroide
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
     * Set prov
     *
     * @param string $prov
     * @return InfraestructuraJuridiccionGeograficaMunicipioCentroide
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
     * Set mun
     *
     * @param string $mun
     * @return InfraestructuraJuridiccionGeograficaMunicipioCentroide
     */
    public function setMun($mun)
    {
        $this->mun = $mun;
    
        return $this;
    }

    /**
     * Get mun
     *
     * @return string 
     */
    public function getMun()
    {
        return $this->mun;
    }

    /**
     * Set pob
     *
     * @param integer $pob
     * @return InfraestructuraJuridiccionGeograficaMunicipioCentroide
     */
    public function setPob($pob)
    {
        $this->pob = $pob;
    
        return $this;
    }

    /**
     * Get pob
     *
     * @return integer 
     */
    public function getPob()
    {
        return $this->pob;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraJuridiccionGeograficaMunicipioCentroide
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
     * @return InfraestructuraJuridiccionGeograficaMunicipioCentroide
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
     * Set latitudX
     *
     * @param float $latitudX
     * @return InfraestructuraJuridiccionGeograficaMunicipioCentroide
     */
    public function setLatitudX($latitudX)
    {
        $this->latitudX = $latitudX;
    
        return $this;
    }

    /**
     * Get latitudX
     *
     * @return float 
     */
    public function getLatitudX()
    {
        return $this->latitudX;
    }

    /**
     * Set longitudY
     *
     * @param float $longitudY
     * @return InfraestructuraJuridiccionGeograficaMunicipioCentroide
     */
    public function setLongitudY($longitudY)
    {
        $this->longitudY = $longitudY;
    
        return $this;
    }

    /**
     * Get longitudY
     *
     * @return float 
     */
    public function getLongitudY()
    {
        return $this->longitudY;
    }

    /**
     * Set lugarTipoMunicipio2012
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $lugarTipoMunicipio2012
     * @return InfraestructuraJuridiccionGeograficaMunicipioCentroide
     */
    public function setLugarTipoMunicipio2012(\Sie\AppWebBundle\Entity\LugarTipo $lugarTipoMunicipio2012 = null)
    {
        $this->lugarTipoMunicipio2012 = $lugarTipoMunicipio2012;
    
        return $this;
    }

    /**
     * Get lugarTipoMunicipio2012
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getLugarTipoMunicipio2012()
    {
        return $this->lugarTipoMunicipio2012;
    }
}
