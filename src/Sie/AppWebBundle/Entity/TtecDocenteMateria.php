<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecDocenteMateria
 */
class TtecDocenteMateria
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
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecParaleloMateria
     */
    private $ttecParaleloMateria;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecDocentePersona
     */
    private $ttecDocentePersona;


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
     * @return TtecDocenteMateria
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
     * @return TtecDocenteMateria
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
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return TtecDocenteMateria
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
     * Set ttecParaleloMateria
     *
     * @param \Sie\AppWebBundle\Entity\TtecParaleloMateria $ttecParaleloMateria
     * @return TtecDocenteMateria
     */
    public function setTtecParaleloMateria(\Sie\AppWebBundle\Entity\TtecParaleloMateria $ttecParaleloMateria = null)
    {
        $this->ttecParaleloMateria = $ttecParaleloMateria;
    
        return $this;
    }

    /**
     * Get ttecParaleloMateria
     *
     * @return \Sie\AppWebBundle\Entity\TtecParaleloMateria 
     */
    public function getTtecParaleloMateria()
    {
        return $this->ttecParaleloMateria;
    }

    /**
     * Set ttecDocentePersona
     *
     * @param \Sie\AppWebBundle\Entity\TtecDocentePersona $ttecDocentePersona
     * @return TtecDocenteMateria
     */
    public function setTtecDocentePersona(\Sie\AppWebBundle\Entity\TtecDocentePersona $ttecDocentePersona = null)
    {
        $this->ttecDocentePersona = $ttecDocentePersona;
    
        return $this;
    }

    /**
     * Get ttecDocentePersona
     *
     * @return \Sie\AppWebBundle\Entity\TtecDocentePersona 
     */
    public function getTtecDocentePersona()
    {
        return $this->ttecDocentePersona;
    }
}
