<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AsignaturaNivelTipo
 */
class AsignaturaNivelTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $asignaturaNivel;


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
     * Set asignaturaNivel
     *
     * @param string $asignaturaNivel
     * @return AsignaturaNivelTipo
     */
    public function setAsignaturaNivel($asignaturaNivel)
    {
        $this->asignaturaNivel = $asignaturaNivel;
    
        return $this;
    }

    /**
     * Get asignaturaNivel
     *
     * @return string 
     */
    public function getAsignaturaNivel()
    {
        return $this->asignaturaNivel;
    }
}
