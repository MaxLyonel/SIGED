<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH1TenenciaTipo
 */
class InfraestructuraH1TenenciaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tenenciaTipo;

    /**
     * @var string
     */
    private $obs;

    public function __toString(){
        return $this->tenenciaTipo;
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
     * Set tenenciaTipo
     *
     * @param string $tenenciaTipo
     * @return InfraestructuraH1TenenciaTipo
     */
    public function setTenenciaTipo($tenenciaTipo)
    {
        $this->tenenciaTipo = $tenenciaTipo;
    
        return $this;
    }

    /**
     * Get tenenciaTipo
     *
     * @return string 
     */
    public function getTenenciaTipo()
    {
        return $this->tenenciaTipo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraH1TenenciaTipo
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
     * @var integer
     */
    private $gestionTipoId;


    /**
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return InfraestructuraH1TenenciaTipo
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }
}
