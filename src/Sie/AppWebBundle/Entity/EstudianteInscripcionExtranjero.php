<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionExtranjero
 */
class EstudianteInscripcionExtranjero
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $institucioneducativaOrigen;

    /**
     * @var string
     */
    private $cursoVencido;

    /**
     * @var string
     */
    private $rutaImagen;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\PaisTipo
     */
    private $paisTipo;


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
     * Set institucioneducativaOrigen
     *
     * @param string $institucioneducativaOrigen
     * @return EstudianteInscripcionExtranjero
     */
    public function setInstitucioneducativaOrigen($institucioneducativaOrigen)
    {
        $this->institucioneducativaOrigen = $institucioneducativaOrigen;
    
        return $this;
    }

    /**
     * Get institucioneducativaOrigen
     *
     * @return string 
     */
    public function getInstitucioneducativaOrigen()
    {
        return $this->institucioneducativaOrigen;
    }

    /**
     * Set cursoVencido
     *
     * @param string $cursoVencido
     * @return EstudianteInscripcionExtranjero
     */
    public function setCursoVencido($cursoVencido)
    {
        $this->cursoVencido = $cursoVencido;
    
        return $this;
    }

    /**
     * Get cursoVencido
     *
     * @return string 
     */
    public function getCursoVencido()
    {
        return $this->cursoVencido;
    }

    /**
     * Set rutaImagen
     *
     * @param string $rutaImagen
     * @return EstudianteInscripcionExtranjero
     */
    public function setRutaImagen($rutaImagen)
    {
        $this->rutaImagen = $rutaImagen;
    
        return $this;
    }

    /**
     * Get rutaImagen
     *
     * @return string 
     */
    public function getRutaImagen()
    {
        return $this->rutaImagen;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EstudianteInscripcionExtranjero
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
     * Set paisTipo
     *
     * @param \Sie\AppWebBundle\Entity\PaisTipo $paisTipo
     * @return EstudianteInscripcionExtranjero
     */
    public function setPaisTipo(\Sie\AppWebBundle\Entity\PaisTipo $paisTipo = null)
    {
        $this->paisTipo = $paisTipo;
    
        return $this;
    }

    /**
     * Get paisTipo
     *
     * @return \Sie\AppWebBundle\Entity\PaisTipo 
     */
    public function getPaisTipo()
    {
        return $this->paisTipo;
    }
}
