<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AcreditacionnivelTipo
 */
class AcreditacionnivelTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $acreditacionnivel;

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
     * Set acreditacionnivel
     *
     * @param string $acreditacionnivel
     * @return AcreditacionnivelTipo
     */
    public function setAcreditacionnivel($acreditacionnivel)
    {
        $this->acreditacionnivel = $acreditacionnivel;

        return $this;
    }

    /**
     * Get acreditacionnivel
     *
     * @return string 
     */
    public function getAcreditacionnivel()
    {
        return $this->acreditacionnivel;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return AcreditacionnivelTipo
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
