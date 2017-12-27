<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecDocentePersona
 */
class TtecDocentePersona
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $docExperienciaLaboral;

    /**
     * @var boolean
     */
    private $docCursosRespaldo;

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
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;


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
     * Set docExperienciaLaboral
     *
     * @param boolean $docExperienciaLaboral
     * @return TtecDocentePersona
     */
    public function setDocExperienciaLaboral($docExperienciaLaboral)
    {
        $this->docExperienciaLaboral = $docExperienciaLaboral;
    
        return $this;
    }

    /**
     * Get docExperienciaLaboral
     *
     * @return boolean 
     */
    public function getDocExperienciaLaboral()
    {
        return $this->docExperienciaLaboral;
    }

    /**
     * Set docCursosRespaldo
     *
     * @param boolean $docCursosRespaldo
     * @return TtecDocentePersona
     */
    public function setDocCursosRespaldo($docCursosRespaldo)
    {
        $this->docCursosRespaldo = $docCursosRespaldo;
    
        return $this;
    }

    /**
     * Get docCursosRespaldo
     *
     * @return boolean 
     */
    public function getDocCursosRespaldo()
    {
        return $this->docCursosRespaldo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecDocentePersona
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
     * @return TtecDocentePersona
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
     * @return TtecDocentePersona
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return TtecDocentePersona
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

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return TtecDocentePersona
     */
    public function setPersona(\Sie\AppWebBundle\Entity\Persona $persona = null)
    {
        $this->persona = $persona;
    
        return $this;
    }

    /**
     * Get persona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getPersona()
    {
        return $this->persona;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return TtecDocentePersona
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
     * @var integer
     */
    private $item;


    /**
     * Set item
     *
     * @param integer $item
     * @return TtecDocentePersona
     */
    public function setItem($item)
    {
        $this->item = $item;
    
        return $this;
    }

    /**
     * Get item
     *
     * @return integer 
     */
    public function getItem()
    {
        return $this->item;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\FinanciamientoTipo
     */
    private $financiamientoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecCargoDesignacionTipo
     */
    private $ttecCargoDesignacionTipo;


    /**
     * Set financiamientoTipo
     *
     * @param \Sie\AppWebBundle\Entity\FinanciamientoTipo $financiamientoTipo
     * @return TtecDocentePersona
     */
    public function setFinanciamientoTipo(\Sie\AppWebBundle\Entity\FinanciamientoTipo $financiamientoTipo = null)
    {
        $this->financiamientoTipo = $financiamientoTipo;
    
        return $this;
    }

    /**
     * Get financiamientoTipo
     *
     * @return \Sie\AppWebBundle\Entity\FinanciamientoTipo 
     */
    public function getFinanciamientoTipo()
    {
        return $this->financiamientoTipo;
    }

    /**
     * Set ttecCargoDesignacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecCargoDesignacionTipo $ttecCargoDesignacionTipo
     * @return TtecDocentePersona
     */
    public function setTtecCargoDesignacionTipo(\Sie\AppWebBundle\Entity\TtecCargoDesignacionTipo $ttecCargoDesignacionTipo = null)
    {
        $this->ttecCargoDesignacionTipo = $ttecCargoDesignacionTipo;
    
        return $this;
    }

    /**
     * Get ttecCargoDesignacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecCargoDesignacionTipo 
     */
    public function getTtecCargoDesignacionTipo()
    {
        return $this->ttecCargoDesignacionTipo;
    }
}
