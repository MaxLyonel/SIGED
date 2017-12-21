<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecCargoDesignacionTipo
 */
class TtecCargoDesignacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $formaDesignacion;

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
     * Set formaDesignacion
     *
     * @param string $formaDesignacion
     * @return TtecCargoDesignacionTipo
     */
    public function setFormaDesignacion($formaDesignacion)
    {
        $this->formaDesignacion = $formaDesignacion;
    
        return $this;
    }

    /**
     * Get formaDesignacion
     *
     * @return string 
     */
    public function getFormaDesignacion()
    {
        return $this->formaDesignacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return TtecCargoDesignacionTipo
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
