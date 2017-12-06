<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TramiteAlternativa
 */
class TramiteAlternativa
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var integer
     */
    private $estudiante;

    /**
     * @var integer
     */
    private $esp;

    /**
     * @var integer
     */
    private $nivel;

    /**
     * @var integer
     */
    private $periodo;

    /**
     * @var integer
     */
    private $institucioneducativa;

    /**
     * @var integer
     */
    private $tramiteId;

    /**
     * @var \DateTime
     */
    private $fecha;


    /**
     * Get id
     *
     * @return string 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set estudiante
     *
     * @param integer $estudiante
     * @return TramiteAlternativa
     */
    public function setEstudiante($estudiante)
    {
        $this->estudiante = $estudiante;
    
        return $this;
    }

    /**
     * Get estudiante
     *
     * @return integer 
     */
    public function getEstudiante()
    {
        return $this->estudiante;
    }

    /**
     * Set esp
     *
     * @param integer $esp
     * @return TramiteAlternativa
     */
    public function setEsp($esp)
    {
        $this->esp = $esp;
    
        return $this;
    }

    /**
     * Get esp
     *
     * @return integer 
     */
    public function getEsp()
    {
        return $this->esp;
    }

    /**
     * Set nivel
     *
     * @param integer $nivel
     * @return TramiteAlternativa
     */
    public function setNivel($nivel)
    {
        $this->nivel = $nivel;
    
        return $this;
    }

    /**
     * Get nivel
     *
     * @return integer 
     */
    public function getNivel()
    {
        return $this->nivel;
    }

    /**
     * Set periodo
     *
     * @param integer $periodo
     * @return TramiteAlternativa
     */
    public function setPeriodo($periodo)
    {
        $this->periodo = $periodo;
    
        return $this;
    }

    /**
     * Get periodo
     *
     * @return integer 
     */
    public function getPeriodo()
    {
        return $this->periodo;
    }

    /**
     * Set institucioneducativa
     *
     * @param integer $institucioneducativa
     * @return TramiteAlternativa
     */
    public function setInstitucioneducativa($institucioneducativa)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return integer 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set tramiteId
     *
     * @param integer $tramiteId
     * @return TramiteAlternativa
     */
    public function setTramiteId($tramiteId)
    {
        $this->tramiteId = $tramiteId;
    
        return $this;
    }

    /**
     * Get tramiteId
     *
     * @return integer 
     */
    public function getTramiteId()
    {
        return $this->tramiteId;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return TramiteAlternativa
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;
    
        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }
}
