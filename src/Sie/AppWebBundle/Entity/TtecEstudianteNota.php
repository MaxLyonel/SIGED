<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecEstudianteNota
 */
class TtecEstudianteNota
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $notaCuantitativa;

    /**
     * @var string
     */
    private $notaCualitativa;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecEstudianteInscripcion
     */
    private $ttecEstudianteInscripcion;


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
     * Set notaCuantitativa
     *
     * @param integer $notaCuantitativa
     * @return TtecEstudianteNota
     */
    public function setNotaCuantitativa($notaCuantitativa)
    {
        $this->notaCuantitativa = $notaCuantitativa;
    
        return $this;
    }

    /**
     * Get notaCuantitativa
     *
     * @return integer 
     */
    public function getNotaCuantitativa()
    {
        return $this->notaCuantitativa;
    }

    /**
     * Set notaCualitativa
     *
     * @param string $notaCualitativa
     * @return TtecEstudianteNota
     */
    public function setNotaCualitativa($notaCualitativa)
    {
        $this->notaCualitativa = $notaCualitativa;
    
        return $this;
    }

    /**
     * Get notaCualitativa
     *
     * @return string 
     */
    public function getNotaCualitativa()
    {
        return $this->notaCualitativa;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return TtecEstudianteNota
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
     * @return TtecEstudianteNota
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
     * Set ttecEstudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\TtecEstudianteInscripcion $ttecEstudianteInscripcion
     * @return TtecEstudianteNota
     */
    public function setTtecEstudianteInscripcion(\Sie\AppWebBundle\Entity\TtecEstudianteInscripcion $ttecEstudianteInscripcion = null)
    {
        $this->ttecEstudianteInscripcion = $ttecEstudianteInscripcion;
    
        return $this;
    }

    /**
     * Get ttecEstudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\TtecEstudianteInscripcion 
     */
    public function getTtecEstudianteInscripcion()
    {
        return $this->ttecEstudianteInscripcion;
    }
}
