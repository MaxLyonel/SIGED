<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TramiteEstado
 */
class TramiteEstado
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $tramiteEstado;

    /**
     * @var string
     */
    private $obs;


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
     * Set tramiteEstado
     *
     * @param string $tramiteEstado
     * @return TramiteEstado
     */
    public function setTramiteEstado($tramiteEstado)
    {
        $this->tramiteEstado = $tramiteEstado;
    
        return $this;
    }

    /**
     * Get tramiteEstado
     *
     * @return string 
     */
    public function getTramiteEstado()
    {
        return $this->tramiteEstado;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return TramiteEstado
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
}
