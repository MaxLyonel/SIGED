<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ServicioBasicoTipo
 */
class ServicioBasicoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $servicioBasico;

    /**
     * @var string
     */
    private $obs;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set servicioBasico
     *
     * @param string $servicioBasico
     * @return ServicioBasicoTipo
     */
    public function setServicioBasico($servicioBasico)
    {
        $this->servicioBasico = $servicioBasico;
    
        return $this;
    }

    /**
     * Get servicioBasico
     *
     * @return string 
     */
    public function getServicioBasico()
    {
        return $this->servicioBasico;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return ServicioBasicoTipo
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
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return ServicioBasicoTipo
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
     * @return ServicioBasicoTipo
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
     * @return ServicioBasicoTipo
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
}
