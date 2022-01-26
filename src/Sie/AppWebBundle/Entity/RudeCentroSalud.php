<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeCentroSalud
 */
class RudeCentroSalud
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
     * @var \Sie\AppWebBundle\Entity\CentroSaludTipo
     */
    private $centroSaludTipo;


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
     * @return RudeCentroSalud
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
     * @return RudeCentroSalud
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
     * @return RudeCentroSalud
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
     * Set centroSaludTipo
     *
     * @param \Sie\AppWebBundle\Entity\CentroSaludTipo $centroSaludTipo
     * @return RudeCentroSalud
     */
    public function setCentroSaludTipo(\Sie\AppWebBundle\Entity\CentroSaludTipo $centroSaludTipo = null)
    {
        $this->centroSaludTipo = $centroSaludTipo;
    
        return $this;
    }

    /**
     * Get centroSaludTipo
     *
     * @return \Sie\AppWebBundle\Entity\CentroSaludTipo 
     */
    public function getCentroSaludTipo()
    {
        return $this->centroSaludTipo;
    }
}
