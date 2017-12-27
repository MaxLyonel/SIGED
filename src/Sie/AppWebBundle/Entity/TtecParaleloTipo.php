<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecParaleloTipo
 */
class TtecParaleloTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $paralelo;

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
     * Set paralelo
     *
     * @param string $paralelo
     * @return TtecParaleloTipo
     */
    public function setParalelo($paralelo)
    {
        $this->paralelo = $paralelo;
    
        return $this;
    }

    /**
     * Get paralelo
     *
     * @return string 
     */
    public function getParalelo()
    {
        return $this->paralelo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return TtecParaleloTipo
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
