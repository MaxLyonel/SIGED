<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteInscripcionDiplomatico
 */
class EstudianteInscripcionDiplomatico
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $documentoNumero;

    /**
     * @var string
     */
    private $documentoPath;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;


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
     * Set documentoNumero
     *
     * @param string $documentoNumero
     * @return EstudianteInscripcionDiplomatico
     */
    public function setDocumentoNumero($documentoNumero)
    {
        $this->documentoNumero = $documentoNumero;
    
        return $this;
    }

    /**
     * Get documentoNumero
     *
     * @return string 
     */
    public function getDocumentoNumero()
    {
        return $this->documentoNumero;
    }

    /**
     * Set documentoPath
     *
     * @param string $documentoPath
     * @return EstudianteInscripcionDiplomatico
     */
    public function setDocumentoPath($documentoPath)
    {
        $this->documentoPath = $documentoPath;
    
        return $this;
    }

    /**
     * Get documentoPath
     *
     * @return string 
     */
    public function getDocumentoPath()
    {
        return $this->documentoPath;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return EstudianteInscripcionDiplomatico
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
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EstudianteInscripcionDiplomatico
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
}
