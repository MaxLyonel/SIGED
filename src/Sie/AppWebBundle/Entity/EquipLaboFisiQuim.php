<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EquipLaboFisiQuim
 */
class EquipLaboFisiQuim
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $secciCantidadTotEstu;

    /**
     * @var integer
     */
    private $secciCantidad1ersec;

    /**
     * @var integer
     */
    private $secciCantidad2dosec;

    /**
     * @var integer
     */
    private $secciCantidad3ersec;

    /**
     * @var integer
     */
    private $secciCantidad4tosec;

    /**
     * @var integer
     */
    private $secciCantidad5tosec;

    /**
     * @var integer
     */
    private $secciCantidad6tosec;

    /**
     * @var boolean
     */
    private $seccivEsLabFisQuim;

    /**
     * @var integer
     */
    private $seccivCantAmb;

    /**
     * @var boolean
     */
    private $seccivCuentaMesones;

    /**
     * @var boolean
     */
    private $seccivEsMesonesCeramica;

    /**
     * @var boolean
     */
    private $seccivCuentaPiletas;

    /**
     * @var integer
     */
    private $seccivCantidadPiletas;

    /**
     * @var boolean
     */
    private $seccivCuentaInstElec;

    /**
     * @var integer
     */
    private $seccivCantidadTomaCorr;

    /**
     * @var boolean
     */
    private $seccvCuentaEquipLabCiencias;

    /**
     * @var integer
     */
    private $seccvAnioEquipado;

    /**
     * @var string
     */
    private $seccvInstitucionEquipo;

    /**
     * @var integer
     */
    private $seccvCantidadItems;

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
    private $nombreAlcalde;

    /**
     * @var string
     */
    private $telefonoAlcalde;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\EquipLaboFisiQuimConstruidaTipo
     */
    private $seccivConstruidaTipo;


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
     * Set secciCantidadTotEstu
     *
     * @param integer $secciCantidadTotEstu
     * @return EquipLaboFisiQuim
     */
    public function setSecciCantidadTotEstu($secciCantidadTotEstu)
    {
        $this->secciCantidadTotEstu = $secciCantidadTotEstu;
    
        return $this;
    }

    /**
     * Get secciCantidadTotEstu
     *
     * @return integer 
     */
    public function getSecciCantidadTotEstu()
    {
        return $this->secciCantidadTotEstu;
    }

    /**
     * Set secciCantidad1ersec
     *
     * @param integer $secciCantidad1ersec
     * @return EquipLaboFisiQuim
     */
    public function setSecciCantidad1ersec($secciCantidad1ersec)
    {
        $this->secciCantidad1ersec = $secciCantidad1ersec;
    
        return $this;
    }

    /**
     * Get secciCantidad1ersec
     *
     * @return integer 
     */
    public function getSecciCantidad1ersec()
    {
        return $this->secciCantidad1ersec;
    }

    /**
     * Set secciCantidad2dosec
     *
     * @param integer $secciCantidad2dosec
     * @return EquipLaboFisiQuim
     */
    public function setSecciCantidad2dosec($secciCantidad2dosec)
    {
        $this->secciCantidad2dosec = $secciCantidad2dosec;
    
        return $this;
    }

    /**
     * Get secciCantidad2dosec
     *
     * @return integer 
     */
    public function getSecciCantidad2dosec()
    {
        return $this->secciCantidad2dosec;
    }

    /**
     * Set secciCantidad3ersec
     *
     * @param integer $secciCantidad3ersec
     * @return EquipLaboFisiQuim
     */
    public function setSecciCantidad3ersec($secciCantidad3ersec)
    {
        $this->secciCantidad3ersec = $secciCantidad3ersec;
    
        return $this;
    }

    /**
     * Get secciCantidad3ersec
     *
     * @return integer 
     */
    public function getSecciCantidad3ersec()
    {
        return $this->secciCantidad3ersec;
    }

    /**
     * Set secciCantidad4tosec
     *
     * @param integer $secciCantidad4tosec
     * @return EquipLaboFisiQuim
     */
    public function setSecciCantidad4tosec($secciCantidad4tosec)
    {
        $this->secciCantidad4tosec = $secciCantidad4tosec;
    
        return $this;
    }

    /**
     * Get secciCantidad4tosec
     *
     * @return integer 
     */
    public function getSecciCantidad4tosec()
    {
        return $this->secciCantidad4tosec;
    }

    /**
     * Set secciCantidad5tosec
     *
     * @param integer $secciCantidad5tosec
     * @return EquipLaboFisiQuim
     */
    public function setSecciCantidad5tosec($secciCantidad5tosec)
    {
        $this->secciCantidad5tosec = $secciCantidad5tosec;
    
        return $this;
    }

    /**
     * Get secciCantidad5tosec
     *
     * @return integer 
     */
    public function getSecciCantidad5tosec()
    {
        return $this->secciCantidad5tosec;
    }

    /**
     * Set secciCantidad6tosec
     *
     * @param integer $secciCantidad6tosec
     * @return EquipLaboFisiQuim
     */
    public function setSecciCantidad6tosec($secciCantidad6tosec)
    {
        $this->secciCantidad6tosec = $secciCantidad6tosec;
    
        return $this;
    }

    /**
     * Get secciCantidad6tosec
     *
     * @return integer 
     */
    public function getSecciCantidad6tosec()
    {
        return $this->secciCantidad6tosec;
    }

    /**
     * Set seccivEsLabFisQuim
     *
     * @param boolean $seccivEsLabFisQuim
     * @return EquipLaboFisiQuim
     */
    public function setSeccivEsLabFisQuim($seccivEsLabFisQuim)
    {
        $this->seccivEsLabFisQuim = $seccivEsLabFisQuim;
    
        return $this;
    }

    /**
     * Get seccivEsLabFisQuim
     *
     * @return boolean 
     */
    public function getSeccivEsLabFisQuim()
    {
        return $this->seccivEsLabFisQuim;
    }

    /**
     * Set seccivCantAmb
     *
     * @param integer $seccivCantAmb
     * @return EquipLaboFisiQuim
     */
    public function setSeccivCantAmb($seccivCantAmb)
    {
        $this->seccivCantAmb = $seccivCantAmb;
    
        return $this;
    }

    /**
     * Get seccivCantAmb
     *
     * @return integer 
     */
    public function getSeccivCantAmb()
    {
        return $this->seccivCantAmb;
    }

    /**
     * Set seccivCuentaMesones
     *
     * @param boolean $seccivCuentaMesones
     * @return EquipLaboFisiQuim
     */
    public function setSeccivCuentaMesones($seccivCuentaMesones)
    {
        $this->seccivCuentaMesones = $seccivCuentaMesones;
    
        return $this;
    }

    /**
     * Get seccivCuentaMesones
     *
     * @return boolean 
     */
    public function getSeccivCuentaMesones()
    {
        return $this->seccivCuentaMesones;
    }

    /**
     * Set seccivEsMesonesCeramica
     *
     * @param boolean $seccivEsMesonesCeramica
     * @return EquipLaboFisiQuim
     */
    public function setSeccivEsMesonesCeramica($seccivEsMesonesCeramica)
    {
        $this->seccivEsMesonesCeramica = $seccivEsMesonesCeramica;
    
        return $this;
    }

    /**
     * Get seccivEsMesonesCeramica
     *
     * @return boolean 
     */
    public function getSeccivEsMesonesCeramica()
    {
        return $this->seccivEsMesonesCeramica;
    }

    /**
     * Set seccivCuentaPiletas
     *
     * @param boolean $seccivCuentaPiletas
     * @return EquipLaboFisiQuim
     */
    public function setSeccivCuentaPiletas($seccivCuentaPiletas)
    {
        $this->seccivCuentaPiletas = $seccivCuentaPiletas;
    
        return $this;
    }

    /**
     * Get seccivCuentaPiletas
     *
     * @return boolean 
     */
    public function getSeccivCuentaPiletas()
    {
        return $this->seccivCuentaPiletas;
    }

    /**
     * Set seccivCantidadPiletas
     *
     * @param integer $seccivCantidadPiletas
     * @return EquipLaboFisiQuim
     */
    public function setSeccivCantidadPiletas($seccivCantidadPiletas)
    {
        $this->seccivCantidadPiletas = $seccivCantidadPiletas;
    
        return $this;
    }

    /**
     * Get seccivCantidadPiletas
     *
     * @return integer 
     */
    public function getSeccivCantidadPiletas()
    {
        return $this->seccivCantidadPiletas;
    }

    /**
     * Set seccivCuentaInstElec
     *
     * @param boolean $seccivCuentaInstElec
     * @return EquipLaboFisiQuim
     */
    public function setSeccivCuentaInstElec($seccivCuentaInstElec)
    {
        $this->seccivCuentaInstElec = $seccivCuentaInstElec;
    
        return $this;
    }

    /**
     * Get seccivCuentaInstElec
     *
     * @return boolean 
     */
    public function getSeccivCuentaInstElec()
    {
        return $this->seccivCuentaInstElec;
    }

    /**
     * Set seccivCantidadTomaCorr
     *
     * @param integer $seccivCantidadTomaCorr
     * @return EquipLaboFisiQuim
     */
    public function setSeccivCantidadTomaCorr($seccivCantidadTomaCorr)
    {
        $this->seccivCantidadTomaCorr = $seccivCantidadTomaCorr;
    
        return $this;
    }

    /**
     * Get seccivCantidadTomaCorr
     *
     * @return integer 
     */
    public function getSeccivCantidadTomaCorr()
    {
        return $this->seccivCantidadTomaCorr;
    }

    /**
     * Set seccvCuentaEquipLabCiencias
     *
     * @param boolean $seccvCuentaEquipLabCiencias
     * @return EquipLaboFisiQuim
     */
    public function setSeccvCuentaEquipLabCiencias($seccvCuentaEquipLabCiencias)
    {
        $this->seccvCuentaEquipLabCiencias = $seccvCuentaEquipLabCiencias;
    
        return $this;
    }

    /**
     * Get seccvCuentaEquipLabCiencias
     *
     * @return boolean 
     */
    public function getSeccvCuentaEquipLabCiencias()
    {
        return $this->seccvCuentaEquipLabCiencias;
    }

    /**
     * Set seccvAnioEquipado
     *
     * @param integer $seccvAnioEquipado
     * @return EquipLaboFisiQuim
     */
    public function setSeccvAnioEquipado($seccvAnioEquipado)
    {
        $this->seccvAnioEquipado = $seccvAnioEquipado;
    
        return $this;
    }

    /**
     * Get seccvAnioEquipado
     *
     * @return integer 
     */
    public function getSeccvAnioEquipado()
    {
        return $this->seccvAnioEquipado;
    }

    /**
     * Set seccvInstitucionEquipo
     *
     * @param string $seccvInstitucionEquipo
     * @return EquipLaboFisiQuim
     */
    public function setSeccvInstitucionEquipo($seccvInstitucionEquipo)
    {
        $this->seccvInstitucionEquipo = $seccvInstitucionEquipo;
    
        return $this;
    }

    /**
     * Get seccvInstitucionEquipo
     *
     * @return string 
     */
    public function getSeccvInstitucionEquipo()
    {
        return $this->seccvInstitucionEquipo;
    }

    /**
     * Set seccvCantidadItems
     *
     * @param integer $seccvCantidadItems
     * @return EquipLaboFisiQuim
     */
    public function setSeccvCantidadItems($seccvCantidadItems)
    {
        $this->seccvCantidadItems = $seccvCantidadItems;
    
        return $this;
    }

    /**
     * Get seccvCantidadItems
     *
     * @return integer 
     */
    public function getSeccvCantidadItems()
    {
        return $this->seccvCantidadItems;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EquipLaboFisiQuim
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
     * @return EquipLaboFisiQuim
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
     * Set nombreAlcalde
     *
     * @param string $nombreAlcalde
     * @return EquipLaboFisiQuim
     */
    public function setNombreAlcalde($nombreAlcalde)
    {
        $this->nombreAlcalde = $nombreAlcalde;
    
        return $this;
    }

    /**
     * Get nombreAlcalde
     *
     * @return string 
     */
    public function getNombreAlcalde()
    {
        return $this->nombreAlcalde;
    }

    /**
     * Set telefonoAlcalde
     *
     * @param string $telefonoAlcalde
     * @return EquipLaboFisiQuim
     */
    public function setTelefonoAlcalde($telefonoAlcalde)
    {
        $this->telefonoAlcalde = $telefonoAlcalde;
    
        return $this;
    }

    /**
     * Get telefonoAlcalde
     *
     * @return string 
     */
    public function getTelefonoAlcalde()
    {
        return $this->telefonoAlcalde;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return EquipLaboFisiQuim
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
     * Set seccivConstruidaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EquipLaboFisiQuimConstruidaTipo $seccivConstruidaTipo
     * @return EquipLaboFisiQuim
     */
    public function setSeccivConstruidaTipo(\Sie\AppWebBundle\Entity\EquipLaboFisiQuimConstruidaTipo $seccivConstruidaTipo = null)
    {
        $this->seccivConstruidaTipo = $seccivConstruidaTipo;
    
        return $this;
    }

    /**
     * Get seccivConstruidaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EquipLaboFisiQuimConstruidaTipo 
     */
    public function getSeccivConstruidaTipo()
    {
        return $this->seccivConstruidaTipo;
    }
}
