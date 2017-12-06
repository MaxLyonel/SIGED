<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JuegosDatosSeguimiento
 */
class JuegosDatosSeguimiento
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
     * @var integer
     */
    private $departamentoTipoId;

    /**
     * @var integer
     */
    private $nivelTipoId;

    /**
     * @var integer
     */
    private $estudianteCantidadLlegada;

    /**
     * @var integer
     */
    private $estudianteCantidadSalida;

    /**
     * @var integer
     */
    private $delegadoCantidadLlegada;

    /**
     * @var integer
     */
    private $delegadoCantidadSalida;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var integer
     */
    private $usuarioRegistro;

    /**
     * @var integer
     */
    private $usuarioModificacion;


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
     * @return JuegosDatosSeguimiento
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
     * Set departamentoTipoId
     *
     * @param integer $departamentoTipoId
     * @return JuegosDatosSeguimiento
     */
    public function setDepartamentoTipoId($departamentoTipoId)
    {
        $this->departamentoTipoId = $departamentoTipoId;
    
        return $this;
    }

    /**
     * Get departamentoTipoId
     *
     * @return integer 
     */
    public function getDepartamentoTipoId()
    {
        return $this->departamentoTipoId;
    }

    /**
     * Set nivelTipoId
     *
     * @param integer $nivelTipoId
     * @return JuegosDatosSeguimiento
     */
    public function setNivelTipoId($nivelTipoId)
    {
        $this->nivelTipoId = $nivelTipoId;
    
        return $this;
    }

    /**
     * Get nivelTipoId
     *
     * @return integer 
     */
    public function getNivelTipoId()
    {
        return $this->nivelTipoId;
    }

    /**
     * Set estudianteCantidadLlegada
     *
     * @param integer $estudianteCantidadLlegada
     * @return JuegosDatosSeguimiento
     */
    public function setEstudianteCantidadLlegada($estudianteCantidadLlegada)
    {
        $this->estudianteCantidadLlegada = $estudianteCantidadLlegada;
    
        return $this;
    }

    /**
     * Get estudianteCantidadLlegada
     *
     * @return integer 
     */
    public function getEstudianteCantidadLlegada()
    {
        return $this->estudianteCantidadLlegada;
    }

    /**
     * Set estudianteCantidadSalida
     *
     * @param integer $estudianteCantidadSalida
     * @return JuegosDatosSeguimiento
     */
    public function setEstudianteCantidadSalida($estudianteCantidadSalida)
    {
        $this->estudianteCantidadSalida = $estudianteCantidadSalida;
    
        return $this;
    }

    /**
     * Get estudianteCantidadSalida
     *
     * @return integer 
     */
    public function getEstudianteCantidadSalida()
    {
        return $this->estudianteCantidadSalida;
    }

    /**
     * Set delegadoCantidadLlegada
     *
     * @param integer $delegadoCantidadLlegada
     * @return JuegosDatosSeguimiento
     */
    public function setDelegadoCantidadLlegada($delegadoCantidadLlegada)
    {
        $this->delegadoCantidadLlegada = $delegadoCantidadLlegada;
    
        return $this;
    }

    /**
     * Get delegadoCantidadLlegada
     *
     * @return integer 
     */
    public function getDelegadoCantidadLlegada()
    {
        return $this->delegadoCantidadLlegada;
    }

    /**
     * Set delegadoCantidadSalida
     *
     * @param integer $delegadoCantidadSalida
     * @return JuegosDatosSeguimiento
     */
    public function setDelegadoCantidadSalida($delegadoCantidadSalida)
    {
        $this->delegadoCantidadSalida = $delegadoCantidadSalida;
    
        return $this;
    }

    /**
     * Get delegadoCantidadSalida
     *
     * @return integer 
     */
    public function getDelegadoCantidadSalida()
    {
        return $this->delegadoCantidadSalida;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return JuegosDatosSeguimiento
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
     * @return JuegosDatosSeguimiento
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
     * Set usuarioRegistro
     *
     * @param integer $usuarioRegistro
     * @return JuegosDatosSeguimiento
     */
    public function setUsuarioRegistro($usuarioRegistro)
    {
        $this->usuarioRegistro = $usuarioRegistro;
    
        return $this;
    }

    /**
     * Get usuarioRegistro
     *
     * @return integer 
     */
    public function getUsuarioRegistro()
    {
        return $this->usuarioRegistro;
    }

    /**
     * Set usuarioModificacion
     *
     * @param integer $usuarioModificacion
     * @return JuegosDatosSeguimiento
     */
    public function setUsuarioModificacion($usuarioModificacion)
    {
        $this->usuarioModificacion = $usuarioModificacion;
    
        return $this;
    }

    /**
     * Get usuarioModificacion
     *
     * @return integer 
     */
    public function getUsuarioModificacion()
    {
        return $this->usuarioModificacion;
    }
}
