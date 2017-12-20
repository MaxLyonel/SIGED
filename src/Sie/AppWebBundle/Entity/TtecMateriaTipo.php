<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecMateriaTipo
 */
class TtecMateriaTipo
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
     * @var string
     */
    private $codigo;

    /**
     * @var integer
     */
    private $horasTeoricas;

    /**
     * @var integer
     */
    private $horasPracticas;

    /**
     * @var integer
     */
    private $horasTotales;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecPeriodoTipo
     */
    private $ttecPeriodoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecPensum
     */
    private $ttecPensum;


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
     * @return TtecMateriaTipo
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
     * Set codigo
     *
     * @param string $codigo
     * @return TtecMateriaTipo
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set horasTeoricas
     *
     * @param integer $horasTeoricas
     * @return TtecMateriaTipo
     */
    public function setHorasTeoricas($horasTeoricas)
    {
        $this->horasTeoricas = $horasTeoricas;
    
        return $this;
    }

    /**
     * Get horasTeoricas
     *
     * @return integer 
     */
    public function getHorasTeoricas()
    {
        return $this->horasTeoricas;
    }

    /**
     * Set horasPracticas
     *
     * @param integer $horasPracticas
     * @return TtecMateriaTipo
     */
    public function setHorasPracticas($horasPracticas)
    {
        $this->horasPracticas = $horasPracticas;
    
        return $this;
    }

    /**
     * Get horasPracticas
     *
     * @return integer 
     */
    public function getHorasPracticas()
    {
        return $this->horasPracticas;
    }

    /**
     * Set horasTotales
     *
     * @param integer $horasTotales
     * @return TtecMateriaTipo
     */
    public function setHorasTotales($horasTotales)
    {
        $this->horasTotales = $horasTotales;
    
        return $this;
    }

    /**
     * Get horasTotales
     *
     * @return integer 
     */
    public function getHorasTotales()
    {
        return $this->horasTotales;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecMateriaTipo
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
     * @return TtecMateriaTipo
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
     * Set ttecPeriodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecPeriodoTipo $ttecPeriodoTipo
     * @return TtecMateriaTipo
     */
    public function setTtecPeriodoTipo(\Sie\AppWebBundle\Entity\TtecPeriodoTipo $ttecPeriodoTipo = null)
    {
        $this->ttecPeriodoTipo = $ttecPeriodoTipo;
    
        return $this;
    }

    /**
     * Get ttecPeriodoTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecPeriodoTipo 
     */
    public function getTtecPeriodoTipo()
    {
        return $this->ttecPeriodoTipo;
    }

    /**
     * Set ttecPensum
     *
     * @param \Sie\AppWebBundle\Entity\TtecPensum $ttecPensum
     * @return TtecMateriaTipo
     */
    public function setTtecPensum(\Sie\AppWebBundle\Entity\TtecPensum $ttecPensum = null)
    {
        $this->ttecPensum = $ttecPensum;
    
        return $this;
    }

    /**
     * Get ttecPensum
     *
     * @return \Sie\AppWebBundle\Entity\TtecPensum 
     */
    public function getTtecPensum()
    {
        return $this->ttecPensum;
    }
}
