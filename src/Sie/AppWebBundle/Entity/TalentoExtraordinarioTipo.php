<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TalentoExtraordinarioTipo
 */
class TalentoExtraordinarioTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $talentoextraordinario;

    /**
     * @var string
     */
    private $obs;


    /**
     * Set id
     *
     * @param integer $id
     * @return TalentoExtraordinarioTipo
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }

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
     * Set talentoextraordinario
     *
     * @param string $talentoextraordinario
     * @return TalentoExtraordinarioTipo
     */
    public function setTalentoextraordinario($talentoextraordinario)
    {
        $this->talentoextraordinario = $talentoextraordinario;
    
        return $this;
    }

    /**
     * Get talentoextraordinario
     *
     * @return string 
     */
    public function getTalentoextraordinario()
    {
        return $this->talentoextraordinario;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return TalentoExtraordinarioTipo
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
