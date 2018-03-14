<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LugarReclusionTipo
 */
class LugarReclusionTipo
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
     * @return LugarReclusionTipo
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
    /**
     * @var string
     */
    private $lugarReclusion;


    /**
     * Set lugarReclusion
     *
     * @param string $lugarReclusion
     * @return LugarReclusionTipo
     */
    public function setLugarReclusion($lugarReclusion)
    {
        $this->lugarReclusion = $lugarReclusion;
    
        return $this;
    }

    /**
     * Get lugarReclusion
     *
     * @return string 
     */
    public function getLugarReclusion()
    {
        return $this->lugarReclusion;
    }
}
