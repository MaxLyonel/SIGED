<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecHorarioParaleloMateria
 */
class TtecHorarioParaleloMateria
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
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecParaleloMateria
     */
    private $ttecParaleloMateria;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecHorarioTipo
     */
    private $ttecHorarioTipo;


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
     * @return TtecHorarioParaleloMateria
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
     * @return TtecHorarioParaleloMateria
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
     * Set obs
     *
     * @param string $obs
     * @return TtecHorarioParaleloMateria
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
     * Set ttecParaleloMateria
     *
     * @param \Sie\AppWebBundle\Entity\TtecParaleloMateria $ttecParaleloMateria
     * @return TtecHorarioParaleloMateria
     */
    public function setTtecParaleloMateria(\Sie\AppWebBundle\Entity\TtecParaleloMateria $ttecParaleloMateria = null)
    {
        $this->ttecParaleloMateria = $ttecParaleloMateria;
    
        return $this;
    }

    /**
     * Get ttecParaleloMateria
     *
     * @return \Sie\AppWebBundle\Entity\TtecParaleloMateria 
     */
    public function getTtecParaleloMateria()
    {
        return $this->ttecParaleloMateria;
    }

    /**
     * Set ttecHorarioTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecHorarioTipo $ttecHorarioTipo
     * @return TtecHorarioParaleloMateria
     */
    public function setTtecHorarioTipo(\Sie\AppWebBundle\Entity\TtecHorarioTipo $ttecHorarioTipo = null)
    {
        $this->ttecHorarioTipo = $ttecHorarioTipo;
    
        return $this;
    }

    /**
     * Get ttecHorarioTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecHorarioTipo 
     */
    public function getTtecHorarioTipo()
    {
        return $this->ttecHorarioTipo;
    }
}
