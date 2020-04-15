<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BfEstudiantesValidados
 */
class BfEstudiantesValidados
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $codigoRude;

    /**
     * @var integer
     */
    private $estudianteInscripcionIdant;

    /**
     * @var integer
     */
    private $estudianteInscripcionId;

    /**
     * @var string
     */
    private $observacion;

    /**
     * @var integer
     */
    private $gestionTipoId;


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
     * Set codigoRude
     *
     * @param string $codigoRude
     * @return BfEstudiantesValidados
     */
    public function setCodigoRude($codigoRude)
    {
        $this->codigoRude = $codigoRude;
    
        return $this;
    }

    /**
     * Get codigoRude
     *
     * @return string 
     */
    public function getCodigoRude()
    {
        return $this->codigoRude;
    }

    /**
     * Set estudianteInscripcionIdant
     *
     * @param integer $estudianteInscripcionIdant
     * @return BfEstudiantesValidados
     */
    public function setEstudianteInscripcionIdant($estudianteInscripcionIdant)
    {
        $this->estudianteInscripcionIdant = $estudianteInscripcionIdant;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionIdant
     *
     * @return integer 
     */
    public function getEstudianteInscripcionIdant()
    {
        return $this->estudianteInscripcionIdant;
    }

    /**
     * Set estudianteInscripcionId
     *
     * @param integer $estudianteInscripcionId
     * @return BfEstudiantesValidados
     */
    public function setEstudianteInscripcionId($estudianteInscripcionId)
    {
        $this->estudianteInscripcionId = $estudianteInscripcionId;
    
        return $this;
    }

    /**
     * Get estudianteInscripcionId
     *
     * @return integer 
     */
    public function getEstudianteInscripcionId()
    {
        return $this->estudianteInscripcionId;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return BfEstudiantesValidados
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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return BfEstudiantesValidados
     */
    public function setGestionTipoId($gestionTipoId)
    {
        $this->gestionTipoId = $gestionTipoId;
    
        return $this;
    }

    /**
     * Get gestionTipoId
     *
     * @return integer 
     */
    public function getGestionTipoId()
    {
        return $this->gestionTipoId;
    }
    /**
     * @var \DateTime
     */
    private $fechaCorte;

    /**
     * @var \Sie\AppWebBundle\Entity\Estudiante
     */
    private $estudiante;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OrgcurricularTipo
     */
    private $orgcurricularTipo;


    /**
     * Set fechaCorte
     *
     * @param \DateTime $fechaCorte
     * @return BfEstudiantesValidados
     */
    public function setFechaCorte($fechaCorte)
    {
        $this->fechaCorte = $fechaCorte;
    
        return $this;
    }

    /**
     * Get fechaCorte
     *
     * @return \DateTime 
     */
    public function getFechaCorte()
    {
        return $this->fechaCorte;
    }

    /**
     * Set estudiante
     *
     * @param \Sie\AppWebBundle\Entity\Estudiante $estudiante
     * @return BfEstudiantesValidados
     */
    public function setEstudiante(\Sie\AppWebBundle\Entity\Estudiante $estudiante = null)
    {
        $this->estudiante = $estudiante;
    
        return $this;
    }

    /**
     * Get estudiante
     *
     * @return \Sie\AppWebBundle\Entity\Estudiante 
     */
    public function getEstudiante()
    {
        return $this->estudiante;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return BfEstudiantesValidados
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
     * Set orgcurricularTipo
     *
     * @param \Sie\AppWebBundle\Entity\OrgcurricularTipo $orgcurricularTipo
     * @return BfEstudiantesValidados
     */
    public function setOrgcurricularTipo(\Sie\AppWebBundle\Entity\OrgcurricularTipo $orgcurricularTipo = null)
    {
        $this->orgcurricularTipo = $orgcurricularTipo;
    
        return $this;
    }

    /**
     * Get orgcurricularTipo
     *
     * @return \Sie\AppWebBundle\Entity\OrgcurricularTipo 
     */
    public function getOrgcurricularTipo()
    {
        return $this->orgcurricularTipo;
    }
}
