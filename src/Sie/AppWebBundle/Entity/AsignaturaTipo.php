<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AsignaturaTipo
 */
class AsignaturaTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $asignatura;

    /**
     * @var string
     */
    private $descAbrev;

    /**
     * @var boolean
     */
    private $oficial;

    /**
     * @var string
     */
    private $contenido;

    /**
     * @var boolean
     */
    private $esobligatorio;

    public function __toString() {
        return $this->asignatura;
    }
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
     * Set asignatura
     *
     * @param string $asignatura
     * @return AsignaturaTipo
     */
    public function setAsignatura($asignatura)
    {
        $this->asignatura = $asignatura;

        return $this;
    }

    /**
     * Get asignatura
     *
     * @return string 
     */
    public function getAsignatura()
    {
        return $this->asignatura;
    }

    /**
     * Set descAbrev
     *
     * @param string $descAbrev
     * @return AsignaturaTipo
     */
    public function setDescAbrev($descAbrev)
    {
        $this->descAbrev = $descAbrev;

        return $this;
    }

    /**
     * Get descAbrev
     *
     * @return string 
     */
    public function getDescAbrev()
    {
        return $this->descAbrev;
    }

    /**
     * Set oficial
     *
     * @param boolean $oficial
     * @return AsignaturaTipo
     */
    public function setOficial($oficial)
    {
        $this->oficial = $oficial;

        return $this;
    }

    /**
     * Get oficial
     *
     * @return boolean 
     */
    public function getOficial()
    {
        return $this->oficial;
    }

    /**
     * Set contenido
     *
     * @param string $contenido
     * @return AsignaturaTipo
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;

        return $this;
    }

    /**
     * Get contenido
     *
     * @return string 
     */
    public function getContenido()
    {
        return $this->contenido;
    }


    /**
     * Set esobligatorio
     *
     * @param boolean $esobligatorio
     * @return AsignaturaTipo
     */
    public function setEsobligatorio($esobligatorio)
    {
        $this->esobligatorio = $esobligatorio;

        return $this;
    }

    /**
     * Get esobligatorio
     *
     * @return boolean 
     */
    public function getEsobligatorio()
    {
        return $this->esobligatorio;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\AsignaturaNivelTipo
     */
    private $asignaturaNivelTipo;


    /**
     * Set asignaturaNivelTipo
     *
     * @param \Sie\AppWebBundle\Entity\AsignaturaNivelTipo $asignaturaNivelTipo
     * @return AsignaturaTipo
     */
    public function setAsignaturaNivelTipo(\Sie\AppWebBundle\Entity\AsignaturaNivelTipo $asignaturaNivelTipo = null)
    {
        $this->asignaturaNivelTipo = $asignaturaNivelTipo;
    
        return $this;
    }

    /**
     * Get asignaturaNivelTipo
     *
     * @return \Sie\AppWebBundle\Entity\AsignaturaNivelTipo 
     */
    public function getAsignaturaNivelTipo()
    {
        return $this->asignaturaNivelTipo;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\AreaTipo
     */
    private $areaTipo;


    /**
     * Set areaTipo
     *
     * @param \Sie\AppWebBundle\Entity\AreaTipo $areaTipo
     * @return AsignaturaTipo
     */
    public function setAreaTipo(\Sie\AppWebBundle\Entity\AreaTipo $areaTipo = null)
    {
        $this->areaTipo = $areaTipo;
    
        return $this;
    }

    /**
     * Get areaTipo
     *
     * @return \Sie\AppWebBundle\Entity\AreaTipo 
     */
    public function getAreaTipo()
    {
        return $this->areaTipo;
    }
    /**
     * @var integer
     */
    private $areaTipoId;


    /**
     * Set areaTipoId
     *
     * @param integer $areaTipoId
     * @return AsignaturaTipo
     */
    public function setAreaTipoId($areaTipoId)
    {
        $this->areaTipoId = $areaTipoId;
    
        return $this;
    }

    /**
     * Get areaTipoId
     *
     * @return integer 
     */
    public function getAreaTipoId()
    {
        return $this->areaTipoId;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\AsignaturaNivelTipo
     */
    private $asignaturaNivel;


    /**
     * Set asignaturaNivel
     *
     * @param \Sie\AppWebBundle\Entity\AsignaturaNivelTipo $asignaturaNivel
     * @return AsignaturaTipo
     */
    public function setAsignaturaNivel(\Sie\AppWebBundle\Entity\AsignaturaNivelTipo $asignaturaNivel = null)
    {
        $this->asignaturaNivel = $asignaturaNivel;
    
        return $this;
    }

    /**
     * Get asignaturaNivel
     *
     * @return \Sie\AppWebBundle\Entity\AsignaturaNivelTipo 
     */
    public function getAsignaturaNivel()
    {
        return $this->asignaturaNivel;
    }
}
