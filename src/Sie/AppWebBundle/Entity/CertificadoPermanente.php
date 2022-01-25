<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CertificadoPermanente
 */
class CertificadoPermanente
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $estado;

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
    private $numeroCertificado;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Tramite
     */
    private $tramite;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorAcreditacionTipo
     */
    private $superiorAcreditacionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorEspecialidadTipo
     */
    private $superiorEspecialidadTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\Institucioneducativa
     */
    private $institucioneducativa;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;


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
     * Set estado
     *
     * @param integer $estado
     * @return CertificadoPermanente
     */
    public function setEstado($estado)
    {
        $this->estado = $estado;
    
        return $this;
    }

    /**
     * Get estado
     *
     * @return integer 
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * Set fechaRegistro
     *
     * @param \DateTime $fechaRegistro
     * @return CertificadoPermanente
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
     * @return CertificadoPermanente
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
     * Set numeroCertificado
     *
     * @param string $numeroCertificado
     * @return CertificadoPermanente
     */
    public function setNumeroCertificado($numeroCertificado)
    {
        $this->numeroCertificado = $numeroCertificado;
    
        return $this;
    }

    /**
     * Get numeroCertificado
     *
     * @return string 
     */
    public function getNumeroCertificado()
    {
        return $this->numeroCertificado;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return CertificadoPermanente
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
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return CertificadoPermanente
     */
    public function setGestionTipo(\Sie\AppWebBundle\Entity\GestionTipo $gestionTipo = null)
    {
        $this->gestionTipo = $gestionTipo;
    
        return $this;
    }

    /**
     * Get gestionTipo
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestionTipo()
    {
        return $this->gestionTipo;
    }

    /**
     * Set tramite
     *
     * @param \Sie\AppWebBundle\Entity\Tramite $tramite
     * @return CertificadoPermanente
     */
    public function setTramite(\Sie\AppWebBundle\Entity\Tramite $tramite = null)
    {
        $this->tramite = $tramite;
    
        return $this;
    }

    /**
     * Get tramite
     *
     * @return \Sie\AppWebBundle\Entity\Tramite 
     */
    public function getTramite()
    {
        return $this->tramite;
    }

    /**
     * Set superiorAcreditacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorAcreditacionTipo $superiorAcreditacionTipo
     * @return CertificadoPermanente
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

    /**
     * Set superiorEspecialidadTipo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorEspecialidadTipo $superiorEspecialidadTipo
     * @return CertificadoPermanente
     */
    public function setSuperiorEspecialidadTipo(\Sie\AppWebBundle\Entity\SuperiorEspecialidadTipo $superiorEspecialidadTipo = null)
    {
        $this->superiorEspecialidadTipo = $superiorEspecialidadTipo;
    
        return $this;
    }

    /**
     * Get superiorEspecialidadTipo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorEspecialidadTipo 
     */
    public function getSuperiorEspecialidadTipo()
    {
        return $this->superiorEspecialidadTipo;
    }

    /**
     * Set institucioneducativa
     *
     * @param \Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa
     * @return CertificadoPermanente
     */
    public function setInstitucioneducativa(\Sie\AppWebBundle\Entity\Institucioneducativa $institucioneducativa = null)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return \Sie\AppWebBundle\Entity\Institucioneducativa 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return CertificadoPermanente
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
}
