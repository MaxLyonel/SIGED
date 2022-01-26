<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TramiteDetalleDerivacion
 */
class TramiteDetalleDerivacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaRecibe;

    /**
     * @var \DateTime
     */
    private $fechaEnvia;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\TramiteDetalle
     */
    private $tramiteDetalleRecibe;

    /**
     * @var \Sie\AppWebBundle\Entity\TramiteDetalle
     */
    private $tramiteDetalleEnvia;


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
     * Set fechaRecibe
     *
     * @param \DateTime $fechaRecibe
     * @return TramiteDetalleDerivacion
     */
    public function setFechaRecibe($fechaRecibe)
    {
        $this->fechaRecibe = $fechaRecibe;
    
        return $this;
    }

    /**
     * Get fechaRecibe
     *
     * @return \DateTime 
     */
    public function getFechaRecibe()
    {
        return $this->fechaRecibe;
    }

    /**
     * Set fechaEnvia
     *
     * @param \DateTime $fechaEnvia
     * @return TramiteDetalleDerivacion
     */
    public function setFechaEnvia($fechaEnvia)
    {
        $this->fechaEnvia = $fechaEnvia;
    
        return $this;
    }

    /**
     * Get fechaEnvia
     *
     * @return \DateTime 
     */
    public function getFechaEnvia()
    {
        return $this->fechaEnvia;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return TramiteDetalleDerivacion
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
     * Set tramiteDetalleRecibe
     *
     * @param \Sie\AppWebBundle\Entity\TramiteDetalle $tramiteDetalleRecibe
     * @return TramiteDetalleDerivacion
     */
    public function setTramiteDetalleRecibe(\Sie\AppWebBundle\Entity\TramiteDetalle $tramiteDetalleRecibe = null)
    {
        $this->tramiteDetalleRecibe = $tramiteDetalleRecibe;
    
        return $this;
    }

    /**
     * Get tramiteDetalleRecibe
     *
     * @return \Sie\AppWebBundle\Entity\TramiteDetalle 
     */
    public function getTramiteDetalleRecibe()
    {
        return $this->tramiteDetalleRecibe;
    }

    /**
     * Set tramiteDetalleEnvia
     *
     * @param \Sie\AppWebBundle\Entity\TramiteDetalle $tramiteDetalleEnvia
     * @return TramiteDetalleDerivacion
     */
    public function setTramiteDetalleEnvia(\Sie\AppWebBundle\Entity\TramiteDetalle $tramiteDetalleEnvia = null)
    {
        $this->tramiteDetalleEnvia = $tramiteDetalleEnvia;
    
        return $this;
    }

    /**
     * Get tramiteDetalleEnvia
     *
     * @return \Sie\AppWebBundle\Entity\TramiteDetalle 
     */
    public function getTramiteDetalleEnvia()
    {
        return $this->tramiteDetalleEnvia;
    }
}
