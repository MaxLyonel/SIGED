<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivRegistroConsolidacion
 */
class UnivRegistroConsolidacion
{
    /**
     * @var string
     */
    private $descripcionError;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var string
     */
    private $usuario;

    /**
     * @var string
     */
    private $consulta;

    /**
     * @var boolean
     */
    private $activo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivOperativoTipo
     */
    private $univOperativoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivSede
     */
    private $univSede;


    /**
     * Set descripcionError
     *
     * @param string $descripcionError
     * @return UnivRegistroConsolidacion
     */
    public function setDescripcionError($descripcionError)
    {
        $this->descripcionError = $descripcionError;
    
        return $this;
    }

    /**
     * Get descripcionError
     *
     * @return string 
     */
    public function getDescripcionError()
    {
        return $this->descripcionError;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return UnivRegistroConsolidacion
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
     * Set usuario
     *
     * @param string $usuario
     * @return UnivRegistroConsolidacion
     */
    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return string 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set consulta
     *
     * @param string $consulta
     * @return UnivRegistroConsolidacion
     */
    public function setConsulta($consulta)
    {
        $this->consulta = $consulta;
    
        return $this;
    }

    /**
     * Get consulta
     *
     * @return string 
     */
    public function getConsulta()
    {
        return $this->consulta;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return UnivRegistroConsolidacion
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;
    
        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean 
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return UnivRegistroConsolidacion
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }

    /**
     * Set univOperativoTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivOperativoTipo $univOperativoTipo
     * @return UnivRegistroConsolidacion
     */
    public function setUnivOperativoTipo(\Sie\AppWebBundle\Entity\UnivOperativoTipo $univOperativoTipo = null)
    {
        $this->univOperativoTipo = $univOperativoTipo;
    
        return $this;
    }

    /**
     * Get univOperativoTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivOperativoTipo 
     */
    public function getUnivOperativoTipo()
    {
        return $this->univOperativoTipo;
    }

    /**
     * Set univSede
     *
     * @param \Sie\AppWebBundle\Entity\UnivSede $univSede
     * @return UnivRegistroConsolidacion
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
