<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimTutor
 */
class OlimTutor
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $telefono1;

    /**
     * @var string
     */
    private $telefono2;

    /**
     * @var string
     */
    private $correoElectronico;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

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
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

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
     * Set telefono1
     *
     * @param string $telefono1
     * @return OlimTutor
     */
    public function setTelefono1($telefono1)
    {
        $this->telefono1 = $telefono1;
    
        return $this;
    }

    /**
     * Get telefono1
     *
     * @return string 
     */
    public function getTelefono1()
    {
        return $this->telefono1;
    }

    /**
     * Set telefono2
     *
     * @param string $telefono2
     * @return OlimTutor
     */
    public function setTelefono2($telefono2)
    {
        $this->telefono2 = $telefono2;
    
        return $this;
    }

    /**
     * Get telefono2
     *
     * @return string 
     */
    public function getTelefono2()
    {
        return $this->telefono2;
    }

    /**
     * Set correoElectronico
     *
     * @param string $correoElectronico
     * @return OlimTutor
     */
    public function setCorreoElectronico($correoElectronico)
    {
        $this->correoElectronico = $correoElectronico;
    
        return $this;
    }

    /**
     * Get correoElectronico
     *
     * @return string 
     */
    public function getCorreoElectronico()
    {
        return $this->correoElectronico;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return OlimTutor
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
     * @return OlimTutor
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
     * @return OlimTutor
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
     * @return OlimTutor
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
     * @return OlimTutor
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
     * Set periodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimPeriodoTipo $periodoTipo
     * @return OlimTutor
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
     * @return OlimTutor
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
     * @return OlimTutor
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return OlimTutor
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return OlimTutor
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
}
