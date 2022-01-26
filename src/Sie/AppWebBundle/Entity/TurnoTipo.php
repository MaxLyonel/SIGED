<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TurnoTipo
 */
class TurnoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $turno;

    /**
     * @var string
     */
    private $abrv;

    public function __toString() {
        return $this->turno;
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
     * Set turno
     *
     * @param string $turno
     * @return TurnoTipo
     */
    public function setTurno($turno)
    {
        $this->turno = $turno;

        return $this;
    }

    /**
     * Get turno
     *
     * @return string 
     */
    public function getTurno()
    {
        return $this->turno;
    }

    /**
     * Set abrv
     *
     * @param string $abrv
     * @return TurnoTipo
     */
    public function setAbrv($abrv)
    {
        $this->abrv = $abrv;

        return $this;
    }

    /**
     * Get abrv
     *
     * @return string 
     */
    public function getAbrv()
    {
        return $this->abrv;
    }
}
