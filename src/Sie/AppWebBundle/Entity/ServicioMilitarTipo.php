<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ServicioMilitarTipo
 */
class ServicioMilitarTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $lugarServicioMilitar;

    /**
     * @var boolean
     */
    private $esActivo;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    public function __toString(){
        return $this->lugarServicioMilitar;
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
     * Set lugarServicioMilitar
     *
     * @param string $lugarServicioMilitar
     * @return ServicioMilitarTipo
     */
    public function setLugarServicioMilitar($lugarServicioMilitar)
    {
        $this->lugarServicioMilitar = $lugarServicioMilitar;
    
        return $this;
    }

    /**
     * Get lugarServicioMilitar
     *
     * @return string 
     */
    public function getLugarServicioMilitar()
    {
        return $this->lugarServicioMilitar;
    }

    /**
     * Set esActivo
     *
     * @param boolean $esActivo
     * @return ServicioMilitarTipo
     */
    public function setEsActivo($esActivo)
    {
        $this->esActivo = $esActivo;
    
        return $this;
    }

    /**
     * Get esActivo
     *
     * @return boolean 
     */
    public function getEsActivo()
    {
        return $this->esActivo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return ServicioMilitarTipo
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
     * @return ServicioMilitarTipo
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
}
