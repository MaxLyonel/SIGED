<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModulosOperativo
 */
class ModulosOperativo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioAct;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioReg;

    /**
     * @var \Sie\AppWebBundle\Entity\ModuloTipo
     */
    private $moduloTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OperativoSieTipo
     */
    private $operativoSieTipo;


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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return ModulosOperativo
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
     * @return ModulosOperativo
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
     * Set usuarioAct
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioAct
     * @return ModulosOperativo
     */
    public function setUsuarioAct(\Sie\AppWebBundle\Entity\Usuario $usuarioAct = null)
    {
        $this->usuarioAct = $usuarioAct;
    
        return $this;
    }

    /**
     * Get usuarioAct
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioAct()
    {
        return $this->usuarioAct;
    }

    /**
     * Set usuarioReg
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioReg
     * @return ModulosOperativo
     */
    public function setUsuarioReg(\Sie\AppWebBundle\Entity\Usuario $usuarioReg = null)
    {
        $this->usuarioReg = $usuarioReg;
    
        return $this;
    }

    /**
     * Get usuarioReg
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioReg()
    {
        return $this->usuarioReg;
    }

    /**
     * Set moduloTipo
     *
     * @param \Sie\AppWebBundle\Entity\ModuloTipo $moduloTipo
     * @return ModulosOperativo
     */
    public function setModuloTipo(\Sie\AppWebBundle\Entity\ModuloTipo $moduloTipo = null)
    {
        $this->moduloTipo = $moduloTipo;
    
        return $this;
    }

    /**
     * Get moduloTipo
     *
     * @return \Sie\AppWebBundle\Entity\ModuloTipo 
     */
    public function getModuloTipo()
    {
        return $this->moduloTipo;
    }

    /**
     * Set operativoSieTipo
     *
     * @param \Sie\AppWebBundle\Entity\OperativoSieTipo $operativoSieTipo
     * @return ModulosOperativo
     */
    public function setOperativoSieTipo(\Sie\AppWebBundle\Entity\OperativoSieTipo $operativoSieTipo = null)
    {
        $this->operativoSieTipo = $operativoSieTipo;
    
        return $this;
    }

    /**
     * Get operativoSieTipo
     *
     * @return \Sie\AppWebBundle\Entity\OperativoSieTipo 
     */
    public function getOperativoSieTipo()
    {
        return $this->operativoSieTipo;
    }
}
