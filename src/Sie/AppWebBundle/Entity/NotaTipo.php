<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotaTipo
 */
class NotaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $notaTipo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var string
     */
    private $abrev;

    /**
     * @var integer
     */
    private $orden;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaTipo
     */
    private $institucioneducativaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OrgcurricularTipo
     */
    private $orgcurricularTipo;


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
     * Set notaTipo
     *
     * @param string $notaTipo
     * @return NotaTipo
     */
    public function setNotaTipo($notaTipo)
    {
        $this->notaTipo = $notaTipo;

        return $this;
    }

    /**
     * Get notaTipo
     *
     * @return string 
     */
    public function getNotaTipo()
    {
        return $this->notaTipo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return NotaTipo
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
     * Set abrev
     *
     * @param string $abrev
     * @return NotaTipo
     */
    public function setAbrev($abrev)
    {
        $this->abrev = $abrev;

        return $this;
    }

    /**
     * Get abrev
     *
     * @return string 
     */
    public function getAbrev()
    {
        return $this->abrev;
    }

    /**
     * Set orden
     *
     * @param integer $orden
     * @return NotaTipo
     */
    public function setOrden($orden)
    {
        $this->orden = $orden;

        return $this;
    }

    /**
     * Get orden
     *
     * @return integer 
     */
    public function getOrden()
    {
        return $this->orden;
    }

    /**
     * Set institucioneducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo
     * @return NotaTipo
     */
    public function setInstitucioneducativaTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo = null)
    {
        $this->institucioneducativaTipo = $institucioneducativaTipo;

        return $this;
    }

    /**
     * Get institucioneducativaTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaTipo 
     */
    public function getInstitucioneducativaTipo()
    {
        return $this->institucioneducativaTipo;
    }

    /**
     * Set orgcurricularTipo
     *
     * @param \Sie\AppWebBundle\Entity\OrgcurricularTipo $orgcurricularTipo
     * @return NotaTipo
     */
    public function setOrgcurricularTipo(\Sie\AppWebBundle\Entity\OrgcurricularTipo $orgcurricularTipo = null)
    {
        $this->orgcurricularTipo = $orgcurricularTipo;

        return $this;
    }

    /**
     * Get orgcurricularTipo
     *
     * @return \Sie\AppWebBundle\Entity\OrgcurricularTipo 
     */
    public function getOrgcurricularTipo()
    {
        return $this->orgcurricularTipo;
    }
}
