<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeRecibioPago
 */
class RudeRecibioPago
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $respuestaPago;

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
     * @var \Sie\AppWebBundle\Entity\PagoTipo
     */
    private $pagoTipo;


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
     * Set respuestaPago
     *
     * @param string $respuestaPago
     * @return RudeRecibioPago
     */
    public function setRespuestaPago($respuestaPago)
    {
        $this->respuestaPago = $respuestaPago;
    
        return $this;
    }

    /**
     * Get respuestaPago
     *
     * @return string 
     */
    public function getRespuestaPago()
    {
        return $this->respuestaPago;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RudeRecibioPago
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
     * @return RudeRecibioPago
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
     * @return RudeRecibioPago
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
     * Set pagoTipo
     *
     * @param \Sie\AppWebBundle\Entity\PagoTipo $pagoTipo
     * @return RudeRecibioPago
     */
    public function setPagoTipo(\Sie\AppWebBundle\Entity\PagoTipo $pagoTipo = null)
    {
        $this->pagoTipo = $pagoTipo;
    
        return $this;
    }

    /**
     * Get pagoTipo
     *
     * @return \Sie\AppWebBundle\Entity\PagoTipo 
     */
    public function getPagoTipo()
    {
        return $this->pagoTipo;
    }
}
