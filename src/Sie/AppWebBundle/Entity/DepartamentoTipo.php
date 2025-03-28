<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DepartamentoTipo
 */
class DepartamentoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $sigla;

    /**
     * @var string
     */
    private $departamento;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var string
     */
    private $codigo;

    /**
     * @var \Sie\AppWebBundle\Entity\PaisTipo
     */
    private $paisTipo;

    
    public function __toString() {
        return $this->getDepartamento();
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
     * Set sigla
     *
     * @param string $sigla
     * @return DepartamentoTipo
     */
    public function setSigla($sigla)
    {
        $this->sigla = $sigla;

        return $this;
    }

    /**
     * Get sigla
     *
     * @return string 
     */
    public function getSigla()
    {
        return $this->sigla;
    }

    /**
     * Set departamento
     *
     * @param string $departamento
     * @return DepartamentoTipo
     */
    public function setDepartamento($departamento)
    {
        $this->departamento = $departamento;

        return $this;
    }

    /**
     * Get departamento
     *
     * @return string 
     */
    public function getDepartamento()
    {
        return $this->departamento;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return DepartamentoTipo
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
     * Set codigo
     *
     * @param string $codigo
     * @return DepartamentoTipo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set paisTipo
     *
     * @param \Sie\AppWebBundle\Entity\PaisTipo $paisTipo
     * @return DepartamentoTipo
     */
    public function setPaisTipo(\Sie\AppWebBundle\Entity\PaisTipo $paisTipo = null)
    {
        $this->paisTipo = $paisTipo;

        return $this;
    }

    /**
     * Get paisTipo
     *
     * @return \Sie\AppWebBundle\Entity\PaisTipo 
     */
    public function getPaisTipo()
    {
        return $this->paisTipo;
    }
}
