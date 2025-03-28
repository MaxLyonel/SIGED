<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LugarTipo
 */
class LugarTipo {

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $paisTipoId;

    /**
     * @var string
     */
    private $codigo;

    /**
     * @var string
     */
    private $lugar;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\DepartamentoTipo
     */
    private $departamentoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarNivelTipo
     */
    private $lugarNivel;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $lugarTipo;

    public function __toString() {
        return $this->lugar;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set paisTipoId
     *
     * @param integer $paisTipoId
     * @return LugarTipo
     */
    public function setPaisTipoId($paisTipoId) {
        $this->paisTipoId = $paisTipoId;

        return $this;
    }

    /**
     * Get paisTipoId
     *
     * @return integer 
     */
    public function getPaisTipoId() {
        return $this->paisTipoId;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     * @return LugarTipo
     */
    public function setCodigo($codigo) {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo() {
        return $this->codigo;
    }

    /**
     * Set lugar
     *
     * @param string $lugar
     * @return LugarTipo
     */
    public function setLugar($lugar) {
        $this->lugar = $lugar;

        return $this;
    }

    /**
     * Get lugar
     *
     * @return string 
     */
    public function getLugar() {
        return $this->lugar;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return LugarTipo
     */
    public function setObs($obs) {
        $this->obs = $obs;

        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs() {
        return $this->obs;
    }

    /**
     * Set departamentoTipo
     *
     * @param \Sie\AppWebBundle\Entity\DepartamentoTipo $departamentoTipo
     * @return LugarTipo
     */
    public function setDepartamentoTipo(\Sie\AppWebBundle\Entity\DepartamentoTipo $departamentoTipo = null) {
        $this->departamentoTipo = $departamentoTipo;

        return $this;
    }

    /**
     * Get departamentoTipo
     *
     * @return \Sie\AppWebBundle\Entity\DepartamentoTipo 
     */
    public function getDepartamentoTipo() {
        return $this->departamentoTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return LugarTipo
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null) {
        $this->gestionTipo = $gestionTipo;

        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo() {
        return $this->gestionTipo;
    }

    /**
     * Set lugarNivel
     *
     * @param \Sie\AppWebBundle\Entity\LugarNivelTipo $lugarNivel
     * @return LugarTipo
     */
    public function setLugarNivel(\Sie\AppWebBundle\Entity\LugarNivelTipo $lugarNivel = null) {
        $this->lugarNivel = $lugarNivel;

        return $this;
    }

    /**
     * Get lugarNivel
     *
     * @return \Sie\AppWebBundle\Entity\LugarNivelTipo 
     */
    public function getLugarNivel() {
        return $this->lugarNivel;
    }

    /**
     * Set lugarTipo
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $lugarTipo
     * @return LugarTipo
     */
    public function setLugarTipo(\Sie\AppWebBundle\Entity\LugarTipo $lugarTipo = null) {
        $this->lugarTipo = $lugarTipo;

        return $this;
    }

    /**
     * Get lugarTipo
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getLugarTipo() {
        return $this->lugarTipo;
    }

    /**
     * @var string
     */
    private $area2001;

    /**
     * @var string
     */
    private $area2012;

    /**
     * Set area2001
     *
     * @param string $area2001
     * @return LugarTipo
     */
    public function setArea2001($area2001) {
        $this->area2001 = $area2001;

        return $this;
    }

    /**
     * Get area2001
     *
     * @return string 
     */
    public function getArea2001() {
        return $this->area2001;
    }

    /**
     * Set area2012
     *
     * @param string $area2012
     * @return LugarTipo
     */
    public function setArea2012($area2012) {
        $this->area2012 = $area2012;

        return $this;
    }

    /**
     * Get area2012
     *
     * @return string 
     */
    public function getArea2012() {
        return $this->area2012;
    }

    /**
     * @var string
     */
    private $areaDistrito;

    /**
     * @var integer
     */
    private $poblacion;

    /**
     * @var integer
     */
    private $viviendas;


    /**
     * Set areaDistrito
     *
     * @param string $areaDistrito
     * @return LugarTipo
     */
    public function setAreaDistrito($areaDistrito)
    {
        $this->areaDistrito = $areaDistrito;
    
        return $this;
    }

    /**
     * Get areaDistrito
     *
     * @return string 
     */
    public function getAreaDistrito()
    {
        return $this->areaDistrito;
    }

    /**
     * Set poblacion
     *
     * @param integer $poblacion
     * @return LugarTipo
     */
    public function setPoblacion($poblacion)
    {
        $this->poblacion = $poblacion;
    
        return $this;
    }

    /**
     * Get poblacion
     *
     * @return integer 
     */
    public function getPoblacion()
    {
        return $this->poblacion;
    }

    /**
     * Set viviendas
     *
     * @param integer $viviendas
     * @return LugarTipo
     */
    public function setViviendas($viviendas)
    {
        $this->viviendas = $viviendas;
    
        return $this;
    }

    /**
     * Get viviendas
     *
     * @return integer 
     */
    public function getViviendas()
    {
        return $this->viviendas;
    }
}
