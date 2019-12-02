<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaHistorialTramite
 */
class InstitucioneducativaHistorialTramite
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nroResolcucion;

    /**
     * @var \DateTime
     */
    private $fechaResolucion;

    /**
     * @var \DateTime
     */
    private $fechaFormulario;

    /**
     * @var string
     */
    private $valorAnterior;

    /**
     * @var string
     */
    private $valorNuevo;

    /**
     * @var string
     */
    private $observacion;

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
    private $usuarioModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuarioRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\TramiteTipo
     */
    private $tramiteTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Tramite
     */
    private $tramite;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;


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
     * Set nroResolcucion
     *
     * @param string $nroResolcucion
     * @return InstitucioneducativaHistorialTramite
     */
    public function setNroResolcucion($nroResolcucion)
    {
        $this->nroResolcucion = $nroResolcucion;
    
        return $this;
    }

    /**
     * Get nroResolcucion
     *
     * @return string 
     */
    public function getNroResolcucion()
    {
        return $this->nroResolcucion;
    }

    /**
     * Set fechaResolucion
     *
     * @param \DateTime $fechaResolucion
     * @return InstitucioneducativaHistorialTramite
     */
    public function setFechaResolucion($fechaResolucion)
    {
        $this->fechaResolucion = $fechaResolucion;
    
        return $this;
    }

    /**
     * Get fechaResolucion
     *
     * @return \DateTime 
     */
    public function getFechaResolucion()
    {
        return $this->fechaResolucion;
    }

    /**
     * Set fechaFormulario
     *
     * @param \DateTime $fechaFormulario
     * @return InstitucioneducativaHistorialTramite
     */
    public function setFechaFormulario($fechaFormulario)
    {
        $this->fechaFormulario = $fechaFormulario;
    
        return $this;
    }

    /**
     * Get fechaFormulario
     *
     * @return \DateTime 
     */
    public function getFechaFormulario()
    {
        return $this->fechaFormulario;
    }

    /**
     * Set valorAnterior
     *
     * @param string $valorAnterior
     * @return InstitucioneducativaHistorialTramite
     */
    public function setValorAnterior($valorAnterior)
    {
        $this->valorAnterior = $valorAnterior;
    
        return $this;
    }

    /**
     * Get valorAnterior
     *
     * @return string 
     */
    public function getValorAnterior()
    {
        return $this->valorAnterior;
    }

    /**
     * Set valorNuevo
     *
     * @param string $valorNuevo
     * @return InstitucioneducativaHistorialTramite
     */
    public function setValorNuevo($valorNuevo)
    {
        $this->valorNuevo = $valorNuevo;
    
        return $this;
    }

    /**
     * Get valorNuevo
     *
     * @return string 
     */
    public function getValorNuevo()
    {
        return $this->valorNuevo;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return InstitucioneducativaHistorialTramite
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaHistorialTramite
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
     * @return InstitucioneducativaHistorialTramite
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
     * Set usuarioModificacion
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioModificacion
     * @return InstitucioneducativaHistorialTramite
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
     * Set usuarioRegistro
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuarioRegistro
     * @return InstitucioneducativaHistorialTramite
     */
    public function setUsuarioRegistro(\Sie\AppWebBundle\Entity\Usuario $usuarioRegistro = null)
    {
        $this->usuarioRegistro = $usuarioRegistro;
    
        return $this;
    }

    /**
     * Get usuarioRegistro
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuarioRegistro()
    {
        return $this->usuarioRegistro;
    }

    /**
     * Set tramiteTipo
     *
     * @param \Sie\AppWebBundle\Entity\TramiteTipo $tramiteTipo
     * @return InstitucioneducativaHistorialTramite
     */
    public function setTramiteTipo(\Sie\AppWebBundle\Entity\TramiteTipo $tramiteTipo = null)
    {
        $this->tramiteTipo = $tramiteTipo;
    
        return $this;
    }

    /**
     * Get tramiteTipo
     *
     * @return \Sie\AppWebBundle\Entity\TramiteTipo 
     */
    public function getTramiteTipo()
    {
        return $this->tramiteTipo;
    }

    /**
     * Set tramite
     *
     * @param \Sie\AppWebBundle\Entity\Tramite $tramite
     * @return InstitucioneducativaHistorialTramite
     */
    public function setTramite(\Sie\AppWebBundle\Entity\Tramite $tramite = null)
    {
        $this->tramite = $tramite;
    
        return $this;
    }

    /**
     * Get tramite
     *
     * @return \Sie\AppWebBundle\Entity\Tramite 
     */
    public function getTramite()
    {
        return $this->tramite;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaHistorialTramite
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
}
