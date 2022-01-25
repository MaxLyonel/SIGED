<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionAlternativaExcepcional
 */
class EstudianteInscripcionAlternativaExcepcional
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fecha;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcionAlternativaExcepcionalTipo
     */
    private $estudianteInscripcionAlternativaExcepcionalTipo;


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
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return EstudianteInscripcionAlternativaExcepcional
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
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionAlternativaExcepcional
     */
    public function setObs($obs)
    {
        $this->obs = $obs;
    
        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }

    /**
     * Set estudianteInscripcionAlternativaExcepcionalTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcionAlternativaExcepcionalTipo $estudianteInscripcionAlternativaExcepcionalTipo
     * @return EstudianteInscripcionAlternativaExcepcional
     */
    public function setEstudianteInscripcionAlternativaExcepcionalTipo(\Sie\AppWebBundle\Entity\EstudianteInscripcionAlternativaExcepcionalTipo $estudianteInscripcionAlternativaExcepcionalTipo = null)
    {
        $this->estudianteInscripcionAlternativaExcepcionalTipo = $estudianteInscripcionAlternativaExcepcionalTipo;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionAlternativaExcepcionalTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcionAlternativaExcepcionalTipo 
     */
    public function getEstudianteInscripcionAlternativaExcepcionalTipo()
    {
        return $this->estudianteInscripcionAlternativaExcepcionalTipo;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EstudianteInscripcionAlternativaExcepcional
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return EstudianteInscripcionAlternativaExcepcional
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
     * @var string
     */
    private $documento;


    /**
     * Set documento
     *
     * @param string $documento
     * @return EstudianteInscripcionAlternativaExcepcional
     */
    public function setDocumento($documento)
    {
        $this->documento = $documento;
    
        return $this;
    }

    /**
     * Get documento
     *
     * @return string 
     */
    public function getDocumento()
    {
        return $this->documento;
    }
}
