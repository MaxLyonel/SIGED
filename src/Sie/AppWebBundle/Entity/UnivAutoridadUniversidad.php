<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivAutoridadUniversidad
 */
class UnivAutoridadUniversidad
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $ref;

    /**
     * @var string
     */
    private $telefono;

    /**
     * @var string
     */
    private $fax;

    /**
     * @var string
     */
    private $casilla;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $formaciondescripcion;

    /**
     * @var string
     */
    private $documentosAcad;

    /**
     * @var integer
     */
    private $ratificacionAnioIni;

    /**
     * @var integer
     */
    private $ratificacionAnioFin;

    /**
     * @var \DateTime
     */
    private $fechaRegistroFirma;

    /**
     * @var \DateTime
     */
    private $fechaCreacion;

    /**
     * @var \DateTime
     */
    private $fechaActualizacion;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionNombramiento;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivFormacionTipo
     */
    private $univFormacionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivCargoJerarquicoTipo
     */
    private $univCargoJerarquicoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivSede
     */
    private $univSede;


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
     * Set ref
     *
     * @param string $ref
     * @return UnivAutoridadUniversidad
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    
        return $this;
    }

    /**
     * Get ref
     *
     * @return string 
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return UnivAutoridadUniversidad
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
     * Set fax
     *
     * @param string $fax
     * @return UnivAutoridadUniversidad
     */
    public function setFax($fax)
    {
        $this->fax = $fax;
    
        return $this;
    }

    /**
     * Get fax
     *
     * @return string 
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set casilla
     *
     * @param string $casilla
     * @return UnivAutoridadUniversidad
     */
    public function setCasilla($casilla)
    {
        $this->casilla = $casilla;
    
        return $this;
    }

    /**
     * Get casilla
     *
     * @return string 
     */
    public function getCasilla()
    {
        return $this->casilla;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return UnivAutoridadUniversidad
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set formaciondescripcion
     *
     * @param string $formaciondescripcion
     * @return UnivAutoridadUniversidad
     */
    public function setFormaciondescripcion($formaciondescripcion)
    {
        $this->formaciondescripcion = $formaciondescripcion;
    
        return $this;
    }

    /**
     * Get formaciondescripcion
     *
     * @return string 
     */
    public function getFormaciondescripcion()
    {
        return $this->formaciondescripcion;
    }

    /**
     * Set documentosAcad
     *
     * @param string $documentosAcad
     * @return UnivAutoridadUniversidad
     */
    public function setDocumentosAcad($documentosAcad)
    {
        $this->documentosAcad = $documentosAcad;
    
        return $this;
    }

    /**
     * Get documentosAcad
     *
     * @return string 
     */
    public function getDocumentosAcad()
    {
        return $this->documentosAcad;
    }

    /**
     * Set ratificacionAnioIni
     *
     * @param integer $ratificacionAnioIni
     * @return UnivAutoridadUniversidad
     */
    public function setRatificacionAnioIni($ratificacionAnioIni)
    {
        $this->ratificacionAnioIni = $ratificacionAnioIni;
    
        return $this;
    }

    /**
     * Get ratificacionAnioIni
     *
     * @return integer 
     */
    public function getRatificacionAnioIni()
    {
        return $this->ratificacionAnioIni;
    }

    /**
     * Set ratificacionAnioFin
     *
     * @param integer $ratificacionAnioFin
     * @return UnivAutoridadUniversidad
     */
    public function setRatificacionAnioFin($ratificacionAnioFin)
    {
        $this->ratificacionAnioFin = $ratificacionAnioFin;
    
        return $this;
    }

    /**
     * Get ratificacionAnioFin
     *
     * @return integer 
     */
    public function getRatificacionAnioFin()
    {
        return $this->ratificacionAnioFin;
    }

    /**
     * Set fechaRegistroFirma
     *
     * @param \DateTime $fechaRegistroFirma
     * @return UnivAutoridadUniversidad
     */
    public function setFechaRegistroFirma($fechaRegistroFirma)
    {
        $this->fechaRegistroFirma = $fechaRegistroFirma;
    
        return $this;
    }

    /**
     * Get fechaRegistroFirma
     *
     * @return \DateTime 
     */
    public function getFechaRegistroFirma()
    {
        return $this->fechaRegistroFirma;
    }

    /**
     * Set fechaCreacion
     *
     * @param \DateTime $fechaCreacion
     * @return UnivAutoridadUniversidad
     */
    public function setFechaCreacion($fechaCreacion)
    {
        $this->fechaCreacion = $fechaCreacion;
    
        return $this;
    }

    /**
     * Get fechaCreacion
     *
     * @return \DateTime 
     */
    public function getFechaCreacion()
    {
        return $this->fechaCreacion;
    }

    /**
     * Set fechaActualizacion
     *
     * @param \DateTime $fechaActualizacion
     * @return UnivAutoridadUniversidad
     */
    public function setFechaActualizacion($fechaActualizacion)
    {
        $this->fechaActualizacion = $fechaActualizacion;
    
        return $this;
    }

    /**
     * Get fechaActualizacion
     *
     * @return \DateTime 
     */
    public function getFechaActualizacion()
    {
        return $this->fechaActualizacion;
    }

    /**
     * Set gestionNombramiento
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionNombramiento
     * @return UnivAutoridadUniversidad
     */
    public function setGestionNombramiento(\Sie\AppWebBundle\Entity\GestionTipo $gestionNombramiento = null)
    {
        $this->gestionNombramiento = $gestionNombramiento;
    
        return $this;
    }

    /**
     * Get gestionNombramiento
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionNombramiento()
    {
        return $this->gestionNombramiento;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return UnivAutoridadUniversidad
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
     * Set univFormacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivFormacionTipo $univFormacionTipo
     * @return UnivAutoridadUniversidad
     */
    public function setUnivFormacionTipo(\Sie\AppWebBundle\Entity\UnivFormacionTipo $univFormacionTipo = null)
    {
        $this->univFormacionTipo = $univFormacionTipo;
    
        return $this;
    }

    /**
     * Get univFormacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivFormacionTipo 
     */
    public function getUnivFormacionTipo()
    {
        return $this->univFormacionTipo;
    }

    /**
     * Set univCargoJerarquicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivCargoJerarquicoTipo $univCargoJerarquicoTipo
     * @return UnivAutoridadUniversidad
     */
    public function setUnivCargoJerarquicoTipo(\Sie\AppWebBundle\Entity\UnivCargoJerarquicoTipo $univCargoJerarquicoTipo = null)
    {
        $this->univCargoJerarquicoTipo = $univCargoJerarquicoTipo;
    
        return $this;
    }

    /**
     * Get univCargoJerarquicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivCargoJerarquicoTipo 
     */
    public function getUnivCargoJerarquicoTipo()
    {
        return $this->univCargoJerarquicoTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return UnivAutoridadUniversidad
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
     * Set univSede
     *
     * @param \Sie\AppWebBundle\Entity\UnivSede $univSede
     * @return UnivAutoridadUniversidad
     */
    public function setUnivSede(\Sie\AppWebBundle\Entity\UnivSede $univSede = null)
    {
        $this->univSede = $univSede;
    
        return $this;
    }

    /**
     * Get univSede
     *
     * @return \Sie\AppWebBundle\Entity\UnivSede 
     */
    public function getUnivSede()
    {
        return $this->univSede;
    }
}
