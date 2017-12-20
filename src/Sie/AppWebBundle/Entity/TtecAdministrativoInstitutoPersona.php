<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecAdministrativoInstitutoPersona
 */
class TtecAdministrativoInstitutoPersona
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
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecCargoTipo
     */
    private $ttecCargoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecCargoDesignacionTipo
     */
    private $ttecCargoDesignacionTipo;


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
     * @return TtecAdministrativoInstitutoPersona
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
     * @return TtecAdministrativoInstitutoPersona
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
     * @return TtecAdministrativoInstitutoPersona
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
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return TtecAdministrativoInstitutoPersona
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
     * Set ttecCargoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecCargoTipo $ttecCargoTipo
     * @return TtecAdministrativoInstitutoPersona
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
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return TtecAdministrativoInstitutoPersona
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return TtecAdministrativoInstitutoPersona
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
     * Set ttecCargoDesignacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecCargoDesignacionTipo $ttecCargoDesignacionTipo
     * @return TtecAdministrativoInstitutoPersona
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
