<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FrecuenciaTrabajoTipo
 */
class FrecuenciaTrabajoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcionFrecuenciaTrabajo;

    /**
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;


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
     * Set descripcionFrecuenciaTrabajo
     *
     * @param string $descripcionFrecuenciaTrabajo
     * @return FrecuenciaTrabajoTipo
     */
    public function setDescripcionFrecuenciaTrabajo($descripcionFrecuenciaTrabajo)
    {
        $this->descripcionFrecuenciaTrabajo = $descripcionFrecuenciaTrabajo;
    
        return $this;
    }

    /**
     * Get descripcionFrecuenciaTrabajo
     *
     * @return string 
     */
    public function getDescripcionFrecuenciaTrabajo()
    {
        return $this->descripcionFrecuenciaTrabajo;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return FrecuenciaTrabajoTipo
     */
    public function setEsVigente($esVigente)
    {
        $this->esVigente = $esVigente;
    
        return $this;
    }

    /**
     * Get esVigente
     *
     * @return boolean 
     */
    public function getEsVigente()
    {
        return $this->esVigente;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return FrecuenciaTrabajoTipo
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
     * @return FrecuenciaTrabajoTipo
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
}
