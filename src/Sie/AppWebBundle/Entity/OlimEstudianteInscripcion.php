<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimEstudianteInscripcion
 */
class OlimEstudianteInscripcion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $telefonoEstudiante;

    /**
     * @var string
     */
    private $correoEstudiante;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var string
     */
    private $carnetCodepedis;

    /**
     * @var string
     */
    private $carnetIbc;

    /**
     * @var string
     */
    private $navegador;

    /**
     * @var string
     */
    private $fotoEstudiante;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var integer
     */
    private $usuarioRegistroId;

    /**
     * @var integer
     */
    private $usuarioModificacionId;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo
     */
    private $olimReglasOlimpiadasTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimPeriodoTipo
     */
    private $periodoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimCategoriaTipo
     */
    private $categoriaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimMateriaTipo
     */
    private $materiaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimDiscapacidadTipo
     */
    private $discapacidadTipo;

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
     * Set telefonoEstudiante
     *
     * @param string $telefonoEstudiante
     * @return OlimEstudianteInscripcion
     */
    public function setTelefonoEstudiante($telefonoEstudiante)
    {
        $this->telefonoEstudiante = $telefonoEstudiante;
    
        return $this;
    }

    /**
     * Get telefonoEstudiante
     *
     * @return string 
     */
    public function getTelefonoEstudiante()
    {
        return $this->telefonoEstudiante;
    }

    /**
     * Set correoEstudiante
     *
     * @param string $correoEstudiante
     * @return OlimEstudianteInscripcion
     */
    public function setCorreoEstudiante($correoEstudiante)
    {
        $this->correoEstudiante = $correoEstudiante;
    
        return $this;
    }

    /**
     * Get correoEstudiante
     *
     * @return string 
     */
    public function getCorreoEstudiante()
    {
        return $this->correoEstudiante;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return OlimEstudianteInscripcion
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
     * Set carnetCodepedis
     *
     * @param string $carnetCodepedis
     * @return OlimEstudianteInscripcion
     */
    public function setCarnetCodepedis($carnetCodepedis)
    {
        $this->carnetCodepedis = $carnetCodepedis;
    
        return $this;
    }

    /**
     * Get carnetCodepedis
     *
     * @return string 
     */
    public function getCarnetCodepedis()
    {
        return $this->carnetCodepedis;
    }

    /**
     * Set carnetIbc
     *
     * @param string $carnetIbc
     * @return OlimEstudianteInscripcion
     */
    public function setCarnetIbc($carnetIbc)
    {
        $this->carnetIbc = $carnetIbc;
    
        return $this;
    }

    /**
     * Get carnetIbc
     *
     * @return string 
     */
    public function getCarnetIbc()
    {
        return $this->carnetIbc;
    }

    /**
     * Set navegador
     *
     * @param string $navegador
     * @return OlimEstudianteInscripcion
     */
    public function setNavegador($navegador)
    {
        $this->navegador = $navegador;
    
        return $this;
    }

    /**
     * Get navegador
     *
     * @return string 
     */
    public function getNavegador()
    {
        return $this->navegador;
    }

    /**
     * Set fotoEstudiante
     *
     * @param string $fotoEstudiante
     * @return OlimEstudianteInscripcion
     */
    public function setFotoEstudiante($fotoEstudiante)
    {
        $this->fotoEstudiante = $fotoEstudiante;
    
        return $this;
    }

    /**
     * Get fotoEstudiante
     *
     * @return string 
     */
    public function getFotoEstudiante()
    {
        return $this->fotoEstudiante;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return OlimEstudianteInscripcion
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
     * Set usuarioRegistroId
     *
     * @param integer $usuarioRegistroId
     * @return OlimEstudianteInscripcion
     */
    public function setUsuarioRegistroId($usuarioRegistroId)
    {
        $this->usuarioRegistroId = $usuarioRegistroId;
    
        return $this;
    }

    /**
     * Get usuarioRegistroId
     *
     * @return integer 
     */
    public function getUsuarioRegistroId()
    {
        return $this->usuarioRegistroId;
    }

    /**
     * Set usuarioModificacionId
     *
     * @param integer $usuarioModificacionId
     * @return OlimEstudianteInscripcion
     */
    public function setUsuarioModificacionId($usuarioModificacionId)
    {
        $this->usuarioModificacionId = $usuarioModificacionId;
    
        return $this;
    }

    /**
     * Get usuarioModificacionId
     *
     * @return integer 
     */
    public function getUsuarioModificacionId()
    {
        return $this->usuarioModificacionId;
    }

    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return OlimEstudianteInscripcion
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }

    /**
     * Set olimReglasOlimpiadasTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo $olimReglasOlimpiadasTipo
     * @return OlimEstudianteInscripcion
     */
    public function setOlimReglasOlimpiadasTipo(\Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo $olimReglasOlimpiadasTipo = null)
    {
        $this->olimReglasOlimpiadasTipo = $olimReglasOlimpiadasTipo;
    
        return $this;
    }

    /**
     * Get olimReglasOlimpiadasTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimReglasOlimpiadasTipo 
     */
    public function getOlimReglasOlimpiadasTipo()
    {
        return $this->olimReglasOlimpiadasTipo;
    }

    /**
     * Set periodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimPeriodoTipo $periodoTipo
     * @return OlimEstudianteInscripcion
     */
    public function setPeriodoTipo(\Sie\AppWebBundle\Entity\OlimPeriodoTipo $periodoTipo = null)
    {
        $this->periodoTipo = $periodoTipo;
    
        return $this;
    }

    /**
     * Get periodoTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimPeriodoTipo 
     */
    public function getPeriodoTipo()
    {
        return $this->periodoTipo;
    }

    /**
     * Set categoriaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimCategoriaTipo $categoriaTipo
     * @return OlimEstudianteInscripcion
     */
    public function setCategoriaTipo(\Sie\AppWebBundle\Entity\OlimCategoriaTipo $categoriaTipo = null)
    {
        $this->categoriaTipo = $categoriaTipo;
    
        return $this;
    }

    /**
     * Get categoriaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimCategoriaTipo 
     */
    public function getCategoriaTipo()
    {
        return $this->categoriaTipo;
    }

    /**
     * Set materiaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimMateriaTipo $materiaTipo
     * @return OlimEstudianteInscripcion
     */
    public function setMateriaTipo(\Sie\AppWebBundle\Entity\OlimMateriaTipo $materiaTipo = null)
    {
        $this->materiaTipo = $materiaTipo;
    
        return $this;
    }

    /**
     * Get materiaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimMateriaTipo 
     */
    public function getMateriaTipo()
    {
        return $this->materiaTipo;
    }

    /**
     * Set discapacidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimDiscapacidadTipo $discapacidadTipo
     * @return OlimEstudianteInscripcion
     */
    public function setDiscapacidadTipo(\Sie\AppWebBundle\Entity\OlimDiscapacidadTipo $discapacidadTipo = null)
    {
        $this->discapacidadTipo = $discapacidadTipo;
    
        return $this;
    }

    /**
     * Get discapacidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimDiscapacidadTipo 
     */
    public function getDiscapacidadTipo()
    {
        return $this->discapacidadTipo;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return OlimEstudianteInscripcion
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
     * @var \Sie\AppWebBundle\Entity\OlimTutor
     */
    private $olimTutor;


    /**
     * Set olimTutor
     *
     * @param \Sie\AppWebBundle\Entity\OlimTutor $olimTutor
     * @return OlimEstudianteInscripcion
     */
    public function setOlimTutor(\Sie\AppWebBundle\Entity\OlimTutor $olimTutor = null)
    {
        $this->olimTutor = $olimTutor;
    
        return $this;
    }

    /**
     * Get olimTutor
     *
     * @return \Sie\AppWebBundle\Entity\OlimTutor 
     */
    public function getOlimTutor()
    {
        return $this->olimTutor;
    }
    /**
     * @var string
     */
    private $transaccion;


    /**
     * Set transaccion
     *
     * @param string $transaccion
     * @return OlimEstudianteInscripcion
     */
    public function setTransaccion($transaccion)
    {
        $this->transaccion = $transaccion;
    
        return $this;
    }

    /**
     * Get transaccion
     *
     * @return string 
     */
    public function getTransaccion()
    {
        return $this->transaccion;
    }
}
