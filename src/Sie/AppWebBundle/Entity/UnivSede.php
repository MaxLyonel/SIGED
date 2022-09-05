<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivSede
 */
class UnivSede
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $sede;

    /**
     * @var string
     */
    private $resolucionSuprema;

    /**
     * @var string
     */
    private $resolucionMinisterial;

    /**
     * @var string
     */
    private $naturalezaJuridica;

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
     * @var \Sie\AppWebBundle\Entity\UnivSedeTipo
     */
    private $univSedeTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivNaturalezajuridicaTipo
     */
    private $univNaturalezajuridicaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivUniversidad
     */
    private $univUniversidad;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivJurisdiccionGeografica
     */
    private $univJuridicciongeografica;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadoinstitucionTipo
     */
    private $estadoinstitucionTipo;

    public function __toString(){
        return $this->sede;
    }

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
     * Set sede
     *
     * @param string $sede
     * @return UnivSede
     */
    public function setSede($sede)
    {
        $this->sede = $sede;
    
        return $this;
    }

    /**
     * Get sede
     *
     * @return string 
     */
    public function getSede()
    {
        return $this->sede;
    }

    /**
     * Set resolucionSuprema
     *
     * @param string $resolucionSuprema
     * @return UnivSede
     */
    public function setResolucionSuprema($resolucionSuprema)
    {
        $this->resolucionSuprema = $resolucionSuprema;
    
        return $this;
    }

    /**
     * Get resolucionSuprema
     *
     * @return string 
     */
    public function getResolucionSuprema()
    {
        return $this->resolucionSuprema;
    }

    /**
     * Set resolucionMinisterial
     *
     * @param string $resolucionMinisterial
     * @return UnivSede
     */
    public function setResolucionMinisterial($resolucionMinisterial)
    {
        $this->resolucionMinisterial = $resolucionMinisterial;
    
        return $this;
    }

    /**
     * Get resolucionMinisterial
     *
     * @return string 
     */
    public function getResolucionMinisterial()
    {
        return $this->resolucionMinisterial;
    }

    /**
     * Set naturalezaJuridica
     *
     * @param string $naturalezaJuridica
     * @return UnivSede
     */
    public function setNaturalezaJuridica($naturalezaJuridica)
    {
        $this->naturalezaJuridica = $naturalezaJuridica;
    
        return $this;
    }

    /**
     * Get naturalezaJuridica
     *
     * @return string 
     */
    public function getNaturalezaJuridica()
    {
        return $this->naturalezaJuridica;
    }

    /**
     * Set telefono1
     *
     * @param string $telefono1
     * @return UnivSede
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
     * @return UnivSede
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
     * @return UnivSede
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
     * @return UnivSede
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
     * @return UnivSede
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
     * @return UnivSede
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
     * @return UnivSede
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
     * @return UnivSede
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
     * @return UnivSede
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
     * @return UnivSede
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
     * Set univSedeTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivSedeTipo $univSedeTipo
     * @return UnivSede
     */
    public function setUnivSedeTipo(\Sie\AppWebBundle\Entity\UnivSedeTipo $univSedeTipo = null)
    {
        $this->univSedeTipo = $univSedeTipo;
    
        return $this;
    }

    /**
     * Get univSedeTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivSedeTipo 
     */
    public function getUnivSedeTipo()
    {
        return $this->univSedeTipo;
    }

    /**
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return UnivSede
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
     * Set univNaturalezajuridicaTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivNaturalezajuridicaTipo $univNaturalezajuridicaTipo
     * @return UnivSede
     */
    public function setUnivNaturalezajuridicaTipo(\Sie\AppWebBundle\Entity\UnivNaturalezajuridicaTipo $univNaturalezajuridicaTipo = null)
    {
        $this->univNaturalezajuridicaTipo = $univNaturalezajuridicaTipo;
    
        return $this;
    }

    /**
     * Get univNaturalezajuridicaTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivNaturalezajuridicaTipo 
     */
    public function getUnivNaturalezajuridicaTipo()
    {
        return $this->univNaturalezajuridicaTipo;
    }

    /**
     * Set univUniversidad
     *
     * @param \Sie\AppWebBundle\Entity\UnivUniversidad $univUniversidad
     * @return UnivSede
     */
    public function setUnivUniversidad(\Sie\AppWebBundle\Entity\UnivUniversidad $univUniversidad = null)
    {
        $this->univUniversidad = $univUniversidad;
    
        return $this;
    }

    /**
     * Get univUniversidad
     *
     * @return \Sie\AppWebBundle\Entity\UnivUniversidad 
     */
    public function getUnivUniversidad()
    {
        return $this->univUniversidad;
    }

    /**
     * Set univJuridicciongeografica
     *
     * @param \Sie\AppWebBundle\Entity\UnivJurisdiccionGeografica $univJuridicciongeografica
     * @return UnivSede
     */
    public function setUnivJuridicciongeografica(\Sie\AppWebBundle\Entity\UnivJurisdiccionGeografica $univJuridicciongeografica = null)
    {
        $this->univJuridicciongeografica = $univJuridicciongeografica;
    
        return $this;
    }

    /**
     * Get univJuridicciongeografica
     *
     * @return \Sie\AppWebBundle\Entity\UnivJurisdiccionGeografica 
     */
    public function getUnivJuridicciongeografica()
    {
        return $this->univJuridicciongeografica;
    }

    /**
     * Set estadoinstitucionTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstadoinstitucionTipo $estadoinstitucionTipo
     * @return UnivSede
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
}
