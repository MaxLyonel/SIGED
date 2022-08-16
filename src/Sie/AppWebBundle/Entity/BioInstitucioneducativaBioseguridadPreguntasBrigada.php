<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BioInstitucioneducativaBioseguridadPreguntasBrigada
 */
class BioInstitucioneducativaBioseguridadPreguntasBrigada
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $respSiNo;

    /**
     * @var integer
     */
    private $respVarios;

    /**
     * @var integer
     */
    private $cantidad;

    /**
     * @var \Sie\AppWebBundle\Entity\BioCuestionarioBrigadaTipo
     */
    private $bioCuestionarioBrigadaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\BioInstitucioneducativaBioseguridadPreguntas
     */
    private $bioInstitucioneducativaBioseguridadPreguntas;


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
     * Set respSiNo
     *
     * @param boolean $respSiNo
     * @return BioInstitucioneducativaBioseguridadPreguntasBrigada
     */
    public function setRespSiNo($respSiNo)
    {
        $this->respSiNo = $respSiNo;
    
        return $this;
    }

    /**
     * Get respSiNo
     *
     * @return boolean 
     */
    public function getRespSiNo()
    {
        return $this->respSiNo;
    }

    /**
     * Set respVarios
     *
     * @param integer $respVarios
     * @return BioInstitucioneducativaBioseguridadPreguntasBrigada
     */
    public function setRespVarios($respVarios)
    {
        $this->respVarios = $respVarios;
    
        return $this;
    }

    /**
     * Get respVarios
     *
     * @return integer 
     */
    public function getRespVarios()
    {
        return $this->respVarios;
    }

    /**
     * Set cantidad
     *
     * @param integer $cantidad
     * @return BioInstitucioneducativaBioseguridadPreguntasBrigada
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;
    
        return $this;
    }

    /**
     * Get cantidad
     *
     * @return integer 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set bioCuestionarioBrigadaTipo
     *
     * @param \Sie\AppWebBundle\Entity\BioCuestionarioBrigadaTipo $bioCuestionarioBrigadaTipo
     * @return BioInstitucioneducativaBioseguridadPreguntasBrigada
     */
    public function setBioCuestionarioBrigadaTipo(\Sie\AppWebBundle\Entity\BioCuestionarioBrigadaTipo $bioCuestionarioBrigadaTipo = null)
    {
        $this->bioCuestionarioBrigadaTipo = $bioCuestionarioBrigadaTipo;
    
        return $this;
    }

    /**
     * Get bioCuestionarioBrigadaTipo
     *
     * @return \Sie\AppWebBundle\Entity\BioCuestionarioBrigadaTipo 
     */
    public function getBioCuestionarioBrigadaTipo()
    {
        return $this->bioCuestionarioBrigadaTipo;
    }

    /**
     * Set bioInstitucioneducativaBioseguridadPreguntas
     *
     * @param \Sie\AppWebBundle\Entity\BioInstitucioneducativaBioseguridadPreguntas $bioInstitucioneducativaBioseguridadPreguntas
     * @return BioInstitucioneducativaBioseguridadPreguntasBrigada
     */
    public function setBioInstitucioneducativaBioseguridadPreguntas(\Sie\AppWebBundle\Entity\BioInstitucioneducativaBioseguridadPreguntas $bioInstitucioneducativaBioseguridadPreguntas = null)
    {
        $this->bioInstitucioneducativaBioseguridadPreguntas = $bioInstitucioneducativaBioseguridadPreguntas;
    
        return $this;
    }

    /**
     * Get bioInstitucioneducativaBioseguridadPreguntas
     *
     * @return \Sie\AppWebBundle\Entity\BioInstitucioneducativaBioseguridadPreguntas 
     */
    public function getBioInstitucioneducativaBioseguridadPreguntas()
    {
        return $this->bioInstitucioneducativaBioseguridadPreguntas;
    }
}
