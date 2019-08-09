<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EspecialidadTecnicoHumanisticoTipo
 */
class EspecialidadTecnicoHumanisticoTipo
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
     * @var string
     */
    private $descAbrev;

    /**
     * @var boolean
     */
    private $oficial;

    /**
     * @var integer
     */
    private $areaTipoId;

    /**
     * @var boolean
     */
    private $esobligatorio;

    public function __toString(){
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
     * @return EspecialidadTecnicoHumanisticoTipo
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
     * Set descAbrev
     *
     * @param string $descAbrev
     * @return EspecialidadTecnicoHumanisticoTipo
     */
    public function setDescAbrev($descAbrev)
    {
        $this->descAbrev = $descAbrev;

        return $this;
    }

    /**
     * Get descAbrev
     *
     * @return string
     */
    public function getDescAbrev()
    {
        return $this->descAbrev;
    }

    /**
     * Set oficial
     *
     * @param boolean $oficial
     * @return EspecialidadTecnicoHumanisticoTipo
     */
    public function setOficial($oficial)
    {
        $this->oficial = $oficial;

        return $this;
    }

    /**
     * Get oficial
     *
     * @return boolean
     */
    public function getOficial()
    {
        return $this->oficial;
    }

    /**
     * Set areaTipoId
     *
     * @param integer $areaTipoId
     * @return EspecialidadTecnicoHumanisticoTipo
     */
    public function setAreaTipoId($areaTipoId)
    {
        $this->areaTipoId = $areaTipoId;

        return $this;
    }

    /**
     * Get areaTipoId
     *
     * @return integer
     */
    public function getAreaTipoId()
    {
        return $this->areaTipoId;
    }

    /**
     * Set esobligatorio
     *
     * @param boolean $esobligatorio
     * @return EspecialidadTecnicoHumanisticoTipo
     */
    public function setEsobligatorio($esobligatorio)
    {
        $this->esobligatorio = $esobligatorio;

        return $this;
    }

    /**
     * Get esobligatorio
     *
     * @return boolean
     */
    public function getEsobligatorio()
    {
        return $this->esobligatorio;
    }
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
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return EspecialidadTecnicoHumanisticoTipo
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
     * @return EspecialidadTecnicoHumanisticoTipo
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
     * @return EspecialidadTecnicoHumanisticoTipo
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
