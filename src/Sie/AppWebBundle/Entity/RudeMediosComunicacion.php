<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeMediosComunicacion
 */
class RudeMediosComunicacion
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
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\AppWebBundle\Entity\MediosComunicacionTipo
     */
    private $mediosComunicacionTipo;


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
     * @return RudeMediosComunicacion
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
     * @return RudeMediosComunicacion
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
     * @return RudeMediosComunicacion
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
     * Set mediosComunicacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\MediosComunicacionTipo $mediosComunicacionTipo
     * @return RudeMediosComunicacion
     */
    public function setMediosComunicacionTipo(\Sie\AppWebBundle\Entity\MediosComunicacionTipo $mediosComunicacionTipo = null)
    {
        $this->mediosComunicacionTipo = $mediosComunicacionTipo;
    
        return $this;
    }

    /**
     * Get mediosComunicacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\MediosComunicacionTipo 
     */
    public function getMediosComunicacionTipo()
    {
        return $this->mediosComunicacionTipo;
    }
}
