<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Penal
 */
class Penal
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $penal;

    /**
     * @var \Sie\AppWebBundle\Entity\PenalTipo
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
     * Set penal
     *
     * @param string $penal
     * @return Penal
     */
    public function setPenal($penal)
    {
        $this->penal = $penal;
    
        return $this;
    }

    /**
     * Get penal
     *
     * @return string 
     */
    public function getPenal()
    {
        return $this->penal;
    }

    /**
     * Set penalTipo
     *
     * @param \Sie\AppWebBundle\Entity\PenalTipo $penalTipo
     * @return Penal
     */
    public function setPenalTipo(\Sie\AppWebBundle\Entity\PenalTipo $penalTipo = null)
    {
        $this->penalTipo = $penalTipo;
    
        return $this;
    }

    /**
     * Get penalTipo
     *
     * @return \Sie\AppWebBundle\Entity\PenalTipo 
     */
    public function getPenalTipo()
    {
        return $this->penalTipo;
    }
}
