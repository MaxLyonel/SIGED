<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CamaraGrabacionTipo
 */
class CamaraGrabacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $medida;

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
     * Set medida
     *
     * @param string $medida
     * @return CamaraGrabacionTipo
     */
    public function setMedida($medida)
    {
        $this->medida = $medida;
    
        return $this;
    }

    /**
     * Get medida
     *
     * @return string 
     */
    public function getMedida()
    {
        return $this->medida;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return CamaraGrabacionTipo
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
