<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObservacionExtranjeroTipo
 */
class ObservacionExtranjeroTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $observacionExtranjero;


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
     * Set observacionExtranjero
     *
     * @param string $observacionExtranjero
     * @return ObservacionExtranjeroTipo
     */
    public function setObservacionExtranjero($observacionExtranjero)
    {
        $this->observacionExtranjero = $observacionExtranjero;
    
        return $this;
    }

    /**
     * Get observacionExtranjero
     *
     * @return string 
     */
    public function getObservacionExtranjero()
    {
        return $this->observacionExtranjero;
    }
}
