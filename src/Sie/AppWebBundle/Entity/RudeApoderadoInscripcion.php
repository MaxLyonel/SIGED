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
    /**
     * @var \Sie\AppWebBundle\Entity\ApoderadoTipo
     */
    private $apoderadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaMaternoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstruccionTipo
     */
    private $instruccionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ApoderadoOcupacionTipo
     */
    private $ocupacionTipo;


    /**
     * Set apoderadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\ApoderadoTipo $apoderadoTipo
     * @return RudeApoderadoInscripcion
     */
    public function setApoderadoTipo(\Sie\AppWebBundle\Entity\ApoderadoTipo $apoderadoTipo = null)
    {
        $this->apoderadoTipo = $apoderadoTipo;
    
        return $this;
    }

    /**
     * Get apoderadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\ApoderadoTipo 
     */
    public function getApoderadoTipo()
    {
        return $this->apoderadoTipo;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return RudeApoderadoInscripcion
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
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return RudeApoderadoInscripcion
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
     * Set idiomaMaternoTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaMaternoTipo
     * @return RudeApoderadoInscripcion
     */
    public function setIdiomaMaternoTipo(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaMaternoTipo = null)
    {
        $this->idiomaMaternoTipo = $idiomaMaternoTipo;
    
        return $this;
    }

    /**
     * Get idiomaMaternoTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaMaternoTipo()
    {
        return $this->idiomaMaternoTipo;
    }

    /**
     * Set instruccionTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstruccionTipo $instruccionTipo
     * @return RudeApoderadoInscripcion
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
     * @return RudeApoderadoInscripcion
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
}
