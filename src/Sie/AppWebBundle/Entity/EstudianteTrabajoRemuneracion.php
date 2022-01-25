<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteTrabajoRemuneracion
 */
class EstudianteTrabajoRemuneracion
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
    private $sie;

    /**
     * @var integer
     */
    private $nivel;

    /**
     * @var integer
     */
    private $grado;

    /**
     * @var integer
     */
    private $paralelo;

    /**
     * @var integer
     */
    private $turno;

    /**
     * @var integer
     */
    private $gestion;

    /**
     * @var integer
     */
    private $ocupacion;

    /**
     * @var string
     */
    private $ocupacionOtro;

    /**
     * @var boolean
     */
    private $remuneracion;

    /**
     * @var string
     */
    private $especificacion;

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
    private $usuarioId;

    /**
     * @var boolean
     */
    private $traslado;


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
     * @return EstudianteTrabajoRemuneracion
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
     * Set sie
     *
     * @param string $sie
     * @return EstudianteTrabajoRemuneracion
     */
    public function setSie($sie)
    {
        $this->sie = $sie;
    
        return $this;
    }

    /**
     * Get sie
     *
     * @return string 
     */
    public function getSie()
    {
        return $this->sie;
    }

    /**
     * Set nivel
     *
     * @param integer $nivel
     * @return EstudianteTrabajoRemuneracion
     */
    public function setNivel($nivel)
    {
        $this->nivel = $nivel;
    
        return $this;
    }

    /**
     * Get nivel
     *
     * @return integer 
     */
    public function getNivel()
    {
        return $this->nivel;
    }

    /**
     * Set grado
     *
     * @param integer $grado
     * @return EstudianteTrabajoRemuneracion
     */
    public function setGrado($grado)
    {
        $this->grado = $grado;
    
        return $this;
    }

    /**
     * Get grado
     *
     * @return integer 
     */
    public function getGrado()
    {
        return $this->grado;
    }

    /**
     * Set paralelo
     *
     * @param integer $paralelo
     * @return EstudianteTrabajoRemuneracion
     */
    public function setParalelo($paralelo)
    {
        $this->paralelo = $paralelo;
    
        return $this;
    }

    /**
     * Get paralelo
     *
     * @return integer 
     */
    public function getParalelo()
    {
        return $this->paralelo;
    }

    /**
     * Set turno
     *
     * @param integer $turno
     * @return EstudianteTrabajoRemuneracion
     */
    public function setTurno($turno)
    {
        $this->turno = $turno;
    
        return $this;
    }

    /**
     * Get turno
     *
     * @return integer 
     */
    public function getTurno()
    {
        return $this->turno;
    }

    /**
     * Set gestion
     *
     * @param integer $gestion
     * @return EstudianteTrabajoRemuneracion
     */
    public function setGestion($gestion)
    {
        $this->gestion = $gestion;
    
        return $this;
    }

    /**
     * Get gestion
     *
     * @return integer 
     */
    public function getGestion()
    {
        return $this->gestion;
    }

    /**
     * Set ocupacion
     *
     * @param integer $ocupacion
     * @return EstudianteTrabajoRemuneracion
     */
    public function setOcupacion($ocupacion)
    {
        $this->ocupacion = $ocupacion;
    
        return $this;
    }

    /**
     * Get ocupacion
     *
     * @return integer 
     */
    public function getOcupacion()
    {
        return $this->ocupacion;
    }

    /**
     * Set ocupacionOtro
     *
     * @param string $ocupacionOtro
     * @return EstudianteTrabajoRemuneracion
     */
    public function setOcupacionOtro($ocupacionOtro)
    {
        $this->ocupacionOtro = $ocupacionOtro;
    
        return $this;
    }

    /**
     * Get ocupacionOtro
     *
     * @return string 
     */
    public function getOcupacionOtro()
    {
        return $this->ocupacionOtro;
    }

    /**
     * Set remuneracion
     *
     * @param boolean $remuneracion
     * @return EstudianteTrabajoRemuneracion
     */
    public function setRemuneracion($remuneracion)
    {
        $this->remuneracion = $remuneracion;
    
        return $this;
    }

    /**
     * Get remuneracion
     *
     * @return boolean 
     */
    public function getRemuneracion()
    {
        return $this->remuneracion;
    }

    /**
     * Set especificacion
     *
     * @param string $especificacion
     * @return EstudianteTrabajoRemuneracion
     */
    public function setEspecificacion($especificacion)
    {
        $this->especificacion = $especificacion;
    
        return $this;
    }

    /**
     * Get especificacion
     *
     * @return string 
     */
    public function getEspecificacion()
    {
        return $this->especificacion;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteTrabajoRemuneracion
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
     * @return EstudianteTrabajoRemuneracion
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
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return EstudianteTrabajoRemuneracion
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return integer 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set traslado
     *
     * @param boolean $traslado
     * @return EstudianteTrabajoRemuneracion
     */
    public function setTraslado($traslado)
    {
        $this->traslado = $traslado;
    
        return $this;
    }

    /**
     * Get traslado
     *
     * @return boolean 
     */
    public function getTraslado()
    {
        return $this->traslado;
    }
}
