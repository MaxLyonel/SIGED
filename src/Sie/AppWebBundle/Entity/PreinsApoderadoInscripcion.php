<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PreinsApoderadoInscripcion
 */
class PreinsApoderadoInscripcion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\PreinsPersona
     */
    private $preinsPersona;

    /**
     * @var \Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion
     */
    private $preinsEstudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\ApoderadoTipo
     */
    private $apoderadoTipo;


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
     * Set obs
     *
     * @param string $obs
     * @return PreinsApoderadoInscripcion
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
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return PreinsApoderadoInscripcion
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
     * @return PreinsApoderadoInscripcion
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
     * Set preinsPersona
     *
     * @param \Sie\AppWebBundle\Entity\PreinsPersona $preinsPersona
     * @return PreinsApoderadoInscripcion
     */
    public function setPreinsPersona(\Sie\AppWebBundle\Entity\PreinsPersona $preinsPersona = null)
    {
        $this->preinsPersona = $preinsPersona;
    
        return $this;
    }

    /**
     * Get preinsPersona
     *
     * @return \Sie\AppWebBundle\Entity\PreinsPersona 
     */
    public function getPreinsPersona()
    {
        return $this->preinsPersona;
    }

    /**
     * Set preinsEstudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion $preinsEstudianteInscripcion
     * @return PreinsApoderadoInscripcion
     */
    public function setPreinsEstudianteInscripcion(\Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion $preinsEstudianteInscripcion = null)
    {
        $this->preinsEstudianteInscripcion = $preinsEstudianteInscripcion;
    
        return $this;
    }

    /**
     * Get preinsEstudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\PreinsEstudianteInscripcion 
     */
    public function getPreinsEstudianteInscripcion()
    {
        return $this->preinsEstudianteInscripcion;
    }

    /**
     * Set apoderadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\ApoderadoTipo $apoderadoTipo
     * @return PreinsApoderadoInscripcion
     */
    public function setApoderadoTipo(\Sie\AppWebBundle\Entity\ApoderadoTipo $apoderadoTipo = null)
    {
        $this->apoderadoTipo = $apoderadoTipo;
    
        return $this;
    }

    /**
     * Get apoderadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\ApoderadoTipo 
     */
    public function getApoderadoTipo()
    {
        return $this->apoderadoTipo;
    }
}
