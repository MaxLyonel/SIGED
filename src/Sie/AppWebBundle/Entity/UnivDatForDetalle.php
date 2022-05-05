<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivDatForDetalle
 */
class UnivDatForDetalle
{
    /**
     * @var string
     */
    private $habilitacionId;

    /**
     * @var string
     */
    private $envioId;

    /**
     * @var string
     */
    private $estado;

    /**
     * @var string
     */
    private $habilitado;


    /**
     * Set habilitacionId
     *
     * @param string $habilitacionId
     * @return UnivDatForDetalle
     */
    public function setHabilitacionId($habilitacionId)
    {
        $this->habilitacionId = $habilitacionId;
    
        return $this;
    }

    /**
     * Get habilitacionId
     *
     * @return string 
     */
    public function getHabilitacionId()
    {
        return $this->habilitacionId;
    }

    /**
     * Set envioId
     *
     * @param string $envioId
     * @return UnivDatForDetalle
     */
    public function setEnvioId($envioId)
    {
        $this->envioId = $envioId;
    
        return $this;
    }

    /**
     * Get envioId
     *
     * @return string 
     */
    public function getEnvioId()
    {
        return $this->envioId;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return UnivDatForDetalle
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return string 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set habilitado
     *
     * @param string $habilitado
     * @return UnivDatForDetalle
     */
    public function setHabilitado($habilitado)
    {
        $this->habilitado = $habilitado;
    
        return $this;
    }

    /**
     * Get habilitado
     *
     * @return string 
     */
    public function getHabilitado()
    {
        return $this->habilitado;
    }
}
