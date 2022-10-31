<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FrecuenciaUsoInternetTipo
 */
class FrecuenciaUsoInternetTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcionFrecuenciaInternet;

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
     * Set descripcionFrecuenciaInternet
     *
     * @param string $descripcionFrecuenciaInternet
     * @return FrecuenciaUsoInternetTipo
     */
    public function setDescripcionFrecuenciaInternet($descripcionFrecuenciaInternet)
    {
        $this->descripcionFrecuenciaInternet = $descripcionFrecuenciaInternet;
    
        return $this;
    }

    /**
     * Get descripcionFrecuenciaInternet
     *
     * @return string 
     */
    public function getDescripcionFrecuenciaInternet()
    {
        return $this->descripcionFrecuenciaInternet;
    }

    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return FrecuenciaUsoInternetTipo
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
     * @return FrecuenciaUsoInternetTipo
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
     * @return FrecuenciaUsoInternetTipo
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
