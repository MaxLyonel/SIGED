<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH6EquipamientoDormitorio
 */
class InfraestructuraH6EquipamientoDormitorio
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $n2DormitorioGeneroTipoId;

    /**
     * @var integer
     */
    private $n2CantidadDormitorios;

    /**
     * @var integer
     */
    private $n2Camaliteras;

    /**
     * @var integer
     */
    private $n2Camasimples;

    /**
     * @var integer
     */
    private $n2Camaotro;

    /**
     * @var integer
     */
    private $n1Aream2;

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
    private $n2CieloEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n2PisoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n2TechoEstadogeneralTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo
     */
    private $n2ParedEstadogeneralTipo;


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
     * Set n2DormitorioGeneroTipoId
     *
     * @param integer $n2DormitorioGeneroTipoId
     * @return InfraestructuraH6EquipamientoDormitorio
     */
    public function setN2DormitorioGeneroTipoId($n2DormitorioGeneroTipoId)
    {
        $this->n2DormitorioGeneroTipoId = $n2DormitorioGeneroTipoId;
    
        return $this;
    }

    /**
     * Get n2DormitorioGeneroTipoId
     *
     * @return integer 
     */
    public function getN2DormitorioGeneroTipoId()
    {
        return $this->n2DormitorioGeneroTipoId;
    }

    /**
     * Set n2CantidadDormitorios
     *
     * @param integer $n2CantidadDormitorios
     * @return InfraestructuraH6EquipamientoDormitorio
     */
    public function setN2CantidadDormitorios($n2CantidadDormitorios)
    {
        $this->n2CantidadDormitorios = $n2CantidadDormitorios;
    
        return $this;
    }

    /**
     * Get n2CantidadDormitorios
     *
     * @return integer 
     */
    public function getN2CantidadDormitorios()
    {
        return $this->n2CantidadDormitorios;
    }

    /**
     * Set n2Camaliteras
     *
     * @param integer $n2Camaliteras
     * @return InfraestructuraH6EquipamientoDormitorio
     */
    public function setN2Camaliteras($n2Camaliteras)
    {
        $this->n2Camaliteras = $n2Camaliteras;
    
        return $this;
    }

    /**
     * Get n2Camaliteras
     *
     * @return integer 
     */
    public function getN2Camaliteras()
    {
        return $this->n2Camaliteras;
    }

    /**
     * Set n2Camasimples
     *
     * @param integer $n2Camasimples
     * @return InfraestructuraH6EquipamientoDormitorio
     */
    public function setN2Camasimples($n2Camasimples)
    {
        $this->n2Camasimples = $n2Camasimples;
    
        return $this;
    }

    /**
     * Get n2Camasimples
     *
     * @return integer 
     */
    public function getN2Camasimples()
    {
        return $this->n2Camasimples;
    }

    /**
     * Set n2Camaotro
     *
     * @param integer $n2Camaotro
     * @return InfraestructuraH6EquipamientoDormitorio
     */
    public function setN2Camaotro($n2Camaotro)
    {
        $this->n2Camaotro = $n2Camaotro;
    
        return $this;
    }

    /**
     * Get n2Camaotro
     *
     * @return integer 
     */
    public function getN2Camaotro()
    {
        return $this->n2Camaotro;
    }

    /**
     * Set n1Aream2
     *
     * @param integer $n1Aream2
     * @return InfraestructuraH6EquipamientoDormitorio
     */
    public function setN1Aream2($n1Aream2)
    {
        $this->n1Aream2 = $n1Aream2;
    
        return $this;
    }

    /**
     * Get n1Aream2
     *
     * @return integer 
     */
    public function getN1Aream2()
    {
        return $this->n1Aream2;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH6EquipamientoDormitorio
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
     * @return InfraestructuraH6EquipamientoDormitorio
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
     * Set n2CieloEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n2CieloEstadogeneralTipo
     * @return InfraestructuraH6EquipamientoDormitorio
     */
    public function setN2CieloEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n2CieloEstadogeneralTipo = null)
    {
        $this->n2CieloEstadogeneralTipo = $n2CieloEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n2CieloEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN2CieloEstadogeneralTipo()
    {
        return $this->n2CieloEstadogeneralTipo;
    }

    /**
     * Set n2PisoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n2PisoEstadogeneralTipo
     * @return InfraestructuraH6EquipamientoDormitorio
     */
    public function setN2PisoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n2PisoEstadogeneralTipo = null)
    {
        $this->n2PisoEstadogeneralTipo = $n2PisoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n2PisoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN2PisoEstadogeneralTipo()
    {
        return $this->n2PisoEstadogeneralTipo;
    }

    /**
     * Set n2TechoEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n2TechoEstadogeneralTipo
     * @return InfraestructuraH6EquipamientoDormitorio
     */
    public function setN2TechoEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n2TechoEstadogeneralTipo = null)
    {
        $this->n2TechoEstadogeneralTipo = $n2TechoEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n2TechoEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN2TechoEstadogeneralTipo()
    {
        return $this->n2TechoEstadogeneralTipo;
    }

    /**
     * Set n2ParedEstadogeneralTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n2ParedEstadogeneralTipo
     * @return InfraestructuraH6EquipamientoDormitorio
     */
    public function setN2ParedEstadogeneralTipo(\Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo $n2ParedEstadogeneralTipo = null)
    {
        $this->n2ParedEstadogeneralTipo = $n2ParedEstadogeneralTipo;
    
        return $this;
    }

    /**
     * Get n2ParedEstadogeneralTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraGenEstadogeneralTipo 
     */
    public function getN2ParedEstadogeneralTipo()
    {
        return $this->n2ParedEstadogeneralTipo;
    }
}
