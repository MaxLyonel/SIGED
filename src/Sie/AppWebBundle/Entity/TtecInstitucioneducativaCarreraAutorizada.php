<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecInstitucioneducativaCarreraAutorizada
 */
class TtecInstitucioneducativaCarreraAutorizada
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecCarreraTipo
     */
    private $ttecCarreraTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo
     */
    private $ttecDenominacionTituloProfesionalTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecResolucionCarrera
     */
    private $ttecResolucionCarrera;


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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecInstitucioneducativaCarreraAutorizada
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
     * @return TtecInstitucioneducativaCarreraAutorizada
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return TtecInstitucioneducativaCarreraAutorizada
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set ttecCarreraTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecCarreraTipo $ttecCarreraTipo
     * @return TtecInstitucioneducativaCarreraAutorizada
     */
    public function setTtecCarreraTipo(\Sie\AppWebBundle\Entity\TtecCarreraTipo $ttecCarreraTipo = null)
    {
        $this->ttecCarreraTipo = $ttecCarreraTipo;
    
        return $this;
    }

    /**
     * Get ttecCarreraTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecCarreraTipo 
     */
    public function getTtecCarreraTipo()
    {
        return $this->ttecCarreraTipo;
    }

    /**
     * Set ttecDenominacionTituloProfesionalTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo $ttecDenominacionTituloProfesionalTipo
     * @return TtecInstitucioneducativaCarreraAutorizada
     */
    public function setTtecDenominacionTituloProfesionalTipo(\Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo $ttecDenominacionTituloProfesionalTipo = null)
    {
        $this->ttecDenominacionTituloProfesionalTipo = $ttecDenominacionTituloProfesionalTipo;
    
        return $this;
    }

    /**
     * Get ttecDenominacionTituloProfesionalTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo 
     */
    public function getTtecDenominacionTituloProfesionalTipo()
    {
        return $this->ttecDenominacionTituloProfesionalTipo;
    }

    /**
     * Set ttecResolucionCarrera
     *
     * @param \Sie\AppWebBundle\Entity\TtecResolucionCarrera $ttecResolucionCarrera
     * @return TtecInstitucioneducativaCarreraAutorizada
     */
    public function setTtecResolucionCarrera(\Sie\AppWebBundle\Entity\TtecResolucionCarrera $ttecResolucionCarrera = null)
    {
        $this->ttecResolucionCarrera = $ttecResolucionCarrera;
    
        return $this;
    }

    /**
     * Get ttecResolucionCarrera
     *
     * @return \Sie\AppWebBundle\Entity\TtecResolucionCarrera 
     */
    public function getTtecResolucionCarrera()
    {
        return $this->ttecResolucionCarrera;
    }
    /**
     * @var boolean
     */
    private $esEnviado;

    /**
     * @var boolean
     */
    private $esVigente;


    /**
     * Set esEnviado
     *
     * @param boolean $esEnviado
     * @return TtecInstitucioneducativaCarreraAutorizada
     */
    public function setEsEnviado($esEnviado)
    {
        $this->esEnviado = $esEnviado;
    
        return $this;
    }

    /**
     * Get esEnviado
     *
     * @return boolean 
     */
    public function getEsEnviado()
    {
        return $this->esEnviado;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return TtecInstitucioneducativaCarreraAutorizada
     */
    public function setEsVigente($esVigente)
    {
        $this->esVigente = $esVigente;
    
        return $this;
    }

    /**
     * Get esVigente
     *
     * @return boolean 
     */
    public function getEsVigente()
    {
        return $this->esVigente;
    }
}
