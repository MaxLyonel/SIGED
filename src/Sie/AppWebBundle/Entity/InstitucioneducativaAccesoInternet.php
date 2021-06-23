<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaAccesoInternet
 */
class InstitucioneducativaAccesoInternet
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $tieneInternet;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var boolean
     */
    private $tieneTv;

    /**
     * @var boolean
     */
    private $tieneEmergenciaSanitaria;

    /**
     * @var boolean
     */
    private $tieneBioseguridad;

    /**
     * @var string
     */
    private $planEmergenciaSanitaria;

    /**
     * @var string
     */
    private $protocoloBioseguridad;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

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
     * Set tieneInternet
     *
     * @param boolean $tieneInternet
     * @return InstitucioneducativaAccesoInternet
     */
    public function setTieneInternet($tieneInternet)
    {
        $this->tieneInternet = $tieneInternet;
    
        return $this;
    }

    /**
     * Get tieneInternet
     *
     * @return boolean 
     */
    public function getTieneInternet()
    {
        return $this->tieneInternet;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return InstitucioneducativaAccesoInternet
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return InstitucioneducativaAccesoInternet
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
     * Set tieneTv
     *
     * @param boolean $tieneTv
     * @return InstitucioneducativaAccesoInternet
     */
    public function setTieneTv($tieneTv)
    {
        $this->tieneTv = $tieneTv;
    
        return $this;
    }

    /**
     * Get tieneTv
     *
     * @return boolean 
     */
    public function getTieneTv()
    {
        return $this->tieneTv;
    }

    /**
     * Set tieneEmergenciaSanitaria
     *
     * @param boolean $tieneEmergenciaSanitaria
     * @return InstitucioneducativaAccesoInternet
     */
    public function setTieneEmergenciaSanitaria($tieneEmergenciaSanitaria)
    {
        $this->tieneEmergenciaSanitaria = $tieneEmergenciaSanitaria;
    
        return $this;
    }

    /**
     * Get tieneEmergenciaSanitaria
     *
     * @return boolean 
     */
    public function getTieneEmergenciaSanitaria()
    {
        return $this->tieneEmergenciaSanitaria;
    }

    /**
     * Set tieneBioseguridad
     *
     * @param boolean $tieneBioseguridad
     * @return InstitucioneducativaAccesoInternet
     */
    public function setTieneBioseguridad($tieneBioseguridad)
    {
        $this->tieneBioseguridad = $tieneBioseguridad;
    
        return $this;
    }

    /**
     * Get tieneBioseguridad
     *
     * @return boolean 
     */
    public function getTieneBioseguridad()
    {
        return $this->tieneBioseguridad;
    }

    /**
     * Set planEmergenciaSanitaria
     *
     * @param string $planEmergenciaSanitaria
     * @return InstitucioneducativaAccesoInternet
     */
    public function setPlanEmergenciaSanitaria($planEmergenciaSanitaria)
    {
        $this->planEmergenciaSanitaria = $planEmergenciaSanitaria;
    
        return $this;
    }

    /**
     * Get planEmergenciaSanitaria
     *
     * @return string 
     */
    public function getPlanEmergenciaSanitaria()
    {
        return $this->planEmergenciaSanitaria;
    }

    /**
     * Set protocoloBioseguridad
     *
     * @param string $protocoloBioseguridad
     * @return InstitucioneducativaAccesoInternet
     */
    public function setProtocoloBioseguridad($protocoloBioseguridad)
    {
        $this->protocoloBioseguridad = $protocoloBioseguridad;
    
        return $this;
    }

    /**
     * Get protocoloBioseguridad
     *
     * @return string 
     */
    public function getProtocoloBioseguridad()
    {
        return $this->protocoloBioseguridad;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return InstitucioneducativaAccesoInternet
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
     * @return InstitucioneducativaAccesoInternet
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
}
