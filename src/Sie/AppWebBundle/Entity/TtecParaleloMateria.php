<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecParaleloMateria
 */
class TtecParaleloMateria
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var integer
     */
    private $cupo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecPeriodoTipo
     */
    private $ttecPeriodoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TurnoTipo
     */
    private $turnoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecParaleloTipo
     */
    private $ttecParaleloTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecMateriaTipo
     */
    private $ttecMateriaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecParaleloMateria
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
     * @return TtecParaleloMateria
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
     * Set cupo
     *
     * @param integer $cupo
     * @return TtecParaleloMateria
     */
    public function setCupo($cupo)
    {
        $this->cupo = $cupo;
    
        return $this;
    }

    /**
     * Get cupo
     *
     * @return integer 
     */
    public function getCupo()
    {
        return $this->cupo;
    }

    /**
     * Set ttecPeriodoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecPeriodoTipo $ttecPeriodoTipo
     * @return TtecParaleloMateria
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
     * Set turnoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TurnoTipo $turnoTipo
     * @return TtecParaleloMateria
     */
    public function setTurnoTipo(\Sie\AppWebBundle\Entity\TurnoTipo $turnoTipo = null)
    {
        $this->turnoTipo = $turnoTipo;
    
        return $this;
    }

    /**
     * Get turnoTipo
     *
     * @return \Sie\AppWebBundle\Entity\TurnoTipo 
     */
    public function getTurnoTipo()
    {
        return $this->turnoTipo;
    }

    /**
     * Set ttecParaleloTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecParaleloTipo $ttecParaleloTipo
     * @return TtecParaleloMateria
     */
    public function setTtecParaleloTipo(\Sie\AppWebBundle\Entity\TtecParaleloTipo $ttecParaleloTipo = null)
    {
        $this->ttecParaleloTipo = $ttecParaleloTipo;
    
        return $this;
    }

    /**
     * Get ttecParaleloTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecParaleloTipo 
     */
    public function getTtecParaleloTipo()
    {
        return $this->ttecParaleloTipo;
    }

    /**
     * Set ttecMateriaTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecMateriaTipo $ttecMateriaTipo
     * @return TtecParaleloMateria
     */
    public function setTtecMateriaTipo(\Sie\AppWebBundle\Entity\TtecMateriaTipo $ttecMateriaTipo = null)
    {
        $this->ttecMateriaTipo = $ttecMateriaTipo;
    
        return $this;
    }

    /**
     * Get ttecMateriaTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecMateriaTipo 
     */
    public function getTtecMateriaTipo()
    {
        return $this->ttecMateriaTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return TtecParaleloMateria
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }
    /**
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \DateTime
     */
    private $fechaRegistroVigente;

    /**
     * @var \DateTime
     */
    private $fechaRegistroNoVigente;


    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return TtecParaleloMateria
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
     * Set fechaRegistroVigente
     *
     * @param \DateTime $fechaRegistroVigente
     * @return TtecParaleloMateria
     */
    public function setFechaRegistroVigente($fechaRegistroVigente)
    {
        $this->fechaRegistroVigente = $fechaRegistroVigente;
    
        return $this;
    }

    /**
     * Get fechaRegistroVigente
     *
     * @return \DateTime 
     */
    public function getFechaRegistroVigente()
    {
        return $this->fechaRegistroVigente;
    }

    /**
     * Set fechaRegistroNoVigente
     *
     * @param \DateTime $fechaRegistroNoVigente
     * @return TtecParaleloMateria
     */
    public function setFechaRegistroNoVigente($fechaRegistroNoVigente)
    {
        $this->fechaRegistroNoVigente = $fechaRegistroNoVigente;
    
        return $this;
    }

    /**
     * Get fechaRegistroNoVigente
     *
     * @return \DateTime 
     */
    public function getFechaRegistroNoVigente()
    {
        return $this->fechaRegistroNoVigente;
    }
}
