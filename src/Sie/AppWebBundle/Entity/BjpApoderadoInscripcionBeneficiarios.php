<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BjpApoderadoInscripcionBeneficiarios
 */
class BjpApoderadoInscripcionBeneficiarios
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
     * @var integer
     */
    private $esValidado;

    /**
     * @var \DateTime
     */
    private $fechaRegistro;

    /**
     * @var \DateTime
     */
    private $fechaModificacion;

    /**
     * @var \Sie\AppWebBundle\Entity\Persona
     */
    private $persona;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\BjpApoderadoTipo
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
     * @return BjpApoderadoInscripcionBeneficiarios
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
     * Set esValidado
     *
     * @param integer $esValidado
     * @return BjpApoderadoInscripcionBeneficiarios
     */
    public function setEsValidado($esValidado)
    {
        $this->esValidado = $esValidado;
    
        return $this;
    }

    /**
     * Get esValidado
     *
     * @return integer 
     */
    public function getEsValidado()
    {
        return $this->esValidado;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return BjpApoderadoInscripcionBeneficiarios
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
     * @return BjpApoderadoInscripcionBeneficiarios
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
     * Set persona
     *
     * @param \Sie\AppWebBundle\Entity\Persona $persona
     * @return BjpApoderadoInscripcionBeneficiarios
     */
    public function setPersona(\Sie\AppWebBundle\Entity\Persona $persona = null)
    {
        $this->persona = $persona;
    
        return $this;
    }

    /**
     * Get persona
     *
     * @return \Sie\AppWebBundle\Entity\Persona 
     */
    public function getPersona()
    {
        return $this->persona;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return BjpApoderadoInscripcionBeneficiarios
     */
    public function setEstudianteInscripcion(\Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion = null)
    {
        $this->estudianteInscripcion = $estudianteInscripcion;
    
        return $this;
    }

    /**
     * Get estudianteInscripcion
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteInscripcion 
     */
    public function getEstudianteInscripcion()
    {
        return $this->estudianteInscripcion;
    }

    /**
     * Set apoderadoTipo
     *
     * @param \Sie\AppWebBundle\Entity\BjpApoderadoTipo $apoderadoTipo
     * @return BjpApoderadoInscripcionBeneficiarios
     */
    public function setApoderadoTipo(\Sie\AppWebBundle\Entity\BjpApoderadoTipo $apoderadoTipo = null)
    {
        $this->apoderadoTipo = $apoderadoTipo;
    
        return $this;
    }

    /**
     * Get apoderadoTipo
     *
     * @return \Sie\AppWebBundle\Entity\BjpApoderadoTipo 
     */
    public function getApoderadoTipo()
    {
        return $this->apoderadoTipo;
    }
}
