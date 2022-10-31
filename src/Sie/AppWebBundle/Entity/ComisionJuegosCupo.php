<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ComisionJuegosCupo
 */
class ComisionJuegosCupo
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
     * @var \Sie\AppWebBundle\Entity\PruebaTipo
     */
    private $pruebaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\ComisionTipo
     */
    private $comisionTipo;


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
     * @return ComisionJuegosCupo
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
     * @return ComisionJuegosCupo
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
     * Set pruebaTipo
     *
     * @param \Sie\AppWebBundle\Entity\PruebaTipo $pruebaTipo
     * @return ComisionJuegosCupo
     */
    public function setPruebaTipo(\Sie\AppWebBundle\Entity\PruebaTipo $pruebaTipo = null)
    {
        $this->pruebaTipo = $pruebaTipo;
    
        return $this;
    }

    /**
     * Get pruebaTipo
     *
     * @return \Sie\AppWebBundle\Entity\PruebaTipo 
     */
    public function getPruebaTipo()
    {
        return $this->pruebaTipo;
    }

    /**
     * Set comisionTipo
     *
     * @param \Sie\AppWebBundle\Entity\ComisionTipo $comisionTipo
     * @return ComisionJuegosCupo
     */
    public function setComisionTipo(\Sie\AppWebBundle\Entity\ComisionTipo $comisionTipo = null)
    {
        $this->comisionTipo = $comisionTipo;
    
        return $this;
    }

    /**
     * Get comisionTipo
     *
     * @return \Sie\AppWebBundle\Entity\ComisionTipo 
     */
    public function getComisionTipo()
    {
        return $this->comisionTipo;
    }
}
