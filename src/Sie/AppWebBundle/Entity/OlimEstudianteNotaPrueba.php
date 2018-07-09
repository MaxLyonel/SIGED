<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimEstudianteNotaPrueba
 */
class OlimEstudianteNotaPrueba
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $nota;

    /**
     * @var boolean
     */
    private $estado;

    /**
     * @var string
     */
    private $observacionSubida;

    /**
     * @var string
     */
    private $estadoSubidaNota;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificado;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimNivelMatematicaTipo
     */
    private $olimNivelMatematicaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimEtapaTipo
     */
    private $olimEtapaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimMedalleroTipo
     */
    private $olimMedalleroTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimEstudianteInscripcion
     */
    private $olimEstudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimModalidadPruebaTipo
     */
    private $olimModalidadPruebaTipo;


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
     * Set nota
     *
     * @param integer $nota
     * @return OlimEstudianteNotaPrueba
     */
    public function setNota($nota)
    {
        $this->nota = $nota;
    
        return $this;
    }

    /**
     * Get nota
     *
     * @return integer 
     */
    public function getNota()
    {
        return $this->nota;
    }

    /**
     * Set estado
     *
     * @param boolean $estado
     * @return OlimEstudianteNotaPrueba
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return boolean 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set observacionSubida
     *
     * @param string $observacionSubida
     * @return OlimEstudianteNotaPrueba
     */
    public function setObservacionSubida($observacionSubida)
    {
        $this->observacionSubida = $observacionSubida;
    
        return $this;
    }

    /**
     * Get observacionSubida
     *
     * @return string 
     */
    public function getObservacionSubida()
    {
        return $this->observacionSubida;
    }

    /**
     * Set estadoSubidaNota
     *
     * @param string $estadoSubidaNota
     * @return OlimEstudianteNotaPrueba
     */
    public function setEstadoSubidaNota($estadoSubidaNota)
    {
        $this->estadoSubidaNota = $estadoSubidaNota;
    
        return $this;
    }

    /**
     * Get estadoSubidaNota
     *
     * @return string 
     */
    public function getEstadoSubidaNota()
    {
        return $this->estadoSubidaNota;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return OlimEstudianteNotaPrueba
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
     * Set fechaModificado
     *
     * @param \DateTime $fechaModificado
     * @return OlimEstudianteNotaPrueba
     */
    public function setFechaModificado($fechaModificado)
    {
        $this->fechaModificado = $fechaModificado;
    
        return $this;
    }

    /**
     * Get fechaModificado
     *
     * @return \DateTime 
     */
    public function getFechaModificado()
    {
        return $this->fechaModificado;
    }

    /**
     * Set olimNivelMatematicaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimNivelMatematicaTipo $olimNivelMatematicaTipo
     * @return OlimEstudianteNotaPrueba
     */
    public function setOlimNivelMatematicaTipo(\Sie\AppWebBundle\Entity\OlimNivelMatematicaTipo $olimNivelMatematicaTipo = null)
    {
        $this->olimNivelMatematicaTipo = $olimNivelMatematicaTipo;
    
        return $this;
    }

    /**
     * Get olimNivelMatematicaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimNivelMatematicaTipo 
     */
    public function getOlimNivelMatematicaTipo()
    {
        return $this->olimNivelMatematicaTipo;
    }

    /**
     * Set olimEtapaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimEtapaTipo $olimEtapaTipo
     * @return OlimEstudianteNotaPrueba
     */
    public function setOlimEtapaTipo(\Sie\AppWebBundle\Entity\OlimEtapaTipo $olimEtapaTipo = null)
    {
        $this->olimEtapaTipo = $olimEtapaTipo;
    
        return $this;
    }

    /**
     * Get olimEtapaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimEtapaTipo 
     */
    public function getOlimEtapaTipo()
    {
        return $this->olimEtapaTipo;
    }

    /**
     * Set olimMedalleroTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimMedalleroTipo $olimMedalleroTipo
     * @return OlimEstudianteNotaPrueba
     */
    public function setOlimMedalleroTipo(\Sie\AppWebBundle\Entity\OlimMedalleroTipo $olimMedalleroTipo = null)
    {
        $this->olimMedalleroTipo = $olimMedalleroTipo;
    
        return $this;
    }

    /**
     * Get olimMedalleroTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimMedalleroTipo 
     */
    public function getOlimMedalleroTipo()
    {
        return $this->olimMedalleroTipo;
    }

    /**
     * Set olimEstudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\OlimEstudianteInscripcion $olimEstudianteInscripcion
     * @return OlimEstudianteNotaPrueba
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

    /**
     * Set olimModalidadPruebaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimModalidadPruebaTipo $olimModalidadPruebaTipo
     * @return OlimEstudianteNotaPrueba
     */
    public function setOlimModalidadPruebaTipo(\Sie\AppWebBundle\Entity\OlimModalidadPruebaTipo $olimModalidadPruebaTipo = null)
    {
        $this->olimModalidadPruebaTipo = $olimModalidadPruebaTipo;
    
        return $this;
    }

    /**
     * Get olimModalidadPruebaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimModalidadPruebaTipo 
     */
    public function getOlimModalidadPruebaTipo()
    {
        return $this->olimModalidadPruebaTipo;
    }
}
