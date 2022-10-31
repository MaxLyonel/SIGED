<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaCursoCorto
 */
class InstitucioneducativaCursoCorto
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $duracionhoras;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaConclusion;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var string
     */
    private $lugar;

    /**
     * @var integer
     */
    private $numero;

    /**
     * @var string
     */
    private $curso;

    /**
     * @var string
     */
    private $poblacionDetalle;

    /**
     * @var boolean
     */
    private $esabierto;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\AreatematicaTipo
     */
    private $areatematicaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\PoblacionTipo
     */
    private $poblacionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $lugarTipoMunicipio;


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
     * Set duracionhoras
     *
     * @param integer $duracionhoras
     * @return InstitucioneducativaCursoCorto
     */
    public function setDuracionhoras($duracionhoras)
    {
        $this->duracionhoras = $duracionhoras;
    
        return $this;
    }

    /**
     * Get duracionhoras
     *
     * @return integer 
     */
    public function getDuracionhoras()
    {
        return $this->duracionhoras;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return InstitucioneducativaCursoCorto
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    
        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime 
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaConclusion
     *
     * @param \DateTime $fechaConclusion
     * @return InstitucioneducativaCursoCorto
     */
    public function setFechaConclusion($fechaConclusion)
    {
        $this->fechaConclusion = $fechaConclusion;
    
        return $this;
    }

    /**
     * Get fechaConclusion
     *
     * @return \DateTime 
     */
    public function getFechaConclusion()
    {
        return $this->fechaConclusion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InstitucioneducativaCursoCorto
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
     * Set lugar
     *
     * @param string $lugar
     * @return InstitucioneducativaCursoCorto
     */
    public function setLugar($lugar)
    {
        $this->lugar = $lugar;
    
        return $this;
    }

    /**
     * Get lugar
     *
     * @return string 
     */
    public function getLugar()
    {
        return $this->lugar;
    }

    /**
     * Set numero
     *
     * @param integer $numero
     * @return InstitucioneducativaCursoCorto
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;
    
        return $this;
    }

    /**
     * Get numero
     *
     * @return integer 
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set curso
     *
     * @param string $curso
     * @return InstitucioneducativaCursoCorto
     */
    public function setCurso($curso)
    {
        $this->curso = $curso;
    
        return $this;
    }

    /**
     * Get curso
     *
     * @return string 
     */
    public function getCurso()
    {
        return $this->curso;
    }

    /**
     * Set poblacionDetalle
     *
     * @param string $poblacionDetalle
     * @return InstitucioneducativaCursoCorto
     */
    public function setPoblacionDetalle($poblacionDetalle)
    {
        $this->poblacionDetalle = $poblacionDetalle;
    
        return $this;
    }

    /**
     * Get poblacionDetalle
     *
     * @return string 
     */
    public function getPoblacionDetalle()
    {
        return $this->poblacionDetalle;
    }

    /**
     * Set esabierto
     *
     * @param boolean $esabierto
     * @return InstitucioneducativaCursoCorto
     */
    public function setEsabierto($esabierto)
    {
        $this->esabierto = $esabierto;
    
        return $this;
    }

    /**
     * Get esabierto
     *
     * @return boolean 
     */
    public function getEsabierto()
    {
        return $this->esabierto;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaCursoCorto
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
     * Set areatematicaTipo
     *
     * @param \Sie\AppWebBundle\Entity\AreatematicaTipo $areatematicaTipo
     * @return InstitucioneducativaCursoCorto
     */
    public function setAreatematicaTipo(\Sie\AppWebBundle\Entity\AreatematicaTipo $areatematicaTipo = null)
    {
        $this->areatematicaTipo = $areatematicaTipo;
    
        return $this;
    }

    /**
     * Get areatematicaTipo
     *
     * @return \Sie\AppWebBundle\Entity\AreatematicaTipo 
     */
    public function getAreatematicaTipo()
    {
        return $this->areatematicaTipo;
    }

    /**
     * Set poblacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\PoblacionTipo $poblacionTipo
     * @return InstitucioneducativaCursoCorto
     */
    public function setPoblacionTipo(\Sie\AppWebBundle\Entity\PoblacionTipo $poblacionTipo = null)
    {
        $this->poblacionTipo = $poblacionTipo;
    
        return $this;
    }

    /**
     * Get poblacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\PoblacionTipo 
     */
    public function getPoblacionTipo()
    {
        return $this->poblacionTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return InstitucioneducativaCursoCorto
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
     * Set lugarTipoMunicipio
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $lugarTipoMunicipio
     * @return InstitucioneducativaCursoCorto
     */
    public function setLugarTipoMunicipio(\Sie\AppWebBundle\Entity\LugarTipo $lugarTipoMunicipio = null)
    {
        $this->lugarTipoMunicipio = $lugarTipoMunicipio;
    
        return $this;
    }

    /**
     * Get lugarTipoMunicipio
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getLugarTipoMunicipio()
    {
        return $this->lugarTipoMunicipio;
    }
}
