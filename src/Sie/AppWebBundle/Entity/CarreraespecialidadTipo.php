<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarreraespecialidadTipo
 */
class CarreraespecialidadTipo
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
     * @var string
     */
    private $codigo;

    /**
     * @var \Sie\AppWebBundle\Entity\CarreraTipo
     */
    private $carreraTipo;

    public function __toString() {
        return $this->especialidad;
    }
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
     * @return CarreraespecialidadTipo
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
     * @return CarreraespecialidadTipo
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
     * @return CarreraespecialidadTipo
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
     * Set codigo
     *
     * @param string $codigo
     * @return CarreraespecialidadTipo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set carreraTipo
     *
     * @param \Sie\AppWebBundle\Entity\CarreraTipo $carreraTipo
     * @return CarreraespecialidadTipo
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
