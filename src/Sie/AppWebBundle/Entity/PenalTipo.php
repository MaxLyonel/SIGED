<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PenalTipo
 */
class PenalTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $penalTipo;


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
     * Set penalTipo
     *
     * @param string $penalTipo
     * @return PenalTipo
     */
    public function setPenalTipo($penalTipo)
    {
        $this->penalTipo = $penalTipo;
    
        return $this;
    }

    /**
     * Get penalTipo
     *
     * @return string 
     */
    public function getPenalTipo()
    {
        return $this->penalTipo;
    }
}
