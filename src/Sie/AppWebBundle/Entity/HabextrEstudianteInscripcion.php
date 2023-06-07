<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * HabextrEstudianteInscripcion
 */
class HabextrEstudianteInscripcion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $estudianteInscripcionId;

    /**
     * @var string
     */
    private $titulo;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $docadjunto;

    /**
     * @var string
     */
    private $urldocumento;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\HabextrFaseTipo
     */
    private $habextrFaseTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\HabextrAreasCamposTipo
     */
    private $habextrAreasCamposTipo;


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
     * Set estudianteInscripcionId
     *
     * @param integer $estudianteInscripcionId
     * @return HabextrEstudianteInscripcion
     */
    public function setEstudianteInscripcionId($estudianteInscripcionId)
    {
        $this->estudianteInscripcionId = $estudianteInscripcionId;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionId
     *
     * @return integer 
     */
    public function getEstudianteInscripcionId()
    {
        return $this->estudianteInscripcionId;
    }

    /**
     * Set titulo
     *
     * @param string $titulo
     * @return HabextrEstudianteInscripcion
     */
    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
    
        return $this;
    }

    /**
     * Get titulo
     *
     * @return string 
     */
    public function getTitulo()
    {
        return $this->titulo;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return HabextrEstudianteInscripcion
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return HabextrEstudianteInscripcion
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
     * @return HabextrEstudianteInscripcion
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
     * Set docadjunto
     *
     * @param string $docadjunto
     * @return HabextrEstudianteInscripcion
     */
    public function setDocadjunto($docadjunto)
    {
        $this->docadjunto = $docadjunto;
    
        return $this;
    }

    /**
     * Get docadjunto
     *
     * @return string 
     */
    public function getDocadjunto()
    {
        return $this->docadjunto;
    }

    /**
     * Set urldocumento
     *
     * @param string $urldocumento
     * @return HabextrEstudianteInscripcion
     */
    public function setUrldocumento($urldocumento)
    {
        $this->urldocumento = $urldocumento;
    
        return $this;
    }

    /**
     * Get urldocumento
     *
     * @return string 
     */
    public function getUrldocumento()
    {
        return $this->urldocumento;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return HabextrEstudianteInscripcion
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set habextrFaseTipo
     *
     * @param \Sie\AppWebBundle\Entity\HabextrFaseTipo $habextrFaseTipo
     * @return HabextrEstudianteInscripcion
     */
    public function setHabextrFaseTipo(\Sie\AppWebBundle\Entity\HabextrFaseTipo $habextrFaseTipo = null)
    {
        $this->habextrFaseTipo = $habextrFaseTipo;
    
        return $this;
    }

    /**
     * Get habextrFaseTipo
     *
     * @return \Sie\AppWebBundle\Entity\HabextrFaseTipo 
     */
    public function getHabextrFaseTipo()
    {
        return $this->habextrFaseTipo;
    }

    /**
     * Set habextrAreasCamposTipo
     *
     * @param \Sie\AppWebBundle\Entity\HabextrAreasCamposTipo $habextrAreasCamposTipo
     * @return HabextrEstudianteInscripcion
     */
    public function setHabextrAreasCamposTipo(\Sie\AppWebBundle\Entity\HabextrAreasCamposTipo $habextrAreasCamposTipo = null)
    {
        $this->habextrAreasCamposTipo = $habextrAreasCamposTipo;
    
        return $this;
    }

    /**
     * Get habextrAreasCamposTipo
     *
     * @return \Sie\AppWebBundle\Entity\HabextrAreasCamposTipo 
     */
    public function getHabextrAreasCamposTipo()
    {
        return $this->habextrAreasCamposTipo;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;


    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return HabextrEstudianteInscripcion
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }
}
