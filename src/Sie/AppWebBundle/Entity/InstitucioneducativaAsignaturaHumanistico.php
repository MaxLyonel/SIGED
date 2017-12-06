<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaAsignaturaHumanistico
 */
class InstitucioneducativaAsignaturaHumanistico
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
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\AsignaturaTipo
     */
    private $asignaturaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestionTipo;


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
     * @return InstitucioneducativaAsignaturaHumanistico
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
     * Set obs
     *
     * @param string $obs
     * @return InstitucioneducativaAsignaturaHumanistico
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
     * Set asignaturaTipo
     *
     * @param \Sie\AppWebBundle\Entity\AsignaturaTipo $asignaturaTipo
     * @return InstitucioneducativaAsignaturaHumanistico
     */
    public function setAsignaturaTipo(\Sie\AppWebBundle\Entity\AsignaturaTipo $asignaturaTipo = null)
    {
        $this->asignaturaTipo = $asignaturaTipo;
    
        return $this;
    }

    /**
     * Get asignaturaTipo
     *
     * @return \Sie\AppWebBundle\Entity\AsignaturaTipo 
     */
    public function getAsignaturaTipo()
    {
        return $this->asignaturaTipo;
    }

    /**
     * Set gestionTipo
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestionTipo
     * @return InstitucioneducativaAsignaturaHumanistico
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
}
