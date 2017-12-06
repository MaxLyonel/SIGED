<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CargoTipo
 */
class CargoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $cargo;

    public function __toString() {
        return $this->cargo;
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
     * Set cargo
     *
     * @param string $cargo
     * @return CargoTipo
     */
    public function setCargo($cargo)
    {
        $this->cargo = $cargo;

        return $this;
    }

    /**
     * Get cargo
     *
     * @return string 
     */
    public function getCargo()
    {
        return $this->cargo;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaTipo
     */
    private $institucioneducativaTipo;


    /**
     * Set institucioneducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo
     * @return CargoTipo
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
     * @var integer
     */
    private $institucioneducativaTipoId;


    /**
     * Set institucioneducativaTipoId
     *
     * @param integer $institucioneducativaTipoId
     * @return CargoTipo
     */
    public function setInstitucioneducativaTipoId($institucioneducativaTipoId)
    {
        $this->institucioneducativaTipoId = $institucioneducativaTipoId;
    
        return $this;
    }

    /**
     * Get institucioneducativaTipoId
     *
     * @return integer 
     */
    public function getInstitucioneducativaTipoId()
    {
        return $this->institucioneducativaTipoId;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\RolTipo
     */
    private $rolTipo;


    /**
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return CargoTipo
     */
    public function setRolTipo(\Sie\AppWebBundle\Entity\RolTipo $rolTipo = null)
    {
        $this->rolTipo = $rolTipo;
    
        return $this;
    }

    /**
     * Get rolTipo
     *
     * @return \Sie\AppWebBundle\Entity\RolTipo 
     */
    public function getRolTipo()
    {
        return $this->rolTipo;
    }
    /**
     * @var boolean
     */
    private $esdirector;


    /**
     * Set esdirector
     *
     * @param boolean $esdirector
     * @return CargoTipo
     */
    public function setEsdirector($esdirector)
    {
        $this->esdirector = $esdirector;
    
        return $this;
    }

    /**
     * Get esdirector
     *
     * @return boolean 
     */
    public function getEsdirector()
    {
        return $this->esdirector;
    }
}
