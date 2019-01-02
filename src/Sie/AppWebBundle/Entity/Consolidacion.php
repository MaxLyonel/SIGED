<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Consolidacion
 */
class Consolidacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var boolean
     */
    private $esonline;

    /**
     * @var string
     */
    private $obs;

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
    private $archivo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal
     */
    private $institucioneducativaSucursal;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaTipo
     */
    private $institucioneducativaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioCreacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\OperativoTipo
     */
    private $operativoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\SistemaTipo
     */
    private $sistemaTipo;


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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return Consolidacion
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
     * Set esonline
     *
     * @param boolean $esonline
     * @return Consolidacion
     */
    public function setEsonline($esonline)
    {
        $this->esonline = $esonline;
    
        return $this;
    }

    /**
     * Get esonline
     *
     * @return boolean 
     */
    public function getEsonline()
    {
        return $this->esonline;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return Consolidacion
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
     * @return Consolidacion
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
     * @return Consolidacion
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
     * Set archivo
     *
     * @param string $archivo
     * @return Consolidacion
     */
    public function setArchivo($archivo)
    {
        $this->archivo = $archivo;
    
        return $this;
    }

    /**
     * Get archivo
     *
     * @return string 
     */
    public function getArchivo()
    {
        return $this->archivo;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return Consolidacion
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
     * Set institucioneducativaSucursal
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal
     * @return Consolidacion
     */
    public function setInstitucioneducativaSucursal(\Sie\AppWebBundle\Entity\InstitucioneducativaSucursal $institucioneducativaSucursal = null)
    {
        $this->institucioneducativaSucursal = $institucioneducativaSucursal;
    
        return $this;
    }

    /**
     * Get institucioneducativaSucursal
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaSucursal 
     */
    public function getInstitucioneducativaSucursal()
    {
        return $this->institucioneducativaSucursal;
    }

    /**
     * Set institucioneducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo
     * @return Consolidacion
     */
    public function setInstitucioneducativaTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo = null)
    {
        $this->institucioneducativaTipo = $institucioneducativaTipo;
    
        return $this;
    }

    /**
     * Get institucioneducativaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaTipo 
     */
    public function getInstitucioneducativaTipo()
    {
        return $this->institucioneducativaTipo;
    }

    /**
     * Set usuarioCreacion
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioCreacion
     * @return Consolidacion
     */
    public function setUsuarioCreacion(\Sie\AppWebBundle\Entity\Usuario $usuarioCreacion = null)
    {
        $this->usuarioCreacion = $usuarioCreacion;
    
        return $this;
    }

    /**
     * Get usuarioCreacion
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioCreacion()
    {
        return $this->usuarioCreacion;
    }

    /**
     * Set usuarioModificacion
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioModificacion
     * @return Consolidacion
     */
    public function setUsuarioModificacion(\Sie\AppWebBundle\Entity\Usuario $usuarioModificacion = null)
    {
        $this->usuarioModificacion = $usuarioModificacion;
    
        return $this;
    }

    /**
     * Get usuarioModificacion
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioModificacion()
    {
        return $this->usuarioModificacion;
    }

    /**
     * Set operativoTipo
     *
     * @param \Sie\AppWebBundle\Entity\OperativoTipo $operativoTipo
     * @return Consolidacion
     */
    public function setOperativoTipo(\Sie\AppWebBundle\Entity\OperativoTipo $operativoTipo = null)
    {
        $this->operativoTipo = $operativoTipo;
    
        return $this;
    }

    /**
     * Get operativoTipo
     *
     * @return \Sie\AppWebBundle\Entity\OperativoTipo 
     */
    public function getOperativoTipo()
    {
        return $this->operativoTipo;
    }

    /**
     * Set sistemaTipo
     *
     * @param \Sie\AppWebBundle\Entity\SistemaTipo $sistemaTipo
     * @return Consolidacion
     */
    public function setSistemaTipo(\Sie\AppWebBundle\Entity\SistemaTipo $sistemaTipo = null)
    {
        $this->sistemaTipo = $sistemaTipo;
    
        return $this;
    }

    /**
     * Get sistemaTipo
     *
     * @return \Sie\AppWebBundle\Entity\SistemaTipo 
     */
    public function getSistemaTipo()
    {
        return $this->sistemaTipo;
    }
}
