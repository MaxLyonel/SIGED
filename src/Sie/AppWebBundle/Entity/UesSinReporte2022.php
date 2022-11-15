<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UesSinReporte2022
 */
class UesSinReporte2022
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descUe;

    /**
     * @var string
     */
    private $codDistrito;

    /**
     * @var string
     */
    private $distrito;

    /**
     * @var string
     */
    private $idDepartamento;

    /**
     * @var string
     */
    private $descDepartamento;

    /**
     * @var integer
     */
    private $codDependenciaId;

    /**
     * @var string
     */
    private $area;


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
     * Set descUe
     *
     * @param string $descUe
     * @return UesSinReporte2022
     */
    public function setDescUe($descUe)
    {
        $this->descUe = $descUe;
    
        return $this;
    }

    /**
     * Get descUe
     *
     * @return string 
     */
    public function getDescUe()
    {
        return $this->descUe;
    }

    /**
     * Set codDistrito
     *
     * @param string $codDistrito
     * @return UesSinReporte2022
     */
    public function setCodDistrito($codDistrito)
    {
        $this->codDistrito = $codDistrito;
    
        return $this;
    }

    /**
     * Get codDistrito
     *
     * @return string 
     */
    public function getCodDistrito()
    {
        return $this->codDistrito;
    }

    /**
     * Set distrito
     *
     * @param string $distrito
     * @return UesSinReporte2022
     */
    public function setDistrito($distrito)
    {
        $this->distrito = $distrito;
    
        return $this;
    }

    /**
     * Get distrito
     *
     * @return string 
     */
    public function getDistrito()
    {
        return $this->distrito;
    }

    /**
     * Set idDepartamento
     *
     * @param string $idDepartamento
     * @return UesSinReporte2022
     */
    public function setIdDepartamento($idDepartamento)
    {
        $this->idDepartamento = $idDepartamento;
    
        return $this;
    }

    /**
     * Get idDepartamento
     *
     * @return string 
     */
    public function getIdDepartamento()
    {
        return $this->idDepartamento;
    }

    /**
     * Set descDepartamento
     *
     * @param string $descDepartamento
     * @return UesSinReporte2022
     */
    public function setDescDepartamento($descDepartamento)
    {
        $this->descDepartamento = $descDepartamento;
    
        return $this;
    }

    /**
     * Get descDepartamento
     *
     * @return string 
     */
    public function getDescDepartamento()
    {
        return $this->descDepartamento;
    }

    /**
     * Set codDependenciaId
     *
     * @param integer $codDependenciaId
     * @return UesSinReporte2022
     */
    public function setCodDependenciaId($codDependenciaId)
    {
        $this->codDependenciaId = $codDependenciaId;
    
        return $this;
    }

    /**
     * Get codDependenciaId
     *
     * @return integer 
     */
    public function getCodDependenciaId()
    {
        return $this->codDependenciaId;
    }

    /**
     * Set area
     *
     * @param string $area
     * @return UesSinReporte2022
     */
    public function setArea($area)
    {
        $this->area = $area;
    
        return $this;
    }

    /**
     * Get area
     *
     * @return string 
     */
    public function getArea()
    {
        return $this->area;
    }
}
