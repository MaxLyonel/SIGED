<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimReglasOlimpiadasTipo
 */
class OlimReglasOlimpiadasTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $cantidadEquipos;

    /**
     * @var integer
     */
    private $cantidadInscritosGrado;

    /**
     * @var integer
     */
    private $edadInicial;

    /**
     * @var integer
     */
    private $edadFinal;

    /**
     * @var \DateTime
     */
    private $fechaComparacion;

    /**
     * @var boolean
     */
    private $siSubirDocumento;

    /**
     * @var boolean
     */
    private $siNombreEquipo;

    /**
     * @var boolean
     */
    private $siNombreProyecto;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var integer
     */
    private $periodoTipoId;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var integer
     */
    private $usuarioRegistroId;

    /**
     * @var integer
     */
    private $usuarioModificacionId;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimMateriaTipo
     */
    private $olimMateriaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimCategoriaTipo
     */
    private $olimCategoriaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimModalidadNumeroIngrantesTipo
     */
    private $modalidadNumeroIntegrantesTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimModalidadParticipacionTipo
     */
    private $modalidadParticipacionTipo;

    public function __toString(){
        return 'krlos';
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
     * Set cantidadEquipos
     *
     * @param integer $cantidadEquipos
     * @return OlimReglasOlimpiadasTipo
     */
    public function setCantidadEquipos($cantidadEquipos)
    {
        $this->cantidadEquipos = $cantidadEquipos;
    
        return $this;
    }

    /**
     * Get cantidadEquipos
     *
     * @return integer 
     */
    public function getCantidadEquipos()
    {
        return $this->cantidadEquipos;
    }

    /**
     * Set cantidadInscritosGrado
     *
     * @param integer $cantidadInscritosGrado
     * @return OlimReglasOlimpiadasTipo
     */
    public function setCantidadInscritosGrado($cantidadInscritosGrado)
    {
        $this->cantidadInscritosGrado = $cantidadInscritosGrado;
    
        return $this;
    }

    /**
     * Get cantidadInscritosGrado
     *
     * @return integer 
     */
    public function getCantidadInscritosGrado()
    {
        return $this->cantidadInscritosGrado;
    }

    /**
     * Set edadInicial
     *
     * @param integer $edadInicial
     * @return OlimReglasOlimpiadasTipo
     */
    public function setEdadInicial($edadInicial)
    {
        $this->edadInicial = $edadInicial;
    
        return $this;
    }

    /**
     * Get edadInicial
     *
     * @return integer 
     */
    public function getEdadInicial()
    {
        return $this->edadInicial;
    }

    /**
     * Set edadFinal
     *
     * @param integer $edadFinal
     * @return OlimReglasOlimpiadasTipo
     */
    public function setEdadFinal($edadFinal)
    {
        $this->edadFinal = $edadFinal;
    
        return $this;
    }

    /**
     * Get edadFinal
     *
     * @return integer 
     */
    public function getEdadFinal()
    {
        return $this->edadFinal;
    }

    /**
     * Set fechaComparacion
     *
     * @param \DateTime $fechaComparacion
     * @return OlimReglasOlimpiadasTipo
     */
    public function setFechaComparacion($fechaComparacion)
    {
        $this->fechaComparacion = $fechaComparacion;
    
        return $this;
    }

    /**
     * Get fechaComparacion
     *
     * @return \DateTime 
     */
    public function getFechaComparacion()
    {
        return $this->fechaComparacion;
    }

    /**
     * Set siSubirDocumento
     *
     * @param boolean $siSubirDocumento
     * @return OlimReglasOlimpiadasTipo
     */
    public function setSiSubirDocumento($siSubirDocumento)
    {
        $this->siSubirDocumento = $siSubirDocumento;
    
        return $this;
    }

    /**
     * Get siSubirDocumento
     *
     * @return boolean 
     */
    public function getSiSubirDocumento()
    {
        return $this->siSubirDocumento;
    }

    /**
     * Set siNombreEquipo
     *
     * @param boolean $siNombreEquipo
     * @return OlimReglasOlimpiadasTipo
     */
    public function setSiNombreEquipo($siNombreEquipo)
    {
        $this->siNombreEquipo = $siNombreEquipo;
    
        return $this;
    }

    /**
     * Get siNombreEquipo
     *
     * @return boolean 
     */
    public function getSiNombreEquipo()
    {
        return $this->siNombreEquipo;
    }

    /**
     * Set siNombreProyecto
     *
     * @param boolean $siNombreProyecto
     * @return OlimReglasOlimpiadasTipo
     */
    public function setSiNombreProyecto($siNombreProyecto)
    {
        $this->siNombreProyecto = $siNombreProyecto;
    
        return $this;
    }

    /**
     * Get siNombreProyecto
     *
     * @return boolean 
     */
    public function getSiNombreProyecto()
    {
        return $this->siNombreProyecto;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return OlimReglasOlimpiadasTipo
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }

    /**
     * Set periodoTipoId
     *
     * @param integer $periodoTipoId
     * @return OlimReglasOlimpiadasTipo
     */
    public function setPeriodoTipoId($periodoTipoId)
    {
        $this->periodoTipoId = $periodoTipoId;
    
        return $this;
    }

    /**
     * Get periodoTipoId
     *
     * @return integer 
     */
    public function getPeriodoTipoId()
    {
        return $this->periodoTipoId;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return OlimReglasOlimpiadasTipo
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
     * @return OlimReglasOlimpiadasTipo
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
     * Set usuarioRegistroId
     *
     * @param integer $usuarioRegistroId
     * @return OlimReglasOlimpiadasTipo
     */
    public function setUsuarioRegistroId($usuarioRegistroId)
    {
        $this->usuarioRegistroId = $usuarioRegistroId;
    
        return $this;
    }

    /**
     * Get usuarioRegistroId
     *
     * @return integer 
     */
    public function getUsuarioRegistroId()
    {
        return $this->usuarioRegistroId;
    }

    /**
     * Set usuarioModificacionId
     *
     * @param integer $usuarioModificacionId
     * @return OlimReglasOlimpiadasTipo
     */
    public function setUsuarioModificacionId($usuarioModificacionId)
    {
        $this->usuarioModificacionId = $usuarioModificacionId;
    
        return $this;
    }

    /**
     * Get usuarioModificacionId
     *
     * @return integer 
     */
    public function getUsuarioModificacionId()
    {
        return $this->usuarioModificacionId;
    }

    /**
     * Set olimMateriaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimMateriaTipo $olimMateriaTipo
     * @return OlimReglasOlimpiadasTipo
     */
    public function setOlimMateriaTipo(\Sie\AppWebBundle\Entity\OlimMateriaTipo $olimMateriaTipo = null)
    {
        $this->olimMateriaTipo = $olimMateriaTipo;
    
        return $this;
    }

    /**
     * Get olimMateriaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimMateriaTipo 
     */
    public function getOlimMateriaTipo()
    {
        return $this->olimMateriaTipo;
    }

    /**
     * Set olimCategoriaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimCategoriaTipo $olimCategoriaTipo
     * @return OlimReglasOlimpiadasTipo
     */
    public function setOlimCategoriaTipo(\Sie\AppWebBundle\Entity\OlimCategoriaTipo $olimCategoriaTipo = null)
    {
        $this->olimCategoriaTipo = $olimCategoriaTipo;
    
        return $this;
    }

    /**
     * Get olimCategoriaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimCategoriaTipo 
     */
    public function getOlimCategoriaTipo()
    {
        return $this->olimCategoriaTipo;
    }

    /**
     * Set modalidadNumeroIntegrantesTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimModalidadNumeroIngrantesTipo $modalidadNumeroIntegrantesTipo
     * @return OlimReglasOlimpiadasTipo
     */
    public function setModalidadNumeroIntegrantesTipo(\Sie\AppWebBundle\Entity\OlimModalidadNumeroIngrantesTipo $modalidadNumeroIntegrantesTipo = null)
    {
        $this->modalidadNumeroIntegrantesTipo = $modalidadNumeroIntegrantesTipo;
    
        return $this;
    }

    /**
     * Get modalidadNumeroIntegrantesTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimModalidadNumeroIngrantesTipo 
     */
    public function getModalidadNumeroIntegrantesTipo()
    {
        return $this->modalidadNumeroIntegrantesTipo;
    }

    /**
     * Set modalidadParticipacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimModalidadParticipacionTipo $modalidadParticipacionTipo
     * @return OlimReglasOlimpiadasTipo
     */
    public function setModalidadParticipacionTipo(\Sie\AppWebBundle\Entity\OlimModalidadParticipacionTipo $modalidadParticipacionTipo = null)
    {
        $this->modalidadParticipacionTipo = $modalidadParticipacionTipo;
    
        return $this;
    }

    /**
     * Get modalidadParticipacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimModalidadParticipacionTipo 
     */
    public function getModalidadParticipacionTipo()
    {
        return $this->modalidadParticipacionTipo;
    }
    /**
     * @var string
     */
    private $categoria;


    /**
     * Set categoria
     *
     * @param string $categoria
     * @return OlimReglasOlimpiadasTipo
     */
    public function setCategoria($categoria)
    {
        $this->categoria = $categoria;
    
        return $this;
    }

    /**
     * Get categoria
     *
     * @return string 
     */
    public function getCategoria()
    {
        return $this->categoria;
    }
    /**
     * @var boolean
     */
    private $siInsExterna;


    /**
     * Set siInsExterna
     *
     * @param boolean $siInsExterna
     * @return OlimReglasOlimpiadasTipo
     */
    public function setSiInsExterna($siInsExterna)
    {
        $this->siInsExterna = $siInsExterna;
    
        return $this;
    }

    /**
     * Get siInsExterna
     *
     * @return boolean 
     */
    public function getSiInsExterna()
    {
        return $this->siInsExterna;
    }
}
