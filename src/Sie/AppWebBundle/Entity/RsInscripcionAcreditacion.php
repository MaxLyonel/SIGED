<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RsInscripcionAcreditacion
 */
class RsInscripcionAcreditacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $rsInscripcionId;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Usuario
     */
    private $usuario;

    /**
     * @var \Sie\AppWebBundle\Entity\EstadomatriculaTipo
     */
    private $estadomatriculaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorAcreditacionTipo
     */
    private $superiorAcreditacionTipo;


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
     * Set rsInscripcionId
     *
     * @param integer $rsInscripcionId
     * @return RsInscripcionAcreditacion
     */
    public function setRsInscripcionId($rsInscripcionId)
    {
        $this->rsInscripcionId = $rsInscripcionId;
    
        return $this;
    }

    /**
     * Get rsInscripcionId
     *
     * @return integer 
     */
    public function getRsInscripcionId()
    {
        return $this->rsInscripcionId;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return RsInscripcionAcreditacion
     */
    public function setObservacion($observacion)
    {
        $this->observacion = $observacion;
    
        return $this;
    }

    /**
     * Get observacion
     *
     * @return string 
     */
    public function getObservacion()
    {
        return $this->observacion;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return RsInscripcionAcreditacion
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
     * @return RsInscripcionAcreditacion
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
     * Set usuario
     *
     * @param \Sie\AppWebBundle\Entity\Usuario $usuario
     * @return RsInscripcionAcreditacion
     */
    public function setUsuario(\Sie\AppWebBundle\Entity\Usuario $usuario = null)
    {
        $this->usuario = $usuario;
    
        return $this;
    }

    /**
     * Get usuario
     *
     * @return \Sie\AppWebBundle\Entity\Usuario 
     */
    public function getUsuario()
    {
        return $this->usuario;
    }

    /**
     * Set estadomatriculaTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipo
     * @return RsInscripcionAcreditacion
     */
    public function setEstadomatriculaTipo(\Sie\AppWebBundle\Entity\EstadomatriculaTipo $estadomatriculaTipo = null)
    {
        $this->estadomatriculaTipo = $estadomatriculaTipo;
    
        return $this;
    }

    /**
     * Get estadomatriculaTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstadomatriculaTipo 
     */
    public function getEstadomatriculaTipo()
    {
        return $this->estadomatriculaTipo;
    }

    /**
     * Set superiorAcreditacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorAcreditacionTipo $superiorAcreditacionTipo
     * @return RsInscripcionAcreditacion
     */
    public function setSuperiorAcreditacionTipo(\Sie\AppWebBundle\Entity\SuperiorAcreditacionTipo $superiorAcreditacionTipo = null)
    {
        $this->superiorAcreditacionTipo = $superiorAcreditacionTipo;
    
        return $this;
    }

    /**
     * Get superiorAcreditacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorAcreditacionTipo 
     */
    public function getSuperiorAcreditacionTipo()
    {
        return $this->superiorAcreditacionTipo;
    }
}
