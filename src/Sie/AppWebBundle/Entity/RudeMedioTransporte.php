<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RudeMedioTransporte
 */
class RudeMedioTransporte
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
     * @var string
     */
    private $llegaOtro;

    /**
     * @var \Sie\AppWebBundle\Entity\Rude
     */
    private $rude;

    /**
     * @var \Sie\AppWebBundle\Entity\TiempoMaximoTrayectoTipo
     */
    private $tiempoMaximoTrayectoTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\MedioTransporteTipo
     */
    private $medioTransporteTipo;


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
     * @return RudeMedioTransporte
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
     * @return RudeMedioTransporte
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
     * Set llegaOtro
     *
     * @param string $llegaOtro
     * @return RudeMedioTransporte
     */
    public function setLlegaOtro($llegaOtro)
    {
        $this->llegaOtro = $llegaOtro;
    
        return $this;
    }

    /**
     * Get llegaOtro
     *
     * @return string 
     */
    public function getLlegaOtro()
    {
        return $this->llegaOtro;
    }

    /**
     * Set rude
     *
     * @param \Sie\AppWebBundle\Entity\Rude $rude
     * @return RudeMedioTransporte
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
     * Set tiempoMaximoTrayectoTipo
     *
     * @param \Sie\AppWebBundle\Entity\TiempoMaximoTrayectoTipo $tiempoMaximoTrayectoTipo
     * @return RudeMedioTransporte
     */
    public function setTiempoMaximoTrayectoTipo(\Sie\AppWebBundle\Entity\TiempoMaximoTrayectoTipo $tiempoMaximoTrayectoTipo = null)
    {
        $this->tiempoMaximoTrayectoTipo = $tiempoMaximoTrayectoTipo;
    
        return $this;
    }

    /**
     * Get tiempoMaximoTrayectoTipo
     *
     * @return \Sie\AppWebBundle\Entity\TiempoMaximoTrayectoTipo 
     */
    public function getTiempoMaximoTrayectoTipo()
    {
        return $this->tiempoMaximoTrayectoTipo;
    }

    /**
     * Set medioTransporteTipo
     *
     * @param \Sie\AppWebBundle\Entity\MedioTransporteTipo $medioTransporteTipo
     * @return RudeMedioTransporte
     */
    public function setMedioTransporteTipo(\Sie\AppWebBundle\Entity\MedioTransporteTipo $medioTransporteTipo = null)
    {
        $this->medioTransporteTipo = $medioTransporteTipo;
    
        return $this;
    }

    /**
     * Get medioTransporteTipo
     *
     * @return \Sie\AppWebBundle\Entity\MedioTransporteTipo 
     */
    public function getMedioTransporteTipo()
    {
        return $this->medioTransporteTipo;
    }
}
