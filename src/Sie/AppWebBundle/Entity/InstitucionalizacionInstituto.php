<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucionalizacionInstituto
 */
class InstitucionalizacionInstituto
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $instituto;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucionalizacionDepartamento
     */
    private $departamento;


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
     * Set instituto
     *
     * @param string $instituto
     * @return InstitucionalizacionInstituto
     */
    public function setInstituto($instituto)
    {
        $this->instituto = $instituto;
    
        return $this;
    }

    /**
     * Get instituto
     *
     * @return string 
     */
    public function getInstituto()
    {
        return $this->instituto;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InstitucionalizacionInstituto
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
     * Set departamento
     *
     * @param \Sie\AppWebBundle\Entity\InstitucionalizacionDepartamento $departamento
     * @return InstitucionalizacionInstituto
     */
    public function setDepartamento(\Sie\AppWebBundle\Entity\InstitucionalizacionDepartamento $departamento = null)
    {
        $this->departamento = $departamento;
    
        return $this;
    }

    /**
     * Get departamento
     *
     * @return \Sie\AppWebBundle\Entity\InstitucionalizacionDepartamento 
     */
    public function getDepartamento()
    {
        return $this->departamento;
    }
}
