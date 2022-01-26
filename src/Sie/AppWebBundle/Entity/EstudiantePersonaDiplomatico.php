<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudiantePersonaDiplomatico
 */
class EstudiantePersonaDiplomatico
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
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;


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
     * @return EstudiantePersonaDiplomatico
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
     * @return EstudiantePersonaDiplomatico
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
     * @return EstudiantePersonaDiplomatico
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
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return EstudiantePersonaDiplomatico
     */
    public function setPersona(\Sie\AppWebBundle\Entity\Persona $persona = null)
    {
        $this->persona = $persona;
    
        return $this;
    }

    /**
     * Get persona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getPersona()
    {
        return $this->persona;
    }
    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var string
     */
    private $lugar;

    /**
     * @var \Sie\AppWebBundle\Entity\DocumentoTipo
     */
    private $documentoTipo;


    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstudiantePersonaDiplomatico
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
     * Set lugar
     *
     * @param string $lugar
     * @return EstudiantePersonaDiplomatico
     */
    public function setLugar($lugar)
    {
        $this->lugar = $lugar;
    
        return $this;
    }

    /**
     * Get lugar
     *
     * @return string 
     */
    public function getLugar()
    {
        return $this->lugar;
    }

    /**
     * Set documentoTipo
     *
     * @param \Sie\AppWebBundle\Entity\DocumentoTipo $documentoTipo
     * @return EstudiantePersonaDiplomatico
     */
    public function setDocumentoTipo(\Sie\AppWebBundle\Entity\DocumentoTipo $documentoTipo = null)
    {
        $this->documentoTipo = $documentoTipo;
    
        return $this;
    }

    /**
     * Get documentoTipo
     *
     * @return \Sie\AppWebBundle\Entity\DocumentoTipo 
     */
    public function getDocumentoTipo()
    {
        return $this->documentoTipo;
    }
}
