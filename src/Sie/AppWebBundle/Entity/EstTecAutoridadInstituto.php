<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstTecAutoridadInstituto
 */
class EstTecAutoridadInstituto
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
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecFormacionTipo
     */
    private $estTecFormacionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecCargoJerarquicoTipo
     */
    private $estTecCargoJerarquicoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecSede
     */
    private $estTecSede;


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
     * @return EstTecAutoridadInstituto
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
     * @return EstTecAutoridadInstituto
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
     * @return EstTecAutoridadInstituto
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
     * @return EstTecAutoridadInstituto
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
     * @return EstTecAutoridadInstituto
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
     * @return EstTecAutoridadInstituto
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
     * @return EstTecAutoridadInstituto
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
     * Set fechaRegistroFirma
     *
     * @param \DateTime $fechaRegistroFirma
     * @return EstTecAutoridadInstituto
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
     * @return EstTecAutoridadInstituto
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
     * @return EstTecAutoridadInstituto
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
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return EstTecAutoridadInstituto
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
     * Set estTecFormacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecFormacionTipo $estTecFormacionTipo
     * @return EstTecAutoridadInstituto
     */
    public function setEstTecFormacionTipo(\Sie\AppWebBundle\Entity\EstTecFormacionTipo $estTecFormacionTipo = null)
    {
        $this->estTecFormacionTipo = $estTecFormacionTipo;
    
        return $this;
    }

    /**
     * Get estTecFormacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstTecFormacionTipo 
     */
    public function getEstTecFormacionTipo()
    {
        return $this->estTecFormacionTipo;
    }

    /**
     * Set estTecCargoJerarquicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecCargoJerarquicoTipo $estTecCargoJerarquicoTipo
     * @return EstTecAutoridadInstituto
     */
    public function setEstTecCargoJerarquicoTipo(\Sie\AppWebBundle\Entity\EstTecCargoJerarquicoTipo $estTecCargoJerarquicoTipo = null)
    {
        $this->estTecCargoJerarquicoTipo = $estTecCargoJerarquicoTipo;
    
        return $this;
    }

    /**
     * Get estTecCargoJerarquicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstTecCargoJerarquicoTipo 
     */
    public function getEstTecCargoJerarquicoTipo()
    {
        return $this->estTecCargoJerarquicoTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return EstTecAutoridadInstituto
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
     * Set estTecSede
     *
     * @param \Sie\AppWebBundle\Entity\EstTecSede $estTecSede
     * @return EstTecAutoridadInstituto
     */
    public function setEstTecSede(\Sie\AppWebBundle\Entity\EstTecSede $estTecSede = null)
    {
        $this->estTecSede = $estTecSede;
    
        return $this;
    }

    /**
     * Get estTecSede
     *
     * @return \Sie\AppWebBundle\Entity\EstTecSede 
     */
    public function getEstTecSede()
    {
        return $this->estTecSede;
    }
    /**
     * @var string
     */
    private $documentosFirma;


    /**
     * Set documentosFirma
     *
     * @param string $documentosFirma
     * @return EstTecAutoridadInstituto
     */
    public function setDocumentosFirma($documentosFirma)
    {
        $this->documentosFirma = $documentosFirma;
    
        return $this;
    }

    /**
     * Get documentosFirma
     *
     * @return string 
     */
    public function getDocumentosFirma()
    {
        return $this->documentosFirma;
    }
}
