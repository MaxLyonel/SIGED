<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstTecSedeTipo
 */
class EstTecSedeTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $sedeTipo;


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
     * Set sedeTipo
     *
     * @param string $sedeTipo
     * @return EstTecSedeTipo
     */
    public function setSedeTipo($sedeTipo)
    {
        $this->sedeTipo = $sedeTipo;
    
        return $this;
    }

    /**
     * Get sedeTipo
     *
     * @return string 
     */
    public function getSedeTipo()
    {
        return $this->sedeTipo;
    }
}
