<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PreinsEstudianteInscripcionJustificativo
 */
class PreinsEstudianteInscripcionJustificativo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaInscripcion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\PreinsJustificativoTipo
     */
    private $preinsJustificativoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion
     */
    private $preinsEstudianteInscripcion;


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
     * Set fechaInscripcion
     *
     * @param \DateTime $fechaInscripcion
     * @return PreinsEstudianteInscripcionJustificativo
     */
    public function setFechaInscripcion($fechaInscripcion)
    {
        $this->fechaInscripcion = $fechaInscripcion;
    
        return $this;
    }

    /**
     * Get fechaInscripcion
     *
     * @return \DateTime 
     */
    public function getFechaInscripcion()
    {
        return $this->fechaInscripcion;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return PreinsEstudianteInscripcionJustificativo
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
     * Set obs
     *
     * @param string $obs
     * @return PreinsEstudianteInscripcionJustificativo
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
     * Set preinsJustificativoTipo
     *
     * @param \Sie\AppWebBundle\Entity\PreinsJustificativoTipo $preinsJustificativoTipo
     * @return PreinsEstudianteInscripcionJustificativo
     */
    public function setPreinsJustificativoTipo(\Sie\AppWebBundle\Entity\PreinsJustificativoTipo $preinsJustificativoTipo = null)
    {
        $this->preinsJustificativoTipo = $preinsJustificativoTipo;
    
        return $this;
    }

    /**
     * Get preinsJustificativoTipo
     *
     * @return \Sie\AppWebBundle\Entity\PreinsJustificativoTipo 
     */
    public function getPreinsJustificativoTipo()
    {
        return $this->preinsJustificativoTipo;
    }

    /**
     * Set preinsEstudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion $preinsEstudianteInscripcion
     * @return PreinsEstudianteInscripcionJustificativo
     */
    public function setPreinsEstudianteInscripcion(\Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion $preinsEstudianteInscripcion = null)
    {
        $this->preinsEstudianteInscripcion = $preinsEstudianteInscripcion;
    
        return $this;
    }

    /**
     * Get preinsEstudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion 
     */
    public function getPreinsEstudianteInscripcion()
    {
        return $this->preinsEstudianteInscripcion;
    }
}
