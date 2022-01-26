<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaCursoDatos
 */
class InstitucioneducativaCursoDatos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $localidad;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaCurso
     */
    private $institucioneducativaCurso;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $lugarTipoSeccion;


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
     * Set localidad
     *
     * @param string $localidad
     * @return InstitucioneducativaCursoDatos
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
     * Set obs
     *
     * @param string $obs
     * @return InstitucioneducativaCursoDatos
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
     * Set institucioneducativaCurso
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCurso $institucioneducativaCurso
     * @return InstitucioneducativaCursoDatos
     */
    public function setInstitucioneducativaCurso(\Sie\AppWebBundle\Entity\InstitucioneducativaCurso $institucioneducativaCurso = null)
    {
        $this->institucioneducativaCurso = $institucioneducativaCurso;
    
        return $this;
    }

    /**
     * Get institucioneducativaCurso
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCurso 
     */
    public function getInstitucioneducativaCurso()
    {
        return $this->institucioneducativaCurso;
    }

    /**
     * Set lugarTipoSeccion
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $lugarTipoSeccion
     * @return InstitucioneducativaCursoDatos
     */
    public function setLugarTipoSeccion(\Sie\AppWebBundle\Entity\LugarTipo $lugarTipoSeccion = null)
    {
        $this->lugarTipoSeccion = $lugarTipoSeccion;
    
        return $this;
    }

    /**
     * Get lugarTipoSeccion
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getLugarTipoSeccion()
    {
        return $this->lugarTipoSeccion;
    }
    /**
     * @var boolean
     */
    private $esactivo;


    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return InstitucioneducativaCursoDatos
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }
    /**
     * @var integer
     */
    private $plancurricularTipoId;


    /**
     * Set plancurricularTipoId
     *
     * @param integer $plancurricularTipoId
     * @return InstitucioneducativaCursoDatos
     */
    public function setPlancurricularTipoId($plancurricularTipoId)
    {
        $this->plancurricularTipoId = $plancurricularTipoId;
    
        return $this;
    }

    /**
     * Get plancurricularTipoId
     *
     * @return integer 
     */
    public function getPlancurricularTipoId()
    {
        return $this->plancurricularTipoId;
    }
    /**
     * @var \DateTime
     */
    private $fechaCerrar;


    /**
     * Set fechaCerrar
     *
     * @param \DateTime $fechaCerrar
     * @return InstitucioneducativaCursoDatos
     */
    public function setFechaCerrar($fechaCerrar)
    {
        $this->fechaCerrar = $fechaCerrar;
    
        return $this;
    }

    /**
     * Get fechaCerrar
     *
     * @return \DateTime 
     */
    public function getFechaCerrar()
    {
        return $this->fechaCerrar;
    }
}
