<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecHorarioTipo
 */
class TtecHorarioTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $horaInicio;

    /**
     * @var \DateTime
     */
    private $horaFin;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecDiaTipo
     */
    private $ttecDiaTipo;


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
     * Set horaInicio
     *
     * @param \DateTime $horaInicio
     * @return TtecHorarioTipo
     */
    public function setHoraInicio($horaInicio)
    {
        $this->horaInicio = $horaInicio;
    
        return $this;
    }

    /**
     * Get horaInicio
     *
     * @return \DateTime 
     */
    public function getHoraInicio()
    {
        return $this->horaInicio;
    }

    /**
     * Set horaFin
     *
     * @param \DateTime $horaFin
     * @return TtecHorarioTipo
     */
    public function setHoraFin($horaFin)
    {
        $this->horaFin = $horaFin;
    
        return $this;
    }

    /**
     * Get horaFin
     *
     * @return \DateTime 
     */
    public function getHoraFin()
    {
        return $this->horaFin;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return TtecHorarioTipo
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
     * Set ttecDiaTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecDiaTipo $ttecDiaTipo
     * @return TtecHorarioTipo
     */
    public function setTtecDiaTipo(\Sie\AppWebBundle\Entity\TtecDiaTipo $ttecDiaTipo = null)
    {
        $this->ttecDiaTipo = $ttecDiaTipo;
    
        return $this;
    }

    /**
     * Get ttecDiaTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecDiaTipo 
     */
    public function getTtecDiaTipo()
    {
        return $this->ttecDiaTipo;
    }
}
