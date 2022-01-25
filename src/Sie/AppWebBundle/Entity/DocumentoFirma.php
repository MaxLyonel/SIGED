<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DocumentoFirma
 */
class DocumentoFirma
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $firma;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var string
     */
    private $tokenFirma;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\LugarTipo
     */
    private $lugarTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;


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
     * Set firma
     *
     * @param string $firma
     * @return DocumentoFirma
     */
    public function setFirma($firma)
    {
        $this->firma = $firma;
    
        return $this;
    }

    /**
     * Get firma
     *
     * @return string 
     */
    public function getFirma()
    {
        return $this->firma;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return DocumentoFirma
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return DocumentoFirma
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return DocumentoFirma
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
     * Set tokenFirma
     *
     * @param string $tokenFirma
     * @return DocumentoFirma
     */
    public function setTokenFirma($tokenFirma)
    {
        $this->tokenFirma = $tokenFirma;
    
        return $this;
    }

    /**
     * Get tokenFirma
     *
     * @return string 
     */
    public function getTokenFirma()
    {
        return $this->tokenFirma;
    }

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return DocumentoFirma
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
     * Set lugarTipo
     *
     * @param \Sie\AppWebBundle\Entity\LugarTipo $lugarTipo
     * @return DocumentoFirma
     */
    public function setLugarTipo(\Sie\AppWebBundle\Entity\LugarTipo $lugarTipo = null)
    {
        $this->lugarTipo = $lugarTipo;
    
        return $this;
    }

    /**
     * Get lugarTipo
     *
     * @return \Sie\AppWebBundle\Entity\LugarTipo 
     */
    public function getLugarTipo()
    {
        return $this->lugarTipo;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return DocumentoFirma
     */
    public function setUsuario(\Sie\AppWebBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\PersonaTipo
     */
    private $personaTipo;


    /**
     * Set personaTipo
     *
     * @param \Sie\AppWebBundle\Entity\PersonaTipo $personaTipo
     * @return DocumentoFirma
     */
    public function setPersonaTipo(\Sie\AppWebBundle\Entity\PersonaTipo $personaTipo = null)
    {
        $this->personaTipo = $personaTipo;
    
        return $this;
    }

    /**
     * Get personaTipo
     *
     * @return \Sie\AppWebBundle\Entity\PersonaTipo 
     */
    public function getPersonaTipo()
    {
        return $this->personaTipo;
    }
}
