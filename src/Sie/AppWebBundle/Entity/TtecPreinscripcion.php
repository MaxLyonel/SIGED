<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecPreinscripcion
 */
class TtecPreinscripcion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $personaId;

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
     * Set personaId
     *
     * @param integer $personaId
     * @return TtecPreinscripcion
     */
    public function setPersonaId($personaId)
    {
        $this->personaId = $personaId;
    
        return $this;
    }

    /**
     * Get personaId
     *
     * @return integer 
     */
    public function getPersonaId()
    {
        return $this->personaId;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecPreinscripcion
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
     * @return TtecPreinscripcion
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
     * @return TtecPreinscripcion
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
