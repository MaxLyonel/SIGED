<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperiorEspecialidadTipo
 */
class SuperiorEspecialidadTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $codigo;

    /**
     * @var string
     */
    private $especialidadEspecialidad;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorFacultadAreaTipo
     */
    private $superiorFacultadAreaTipo;


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
     * Set codigo
     *
     * @param integer $codigo
     * @return SuperiorEspecialidadTipo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return integer 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set especialidadEspecialidad
     *
     * @param string $especialidadEspecialidad
     * @return SuperiorEspecialidadTipo
     */
    public function setEspecialidadEspecialidad($especialidadEspecialidad)
    {
        $this->especialidadEspecialidad = $especialidadEspecialidad;
    
        return $this;
    }

    /**
     * Get especialidadEspecialidad
     *
     * @return string 
     */
    public function getEspecialidadEspecialidad()
    {
        return $this->especialidadEspecialidad;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return SuperiorEspecialidadTipo
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
     * Set superiorFacultadAreaTipo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorFacultadAreaTipo $superiorFacultadAreaTipo
     * @return SuperiorEspecialidadTipo
     */
    public function setSuperiorFacultadAreaTipo(\Sie\AppWebBundle\Entity\SuperiorFacultadAreaTipo $superiorFacultadAreaTipo = null)
    {
        $this->superiorFacultadAreaTipo = $superiorFacultadAreaTipo;
    
        return $this;
    }

    /**
     * Get superiorFacultadAreaTipo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorFacultadAreaTipo 
     */
    public function getSuperiorFacultadAreaTipo()
    {
        return $this->superiorFacultadAreaTipo;
    }
    /**
     * @var string
     */
    private $especialidad;


    /**
     * Set especialidad
     *
     * @param string $especialidad
     * @return SuperiorEspecialidadTipo
     */
    public function setEspecialidad($especialidad)
    {
        $this->especialidad = $especialidad;
    
        return $this;
    }

    /**
     * Get especialidad
     *
     * @return string 
     */
    public function getEspecialidad()
    {
        return $this->especialidad;
    }
}
