<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaEncuesta
 */
class InstitucioneducativaEncuesta
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $cantidadSala;

    /**
     * @var integer
     */
    private $cantidadComputadora;

    /**
     * @var integer
     */
    private $anioAdquisicion;

    /**
     * @var integer
     */
    private $costoInternet;

    /**
     * @var string
     */
    private $proveedorOtro;

    /**
     * @var string
     */
    private $foto1;

    /**
     * @var string
     */
    private $foto2;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var string
     */
    private $pagoOtro;

    /**
     * @var integer
     */
    private $tieneSala;

    /**
     * @var integer
     */
    private $tienePiso;

    /**
     * @var integer
     */
    private $tieneInternet;

    /**
     * @var integer
     */
    private $tieneSenal;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ProveedorTipo
     */
    private $proveedorUe;

    /**
     * @var \Sie\AppWebBundle\Entity\ProveedorTipo
     */
    private $proveedorZona;

    /**
     * @var \Sie\AppWebBundle\Entity\ProveedorTipo
     */
    private $realizaPago;


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
     * Set cantidadSala
     *
     * @param integer $cantidadSala
     * @return InstitucioneducativaEncuesta
     */
    public function setCantidadSala($cantidadSala)
    {
        $this->cantidadSala = $cantidadSala;
    
        return $this;
    }

    /**
     * Get cantidadSala
     *
     * @return integer 
     */
    public function getCantidadSala()
    {
        return $this->cantidadSala;
    }

    /**
     * Set cantidadComputadora
     *
     * @param integer $cantidadComputadora
     * @return InstitucioneducativaEncuesta
     */
    public function setCantidadComputadora($cantidadComputadora)
    {
        $this->cantidadComputadora = $cantidadComputadora;
    
        return $this;
    }

    /**
     * Get cantidadComputadora
     *
     * @return integer 
     */
    public function getCantidadComputadora()
    {
        return $this->cantidadComputadora;
    }

    /**
     * Set anioAdquisicion
     *
     * @param integer $anioAdquisicion
     * @return InstitucioneducativaEncuesta
     */
    public function setAnioAdquisicion($anioAdquisicion)
    {
        $this->anioAdquisicion = $anioAdquisicion;
    
        return $this;
    }

    /**
     * Get anioAdquisicion
     *
     * @return integer 
     */
    public function getAnioAdquisicion()
    {
        return $this->anioAdquisicion;
    }

    /**
     * Set costoInternet
     *
     * @param integer $costoInternet
     * @return InstitucioneducativaEncuesta
     */
    public function setCostoInternet($costoInternet)
    {
        $this->costoInternet = $costoInternet;
    
        return $this;
    }

    /**
     * Get costoInternet
     *
     * @return integer 
     */
    public function getCostoInternet()
    {
        return $this->costoInternet;
    }

    /**
     * Set proveedorOtro
     *
     * @param string $proveedorOtro
     * @return InstitucioneducativaEncuesta
     */
    public function setProveedorOtro($proveedorOtro)
    {
        $this->proveedorOtro = $proveedorOtro;
    
        return $this;
    }

    /**
     * Get proveedorOtro
     *
     * @return string 
     */
    public function getProveedorOtro()
    {
        return $this->proveedorOtro;
    }

    /**
     * Set foto1
     *
     * @param string $foto1
     * @return InstitucioneducativaEncuesta
     */
    public function setFoto1($foto1)
    {
        $this->foto1 = $foto1;
    
        return $this;
    }

    /**
     * Get foto1
     *
     * @return string 
     */
    public function getFoto1()
    {
        return $this->foto1;
    }

    /**
     * Set foto2
     *
     * @param string $foto2
     * @return InstitucioneducativaEncuesta
     */
    public function setFoto2($foto2)
    {
        $this->foto2 = $foto2;
    
        return $this;
    }

    /**
     * Get foto2
     *
     * @return string 
     */
    public function getFoto2()
    {
        return $this->foto2;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaEncuesta
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
     * @return InstitucioneducativaEncuesta
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
     * Set observacion
     *
     * @param string $observacion
     * @return InstitucioneducativaEncuesta
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set pagoOtro
     *
     * @param string $pagoOtro
     * @return InstitucioneducativaEncuesta
     */
    public function setPagoOtro($pagoOtro)
    {
        $this->pagoOtro = $pagoOtro;
    
        return $this;
    }

    /**
     * Get pagoOtro
     *
     * @return string 
     */
    public function getPagoOtro()
    {
        return $this->pagoOtro;
    }

    /**
     * Set tieneSala
     *
     * @param integer $tieneSala
     * @return InstitucioneducativaEncuesta
     */
    public function setTieneSala($tieneSala)
    {
        $this->tieneSala = $tieneSala;
    
        return $this;
    }

    /**
     * Get tieneSala
     *
     * @return integer 
     */
    public function getTieneSala()
    {
        return $this->tieneSala;
    }

    /**
     * Set tienePiso
     *
     * @param integer $tienePiso
     * @return InstitucioneducativaEncuesta
     */
    public function setTienePiso($tienePiso)
    {
        $this->tienePiso = $tienePiso;
    
        return $this;
    }

    /**
     * Get tienePiso
     *
     * @return integer 
     */
    public function getTienePiso()
    {
        return $this->tienePiso;
    }

    /**
     * Set tieneInternet
     *
     * @param integer $tieneInternet
     * @return InstitucioneducativaEncuesta
     */
    public function setTieneInternet($tieneInternet)
    {
        $this->tieneInternet = $tieneInternet;
    
        return $this;
    }

    /**
     * Get tieneInternet
     *
     * @return integer 
     */
    public function getTieneInternet()
    {
        return $this->tieneInternet;
    }

    /**
     * Set tieneSenal
     *
     * @param integer $tieneSenal
     * @return InstitucioneducativaEncuesta
     */
    public function setTieneSenal($tieneSenal)
    {
        $this->tieneSenal = $tieneSenal;
    
        return $this;
    }

    /**
     * Get tieneSenal
     *
     * @return integer 
     */
    public function getTieneSenal()
    {
        return $this->tieneSenal;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaEncuesta
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return InstitucioneducativaEncuesta
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
     * Set proveedorUe
     *
     * @param \Sie\AppWebBundle\Entity\ProveedorTipo $proveedorUe
     * @return InstitucioneducativaEncuesta
     */
    public function setProveedorUe(\Sie\AppWebBundle\Entity\ProveedorTipo $proveedorUe = null)
    {
        $this->proveedorUe = $proveedorUe;
    
        return $this;
    }

    /**
     * Get proveedorUe
     *
     * @return \Sie\AppWebBundle\Entity\ProveedorTipo 
     */
    public function getProveedorUe()
    {
        return $this->proveedorUe;
    }

    /**
     * Set proveedorZona
     *
     * @param \Sie\AppWebBundle\Entity\ProveedorTipo $proveedorZona
     * @return InstitucioneducativaEncuesta
     */
    public function setProveedorZona(\Sie\AppWebBundle\Entity\ProveedorTipo $proveedorZona = null)
    {
        $this->proveedorZona = $proveedorZona;
    
        return $this;
    }

    /**
     * Get proveedorZona
     *
     * @return \Sie\AppWebBundle\Entity\ProveedorTipo 
     */
    public function getProveedorZona()
    {
        return $this->proveedorZona;
    }

    /**
     * Set realizaPago
     *
     * @param \Sie\AppWebBundle\Entity\RealizaPagoTipo $realizaPago
     * @return InstitucioneducativaEncuesta
     */
    public function setRealizaPago(\Sie\AppWebBundle\Entity\RealizaPagoTipo $realizaPago = null)
    {
        $this->realizaPago = $realizaPago;
    
        return $this;
    }

    /**
     * Get realizaPago
     *
     * @return \Sie\AppWebBundle\Entity\RealizaPagoTipo 
     */
    public function getRealizaPago()
    {
        return $this->realizaPago;
    }
    /**
     * @var string
     */
    private $proveedorZonaOtro;


    /**
     * Set proveedorZonaOtro
     *
     * @param string $proveedorZonaOtro
     * @return InstitucioneducativaEncuesta
     */
    public function setProveedorZonaOtro($proveedorZonaOtro)
    {
        $this->proveedorZonaOtro = $proveedorZonaOtro;
    
        return $this;
    }

    /**
     * Get proveedorZonaOtro
     *
     * @return string 
     */
    public function getProveedorZonaOtro()
    {
        return $this->proveedorZonaOtro;
    }
}
