<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH6EquipamientoPedagogico
 */
class InfraestructuraH6EquipamientoPedagogico
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n4NumeroAula;

    /**
     * @var integer
     */
    private $n4NumeroTaller;

    /**
     * @var integer
     */
    private $n4NumeroLaboratorio;

    /**
     * @var integer
     */
    private $n4NumeroBiblioteca;

    /**
     * @var integer
     */
    private $n4NumeroSala;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6Equipamiento
     */
    private $infraestructuraH6Equipamiento;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n4MobiliarioEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH6MobiliarioTipo
     */
    private $n4MobiliarioTipo;


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
     * Set n4NumeroAula
     *
     * @param integer $n4NumeroAula
     * @return InfraestructuraH6EquipamientoPedagogico
     */
    public function setN4NumeroAula($n4NumeroAula)
    {
        $this->n4NumeroAula = $n4NumeroAula;
    
        return $this;
    }

    /**
     * Get n4NumeroAula
     *
     * @return integer 
     */
    public function getN4NumeroAula()
    {
        return $this->n4NumeroAula;
    }

    /**
     * Set n4NumeroTaller
     *
     * @param integer $n4NumeroTaller
     * @return InfraestructuraH6EquipamientoPedagogico
     */
    public function setN4NumeroTaller($n4NumeroTaller)
    {
        $this->n4NumeroTaller = $n4NumeroTaller;
    
        return $this;
    }

    /**
     * Get n4NumeroTaller
     *
     * @return integer 
     */
    public function getN4NumeroTaller()
    {
        return $this->n4NumeroTaller;
    }

    /**
     * Set n4NumeroLaboratorio
     *
     * @param integer $n4NumeroLaboratorio
     * @return InfraestructuraH6EquipamientoPedagogico
     */
    public function setN4NumeroLaboratorio($n4NumeroLaboratorio)
    {
        $this->n4NumeroLaboratorio = $n4NumeroLaboratorio;
    
        return $this;
    }

    /**
     * Get n4NumeroLaboratorio
     *
     * @return integer 
     */
    public function getN4NumeroLaboratorio()
    {
        return $this->n4NumeroLaboratorio;
    }

    /**
     * Set n4NumeroBiblioteca
     *
     * @param integer $n4NumeroBiblioteca
     * @return InfraestructuraH6EquipamientoPedagogico
     */
    public function setN4NumeroBiblioteca($n4NumeroBiblioteca)
    {
        $this->n4NumeroBiblioteca = $n4NumeroBiblioteca;
    
        return $this;
    }

    /**
     * Get n4NumeroBiblioteca
     *
     * @return integer 
     */
    public function getN4NumeroBiblioteca()
    {
        return $this->n4NumeroBiblioteca;
    }

    /**
     * Set n4NumeroSala
     *
     * @param integer $n4NumeroSala
     * @return InfraestructuraH6EquipamientoPedagogico
     */
    public function setN4NumeroSala($n4NumeroSala)
    {
        $this->n4NumeroSala = $n4NumeroSala;
    
        return $this;
    }

    /**
     * Get n4NumeroSala
     *
     * @return integer 
     */
    public function getN4NumeroSala()
    {
        return $this->n4NumeroSala;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH6EquipamientoPedagogico
     */
    public function setFecharegistro($fecharegistro)
    {
        $this->fecharegistro = $fecharegistro;
    
        return $this;
    }

    /**
     * Get fecharegistro
     *
     * @return \DateTime 
     */
    public function getFecharegistro()
    {
        return $this->fecharegistro;
    }

    /**
     * Set infraestructuraH6Equipamiento
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6Equipamiento $infraestructuraH6Equipamiento
     * @return InfraestructuraH6EquipamientoPedagogico
     */
    public function setInfraestructuraH6Equipamiento(\Sie\AppWebBundle\Entity\InfraestructuraH6Equipamiento $infraestructuraH6Equipamiento = null)
    {
        $this->infraestructuraH6Equipamiento = $infraestructuraH6Equipamiento;
    
        return $this;
    }

    /**
     * Get infraestructuraH6Equipamiento
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6Equipamiento 
     */
    public function getInfraestructuraH6Equipamiento()
    {
        return $this->infraestructuraH6Equipamiento;
    }

    /**
     * Set n4MobiliarioEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n4MobiliarioEstadogeneralTipo
     * @return InfraestructuraH6EquipamientoPedagogico
     */
    public function setN4MobiliarioEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n4MobiliarioEstadogeneralTipo = null)
    {
        $this->n4MobiliarioEstadogeneralTipo = $n4MobiliarioEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n4MobiliarioEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN4MobiliarioEstadogeneralTipo()
    {
        return $this->n4MobiliarioEstadogeneralTipo;
    }

    /**
     * Set n4MobiliarioTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH6MobiliarioTipo $n4MobiliarioTipo
     * @return InfraestructuraH6EquipamientoPedagogico
     */
    public function setN4MobiliarioTipo(\Sie\AppWebBundle\Entity\InfraestructuraH6MobiliarioTipo $n4MobiliarioTipo = null)
    {
        $this->n4MobiliarioTipo = $n4MobiliarioTipo;
    
        return $this;
    }

    /**
     * Get n4MobiliarioTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH6MobiliarioTipo 
     */
    public function getN4MobiliarioTipo()
    {
        return $this->n4MobiliarioTipo;
    }
}
