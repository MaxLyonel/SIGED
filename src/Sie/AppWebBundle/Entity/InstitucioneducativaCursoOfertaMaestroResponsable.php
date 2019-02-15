<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaCursoOfertaMaestroResponsable
 */
class InstitucioneducativaCursoOfertaMaestroResponsable
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro
     */
    private $institucioneducativaCursoOfertaMaestroVoluntario;

    /**
     * @var \Sie\AppWebBundle\Entity\MaestroInscripcion
     */
    private $maestroInscripcionResponsable;


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
     * Set institucioneducativaCursoOfertaMaestroVoluntario
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro $institucioneducativaCursoOfertaMaestroVoluntario
     * @return InstitucioneducativaCursoOfertaMaestroResponsable
     */
    public function setInstitucioneducativaCursoOfertaMaestroVoluntario(\Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro $institucioneducativaCursoOfertaMaestroVoluntario = null)
    {
        $this->institucioneducativaCursoOfertaMaestroVoluntario = $institucioneducativaCursoOfertaMaestroVoluntario;
    
        return $this;
    }

    /**
     * Get institucioneducativaCursoOfertaMaestroVoluntario
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCursoOfertaMaestro 
     */
    public function getInstitucioneducativaCursoOfertaMaestroVoluntario()
    {
        return $this->institucioneducativaCursoOfertaMaestroVoluntario;
    }

    /**
     * Set maestroInscripcionResponsable
     *
     * @param \Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcionResponsable
     * @return InstitucioneducativaCursoOfertaMaestroResponsable
     */
    public function setMaestroInscripcionResponsable(\Sie\AppWebBundle\Entity\MaestroInscripcion $maestroInscripcionResponsable = null)
    {
        $this->maestroInscripcionResponsable = $maestroInscripcionResponsable;
    
        return $this;
    }

    /**
     * Get maestroInscripcionResponsable
     *
     * @return \Sie\AppWebBundle\Entity\MaestroInscripcion 
     */
    public function getMaestroInscripcionResponsable()
    {
        return $this->maestroInscripcionResponsable;
    }
}
