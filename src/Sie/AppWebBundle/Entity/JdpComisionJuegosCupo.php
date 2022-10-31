<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpComisionJuegosCupo
 */
class JdpComisionJuegosCupo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $cupo;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpComisionTipo
     */
    private $comisionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpPruebaTipo
     */
    private $pruebaTipo;


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
     * Set cupo
     *
     * @param integer $cupo
     * @return JdpComisionJuegosCupo
     */
    public function setCupo($cupo)
    {
        $this->cupo = $cupo;
    
        return $this;
    }

    /**
     * Get cupo
     *
     * @return integer 
     */
    public function getCupo()
    {
        return $this->cupo;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return JdpComisionJuegosCupo
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

    /**
     * Set comisionTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpComisionTipo $comisionTipo
     * @return JdpComisionJuegosCupo
     */
    public function setComisionTipo(\Sie\AppWebBundle\Entity\JdpComisionTipo $comisionTipo = null)
    {
        $this->comisionTipo = $comisionTipo;
    
        return $this;
    }

    /**
     * Get comisionTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpComisionTipo 
     */
    public function getComisionTipo()
    {
        return $this->comisionTipo;
    }

    /**
     * Set pruebaTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpPruebaTipo $pruebaTipo
     * @return JdpComisionJuegosCupo
     */
    public function setPruebaTipo(\Sie\AppWebBundle\Entity\JdpPruebaTipo $pruebaTipo = null)
    {
        $this->pruebaTipo = $pruebaTipo;
    
        return $this;
    }

    /**
     * Get pruebaTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpPruebaTipo 
     */
    public function getPruebaTipo()
    {
        return $this->pruebaTipo;
    }
}
