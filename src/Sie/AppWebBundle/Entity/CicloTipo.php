<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CicloTipo
 */
class CicloTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $ciclo;

    /**
     * @var integer
     */
    private $acreditacionId;

    /**
     * @var boolean
     */
    private $vigente;


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
     * Set ciclo
     *
     * @param string $ciclo
     * @return CicloTipo
     */
    public function setCiclo($ciclo)
    {
        $this->ciclo = $ciclo;

        return $this;
    }

    /**
     * Get ciclo
     *
     * @return string 
     */
    public function getCiclo()
    {
        return $this->ciclo;
    }

    /**
     * Set acreditacionId
     *
     * @param integer $acreditacionId
     * @return CicloTipo
     */
    public function setAcreditacionId($acreditacionId)
    {
        $this->acreditacionId = $acreditacionId;

        return $this;
    }

    /**
     * Get acreditacionId
     *
     * @return integer 
     */
    public function getAcreditacionId()
    {
        return $this->acreditacionId;
    }

    /**
     * Set vigente
     *
     * @param boolean $vigente
     * @return CicloTipo
     */
    public function setVigente($vigente)
    {
        $this->vigente = $vigente;

        return $this;
    }

    /**
     * Get vigente
     *
     * @return boolean 
     */
    public function getVigente()
    {
        return $this->vigente;
    }
}
