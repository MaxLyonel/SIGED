<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstTecInstitutoCarrera
 */
class EstTecInstitutoCarrera
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $tiempoEstudio;

    /**
     * @var integer
     */
    private $cargaHoraria;

    /**
     * @var string
     */
    private $carrera;

    /**
     * @var string
     */
    private $resolucion;

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
     * @var \Sie\AppWebBundle\Entity\EstTecAreaFormacionTipo
     */
    private $estTecAreaFormacionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecRegimenEstudioTipo
     */
    private $estTecRegimenEstudioTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecSede
     */
    private $estTecSede;


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
     * Set tiempoEstudio
     *
     * @param integer $tiempoEstudio
     * @return EstTecInstitutoCarrera
     */
    public function setTiempoEstudio($tiempoEstudio)
    {
        $this->tiempoEstudio = $tiempoEstudio;
    
        return $this;
    }

    /**
     * Get tiempoEstudio
     *
     * @return integer 
     */
    public function getTiempoEstudio()
    {
        return $this->tiempoEstudio;
    }

    /**
     * Set cargaHoraria
     *
     * @param integer $cargaHoraria
     * @return EstTecInstitutoCarrera
     */
    public function setCargaHoraria($cargaHoraria)
    {
        $this->cargaHoraria = $cargaHoraria;
    
        return $this;
    }

    /**
     * Get cargaHoraria
     *
     * @return integer 
     */
    public function getCargaHoraria()
    {
        return $this->cargaHoraria;
    }

    /**
     * Set carrera
     *
     * @param string $carrera
     * @return EstTecInstitutoCarrera
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
     * @return EstTecInstitutoCarrera
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
     * Set estado
     *
     * @param integer $estado
     * @return EstTecInstitutoCarrera
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
     * @return EstTecInstitutoCarrera
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
     * @return EstTecInstitutoCarrera
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
     * Set estTecAreaFormacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecAreaFormacionTipo $estTecAreaFormacionTipo
     * @return EstTecInstitutoCarrera
     */
    public function setEstTecAreaFormacionTipo(\Sie\AppWebBundle\Entity\EstTecAreaFormacionTipo $estTecAreaFormacionTipo = null)
    {
        $this->estTecAreaFormacionTipo = $estTecAreaFormacionTipo;
    
        return $this;
    }

    /**
     * Get estTecAreaFormacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstTecAreaFormacionTipo 
     */
    public function getEstTecAreaFormacionTipo()
    {
        return $this->estTecAreaFormacionTipo;
    }

    /**
     * Set estTecRegimenEstudioTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecRegimenEstudioTipo $estTecRegimenEstudioTipo
     * @return EstTecInstitutoCarrera
     */
    public function setEstTecRegimenEstudioTipo(\Sie\AppWebBundle\Entity\EstTecRegimenEstudioTipo $estTecRegimenEstudioTipo = null)
    {
        $this->estTecRegimenEstudioTipo = $estTecRegimenEstudioTipo;
    
        return $this;
    }

    /**
     * Get estTecRegimenEstudioTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstTecRegimenEstudioTipo 
     */
    public function getEstTecRegimenEstudioTipo()
    {
        return $this->estTecRegimenEstudioTipo;
    }

    /**
     * Set estTecSede
     *
     * @param \Sie\AppWebBundle\Entity\EstTecSede $estTecSede
     * @return EstTecInstitutoCarrera
     */
    public function setEstTecSede(\Sie\AppWebBundle\Entity\EstTecSede $estTecSede = null)
    {
        $this->estTecSede = $estTecSede;
    
        return $this;
    }

    /**
     * Get estTecSede
     *
     * @return \Sie\AppWebBundle\Entity\EstTecSede 
     */
    public function getEstTecSede()
    {
        return $this->estTecSede;
    }
}
