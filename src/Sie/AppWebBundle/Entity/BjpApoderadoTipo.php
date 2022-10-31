<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BjpApoderadoTipo
 */
class BjpApoderadoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $apoderado;


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
     * Set apoderado
     *
     * @param string $apoderado
     * @return BjpApoderadoTipo
     */
    public function setApoderado($apoderado)
    {
        $this->apoderado = $apoderado;
    
        return $this;
    }

    /**
     * Get apoderado
     *
     * @return string 
     */
    public function getApoderado()
    {
        return $this->apoderado;
    }
}
