<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatUniSede
 */
class UnivDatUniSede
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var string
     */
    private $universidadId;

    /**
     * @var string
     */
    private $resolucion;

    /**
     * @var string
     */
    private $direccion;

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
    private $sitioWeb;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $departamentoId;

    /**
     * @var string
     */
    private $createdAt;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return UnivDatUniSede
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set universidadId
     *
     * @param string $universidadId
     * @return UnivDatUniSede
     */
    public function setUniversidadId($universidadId)
    {
        $this->universidadId = $universidadId;
    
        return $this;
    }

    /**
     * Get universidadId
     *
     * @return string 
     */
    public function getUniversidadId()
    {
        return $this->universidadId;
    }

    /**
     * Set resolucion
     *
     * @param string $resolucion
     * @return UnivDatUniSede
     */
    public function setResolucion($resolucion)
    {
        $this->resolucion = $resolucion;
    
        return $this;
    }

    /**
     * Get resolucion
     *
     * @return string 
     */
    public function getResolucion()
    {
        return $this->resolucion;
    }

    /**
     * Set direccion
     *
     * @param string $direccion
     * @return UnivDatUniSede
     */
    public function setDireccion($direccion)
    {
        $this->direccion = $direccion;
    
        return $this;
    }

    /**
     * Get direccion
     *
     * @return string 
     */
    public function getDireccion()
    {
        return $this->direccion;
    }

    /**
     * Set telefono
     *
     * @param string $telefono
     * @return UnivDatUniSede
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
     * @return UnivDatUniSede
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
     * Set sitioWeb
     *
     * @param string $sitioWeb
     * @return UnivDatUniSede
     */
    public function setSitioWeb($sitioWeb)
    {
        $this->sitioWeb = $sitioWeb;
    
        return $this;
    }

    /**
     * Get sitioWeb
     *
     * @return string 
     */
    public function getSitioWeb()
    {
        return $this->sitioWeb;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return UnivDatUniSede
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
     * Set departamentoId
     *
     * @param string $departamentoId
     * @return UnivDatUniSede
     */
    public function setDepartamentoId($departamentoId)
    {
        $this->departamentoId = $departamentoId;
    
        return $this;
    }

    /**
     * Get departamentoId
     *
     * @return string 
     */
    public function getDepartamentoId()
    {
        return $this->departamentoId;
    }

    /**
     * Set createdAt
     *
     * @param string $createdAt
     * @return UnivDatUniSede
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return string 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
