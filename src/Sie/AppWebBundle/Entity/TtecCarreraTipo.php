<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecCarreraTipo
 */
class TtecCarreraTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nombre;

    /**
     * @var string
     */
    private $codigo;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo
     */
    private $ttecEstadoCarreraTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecAreaFormacionTipo
     */
    private $ttecAreaFormacionTipo;


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
     * Set nombre
     *
     * @param string $nombre
     * @return TtecCarreraTipo
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    
        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * Set codigo
     *
     * @param string $codigo
     * @return TtecCarreraTipo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecCarreraTipo
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
     * @return TtecCarreraTipo
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
     * Set ttecEstadoCarreraTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo $ttecEstadoCarreraTipo
     * @return TtecCarreraTipo
     */
    public function setTtecEstadoCarreraTipo(\Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo $ttecEstadoCarreraTipo = null)
    {
        $this->ttecEstadoCarreraTipo = $ttecEstadoCarreraTipo;
    
        return $this;
    }

    /**
     * Get ttecEstadoCarreraTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecEstadoCarreraTipo 
     */
    public function getTtecEstadoCarreraTipo()
    {
        return $this->ttecEstadoCarreraTipo;
    }

    /**
     * Set ttecAreaFormacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecAreaFormacionTipo $ttecAreaFormacionTipo
     * @return TtecCarreraTipo
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
}
