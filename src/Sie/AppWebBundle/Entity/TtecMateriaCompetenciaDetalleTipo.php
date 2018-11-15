<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecMateriaCompetenciaDetalleTipo
 */
class TtecMateriaCompetenciaDetalleTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $competenciaDetalle;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecMateriaCompetenciaTipo
     */
    private $ttecMateriaCompetenciaTipo;


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
     * Set competenciaDetalle
     *
     * @param string $competenciaDetalle
     * @return TtecMateriaCompetenciaDetalleTipo
     */
    public function setCompetenciaDetalle($competenciaDetalle)
    {
        $this->competenciaDetalle = $competenciaDetalle;
    
        return $this;
    }

    /**
     * Get competenciaDetalle
     *
     * @return string 
     */
    public function getCompetenciaDetalle()
    {
        return $this->competenciaDetalle;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecMateriaCompetenciaDetalleTipo
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
     * @return TtecMateriaCompetenciaDetalleTipo
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
     * Set ttecMateriaCompetenciaTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecMateriaCompetenciaTipo $ttecMateriaCompetenciaTipo
     * @return TtecMateriaCompetenciaDetalleTipo
     */
    public function setTtecMateriaCompetenciaTipo(\Sie\AppWebBundle\Entity\TtecMateriaCompetenciaTipo $ttecMateriaCompetenciaTipo = null)
    {
        $this->ttecMateriaCompetenciaTipo = $ttecMateriaCompetenciaTipo;
    
        return $this;
    }

    /**
     * Get ttecMateriaCompetenciaTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecMateriaCompetenciaTipo 
     */
    public function getTtecMateriaCompetenciaTipo()
    {
        return $this->ttecMateriaCompetenciaTipo;
    }
}
