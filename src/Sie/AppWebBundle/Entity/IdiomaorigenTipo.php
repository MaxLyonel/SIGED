<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IdiomaorigenTipo
 */
class IdiomaorigenTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $idiomaorigen;


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
     * Set idiomaorigen
     *
     * @param string $idiomaorigen
     * @return IdiomaorigenTipo
     */
    public function setIdiomaorigen($idiomaorigen)
    {
        $this->idiomaorigen = $idiomaorigen;
    
        return $this;
    }

    /**
     * Get idiomaorigen
     *
     * @return string 
     */
    public function getIdiomaorigen()
    {
        return $this->idiomaorigen;
    }
}
