<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InfraestructuraJuridiccionGeografica
 */
class InfraestructuraJuridiccionGeografica
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaoperativo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fecharegistro;

    /**
     * @var integer
     */
    private $infraestructura;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\JurisdiccionGeografica
     */
    private $juridiccionGeografica;

    public function __toString(){
        return $this->infraestructura;
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
     * Set fechaoperativo
     *
     * @param \DateTime $fechaoperativo
     * @return InfraestructuraJuridiccionGeografica
     */
    public function setFechaoperativo($fechaoperativo)
    {
        $this->fechaoperativo = $fechaoperativo;
    
        return $this;
    }

    /**
     * Get fechaoperativo
     *
     * @return \DateTime 
     */
    public function getFechaoperativo()
    {
        return $this->fechaoperativo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return InfraestructuraJuridiccionGeografica
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
     * Set fecharegistro
     *
     * @param \DateTime $fecharegistro
     * @return InfraestructuraJuridiccionGeografica
     */
    public function setFecharegistro($fecharegistro)
    {
        $this->fecharegistro = $fecharegistro;
    
        return $this;
    }

    /**
     * Get fecharegistro
     *
     * @return \DateTime 
     */
    public function getFecharegistro()
    {
        return $this->fecharegistro;
    }

    /**
     * Set infraestructura
     *
     * @param integer $infraestructura
     * @return InfraestructuraJuridiccionGeografica
     */
    public function setInfraestructura($infraestructura)
    {
        $this->infraestructura = $infraestructura;
    
        return $this;
    }

    /**
     * Get infraestructura
     *
     * @return integer 
     */
    public function getInfraestructura()
    {
        return $this->infraestructura;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return InfraestructuraJuridiccionGeografica
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
     * Set juridiccionGeografica
     *
     * @param \Sie\AppWebBundle\Entity\JurisdiccionGeografica $juridiccionGeografica
     * @return InfraestructuraJuridiccionGeografica
     */
    public function setJuridiccionGeografica(\Sie\AppWebBundle\Entity\JurisdiccionGeografica $juridiccionGeografica = null)
    {
        $this->juridiccionGeografica = $juridiccionGeografica;
    
        return $this;
    }

    /**
     * Get juridiccionGeografica
     *
     * @return \Sie\AppWebBundle\Entity\JurisdiccionGeografica 
     */
    public function getJuridiccionGeografica()
    {
        return $this->juridiccionGeografica;
    }
}
