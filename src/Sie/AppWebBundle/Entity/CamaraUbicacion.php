<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CamaraUbicacion
 */
class CamaraUbicacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $ubicacionOtro;

    /**
     * @var string
     */
    private $marca;

    /**
     * @var string
     */
    private $modelo;

    /**
     * @var \Sie\AppWebBundle\Entity\CamaraInstitucioneducativa
     */
    private $camaraInstitucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\CamaraEstadoTipo
     */
    private $camaraEstadoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\CamaraUbicacionTipo
     */
    private $camaraUbicacionTipo;


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
     * Set ubicacionOtro
     *
     * @param string $ubicacionOtro
     * @return CamaraUbicacion
     */
    public function setUbicacionOtro($ubicacionOtro)
    {
        $this->ubicacionOtro = $ubicacionOtro;
    
        return $this;
    }

    /**
     * Get ubicacionOtro
     *
     * @return string 
     */
    public function getUbicacionOtro()
    {
        return $this->ubicacionOtro;
    }

    /**
     * Set marca
     *
     * @param string $marca
     * @return CamaraUbicacion
     */
    public function setMarca($marca)
    {
        $this->marca = $marca;
    
        return $this;
    }

    /**
     * Get marca
     *
     * @return string 
     */
    public function getMarca()
    {
        return $this->marca;
    }

    /**
     * Set modelo
     *
     * @param string $modelo
     * @return CamaraUbicacion
     */
    public function setModelo($modelo)
    {
        $this->modelo = $modelo;
    
        return $this;
    }

    /**
     * Get modelo
     *
     * @return string 
     */
    public function getModelo()
    {
        return $this->modelo;
    }

    /**
     * Set camaraInstitucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\CamaraInstitucioneducativa $camaraInstitucioneducativa
     * @return CamaraUbicacion
     */
    public function setCamaraInstitucioneducativa(\Sie\AppWebBundle\Entity\CamaraInstitucioneducativa $camaraInstitucioneducativa = null)
    {
        $this->camaraInstitucioneducativa = $camaraInstitucioneducativa;
    
        return $this;
    }

    /**
     * Get camaraInstitucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\CamaraInstitucioneducativa 
     */
    public function getCamaraInstitucioneducativa()
    {
        return $this->camaraInstitucioneducativa;
    }

    /**
     * Set camaraEstadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\CamaraEstadoTipo $camaraEstadoTipo
     * @return CamaraUbicacion
     */
    public function setCamaraEstadoTipo(\Sie\AppWebBundle\Entity\CamaraEstadoTipo $camaraEstadoTipo = null)
    {
        $this->camaraEstadoTipo = $camaraEstadoTipo;
    
        return $this;
    }

    /**
     * Get camaraEstadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\CamaraEstadoTipo 
     */
    public function getCamaraEstadoTipo()
    {
        return $this->camaraEstadoTipo;
    }

    /**
     * Set camaraUbicacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\CamaraUbicacionTipo $camaraUbicacionTipo
     * @return CamaraUbicacion
     */
    public function setCamaraUbicacionTipo(\Sie\AppWebBundle\Entity\CamaraUbicacionTipo $camaraUbicacionTipo = null)
    {
        $this->camaraUbicacionTipo = $camaraUbicacionTipo;
    
        return $this;
    }

    /**
     * Get camaraUbicacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\CamaraUbicacionTipo 
     */
    public function getCamaraUbicacionTipo()
    {
        return $this->camaraUbicacionTipo;
    }
}
