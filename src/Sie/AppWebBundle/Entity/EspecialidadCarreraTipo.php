<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialidadCarreraTipo
 */
class EspecialidadCarreraTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $especialidad;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\CarreraTipo
     */
    private $carreraTipo;


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
     * Set especialidad
     *
     * @param string $especialidad
     * @return EspecialidadCarreraTipo
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

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return EspecialidadCarreraTipo
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
     * Set obs
     *
     * @param string $obs
     * @return EspecialidadCarreraTipo
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
     * Set carreraTipo
     *
     * @param \Sie\AppWebBundle\Entity\CarreraTipo $carreraTipo
     * @return EspecialidadCarreraTipo
     */
    public function setCarreraTipo(\Sie\AppWebBundle\Entity\CarreraTipo $carreraTipo = null)
    {
        $this->carreraTipo = $carreraTipo;
    
        return $this;
    }

    /**
     * Get carreraTipo
     *
     * @return \Sie\AppWebBundle\Entity\CarreraTipo 
     */
    public function getCarreraTipo()
    {
        return $this->carreraTipo;
    }
}
