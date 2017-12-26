<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecInstitucioneducativaHistorico
 */
class TtecInstitucioneducativaHistorico
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nroResolucion;

    /**
     * @var \DateTime
     */
    private $fechaResolucion;

    /**
     * @var string
     */
    private $descripcion;

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
    private $datoAdicional;

    /**
     * @var string
     */
    private $archivo;

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
     * Set nroResolucion
     *
     * @param string $nroResolucion
     * @return TtecInstitucioneducativaHistorico
     */
    public function setNroResolucion($nroResolucion)
    {
        $this->nroResolucion = $nroResolucion;
    
        return $this;
    }

    /**
     * Get nroResolucion
     *
     * @return string 
     */
    public function getNroResolucion()
    {
        return $this->nroResolucion;
    }

    /**
     * Set fechaResolucion
     *
     * @param \DateTime $fechaResolucion
     * @return TtecInstitucioneducativaHistorico
     */
    public function setFechaResolucion($fechaResolucion)
    {
        $this->fechaResolucion = $fechaResolucion;
    
        return $this;
    }

    /**
     * Get fechaResolucion
     *
     * @return \DateTime 
     */
    public function getFechaResolucion()
    {
        return $this->fechaResolucion;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return TtecInstitucioneducativaHistorico
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecInstitucioneducativaHistorico
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
     * @return TtecInstitucioneducativaHistorico
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
     * Set datoAdicional
     *
     * @param string $datoAdicional
     * @return TtecInstitucioneducativaHistorico
     */
    public function setDatoAdicional($datoAdicional)
    {
        $this->datoAdicional = $datoAdicional;
    
        return $this;
    }

    /**
     * Get datoAdicional
     *
     * @return string 
     */
    public function getDatoAdicional()
    {
        return $this->datoAdicional;
    }

    /**
     * Set archivo
     *
     * @param string $archivo
     * @return TtecInstitucioneducativaHistorico
     */
    public function setArchivo($archivo)
    {
        $this->archivo = $archivo;
    
        return $this;
    }

    /**
     * Get archivo
     *
     * @return string 
     */
    public function getArchivo()
    {
        return $this->archivo;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return TtecInstitucioneducativaHistorico
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
