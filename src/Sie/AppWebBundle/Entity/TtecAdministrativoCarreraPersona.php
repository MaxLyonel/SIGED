<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecAdministrativoCarreraPersona
 */
class TtecAdministrativoCarreraPersona
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecCarreraTipo
     */
    private $ttecCarreraTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecCargoTipo
     */
    private $ttecCargoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecCargoDesignacionTipo
     */
    private $ttecCargoDesignacionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

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
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return TtecAdministrativoCarreraPersona
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecAdministrativoCarreraPersona
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
     * @return TtecAdministrativoCarreraPersona
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
     * Set ttecCarreraTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecCarreraTipo $ttecCarreraTipo
     * @return TtecAdministrativoCarreraPersona
     */
    public function setTtecCarreraTipo(\Sie\AppWebBundle\Entity\TtecCarreraTipo $ttecCarreraTipo = null)
    {
        $this->ttecCarreraTipo = $ttecCarreraTipo;
    
        return $this;
    }

    /**
     * Get ttecCarreraTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecCarreraTipo 
     */
    public function getTtecCarreraTipo()
    {
        return $this->ttecCarreraTipo;
    }

    /**
     * Set ttecCargoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecCargoTipo $ttecCargoTipo
     * @return TtecAdministrativoCarreraPersona
     */
    public function setTtecCargoTipo(\Sie\AppWebBundle\Entity\TtecCargoTipo $ttecCargoTipo = null)
    {
        $this->ttecCargoTipo = $ttecCargoTipo;
    
        return $this;
    }

    /**
     * Get ttecCargoTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecCargoTipo 
     */
    public function getTtecCargoTipo()
    {
        return $this->ttecCargoTipo;
    }

    /**
     * Set ttecCargoDesignacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecCargoDesignacionTipo $ttecCargoDesignacionTipo
     * @return TtecAdministrativoCarreraPersona
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

    /**
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return TtecAdministrativoCarreraPersona
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return TtecAdministrativoCarreraPersona
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
     * @var \Sie\AppWebBundle\Entity\FinanciamientoTipo
     */
    private $financiamientoTipo;


    /**
     * Set financiamientoTipo
     *
     * @param \Sie\AppWebBundle\Entity\FinanciamientoTipo $financiamientoTipo
     * @return TtecAdministrativoCarreraPersona
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
}
