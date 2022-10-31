<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecAreaFormacionCarreraTipo
 */
class TtecAreaFormacionCarreraTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $usuarioRegistro;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecAreaFormacionTipo
     */
    private $ttecAreaFormacionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecCarreraTipo
     */
    private $ttecCarreraTipo;


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
     * Set usuarioRegistro
     *
     * @param integer $usuarioRegistro
     * @return TtecAreaFormacionCarreraTipo
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecAreaFormacionCarreraTipo
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
     * @return TtecAreaFormacionCarreraTipo
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
     * Set ttecAreaFormacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecAreaFormacionTipo $ttecAreaFormacionTipo
     * @return TtecAreaFormacionCarreraTipo
     */
    public function setTtecAreaFormacionTipo(\Sie\AppWebBundle\Entity\TtecAreaFormacionTipo $ttecAreaFormacionTipo = null)
    {
        $this->ttecAreaFormacionTipo = $ttecAreaFormacionTipo;
    
        return $this;
    }

    /**
     * Get ttecAreaFormacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecAreaFormacionTipo 
     */
    public function getTtecAreaFormacionTipo()
    {
        return $this->ttecAreaFormacionTipo;
    }

    /**
     * Set ttecCarreraTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecCarreraTipo $ttecCarreraTipo
     * @return TtecAreaFormacionCarreraTipo
     */
    public function setTtecCarreraTipo(\Sie\AppWebBundle\Entity\TtecCarreraTipo $ttecCarreraTipo = null)
    {
        $this->ttecCarreraTipo = $ttecCarreraTipo;
    
        return $this;
    }

    /**
     * Get ttecCarreraTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecCarreraTipo 
     */
    public function getTtecCarreraTipo()
    {
        return $this->ttecCarreraTipo;
    }
}
