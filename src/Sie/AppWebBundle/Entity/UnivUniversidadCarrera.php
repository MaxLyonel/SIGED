<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivUniversidadCarrera
 */
class UnivUniversidadCarrera
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $univAreaConocimiento;

    /**
     * @var string
     */
    private $carrera;

    /**
     * @var string
     */
    private $resolucion;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var string
     */
    private $rmApertura;

    /**
     * @var \DateTime
     */
    private $fechaApertura;

    /**
     * @var string
     */
    private $duracion;

    /**
     * @var integer
     */
    private $duracionAnios;

    /**
     * @var integer
     */
    private $estado;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivPeriodoAcademicoTipo
     */
    private $univPeriodoAcademicoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivModalidadEnsenanzaTipo
     */
    private $univModalidadEnsenanzaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivRegimenEstudiosTipo
     */
    private $univRegimenEstudiosTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivNivelAcademicoTipo
     */
    private $univNivelAcademicoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivGradoacademicoTipo
     */
    private $univGradoacademicoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivSede
     */
    private $univSede;


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
     * Set univAreaConocimiento
     *
     * @param string $univAreaConocimiento
     * @return UnivUniversidadCarrera
     */
    public function setUnivAreaConocimiento($univAreaConocimiento)
    {
        $this->univAreaConocimiento = $univAreaConocimiento;
    
        return $this;
    }

    /**
     * Get univAreaConocimiento
     *
     * @return string 
     */
    public function getUnivAreaConocimiento()
    {
        return $this->univAreaConocimiento;
    }

    /**
     * Set carrera
     *
     * @param string $carrera
     * @return UnivUniversidadCarrera
     */
    public function setCarrera($carrera)
    {
        $this->carrera = $carrera;
    
        return $this;
    }

    /**
     * Get carrera
     *
     * @return string 
     */
    public function getCarrera()
    {
        return $this->carrera;
    }

    /**
     * Set resolucion
     *
     * @param string $resolucion
     * @return UnivUniversidadCarrera
     */
    public function setResolucion($resolucion)
    {
        $this->resolucion = $resolucion;
    
        return $this;
    }

    /**
     * Get resolucion
     *
     * @return string 
     */
    public function getResolucion()
    {
        return $this->resolucion;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return UnivUniversidadCarrera
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set rmApertura
     *
     * @param string $rmApertura
     * @return UnivUniversidadCarrera
     */
    public function setRmApertura($rmApertura)
    {
        $this->rmApertura = $rmApertura;
    
        return $this;
    }

    /**
     * Get rmApertura
     *
     * @return string 
     */
    public function getRmApertura()
    {
        return $this->rmApertura;
    }

    /**
     * Set fechaApertura
     *
     * @param \DateTime $fechaApertura
     * @return UnivUniversidadCarrera
     */
    public function setFechaApertura($fechaApertura)
    {
        $this->fechaApertura = $fechaApertura;
    
        return $this;
    }

    /**
     * Get fechaApertura
     *
     * @return \DateTime 
     */
    public function getFechaApertura()
    {
        return $this->fechaApertura;
    }

    /**
     * Set duracion
     *
     * @param string $duracion
     * @return UnivUniversidadCarrera
     */
    public function setDuracion($duracion)
    {
        $this->duracion = $duracion;
    
        return $this;
    }

    /**
     * Get duracion
     *
     * @return string 
     */
    public function getDuracion()
    {
        return $this->duracion;
    }

    /**
     * Set duracionAnios
     *
     * @param integer $duracionAnios
     * @return UnivUniversidadCarrera
     */
    public function setDuracionAnios($duracionAnios)
    {
        $this->duracionAnios = $duracionAnios;
    
        return $this;
    }

    /**
     * Get duracionAnios
     *
     * @return integer 
     */
    public function getDuracionAnios()
    {
        return $this->duracionAnios;
    }

    /**
     * Set estado
     *
     * @param integer $estado
     * @return UnivUniversidadCarrera
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return integer 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return UnivUniversidadCarrera
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
     * @return UnivUniversidadCarrera
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
     * Set univPeriodoAcademicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivPeriodoAcademicoTipo $univPeriodoAcademicoTipo
     * @return UnivUniversidadCarrera
     */
    public function setUnivPeriodoAcademicoTipo(\Sie\AppWebBundle\Entity\UnivPeriodoAcademicoTipo $univPeriodoAcademicoTipo = null)
    {
        $this->univPeriodoAcademicoTipo = $univPeriodoAcademicoTipo;
    
        return $this;
    }

    /**
     * Get univPeriodoAcademicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivPeriodoAcademicoTipo 
     */
    public function getUnivPeriodoAcademicoTipo()
    {
        return $this->univPeriodoAcademicoTipo;
    }

    /**
     * Set univModalidadEnsenanzaTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivModalidadEnsenanzaTipo $univModalidadEnsenanzaTipo
     * @return UnivUniversidadCarrera
     */
    public function setUnivModalidadEnsenanzaTipo(\Sie\AppWebBundle\Entity\UnivModalidadEnsenanzaTipo $univModalidadEnsenanzaTipo = null)
    {
        $this->univModalidadEnsenanzaTipo = $univModalidadEnsenanzaTipo;
    
        return $this;
    }

    /**
     * Get univModalidadEnsenanzaTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivModalidadEnsenanzaTipo 
     */
    public function getUnivModalidadEnsenanzaTipo()
    {
        return $this->univModalidadEnsenanzaTipo;
    }

    /**
     * Set univRegimenEstudiosTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivRegimenEstudiosTipo $univRegimenEstudiosTipo
     * @return UnivUniversidadCarrera
     */
    public function setUnivRegimenEstudiosTipo(\Sie\AppWebBundle\Entity\UnivRegimenEstudiosTipo $univRegimenEstudiosTipo = null)
    {
        $this->univRegimenEstudiosTipo = $univRegimenEstudiosTipo;
    
        return $this;
    }

    /**
     * Get univRegimenEstudiosTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivRegimenEstudiosTipo 
     */
    public function getUnivRegimenEstudiosTipo()
    {
        return $this->univRegimenEstudiosTipo;
    }

    /**
     * Set univNivelAcademicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivNivelAcademicoTipo $univNivelAcademicoTipo
     * @return UnivUniversidadCarrera
     */
    public function setUnivNivelAcademicoTipo(\Sie\AppWebBundle\Entity\UnivNivelAcademicoTipo $univNivelAcademicoTipo = null)
    {
        $this->univNivelAcademicoTipo = $univNivelAcademicoTipo;
    
        return $this;
    }

    /**
     * Get univNivelAcademicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivNivelAcademicoTipo 
     */
    public function getUnivNivelAcademicoTipo()
    {
        return $this->univNivelAcademicoTipo;
    }

    /**
     * Set univGradoacademicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivGradoacademicoTipo $univGradoacademicoTipo
     * @return UnivUniversidadCarrera
     */
    public function setUnivGradoacademicoTipo(\Sie\AppWebBundle\Entity\UnivGradoacademicoTipo $univGradoacademicoTipo = null)
    {
        $this->univGradoacademicoTipo = $univGradoacademicoTipo;
    
        return $this;
    }

    /**
     * Get univGradoacademicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivGradoacademicoTipo 
     */
    public function getUnivGradoacademicoTipo()
    {
        return $this->univGradoacademicoTipo;
    }

    /**
     * Set univSede
     *
     * @param \Sie\AppWebBundle\Entity\UnivSede $univSede
     * @return UnivUniversidadCarrera
     */
    public function setUnivSede(\Sie\AppWebBundle\Entity\UnivSede $univSede = null)
    {
        $this->univSede = $univSede;
    
        return $this;
    }

    /**
     * Get univSede
     *
     * @return \Sie\AppWebBundle\Entity\UnivSede 
     */
    public function getUnivSede()
    {
        return $this->univSede;
    }
}
