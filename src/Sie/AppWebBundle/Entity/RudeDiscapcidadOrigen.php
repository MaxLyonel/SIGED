<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeDiscapcidadOrigen
 */
class RudeDiscapcidadOrigen
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\EspecialBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\EspecialBundle\Entity\DiscapacidadOrigenTipo
     */
    private $discapacidadOrigenTipo;


    /**
     * Set id
     *
     * @param integer $id
     * @return RudeDiscapcidadOrigen
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RudeDiscapcidadOrigen
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
     * @return RudeDiscapcidadOrigen
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
     * @return RudeDiscapcidadOrigen
     */
    public function setRude(\Sie\AppWebBundle\Entity\Rude $rude = null)
    {
        $this->rude = $rude;
    
        return $this;
    }

    /**
     * Get rude
     *
     * @return \Sie\EspecialBundle\Entity\Rude 
     */
    public function getRude()
    {
        return $this->rude;
    }

    /**
     * Set discapacidadOrigenTipo
     *
     * @param \Sie\EspecialBundle\Entity\DiscapacidadOrigenTipo $discapacidadOrigenTipo
     * @return RudeDiscapcidadOrigen
     */
    public function setDiscapacidadOrigenTipo(\Sie\AppWebBundle\Entity\DiscapacidadOrigenTipo $discapacidadOrigenTipo = null)
    {
        $this->discapacidadOrigenTipo = $discapacidadOrigenTipo;
    
        return $this;
    }

    /**
     * Get discapacidadOrigenTipo
     *
     * @return \Sie\EspecialBundle\Entity\DiscapacidadOrigenTipo 
     */
    public function getDiscapacidadOrigenTipo()
    {
        return $this->discapacidadOrigenTipo;
    }
}
