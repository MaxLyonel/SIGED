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
}
