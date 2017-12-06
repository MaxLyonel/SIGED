<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecInstitucioneducativaAreaFormacionAutorizado
 */
class TtecInstitucioneducativaAreaFormacionAutorizado
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
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecInstitucioneducativaAreaFormacionAutorizado
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
     * @return TtecInstitucioneducativaAreaFormacionAutorizado
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return TtecInstitucioneducativaAreaFormacionAutorizado
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
     * Set ttecAreaFormacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecAreaFormacionTipo $ttecAreaFormacionTipo
     * @return TtecInstitucioneducativaAreaFormacionAutorizado
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
