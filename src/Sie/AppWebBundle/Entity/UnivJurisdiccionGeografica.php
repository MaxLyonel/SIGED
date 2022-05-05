<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivJurisdiccionGeografica
 */
class UnivJurisdiccionGeografica
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var float
     */
    private $cordx;

    /**
     * @var float
     */
    private $cordy;

    /**
     * @var string
     */
    private $direccion;

    /**
     * @var string
     */
    private $zona;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $lugarTipoLocalidad2012;


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
     * Set obs
     *
     * @param string $obs
     * @return UnivJurisdiccionGeografica
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
     * Set cordx
     *
     * @param float $cordx
     * @return UnivJurisdiccionGeografica
     */
    public function setCordx($cordx)
    {
        $this->cordx = $cordx;
    
        return $this;
    }

    /**
     * Get cordx
     *
     * @return float 
     */
    public function getCordx()
    {
        return $this->cordx;
    }

    /**
     * Set cordy
     *
     * @param float $cordy
     * @return UnivJurisdiccionGeografica
     */
    public function setCordy($cordy)
    {
        $this->cordy = $cordy;
    
        return $this;
    }

    /**
     * Get cordy
     *
     * @return float 
     */
    public function getCordy()
    {
        return $this->cordy;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return UnivJurisdiccionGeografica
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
     * Set zona
     *
     * @param string $zona
     * @return UnivJurisdiccionGeografica
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return UnivJurisdiccionGeografica
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return UnivJurisdiccionGeografica
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set lugarTipoLocalidad2012
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $lugarTipoLocalidad2012
     * @return UnivJurisdiccionGeografica
     */
    public function setLugarTipoLocalidad2012(\Sie\AppWebBundle\Entity\LugarTipo $lugarTipoLocalidad2012 = null)
    {
        $this->lugarTipoLocalidad2012 = $lugarTipoLocalidad2012;
    
        return $this;
    }

    /**
     * Get lugarTipoLocalidad2012
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getLugarTipoLocalidad2012()
    {
        return $this->lugarTipoLocalidad2012;
    }
}
