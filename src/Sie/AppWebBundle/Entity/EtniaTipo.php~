<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EtniaTipo
 */
class EtniaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $etnia;


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
     * Set etnia
     *
     * @param string $etnia
     * @return EtniaTipo
     */
    public function setEtnia($etnia)
    {
        $this->etnia = $etnia;

        return $this;
    }

    /**
     * Get etnia
     *
     * @return string 
     */
    public function getEtnia()
    {
        return $this->etnia;
    }
    
    public function __toString() {
        return $this->etnia;
    }
}
