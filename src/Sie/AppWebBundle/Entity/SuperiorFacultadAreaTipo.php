<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SuperiorFacultadAreaTipo
 */
class SuperiorFacultadAreaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $codigo;

    /**
     * @var string
     */
    private $facultadArea;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaTipo
     */
    private $institucioneducativaTipo;


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
     * Set codigo
     *
     * @param integer $codigo
     * @return SuperiorFacultadAreaTipo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return integer 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set facultadArea
     *
     * @param string $facultadArea
     * @return SuperiorFacultadAreaTipo
     */
    public function setFacultadArea($facultadArea)
    {
        $this->facultadArea = $facultadArea;
    
        return $this;
    }

    /**
     * Get facultadArea
     *
     * @return string 
     */
    public function getFacultadArea()
    {
        return $this->facultadArea;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return SuperiorFacultadAreaTipo
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
     * Set institucioneducativaTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaTipo $institucioneducativaTipo
     * @return SuperiorFacultadAreaTipo
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
}
