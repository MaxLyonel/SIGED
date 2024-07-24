<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteContenido
 */
class EstudianteNotaCualitativaDetalle
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $contenido;

    /**
     * @var string
     */
    private $resultado;

    /**
     * @var string
     */
    private $recomendacion;

    /**
     * @var integer
     */
    private $usuarioId;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\NotaTipo
     */
    private $notaTipo;

/**
     * @var \Sie\AppWebBundle\Entity\EstudianteNotaCualitativa
     */
    private $estudianteNotaCualitativa;

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
     * Set Contenido
     *
     * @param string $Contenido
     * @return EstudianteContenido
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;
    
        return $this;
    }

    /**
     * Get Contenido
     *
     * @return string 
     */
    public function getContenido()
    {
        return $this->contenido;
    }

    /**
     * Set recomendacion
     *
     * @param string $recomendacion
     * @return EstudianteNotaCualitativaDetalle
     */
    public function setRecomendacion($recomendacion)
    {
        $this->recomendacion = $recomendacion;
    
        return $this;
    }

    /**
     * Get recomendacion
     *
     * @return string 
     */
    public function getRecomendacion()
    {
        return $this->recomendacion;
    }

    /**
     * Set usuarioId
     *
     * @param integer $usuarioId
     * @return EstudianteNotaCualitativaDetalle
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudianteNotaCualitativaDetalle
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
     * @return EstudianteNotaCualitativaDetalle
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
     * Set Resultado
     *
     * @param string $Resultado
     * @return EstudianteNotaCualitativaDetalle
     */
    public function setResultado($resultado)
    {
        $this->resultado = $resultado;
    
        return $this;
    }

    /**
     * Get Resultado
     *
     * @return string 
     */
    public function getResultado()
    {
        return $this->resultado;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EstudianteNotaCualitativaDetalle
     */
    public function setEstudianteInscripcion(\Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion = null)
    {
        $this->estudianteInscripcion = $estudianteInscripcion;
    
        return $this;
    }

    /**
     * Get estudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcion 
     */
    public function getEstudianteInscripcion()
    {
        return $this->estudianteInscripcion;
    }

    /**
     * Set notaTipo
     *
     * @param \Sie\AppWebBundle\Entity\NotaTipo $notaTipo
     * @return EstudianteNotaCualitativaDetalle
     */
    public function setNotaTipo(\Sie\AppWebBundle\Entity\NotaTipo $notaTipo = null)
    {
        $this->notaTipo = $notaTipo;
    
        return $this;
    }

    /**
     * Get notaTipo
     *
     * @return \Sie\AppWebBundle\Entity\NotaTipo 
     */
    public function getNotaTipo()
    {
        return $this->notaTipo;
    }

     /**
     * Set estudianteNotaCualitativa
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteNotaCualitativa $notaTipo
     * @return EstudianteNotaCualitativaDetalle
     */
    public function setEstudianteNotaCualitativa(\Sie\AppWebBundle\Entity\EstudianteNotaCualitativa $estudianteNotaCualitativa = null)
    {
        $this->estudianteNotaCualitativa = $estudianteNotaCualitativa;
    
        return $this;
    }

    /**
     * Get estudianteNotaCualitativa
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteNotaCualitativa 
     */
    public function getEstudianteNotaCualitativa()
    {
        return $this->estudianteNotaCualitativa;
    }
}
