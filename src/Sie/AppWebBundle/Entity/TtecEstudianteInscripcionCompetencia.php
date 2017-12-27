<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecEstudianteInscripcionCompetencia
 */
class TtecEstudianteInscripcionCompetencia
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $cumple;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecEstudianteInscripcion
     */
    private $ttecEstudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecMateriaCompetenciaDetalleTipo
     */
    private $ttecMateriaCompetenciaDetalleTipo;


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
     * Set cumple
     *
     * @param boolean $cumple
     * @return TtecEstudianteInscripcionCompetencia
     */
    public function setCumple($cumple)
    {
        $this->cumple = $cumple;
    
        return $this;
    }

    /**
     * Get cumple
     *
     * @return boolean 
     */
    public function getCumple()
    {
        return $this->cumple;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecEstudianteInscripcionCompetencia
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
     * @return TtecEstudianteInscripcionCompetencia
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
     * Set ttecEstudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\TtecEstudianteInscripcion $ttecEstudianteInscripcion
     * @return TtecEstudianteInscripcionCompetencia
     */
    public function setTtecEstudianteInscripcion(\Sie\AppWebBundle\Entity\TtecEstudianteInscripcion $ttecEstudianteInscripcion = null)
    {
        $this->ttecEstudianteInscripcion = $ttecEstudianteInscripcion;
    
        return $this;
    }

    /**
     * Get ttecEstudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\TtecEstudianteInscripcion 
     */
    public function getTtecEstudianteInscripcion()
    {
        return $this->ttecEstudianteInscripcion;
    }

    /**
     * Set ttecMateriaCompetenciaDetalleTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecMateriaCompetenciaDetalleTipo $ttecMateriaCompetenciaDetalleTipo
     * @return TtecEstudianteInscripcionCompetencia
     */
    public function setTtecMateriaCompetenciaDetalleTipo(\Sie\AppWebBundle\Entity\TtecMateriaCompetenciaDetalleTipo $ttecMateriaCompetenciaDetalleTipo = null)
    {
        $this->ttecMateriaCompetenciaDetalleTipo = $ttecMateriaCompetenciaDetalleTipo;
    
        return $this;
    }

    /**
     * Get ttecMateriaCompetenciaDetalleTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecMateriaCompetenciaDetalleTipo 
     */
    public function getTtecMateriaCompetenciaDetalleTipo()
    {
        return $this->ttecMateriaCompetenciaDetalleTipo;
    }
}
