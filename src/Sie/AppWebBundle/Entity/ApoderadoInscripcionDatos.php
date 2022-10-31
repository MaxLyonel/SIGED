<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ApoderadoInscripcionDatos
 */
class ApoderadoInscripcionDatos
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $empleo;

    /**
     * @var string
     */
    private $telefono;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var boolean
     */
    private $tieneocupacion;

    /**
     * @var \Sie\AppWebBundle\Entity\ApoderadoActividadTipo
     */
    private $actividadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ApoderadoInscripcion
     */
    private $apoderadoInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\InstruccionTipo
     */
    private $instruccionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ApoderadoOcupacionTipo
     */
    private $ocupacionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaMaterno;


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
     * Set empleo
     *
     * @param string $empleo
     * @return ApoderadoInscripcionDatos
     */
    public function setEmpleo($empleo)
    {
        $this->empleo = $empleo;
    
        return $this;
    }

    /**
     * Get empleo
     *
     * @return string 
     */
    public function getEmpleo()
    {
        return $this->empleo;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return ApoderadoInscripcionDatos
     */
    public function setTelefono($telefono)
    {
        $this->telefono = $telefono;
    
        return $this;
    }

    /**
     * Get telefono
     *
     * @return string 
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return ApoderadoInscripcionDatos
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
     * Set tieneocupacion
     *
     * @param boolean $tieneocupacion
     * @return ApoderadoInscripcionDatos
     */
    public function setTieneocupacion($tieneocupacion)
    {
        $this->tieneocupacion = $tieneocupacion;
    
        return $this;
    }

    /**
     * Get tieneocupacion
     *
     * @return boolean 
     */
    public function getTieneocupacion()
    {
        return $this->tieneocupacion;
    }

    /**
     * Set actividadTipo
     *
     * @param \Sie\AppWebBundle\Entity\ApoderadoActividadTipo $actividadTipo
     * @return ApoderadoInscripcionDatos
     */
    public function setActividadTipo(\Sie\AppWebBundle\Entity\ApoderadoActividadTipo $actividadTipo = null)
    {
        $this->actividadTipo = $actividadTipo;
    
        return $this;
    }

    /**
     * Get actividadTipo
     *
     * @return \Sie\AppWebBundle\Entity\ApoderadoActividadTipo 
     */
    public function getActividadTipo()
    {
        return $this->actividadTipo;
    }

    /**
     * Set apoderadoInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\ApoderadoInscripcion $apoderadoInscripcion
     * @return ApoderadoInscripcionDatos
     */
    public function setApoderadoInscripcion(\Sie\AppWebBundle\Entity\ApoderadoInscripcion $apoderadoInscripcion = null)
    {
        $this->apoderadoInscripcion = $apoderadoInscripcion;
    
        return $this;
    }

    /**
     * Get apoderadoInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\ApoderadoInscripcion 
     */
    public function getApoderadoInscripcion()
    {
        return $this->apoderadoInscripcion;
    }

    /**
     * Set instruccionTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstruccionTipo $instruccionTipo
     * @return ApoderadoInscripcionDatos
     */
    public function setInstruccionTipo(\Sie\AppWebBundle\Entity\InstruccionTipo $instruccionTipo = null)
    {
        $this->instruccionTipo = $instruccionTipo;
    
        return $this;
    }

    /**
     * Get instruccionTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstruccionTipo 
     */
    public function getInstruccionTipo()
    {
        return $this->instruccionTipo;
    }

    /**
     * Set ocupacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\ApoderadoOcupacionTipo $ocupacionTipo
     * @return ApoderadoInscripcionDatos
     */
    public function setOcupacionTipo(\Sie\AppWebBundle\Entity\ApoderadoOcupacionTipo $ocupacionTipo = null)
    {
        $this->ocupacionTipo = $ocupacionTipo;
    
        return $this;
    }

    /**
     * Get ocupacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\ApoderadoOcupacionTipo 
     */
    public function getOcupacionTipo()
    {
        return $this->ocupacionTipo;
    }

    /**
     * Set idiomaMaterno
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaMaterno
     * @return ApoderadoInscripcionDatos
     */
    public function setIdiomaMaterno(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaMaterno = null)
    {
        $this->idiomaMaterno = $idiomaMaterno;
    
        return $this;
    }

    /**
     * Get idiomaMaterno
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaMaterno()
    {
        return $this->idiomaMaterno;
    }
}
