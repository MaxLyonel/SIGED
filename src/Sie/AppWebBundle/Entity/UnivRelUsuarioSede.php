<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivRelUsuarioSede
 */
class UnivRelUsuarioSede
{
    /**
     * @var string
     */
    private $usuarioId;

    /**
     * @var string
     */
    private $sedeId;


    /**
     * Set usuarioId
     *
     * @param string $usuarioId
     * @return UnivRelUsuarioSede
     */
    public function setUsuarioId($usuarioId)
    {
        $this->usuarioId = $usuarioId;
    
        return $this;
    }

    /**
     * Get usuarioId
     *
     * @return string 
     */
    public function getUsuarioId()
    {
        return $this->usuarioId;
    }

    /**
     * Set sedeId
     *
     * @param string $sedeId
     * @return UnivRelUsuarioSede
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
