<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstudianteAsignatura
 */
class EstudianteAsignatura
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
     * @var integer
     */
    private $institucioneducativaTecnicaCursoOfertaId;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteInscripcion
     */
    private $estudianteInscripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\EstudianteasignaturaEstado
     */
    private $estudianteasignaturaEstado;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta
     */
    private $institucioneducativaCursoOferta;


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
     * @return EstudianteAsignatura
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
     * Set institucioneducativaTecnicaCursoOfertaId
     *
     * @param integer $institucioneducativaTecnicaCursoOfertaId
     * @return EstudianteAsignatura
     */
    public function setInstitucioneducativaTecnicaCursoOfertaId($institucioneducativaTecnicaCursoOfertaId)
    {
        $this->institucioneducativaTecnicaCursoOfertaId = $institucioneducativaTecnicaCursoOfertaId;
    
        return $this;
    }

    /**
     * Get institucioneducativaTecnicaCursoOfertaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaTecnicaCursoOfertaId()
    {
        return $this->institucioneducativaTecnicaCursoOfertaId;
    }

    /**
     * Set estudianteInscripcion
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteInscripcion $estudianteInscripcion
     * @return EstudianteAsignatura
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
     * Set estudianteasignaturaEstado
     *
     * @param \Sie\AppWebBundle\Entity\EstudianteasignaturaEstado $estudianteasignaturaEstado
     * @return EstudianteAsignatura
     */
    public function setEstudianteasignaturaEstado(\Sie\AppWebBundle\Entity\EstudianteasignaturaEstado $estudianteasignaturaEstado = null)
    {
        $this->estudianteasignaturaEstado = $estudianteasignaturaEstado;
    
        return $this;
    }

    /**
     * Get estudianteasignaturaEstado
     *
     * @return \Sie\AppWebBundle\Entity\EstudianteasignaturaEstado 
     */
    public function getEstudianteasignaturaEstado()
    {
        return $this->estudianteasignaturaEstado;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return EstudianteAsignatura
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
     * Set institucioneducativaCursoOferta
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta $institucioneducativaCursoOferta
     * @return EstudianteAsignatura
     */
    public function setInstitucioneducativaCursoOferta(\Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta $institucioneducativaCursoOferta = null)
    {
        $this->institucioneducativaCursoOferta = $institucioneducativaCursoOferta;
    
        return $this;
    }

    /**
     * Get institucioneducativaCursoOferta
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOferta 
     */
    public function getInstitucioneducativaCursoOferta()
    {
        return $this->institucioneducativaCursoOferta;
    }
}
