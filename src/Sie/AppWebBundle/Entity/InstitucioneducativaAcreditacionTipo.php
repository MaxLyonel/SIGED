<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaAcreditacionTipo
 */
class InstitucioneducativaAcreditacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $institucioneducativaAcreditacion;

    /**
     * @var string
     */
    private $obs;


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
     * Set institucioneducativaAcreditacion
     *
     * @param string $institucioneducativaAcreditacion
     * @return InstitucioneducativaAcreditacionTipo
     */
    public function setInstitucioneducativaAcreditacion($institucioneducativaAcreditacion)
    {
        $this->institucioneducativaAcreditacion = $institucioneducativaAcreditacion;
    
        return $this;
    }

    /**
     * Get institucioneducativaAcreditacion
     *
     * @return string 
     */
    public function getInstitucioneducativaAcreditacion()
    {
        return $this->institucioneducativaAcreditacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InstitucioneducativaAcreditacionTipo
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
}
