<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperiorInstitucioneducativaPeriodo
 */
class SuperiorInstitucioneducativaPeriodo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var integer
     */
    private $horasPeriodo;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaAcreditacion
     */
    private $superiorInstitucioneducativaAcreditacion;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorPeriodoTipo
     */
    private $superiorPeriodoTipo;


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
     * Set obs
     *
     * @param string $obs
     * @return SuperiorInstitucioneducativaPeriodo
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
     * Set horasPeriodo
     *
     * @param integer $horasPeriodo
     * @return SuperiorInstitucioneducativaPeriodo
     */
    public function setHorasPeriodo($horasPeriodo)
    {
        $this->horasPeriodo = $horasPeriodo;
    
        return $this;
    }

    /**
     * Get horasPeriodo
     *
     * @return integer 
     */
    public function getHorasPeriodo()
    {
        return $this->horasPeriodo;
    }

    /**
     * Set superiorInstitucioneducativaAcreditacion
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaAcreditacion $superiorInstitucioneducativaAcreditacion
     * @return SuperiorInstitucioneducativaPeriodo
     */
    public function setSuperiorInstitucioneducativaAcreditacion(\Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaAcreditacion $superiorInstitucioneducativaAcreditacion = null)
    {
        $this->superiorInstitucioneducativaAcreditacion = $superiorInstitucioneducativaAcreditacion;
    
        return $this;
    }

    /**
     * Get superiorInstitucioneducativaAcreditacion
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorInstitucioneducativaAcreditacion 
     */
    public function getSuperiorInstitucioneducativaAcreditacion()
    {
        return $this->superiorInstitucioneducativaAcreditacion;
    }

    /**
     * Set superiorPeriodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorPeriodoTipo $superiorPeriodoTipo
     * @return SuperiorInstitucioneducativaPeriodo
     */
    public function setSuperiorPeriodoTipo(\Sie\AppWebBundle\Entity\SuperiorPeriodoTipo $superiorPeriodoTipo = null)
    {
        $this->superiorPeriodoTipo = $superiorPeriodoTipo;
    
        return $this;
    }

    /**
     * Get superiorPeriodoTipo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorPeriodoTipo 
     */
    public function getSuperiorPeriodoTipo()
    {
        return $this->superiorPeriodoTipo;
    }
}
