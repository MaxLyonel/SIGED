<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeIdioma
 */
class RudeIdioma
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $observaciones;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\AppWebBundle\Entity\HablaTipo
     */
    private $hablaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\IdiomaTipo
     */
    private $idiomaTipo;


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
     * Set observaciones
     *
     * @param string $observaciones
     * @return RudeIdioma
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;
    
        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RudeIdioma
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
     * @return RudeIdioma
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

    /**
     * Set rude
     *
     * @param \Sie\AppWebBundle\Entity\Rude $rude
     * @return RudeIdioma
     */
    public function setRude(\Sie\AppWebBundle\Entity\Rude $rude = null)
    {
        $this->rude = $rude;
    
        return $this;
    }

    /**
     * Get rude
     *
     * @return \Sie\AppWebBundle\Entity\Rude 
     */
    public function getRude()
    {
        return $this->rude;
    }

    /**
     * Set hablaTipo
     *
     * @param \Sie\AppWebBundle\Entity\HablaTipo $hablaTipo
     * @return RudeIdioma
     */
    public function setHablaTipo(\Sie\AppWebBundle\Entity\HablaTipo $hablaTipo = null)
    {
        $this->hablaTipo = $hablaTipo;
    
        return $this;
    }

    /**
     * Get hablaTipo
     *
     * @return \Sie\AppWebBundle\Entity\HablaTipo 
     */
    public function getHablaTipo()
    {
        return $this->hablaTipo;
    }

    /**
     * Set idiomaTipo
     *
     * @param \Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo
     * @return RudeIdioma
     */
    public function setIdiomaTipo(\Sie\AppWebBundle\Entity\IdiomaTipo $idiomaTipo = null)
    {
        $this->idiomaTipo = $idiomaTipo;
    
        return $this;
    }

    /**
     * Get idiomaTipo
     *
     * @return \Sie\AppWebBundle\Entity\IdiomaTipo 
     */
    public function getIdiomaTipo()
    {
        return $this->idiomaTipo;
    }
}
