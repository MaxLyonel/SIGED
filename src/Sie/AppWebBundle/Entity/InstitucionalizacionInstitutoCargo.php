<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucionalizacionInstitutoCargo
 */
class InstitucionalizacionInstitutoCargo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucionalizacionCargo
     */
    private $cargo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucionalizacionInstituto
     */
    private $instituto;


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
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return InstitucionalizacionInstitutoCargo
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InstitucionalizacionInstitutoCargo
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
     * Set cargo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucionalizacionCargo $cargo
     * @return InstitucionalizacionInstitutoCargo
     */
    public function setCargo(\Sie\AppWebBundle\Entity\InstitucionalizacionCargo $cargo = null)
    {
        $this->cargo = $cargo;
    
        return $this;
    }

    /**
     * Get cargo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucionalizacionCargo 
     */
    public function getCargo()
    {
        return $this->cargo;
    }

    /**
     * Set instituto
     *
     * @param \Sie\AppWebBundle\Entity\InstitucionalizacionInstituto $instituto
     * @return InstitucionalizacionInstitutoCargo
     */
    public function setInstituto(\Sie\AppWebBundle\Entity\InstitucionalizacionInstituto $instituto = null)
    {
        $this->instituto = $instituto;
    
        return $this;
    }

    /**
     * Get instituto
     *
     * @return \Sie\AppWebBundle\Entity\InstitucionalizacionInstituto 
     */
    public function getInstituto()
    {
        return $this->instituto;
    }
}
