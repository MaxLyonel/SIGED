<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivCtrResponsable
 */
class UnivCtrResponsable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $responsableHabilitacion;

    /**
     * @var string
     */
    private $cargoHabilitacion;

    /**
     * @var string
     */
    private $responsableLlenado;

    /**
     * @var string
     */
    private $cargoLlenado;

    /**
     * @var string
     */
    private $sedeId;


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
     * Set responsableHabilitacion
     *
     * @param string $responsableHabilitacion
     * @return UnivCtrResponsable
     */
    public function setResponsableHabilitacion($responsableHabilitacion)
    {
        $this->responsableHabilitacion = $responsableHabilitacion;
    
        return $this;
    }

    /**
     * Get responsableHabilitacion
     *
     * @return string 
     */
    public function getResponsableHabilitacion()
    {
        return $this->responsableHabilitacion;
    }

    /**
     * Set cargoHabilitacion
     *
     * @param string $cargoHabilitacion
     * @return UnivCtrResponsable
     */
    public function setCargoHabilitacion($cargoHabilitacion)
    {
        $this->cargoHabilitacion = $cargoHabilitacion;
    
        return $this;
    }

    /**
     * Get cargoHabilitacion
     *
     * @return string 
     */
    public function getCargoHabilitacion()
    {
        return $this->cargoHabilitacion;
    }

    /**
     * Set responsableLlenado
     *
     * @param string $responsableLlenado
     * @return UnivCtrResponsable
     */
    public function setResponsableLlenado($responsableLlenado)
    {
        $this->responsableLlenado = $responsableLlenado;
    
        return $this;
    }

    /**
     * Get responsableLlenado
     *
     * @return string 
     */
    public function getResponsableLlenado()
    {
        return $this->responsableLlenado;
    }

    /**
     * Set cargoLlenado
     *
     * @param string $cargoLlenado
     * @return UnivCtrResponsable
     */
    public function setCargoLlenado($cargoLlenado)
    {
        $this->cargoLlenado = $cargoLlenado;
    
        return $this;
    }

    /**
     * Get cargoLlenado
     *
     * @return string 
     */
    public function getCargoLlenado()
    {
        return $this->cargoLlenado;
    }

    /**
     * Set sedeId
     *
     * @param string $sedeId
     * @return UnivCtrResponsable
     */
    public function setSedeId($sedeId)
    {
        $this->sedeId = $sedeId;
    
        return $this;
    }

    /**
     * Get sedeId
     *
     * @return string 
     */
    public function getSedeId()
    {
        return $this->sedeId;
    }
}
