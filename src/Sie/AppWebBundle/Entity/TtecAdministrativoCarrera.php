<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecAdministrativoCarrera
 */
class TtecAdministrativoCarrera
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
     * @var \Sie\AppWebBundle\Entity\TtecCarreraTipo
     */
    private $ttecCarreraTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecCargoTipo
     */
    private $ttecCargoTipo;


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
     * @return TtecAdministrativoCarrera
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
     * @return TtecAdministrativoCarrera
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
     * Set ttecCarreraTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecCarreraTipo $ttecCarreraTipo
     * @return TtecAdministrativoCarrera
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

    /**
     * Set ttecCargoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecCargoTipo $ttecCargoTipo
     * @return TtecAdministrativoCarrera
     */
    public function setTtecCargoTipo(\Sie\AppWebBundle\Entity\TtecCargoTipo $ttecCargoTipo = null)
    {
        $this->ttecCargoTipo = $ttecCargoTipo;
    
        return $this;
    }

    /**
     * Get ttecCargoTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecCargoTipo 
     */
    public function getTtecCargoTipo()
    {
        return $this->ttecCargoTipo;
    }
}
