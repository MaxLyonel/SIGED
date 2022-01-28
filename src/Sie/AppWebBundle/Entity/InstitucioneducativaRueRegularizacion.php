<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaRueRegularizacion
 */
class InstitucioneducativaRueRegularizacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaFundacion;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var string
     */
    private $rutaAdjunto;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;


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
     * Set fechaFundacion
     *
     * @param \DateTime $fechaFundacion
     * @return InstitucioneducativaRueRegularizacion
     */
    public function setFechaFundacion($fechaFundacion)
    {
        $this->fechaFundacion = $fechaFundacion;
    
        return $this;
    }

    /**
     * Get fechaFundacion
     *
     * @return \DateTime 
     */
    public function getFechaFundacion()
    {
        return $this->fechaFundacion;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return InstitucioneducativaRueRegularizacion
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }

    /**
     * Set rutaAdjunto
     *
     * @param string $rutaAdjunto
     * @return InstitucioneducativaRueRegularizacion
     */
    public function setRutaAdjunto($rutaAdjunto)
    {
        $this->rutaAdjunto = $rutaAdjunto;
    
        return $this;
    }

    /**
     * Get rutaAdjunto
     *
     * @return string 
     */
    public function getRutaAdjunto()
    {
        return $this->rutaAdjunto;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaRueRegularizacion
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaRueRegularizacion
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }
}
