<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OlimMateriaTipo
 */
class OlimMateriaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $materia;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    public function __toString(){
        return $this->materia;
    }
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
     * Set materia
     *
     * @param string $materia
     * @return OlimMateriaTipo
     */
    public function setMateria($materia)
    {
        $this->materia = $materia;
    
        return $this;
    }

    /**
     * Get materia
     *
     * @return string 
     */
    public function getMateria()
    {
        return $this->materia;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return OlimMateriaTipo
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
     * @var \DateTime
     */
    private $fechaInsIni;

    /**
     * @var \DateTime
     */
    private $fechaInsFin;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\OlimRegistroOlimpiada
     */
    private $olimRegistroOlimpiada;


    /**
     * Set fechaInsIni
     *
     * @param \DateTime $fechaInsIni
     * @return OlimMateriaTipo
     */
    public function setFechaInsIni($fechaInsIni)
    {
        $this->fechaInsIni = $fechaInsIni;
    
        return $this;
    }

    /**
     * Get fechaInsIni
     *
     * @return \DateTime 
     */
    public function getFechaInsIni()
    {
        return $this->fechaInsIni;
    }

    /**
     * Set fechaInsFin
     *
     * @param \DateTime $fechaInsFin
     * @return OlimMateriaTipo
     */
    public function setFechaInsFin($fechaInsFin)
    {
        $this->fechaInsFin = $fechaInsFin;
    
        return $this;
    }

    /**
     * Get fechaInsFin
     *
     * @return \DateTime 
     */
    public function getFechaInsFin()
    {
        return $this->fechaInsFin;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return OlimMateriaTipo
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
     * Set olimRegistroOlimpiada
     *
     * @param \Sie\AppWebBundle\Entity\OlimRegistroOlimpiada $olimRegistroOlimpiada
     * @return OlimMateriaTipo
     */
    public function setOlimRegistroOlimpiada(\Sie\AppWebBundle\Entity\OlimRegistroOlimpiada $olimRegistroOlimpiada = null)
    {
        $this->olimRegistroOlimpiada = $olimRegistroOlimpiada;
    
        return $this;
    }

    /**
     * Get olimRegistroOlimpiada
     *
     * @return \Sie\AppWebBundle\Entity\OlimRegistroOlimpiada 
     */
    public function getOlimRegistroOlimpiada()
    {
        return $this->olimRegistroOlimpiada;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\OlimClasificadorMateriaTipo
     */
    private $olimClasificadoMateriaTipo;


    /**
     * Set olimClasificadoMateriaTipo
     *
     * @param \Sie\AppWebBundle\Entity\OlimClasificadorMateriaTipo $olimClasificadoMateriaTipo
     * @return OlimMateriaTipo
     */
    public function setOlimClasificadoMateriaTipo(\Sie\AppWebBundle\Entity\OlimClasificadorMateriaTipo $olimClasificadoMateriaTipo = null)
    {
        $this->olimClasificadoMateriaTipo = $olimClasificadoMateriaTipo;
    
        return $this;
    }

    /**
     * Get olimClasificadoMateriaTipo
     *
     * @return \Sie\AppWebBundle\Entity\OlimClasificadorMateriaTipo 
     */
    public function getOlimClasificadoMateriaTipo()
    {
        return $this->olimClasificadoMateriaTipo;
    }
}
