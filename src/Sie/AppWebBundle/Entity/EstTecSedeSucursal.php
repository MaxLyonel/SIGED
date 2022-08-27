<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstTecSedeSucursal
 */
class EstTecSedeSucursal
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
    private $referenciaTelefono2;

    /**
     * @var \DateTime
     */
    private $inicioCalendarioAcademico;

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
    private $sitioWeb;

    /**
     * @var string
     */
    private $email;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadoinstitucionTipo
     */
    private $estadoinstitucionTipo;

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
     * Set telefono1
     *
     * @param string $telefono1
     * @return EstTecSedeSucursal
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
     * @return EstTecSedeSucursal
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
     * Set referenciaTelefono2
     *
     * @param string $referenciaTelefono2
     * @return EstTecSedeSucursal
     */
    public function setReferenciaTelefono2($referenciaTelefono2)
    {
        $this->referenciaTelefono2 = $referenciaTelefono2;
    
        return $this;
    }

    /**
     * Get referenciaTelefono2
     *
     * @return string 
     */
    public function getReferenciaTelefono2()
    {
        return $this->referenciaTelefono2;
    }

    /**
     * Set inicioCalendarioAcademico
     *
     * @param \DateTime $inicioCalendarioAcademico
     * @return EstTecSedeSucursal
     */
    public function setInicioCalendarioAcademico($inicioCalendarioAcademico)
    {
        $this->inicioCalendarioAcademico = $inicioCalendarioAcademico;
    
        return $this;
    }

    /**
     * Get inicioCalendarioAcademico
     *
     * @return \DateTime 
     */
    public function getInicioCalendarioAcademico()
    {
        return $this->inicioCalendarioAcademico;
    }

    /**
     * Set fax
     *
     * @param string $fax
     * @return EstTecSedeSucursal
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
     * @return EstTecSedeSucursal
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
     * Set sitioWeb
     *
     * @param string $sitioWeb
     * @return EstTecSedeSucursal
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
     * @return EstTecSedeSucursal
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EstTecSedeSucursal
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
     * @return EstTecSedeSucursal
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
     * Set estadoinstitucionTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstadoinstitucionTipo $estadoinstitucionTipo
     * @return EstTecSedeSucursal
     */
    public function setEstadoinstitucionTipo(\Sie\AppWebBundle\Entity\EstadoinstitucionTipo $estadoinstitucionTipo = null)
    {
        $this->estadoinstitucionTipo = $estadoinstitucionTipo;
    
        return $this;
    }

    /**
     * Get estadoinstitucionTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstadoinstitucionTipo 
     */
    public function getEstadoinstitucionTipo()
    {
        return $this->estadoinstitucionTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return EstTecSedeSucursal
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
     * @return EstTecSedeSucursal
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
}
