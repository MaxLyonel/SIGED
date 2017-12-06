<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaCursoOferta
 */
class InstitucioneducativaCursoOferta
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $horasmes;

    /**
     * @var \Sie\AppWebBundle\Entity\AsignaturaTipo
     */
    private $asignaturaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaCurso
     */
    private $insitucioneducativaCurso;


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
     * Set horasmes
     *
     * @param integer $horasmes
     * @return InstitucioneducativaCursoOferta
     */
    public function setHorasmes($horasmes)
    {
        $this->horasmes = $horasmes;
    
        return $this;
    }

    /**
     * Get horasmes
     *
     * @return integer 
     */
    public function getHorasmes()
    {
        return $this->horasmes;
    }

    /**
     * Set asignaturaTipo
     *
     * @param \Sie\AppWebBundle\Entity\AsignaturaTipo $asignaturaTipo
     * @return InstitucioneducativaCursoOferta
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
     * Set insitucioneducativaCurso
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCurso $insitucioneducativaCurso
     * @return InstitucioneducativaCursoOferta
     */
    public function setInsitucioneducativaCurso(\Sie\AppWebBundle\Entity\InstitucioneducativaCurso $insitucioneducativaCurso = null)
    {
        $this->insitucioneducativaCurso = $insitucioneducativaCurso;
    
        return $this;
    }

    /**
     * Get insitucioneducativaCurso
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCurso 
     */
    public function getInsitucioneducativaCurso()
    {
        return $this->insitucioneducativaCurso;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\SuperiorModuloPeriodo
     */
    private $superiorModuloPeriodo;


    /**
     * Set superiorModuloPeriodo
     *
     * @param \Sie\AppWebBundle\Entity\SuperiorModuloPeriodo $superiorModuloPeriodo
     * @return InstitucioneducativaCursoOferta
     */
    public function setSuperiorModuloPeriodo(\Sie\AppWebBundle\Entity\SuperiorModuloPeriodo $superiorModuloPeriodo = null)
    {
        $this->superiorModuloPeriodo = $superiorModuloPeriodo;
    
        return $this;
    }

    /**
     * Get superiorModuloPeriodo
     *
     * @return \Sie\AppWebBundle\Entity\SuperiorModuloPeriodo 
     */
    public function getSuperiorModuloPeriodo()
    {
        return $this->superiorModuloPeriodo;
    }
}
