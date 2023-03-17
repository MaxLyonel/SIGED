<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EveFaseTipo
 */
class EveFaseTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var string
     */
    private $fechas;

    /**
     * @var \Sie\AppWebBundle\Entity\EveModalidadesTipo
     */
    private $eveModalidadesTipo;


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
     * Set descripcion
     *
     * @param string $descripcion
     * @return EveFaseTipo
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return EveFaseTipo
     */
    public function setEsVigente($esVigente)
    {
        $this->esVigente = $esVigente;
    
        return $this;
    }

    /**
     * Get esVigente
     *
     * @return boolean 
     */
    public function getEsVigente()
    {
        return $this->esVigente;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return EveFaseTipo
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return EveFaseTipo
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }

    /**
     * Set fechas
     *
     * @param string $fechas
     * @return EveFaseTipo
     */
    public function setFechas($fechas)
    {
        $this->fechas = $fechas;
    
        return $this;
    }

    /**
     * Get fechas
     *
     * @return string 
     */
    public function getFechas()
    {
        return $this->fechas;
    }

    /**
     * Set eveModalidadesTipo
     *
     * @param \Sie\AppWebBundle\Entity\EveModalidadesTipo $eveModalidadesTipo
     * @return EveFaseTipo
     */
    public function setEveModalidadesTipo(\Sie\AppWebBundle\Entity\EveModalidadesTipo $eveModalidadesTipo = null)
    {
        $this->eveModalidadesTipo = $eveModalidadesTipo;
    
        return $this;
    }

    /**
     * Get eveModalidadesTipo
     *
     * @return \Sie\AppWebBundle\Entity\EveModalidadesTipo 
     */
    public function getEveModalidadesTipo()
    {
        return $this->eveModalidadesTipo;
    }
}
