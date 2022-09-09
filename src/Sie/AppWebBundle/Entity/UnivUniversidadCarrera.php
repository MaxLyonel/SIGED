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
    private $univFacultad;

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
     * @var integer
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
     * @var integer
     */
    private $esSiesu;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivGradoTipo
     */
    private $univGradoAcademicoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivAreaConocimientoTipo
     */
    private $univAreaConocimientoTipo;

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
     * Set univFacultad
     *
     * @param string $univFacultad
     * @return UnivUniversidadCarrera
     */
    public function setUnivFacultad($univFacultad)
    {
        $this->univFacultad = $univFacultad;
    
        return $this;
    }

    /**
     * Get univFacultad
     *
     * @return string 
     */
    public function getUnivFacultad()
    {
        return $this->univFacultad;
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
     * @param integer $duracion
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
     * @return integer 
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
     * Set esSiesu
     *
     * @param integer $esSiesu
     * @return UnivUniversidadCarrera
     */
    public function setEsSiesu($esSiesu)
    {
        $this->esSiesu = $esSiesu;
    
        return $this;
    }

    /**
     * Get esSiesu
     *
     * @return integer 
     */
    public function getEsSiesu()
    {
        return $this->esSiesu;
    }

    /**
     * Set univGradoAcademicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivGradoTipo $univGradoAcademicoTipo
     * @return UnivUniversidadCarrera
     */
    public function setUnivGradoAcademicoTipo(\Sie\AppWebBundle\Entity\UnivGradoTipo $univGradoAcademicoTipo = null)
    {
        $this->univGradoAcademicoTipo = $univGradoAcademicoTipo;
    
        return $this;
    }

    /**
     * Get univGradoAcademicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivGradoTipo 
     */
    public function getUnivGradoAcademicoTipo()
    {
        return $this->univGradoAcademicoTipo;
    }

    /**
     * Set univAreaConocimientoTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivAreaConocimientoTipo $univAreaConocimientoTipo
     * @return UnivUniversidadCarrera
     */
    public function setUnivAreaConocimientoTipo(\Sie\AppWebBundle\Entity\UnivAreaConocimientoTipo $univAreaConocimientoTipo = null)
    {
        $this->univAreaConocimientoTipo = $univAreaConocimientoTipo;
    
        return $this;
    }

    /**
     * Get univAreaConocimientoTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivAreaConocimientoTipo 
     */
    public function getUnivAreaConocimientoTipo()
    {
        return $this->univAreaConocimientoTipo;
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
