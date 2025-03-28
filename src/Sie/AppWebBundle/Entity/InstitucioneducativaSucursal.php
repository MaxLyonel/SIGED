<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaSucursal
 */
class InstitucioneducativaSucursal
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombreSubcea;

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
     * @var string
     */
    private $fax;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $casilla;

    /**
     * @var integer
     */
    private $codCerradaId;

    /**
     * @var integer
     */
    private $periodoTipoId;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\JurisdiccionGeografica
     */
    private $leJuridicciongeografica;

    /**
     * @var \Sie\AppWebBundle\Entity\SucursalTipo
     */
    private $sucursalTipo;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Set nombreSubcea
     *
     * @param string $nombreSubcea
     * @return InstitucioneducativaSucursal
     */
    public function setNombreSubcea($nombreSubcea)
    {
        $this->nombreSubcea = $nombreSubcea;

        return $this;
    }

    /**
     * Get nombreSubcea
     *
     * @return string 
     */
    public function getNombreSubcea()
    {
        return $this->nombreSubcea;
    }

    /**
     * Set telefono1
     *
     * @param string $telefono1
     * @return InstitucioneducativaSucursal
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
     * @return InstitucioneducativaSucursal
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
     * @return InstitucioneducativaSucursal
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
     * Set fax
     *
     * @param string $fax
     * @return InstitucioneducativaSucursal
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
     * Set email
     *
     * @param string $email
     * @return InstitucioneducativaSucursal
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
     * Set casilla
     *
     * @param string $casilla
     * @return InstitucioneducativaSucursal
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
     * Set codCerradaId
     *
     * @param integer $codCerradaId
     * @return InstitucioneducativaSucursal
     */
    public function setCodCerradaId($codCerradaId)
    {
        $this->codCerradaId = $codCerradaId;

        return $this;
    }

    /**
     * Get codCerradaId
     *
     * @return integer 
     */
    public function getCodCerradaId()
    {
        return $this->codCerradaId;
    }

    /**
     * Set periodoTipoId
     *
     * @param integer $periodoTipoId
     * @return InstitucioneducativaSucursal
     */
    public function setPeriodoTipoId($periodoTipoId)
    {
        $this->periodoTipoId = $periodoTipoId;

        return $this;
    }

    /**
     * Get periodoTipoId
     *
     * @return integer 
     */
    public function getPeriodoTipoId()
    {
        return $this->periodoTipoId;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return InstitucioneducativaSucursal
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaSucursal
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
     * Set leJuridicciongeografica
     *
     * @param \Sie\AppWebBundle\Entity\JurisdiccionGeografica $leJuridicciongeografica
     * @return InstitucioneducativaSucursal
     */
    public function setLeJuridicciongeografica(\Sie\AppWebBundle\Entity\JurisdiccionGeografica $leJuridicciongeografica = null)
    {
        $this->leJuridicciongeografica = $leJuridicciongeografica;

        return $this;
    }

    /**
     * Get leJuridicciongeografica
     *
     * @return \Sie\AppWebBundle\Entity\JurisdiccionGeografica 
     */
    public function getLeJuridicciongeografica()
    {
        return $this->leJuridicciongeografica;
    }

    /**
     * Set sucursalTipo
     *
     * @param \Sie\AppWebBundle\Entity\SucursalTipo $sucursalTipo
     * @return InstitucioneducativaSucursal
     */
    public function setSucursalTipo(\Sie\AppWebBundle\Entity\SucursalTipo $sucursalTipo = null)
    {
        $this->sucursalTipo = $sucursalTipo;

        return $this;
    }

    /**
     * Get sucursalTipo
     *
     * @return \Sie\AppWebBundle\Entity\SucursalTipo 
     */
    public function getSucursalTipo()
    {
        return $this->sucursalTipo;
    }
    /**
     * @var string
     */
    private $direccion;

    /**
     * @var string
     */
    private $zona;

    /**
     * @var boolean
     */
    private $esabierta;

    /**
     * @var \Sie\AppWebBundle\Entity\TurnoTipo
     */
    private $turnoTipo;


    /**
     * Set direccion
     *
     * @param string $direccion
     * @return InstitucioneducativaSucursal
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
     * Set zona
     *
     * @param string $zona
     * @return InstitucioneducativaSucursal
     */
    public function setZona($zona)
    {
        $this->zona = $zona;
    
        return $this;
    }

    /**
     * Get zona
     *
     * @return string 
     */
    public function getZona()
    {
        return $this->zona;
    }

    /**
     * Set esabierta
     *
     * @param boolean $esabierta
     * @return InstitucioneducativaSucursal
     */
    public function setEsabierta($esabierta)
    {
        $this->esabierta = $esabierta;
    
        return $this;
    }

    /**
     * Get esabierta
     *
     * @return boolean 
     */
    public function getEsabierta()
    {
        return $this->esabierta;
    }

    /**
     * Set turnoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TurnoTipo $turnoTipo
     * @return InstitucioneducativaSucursal
     */
    public function setTurnoTipo(\Sie\AppWebBundle\Entity\TurnoTipo $turnoTipo = null)
    {
        $this->turnoTipo = $turnoTipo;
    
        return $this;
    }

    /**
     * Get turnoTipo
     *
     * @return \Sie\AppWebBundle\Entity\TurnoTipo 
     */
    public function getTurnoTipo()
    {
        return $this->turnoTipo;
    }
}
