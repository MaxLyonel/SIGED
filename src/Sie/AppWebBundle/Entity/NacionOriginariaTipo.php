<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NacionOriginariaTipo
 */
class NacionOriginariaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $nacionOriginaria;

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
     * Set nacionOriginaria
     *
     * @param string $nacionOriginaria
     * @return NacionOriginariaTipo
     */
    public function setNacionOriginaria($nacionOriginaria)
    {
        $this->nacionOriginaria = $nacionOriginaria;
    
        return $this;
    }

    /**
     * Get nacionOriginaria
     *
     * @return string 
     */
    public function getNacionOriginaria()
    {
        return $this->nacionOriginaria;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return NacionOriginariaTipo
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
     * @var boolean
     */
    private $esVigente;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;


    /**
     * Set esVigente
     *
     * @param boolean $esVigente
     * @return NacionOriginariaTipo
     */
    public function setEsVigente($esVigente)
    {
        $this->esVigente = $esVigente;
    
        return $this;
    }

    /**
     * Get esVigente
     *
     * @return boolean 
     */
    public function getEsVigente()
    {
        return $this->esVigente;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return NacionOriginariaTipo
     */
    public function setFechaRegistro($fechaRegistro)
    {
        $this->fechaRegistro = $fechaRegistro;
    
        return $this;
    }

    /**
     * Get fechaRegistro
     *
     * @return \DateTime 
     */
    public function getFechaRegistro()
    {
        return $this->fechaRegistro;
    }

    /**
     * Set fechaModificacion
     *
     * @param \DateTime $fechaModificacion
     * @return NacionOriginariaTipo
     */
    public function setFechaModificacion($fechaModificacion)
    {
        $this->fechaModificacion = $fechaModificacion;
    
        return $this;
    }

    /**
     * Get fechaModificacion
     *
     * @return \DateTime 
     */
    public function getFechaModificacion()
    {
        return $this->fechaModificacion;
    }
}
