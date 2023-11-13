<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BthSuspensionTte
 */
class BthSuspensionTte
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $codigoRude;

    /**
     * @var string
     */
    private $datos;

    /**
     * @var integer
     */
    private $tramiteId;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \DateTime
     */
    private $ferchaRegistro;

    /**
     * @var string
     */
    private $fechaModfiicacion;

    /**
     * @var integer
     */
    private $estudianteInscripcionHumnisticoTecnicoId;

    /**
     * @var integer
     */
    private $especialidadTecnicoHumanisticoTipoId;

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
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return BthSuspensionTte
     */
    public function setCodigoRude($codigoRude)
    {
        $this->codigoRude = $codigoRude;
    
        return $this;
    }

    /**
     * Get codigoRude
     *
     * @return string 
     */
    public function getCodigoRude()
    {
        return $this->codigoRude;
    }

    /**
     * Set datos
     *
     * @param string $datos
     * @return BthSuspensionTte
     */
    public function setDatos($datos)
    {
        $this->datos = $datos;
    
        return $this;
    }

    /**
     * Get datos
     *
     * @return string 
     */
    public function getDatos()
    {
        return $this->datos;
    }

    /**
     * Set tramiteId
     *
     * @param integer $tramiteId
     * @return BthSuspensionTte
     */
    public function setTramiteId($tramiteId)
    {
        $this->tramiteId = $tramiteId;
    
        return $this;
    }

    /**
     * Get tramiteId
     *
     * @return integer 
     */
    public function getTramiteId()
    {
        return $this->tramiteId;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return BthSuspensionTte
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
     * Set ferchaRegistro
     *
     * @param \DateTime $ferchaRegistro
     * @return BthSuspensionTte
     */
    public function setFerchaRegistro($ferchaRegistro)
    {
        $this->ferchaRegistro = $ferchaRegistro;
    
        return $this;
    }

    /**
     * Get ferchaRegistro
     *
     * @return \DateTime 
     */
    public function getFerchaRegistro()
    {
        return $this->ferchaRegistro;
    }

    /**
     * Set fechaModfiicacion
     *
     * @param string $fechaModfiicacion
     * @return BthSuspensionTte
     */
    public function setFechaModfiicacion($fechaModfiicacion)
    {
        $this->fechaModfiicacion = $fechaModfiicacion;
    
        return $this;
    }

    /**
     * Get fechaModfiicacion
     *
     * @return string 
     */
    public function getFechaModfiicacion()
    {
        return $this->fechaModfiicacion;
    }

    /**
     * Set estudianteInscripcionHumnisticoTecnicoId
     *
     * @param integer $estudianteInscripcionHumnisticoTecnicoId
     * @return BthSuspensionTte
     */
    public function setEstudianteInscripcionHumnisticoTecnicoId($estudianteInscripcionHumnisticoTecnicoId)
    {
        $this->estudianteInscripcionHumnisticoTecnicoId = $estudianteInscripcionHumnisticoTecnicoId;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionHumnisticoTecnicoId
     *
     * @return integer 
     */
    public function getEstudianteInscripcionHumnisticoTecnicoId()
    {
        return $this->estudianteInscripcionHumnisticoTecnicoId;
    }

    /**
     * Set especialidadTecnicoHumanisticoTipoId
     *
     * @param integer $especialidadTecnicoHumanisticoTipoId
     * @return BthSuspensionTte
     */
    public function setEspecialidadTecnicoHumanisticoTipoId($especialidadTecnicoHumanisticoTipoId)
    {
        $this->especialidadTecnicoHumanisticoTipoId = $especialidadTecnicoHumanisticoTipoId;
    
        return $this;
    }

    /**
     * Get especialidadTecnicoHumanisticoTipoId
     *
     * @return integer 
     */
    public function getEspecialidadTecnicoHumanisticoTipoId()
    {
        return $this->especialidadTecnicoHumanisticoTipoId;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return BthSuspensionTte
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
