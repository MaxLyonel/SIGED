<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeApoderadoInscripcion
 */
class RudeApoderadoInscripcion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $apoderadoTipoId;

    /**
     * @var integer
     */
    private $personaId;

    /**
     * @var integer
     */
    private $estudianteInscripcionId;

    /**
     * @var integer
     */
    private $idiomaMaternoTipoId;

    /**
     * @var integer
     */
    private $instruccionTipoId;

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
     * @var integer
     */
    private $ocupacionTipoId;


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
     * Set apoderadoTipoId
     *
     * @param integer $apoderadoTipoId
     * @return RudeApoderadoInscripcion
     */
    public function setApoderadoTipoId($apoderadoTipoId)
    {
        $this->apoderadoTipoId = $apoderadoTipoId;
    
        return $this;
    }

    /**
     * Get apoderadoTipoId
     *
     * @return integer 
     */
    public function getApoderadoTipoId()
    {
        return $this->apoderadoTipoId;
    }

    /**
     * Set personaId
     *
     * @param integer $personaId
     * @return RudeApoderadoInscripcion
     */
    public function setPersonaId($personaId)
    {
        $this->personaId = $personaId;
    
        return $this;
    }

    /**
     * Get personaId
     *
     * @return integer 
     */
    public function getPersonaId()
    {
        return $this->personaId;
    }

    /**
     * Set estudianteInscripcionId
     *
     * @param integer $estudianteInscripcionId
     * @return RudeApoderadoInscripcion
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
     * Set idiomaMaternoTipoId
     *
     * @param integer $idiomaMaternoTipoId
     * @return RudeApoderadoInscripcion
     */
    public function setIdiomaMaternoTipoId($idiomaMaternoTipoId)
    {
        $this->idiomaMaternoTipoId = $idiomaMaternoTipoId;
    
        return $this;
    }

    /**
     * Get idiomaMaternoTipoId
     *
     * @return integer 
     */
    public function getIdiomaMaternoTipoId()
    {
        return $this->idiomaMaternoTipoId;
    }

    /**
     * Set instruccionTipoId
     *
     * @param integer $instruccionTipoId
     * @return RudeApoderadoInscripcion
     */
    public function setInstruccionTipoId($instruccionTipoId)
    {
        $this->instruccionTipoId = $instruccionTipoId;
    
        return $this;
    }

    /**
     * Get instruccionTipoId
     *
     * @return integer 
     */
    public function getInstruccionTipoId()
    {
        return $this->instruccionTipoId;
    }

    /**
     * Set empleo
     *
     * @param string $empleo
     * @return RudeApoderadoInscripcion
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
     * @return RudeApoderadoInscripcion
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
     * @return RudeApoderadoInscripcion
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
     * @return RudeApoderadoInscripcion
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
     * Set ocupacionTipoId
     *
     * @param integer $ocupacionTipoId
     * @return RudeApoderadoInscripcion
     */
    public function setOcupacionTipoId($ocupacionTipoId)
    {
        $this->ocupacionTipoId = $ocupacionTipoId;
    
        return $this;
    }

    /**
     * Get ocupacionTipoId
     *
     * @return integer 
     */
    public function getOcupacionTipoId()
    {
        return $this->ocupacionTipoId;
    }
}
