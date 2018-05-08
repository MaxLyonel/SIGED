<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimInscripcionGrupoProyecto
 */
class OlimInscripcionGrupoProyecto
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
     * @var \Sie\AppWebBundle\Entity\OlimGrupoProyecto
     */
    private $olimGrupoProyecto;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimEstudianteInscripcion
     */
    private $olimEstudianteInscripcion;


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
     * @return OlimInscripcionGrupoProyecto
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
     * Set olimGrupoProyecto
     *
     * @param \Sie\AppWebBundle\Entity\OlimGrupoProyecto $olimGrupoProyecto
     * @return OlimInscripcionGrupoProyecto
     */
    public function setOlimGrupoProyecto(\Sie\AppWebBundle\Entity\OlimGrupoProyecto $olimGrupoProyecto = null)
    {
        $this->olimGrupoProyecto = $olimGrupoProyecto;
    
        return $this;
    }

    /**
     * Get olimGrupoProyecto
     *
     * @return \Sie\AppWebBundle\Entity\OlimGrupoProyecto 
     */
    public function getOlimGrupoProyecto()
    {
        return $this->olimGrupoProyecto;
    }

    /**
     * Set olimEstudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\OlimEstudianteInscripcion $olimEstudianteInscripcion
     * @return OlimInscripcionGrupoProyecto
     */
    public function setOlimEstudianteInscripcion(\Sie\AppWebBundle\Entity\OlimEstudianteInscripcion $olimEstudianteInscripcion = null)
    {
        $this->olimEstudianteInscripcion = $olimEstudianteInscripcion;
    
        return $this;
    }

    /**
     * Get olimEstudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\OlimEstudianteInscripcion 
     */
    public function getOlimEstudianteInscripcion()
    {
        return $this->olimEstudianteInscripcion;
    }
}
