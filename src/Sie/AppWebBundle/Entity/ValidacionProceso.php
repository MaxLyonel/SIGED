<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ValidacionProceso
 */
class ValidacionProceso
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaProceso;

    /**
     * @var string
     */
    private $llave;

    /**
     * @var boolean
     */
    private $esActivo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\PeriodoTipo
     */
    private $periodoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ValidacionReglaTipo
     */
    private $validacionReglaTipo;


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
     * Set fechaProceso
     *
     * @param \DateTime $fechaProceso
     * @return ValidacionProceso
     */
    public function setFechaProceso($fechaProceso)
    {
        $this->fechaProceso = $fechaProceso;
    
        return $this;
    }

    /**
     * Get fechaProceso
     *
     * @return \DateTime 
     */
    public function getFechaProceso()
    {
        return $this->fechaProceso;
    }

    /**
     * Set llave
     *
     * @param string $llave
     * @return ValidacionProceso
     */
    public function setLlave($llave)
    {
        $this->llave = $llave;
    
        return $this;
    }

    /**
     * Get llave
     *
     * @return string 
     */
    public function getLlave()
    {
        return $this->llave;
    }

    /**
     * Set esActivo
     *
     * @param boolean $esActivo
     * @return ValidacionProceso
     */
    public function setEsActivo($esActivo)
    {
        $this->esActivo = $esActivo;
    
        return $this;
    }

    /**
     * Get esActivo
     *
     * @return boolean 
     */
    public function getEsActivo()
    {
        return $this->esActivo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return ValidacionProceso
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
     * Set periodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\PeriodoTipo $periodoTipo
     * @return ValidacionProceso
     */
    public function setPeriodoTipo(\Sie\AppWebBundle\Entity\PeriodoTipo $periodoTipo = null)
    {
        $this->periodoTipo = $periodoTipo;
    
        return $this;
    }

    /**
     * Get periodoTipo
     *
     * @return \Sie\AppWebBundle\Entity\PeriodoTipo 
     */
    public function getPeriodoTipo()
    {
        return $this->periodoTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return ValidacionProceso
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }

    /**
     * Set validacionReglaTipo
     *
     * @param \Sie\AppWebBundle\Entity\ValidacionReglaTipo $validacionReglaTipo
     * @return ValidacionProceso
     */
    public function setValidacionReglaTipo(\Sie\AppWebBundle\Entity\ValidacionReglaTipo $validacionReglaTipo = null)
    {
        $this->validacionReglaTipo = $validacionReglaTipo;
    
        return $this;
    }

    /**
     * Get validacionReglaTipo
     *
     * @return \Sie\AppWebBundle\Entity\ValidacionReglaTipo 
     */
    public function getValidacionReglaTipo()
    {
        return $this->validacionReglaTipo;
    }
    /**
     * @var integer
     */
    private $validacionReglaTipoId;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var integer
     */
    private $periodoTipoId;


    /**
     * Set validacionReglaTipoId
     *
     * @param integer $validacionReglaTipoId
     * @return ValidacionProceso
     */
    public function setValidacionReglaTipoId($validacionReglaTipoId)
    {
        $this->validacionReglaTipoId = $validacionReglaTipoId;
    
        return $this;
    }

    /**
     * Get validacionReglaTipoId
     *
     * @return integer 
     */
    public function getValidacionReglaTipoId()
    {
        return $this->validacionReglaTipoId;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return ValidacionProceso
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }

    /**
     * Set periodoTipoId
     *
     * @param integer $periodoTipoId
     * @return ValidacionProceso
     */
    public function setPeriodoTipoId($periodoTipoId)
    {
        $this->periodoTipoId = $periodoTipoId;
    
        return $this;
    }

    /**
     * Get periodoTipoId
     *
     * @return integer 
     */
    public function getPeriodoTipoId()
    {
        return $this->periodoTipoId;
    }
    /**
     * @var integer
     */
    private $institucionEducativaId;


    /**
     * Set institucionEducativaId
     *
     * @param integer $institucionEducativaId
     * @return ValidacionProceso
     */
    public function setInstitucionEducativaId($institucionEducativaId)
    {
        $this->institucionEducativaId = $institucionEducativaId;
    
        return $this;
    }

    /**
     * Get institucionEducativaId
     *
     * @return integer 
     */
    public function getInstitucionEducativaId()
    {
        return $this->institucionEducativaId;
    }
    /**
     * @var integer
     */
    private $lugarTipoIdDistrito;

    /**
     * @var integer
     */
    private $solucionTipoId;


    /**
     * Set lugarTipoIdDistrito
     *
     * @param integer $lugarTipoIdDistrito
     * @return ValidacionProceso
     */
    public function setLugarTipoIdDistrito($lugarTipoIdDistrito)
    {
        $this->lugarTipoIdDistrito = $lugarTipoIdDistrito;
    
        return $this;
    }

    /**
     * Get lugarTipoIdDistrito
     *
     * @return integer 
     */
    public function getLugarTipoIdDistrito()
    {
        return $this->lugarTipoIdDistrito;
    }

    /**
     * Set solucionTipoId
     *
     * @param integer $solucionTipoId
     * @return ValidacionProceso
     */
    public function setSolucionTipoId($solucionTipoId)
    {
        $this->solucionTipoId = $solucionTipoId;
    
        return $this;
    }

    /**
     * Get solucionTipoId
     *
     * @return integer 
     */
    public function getSolucionTipoId()
    {
        return $this->solucionTipoId;
    }
    /**
     * @var integer
     */
    private $omitido;


    /**
     * Set omitido
     *
     * @param integer $omitido
     * @return ValidacionProceso
     */
    public function setOmitido($omitido)
    {
        $this->omitido = $omitido;
    
        return $this;
    }

    /**
     * Get omitido
     *
     * @return integer 
     */
    public function getOmitido()
    {
        return $this->omitido;
    }
}
