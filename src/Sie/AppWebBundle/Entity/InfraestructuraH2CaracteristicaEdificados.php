<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraH2CaracteristicaEdificados
 */
class InfraestructuraH2CaracteristicaEdificados
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $n3NombreBloque;

    /**
     * @var integer
     */
    private $n3AreaM2;

    /**
     * @var integer
     */
    private $n3Numeroplantas;

    /**
     * @var integer
     */
    private $n3NumeroAmbientesPedagogicos;

    /**
     * @var integer
     */
    private $n3NumeroNoAmbientesPedagogicos;

    /**
     * @var integer
     */
    private $n3NumeroTotalPedagogicos;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2TechoMaterialTipo
     */
    private $n3TechoMaterialTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2ParedMaterialTipo
     */
    private $n3ParedMaterialTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2CieloMaterialTipo
     */
    private $n3CieloMaterialTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2PisoMaterialTipo
     */
    private $n3PisoMaterialTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica
     */
    private $infraestructuraH2Caracteristica;


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
     * Set n3NombreBloque
     *
     * @param string $n3NombreBloque
     * @return InfraestructuraH2CaracteristicaEdificados
     */
    public function setN3NombreBloque($n3NombreBloque)
    {
        $this->n3NombreBloque = $n3NombreBloque;
    
        return $this;
    }

    /**
     * Get n3NombreBloque
     *
     * @return string 
     */
    public function getN3NombreBloque()
    {
        return $this->n3NombreBloque;
    }

    /**
     * Set n3AreaM2
     *
     * @param integer $n3AreaM2
     * @return InfraestructuraH2CaracteristicaEdificados
     */
    public function setN3AreaM2($n3AreaM2)
    {
        $this->n3AreaM2 = $n3AreaM2;
    
        return $this;
    }

    /**
     * Get n3AreaM2
     *
     * @return integer 
     */
    public function getN3AreaM2()
    {
        return $this->n3AreaM2;
    }

    /**
     * Set n3Numeroplantas
     *
     * @param integer $n3Numeroplantas
     * @return InfraestructuraH2CaracteristicaEdificados
     */
    public function setN3Numeroplantas($n3Numeroplantas)
    {
        $this->n3Numeroplantas = $n3Numeroplantas;
    
        return $this;
    }

    /**
     * Get n3Numeroplantas
     *
     * @return integer 
     */
    public function getN3Numeroplantas()
    {
        return $this->n3Numeroplantas;
    }

    /**
     * Set n3NumeroAmbientesPedagogicos
     *
     * @param integer $n3NumeroAmbientesPedagogicos
     * @return InfraestructuraH2CaracteristicaEdificados
     */
    public function setN3NumeroAmbientesPedagogicos($n3NumeroAmbientesPedagogicos)
    {
        $this->n3NumeroAmbientesPedagogicos = $n3NumeroAmbientesPedagogicos;
    
        return $this;
    }

    /**
     * Get n3NumeroAmbientesPedagogicos
     *
     * @return integer 
     */
    public function getN3NumeroAmbientesPedagogicos()
    {
        return $this->n3NumeroAmbientesPedagogicos;
    }

    /**
     * Set n3NumeroNoAmbientesPedagogicos
     *
     * @param integer $n3NumeroNoAmbientesPedagogicos
     * @return InfraestructuraH2CaracteristicaEdificados
     */
    public function setN3NumeroNoAmbientesPedagogicos($n3NumeroNoAmbientesPedagogicos)
    {
        $this->n3NumeroNoAmbientesPedagogicos = $n3NumeroNoAmbientesPedagogicos;
    
        return $this;
    }

    /**
     * Get n3NumeroNoAmbientesPedagogicos
     *
     * @return integer 
     */
    public function getN3NumeroNoAmbientesPedagogicos()
    {
        return $this->n3NumeroNoAmbientesPedagogicos;
    }

    /**
     * Set n3NumeroTotalPedagogicos
     *
     * @param integer $n3NumeroTotalPedagogicos
     * @return InfraestructuraH2CaracteristicaEdificados
     */
    public function setN3NumeroTotalPedagogicos($n3NumeroTotalPedagogicos)
    {
        $this->n3NumeroTotalPedagogicos = $n3NumeroTotalPedagogicos;
    
        return $this;
    }

    /**
     * Get n3NumeroTotalPedagogicos
     *
     * @return integer 
     */
    public function getN3NumeroTotalPedagogicos()
    {
        return $this->n3NumeroTotalPedagogicos;
    }

    /**
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraH2CaracteristicaEdificados
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
     * Set n3TechoMaterialTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2TechoMaterialTipo $n3TechoMaterialTipo
     * @return InfraestructuraH2CaracteristicaEdificados
     */
    public function setN3TechoMaterialTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2TechoMaterialTipo $n3TechoMaterialTipo = null)
    {
        $this->n3TechoMaterialTipo = $n3TechoMaterialTipo;
    
        return $this;
    }

    /**
     * Get n3TechoMaterialTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2TechoMaterialTipo 
     */
    public function getN3TechoMaterialTipo()
    {
        return $this->n3TechoMaterialTipo;
    }

    /**
     * Set n3ParedMaterialTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2ParedMaterialTipo $n3ParedMaterialTipo
     * @return InfraestructuraH2CaracteristicaEdificados
     */
    public function setN3ParedMaterialTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2ParedMaterialTipo $n3ParedMaterialTipo = null)
    {
        $this->n3ParedMaterialTipo = $n3ParedMaterialTipo;
    
        return $this;
    }

    /**
     * Get n3ParedMaterialTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2ParedMaterialTipo 
     */
    public function getN3ParedMaterialTipo()
    {
        return $this->n3ParedMaterialTipo;
    }

    /**
     * Set n3CieloMaterialTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2CieloMaterialTipo $n3CieloMaterialTipo
     * @return InfraestructuraH2CaracteristicaEdificados
     */
    public function setN3CieloMaterialTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2CieloMaterialTipo $n3CieloMaterialTipo = null)
    {
        $this->n3CieloMaterialTipo = $n3CieloMaterialTipo;
    
        return $this;
    }

    /**
     * Get n3CieloMaterialTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2CieloMaterialTipo 
     */
    public function getN3CieloMaterialTipo()
    {
        return $this->n3CieloMaterialTipo;
    }

    /**
     * Set n3PisoMaterialTipo
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2PisoMaterialTipo $n3PisoMaterialTipo
     * @return InfraestructuraH2CaracteristicaEdificados
     */
    public function setN3PisoMaterialTipo(\Sie\AppWebBundle\Entity\InfraestructuraH2PisoMaterialTipo $n3PisoMaterialTipo = null)
    {
        $this->n3PisoMaterialTipo = $n3PisoMaterialTipo;
    
        return $this;
    }

    /**
     * Get n3PisoMaterialTipo
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2PisoMaterialTipo 
     */
    public function getN3PisoMaterialTipo()
    {
        return $this->n3PisoMaterialTipo;
    }

    /**
     * Set infraestructuraH2Caracteristica
     *
     * @param \Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica $infraestructuraH2Caracteristica
     * @return InfraestructuraH2CaracteristicaEdificados
     */
    public function setInfraestructuraH2Caracteristica(\Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica $infraestructuraH2Caracteristica = null)
    {
        $this->infraestructuraH2Caracteristica = $infraestructuraH2Caracteristica;
    
        return $this;
    }

    /**
     * Get infraestructuraH2Caracteristica
     *
     * @return \Sie\AppWebBundle\Entity\InfraestructuraH2Caracteristica 
     */
    public function getInfraestructuraH2Caracteristica()
    {
        return $this->infraestructuraH2Caracteristica;
    }
}
