<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecMateriaCompetenciaTipo
 */
class TtecMateriaCompetenciaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $competencia;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecMateriaTipo
     */
    private $ttecMateriaTipo;


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
     * Set competencia
     *
     * @param string $competencia
     * @return TtecMateriaCompetenciaTipo
     */
    public function setCompetencia($competencia)
    {
        $this->competencia = $competencia;
    
        return $this;
    }

    /**
     * Get competencia
     *
     * @return string 
     */
    public function getCompetencia()
    {
        return $this->competencia;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecMateriaCompetenciaTipo
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
     * @return TtecMateriaCompetenciaTipo
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
     * Set ttecMateriaTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecMateriaTipo $ttecMateriaTipo
     * @return TtecMateriaCompetenciaTipo
     */
    public function setTtecMateriaTipo(\Sie\AppWebBundle\Entity\TtecMateriaTipo $ttecMateriaTipo = null)
    {
        $this->ttecMateriaTipo = $ttecMateriaTipo;
    
        return $this;
    }

    /**
     * Get ttecMateriaTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecMateriaTipo 
     */
    public function getTtecMateriaTipo()
    {
        return $this->ttecMateriaTipo;
    }
}
