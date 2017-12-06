<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InstitucioneducativaHumanisticoTecnico
 */
class InstitucioneducativaHumanisticoTecnico
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $gestionTipoId;

    /**
     * @var integer
     */
    private $institucioneducativaId;

    /**
     * @var string
     */
    private $institucioneducativa;


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
     * Set gestionTipoId
     *
     * @param integer $gestionTipoId
     * @return InstitucioneducativaHumanisticoTecnico
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
     * Set institucioneducativaId
     *
     * @param integer $institucioneducativaId
     * @return InstitucioneducativaHumanisticoTecnico
     */
    public function setInstitucioneducativaId($institucioneducativaId)
    {
        $this->institucioneducativaId = $institucioneducativaId;
    
        return $this;
    }

    /**
     * Get institucioneducativaId
     *
     * @return integer 
     */
    public function getInstitucioneducativaId()
    {
        return $this->institucioneducativaId;
    }

    /**
     * Set institucioneducativa
     *
     * @param string $institucioneducativa
     * @return InstitucioneducativaHumanisticoTecnico
     */
    public function setInstitucioneducativa($institucioneducativa)
    {
        $this->institucioneducativa = $institucioneducativa;
    
        return $this;
    }

    /**
     * Get institucioneducativa
     *
     * @return string 
     */
    public function getInstitucioneducativa()
    {
        return $this->institucioneducativa;
    }
    /**
     * @var boolean
     */
    private $esimpreso;


    /**
     * Set esimpreso
     *
     * @param boolean $esimpreso
     * @return InstitucioneducativaHumanisticoTecnico
     */
    public function setEsimpreso($esimpreso)
    {
        $this->esimpreso = $esimpreso;
    
        return $this;
    }

    /**
     * Get esimpreso
     *
     * @return boolean 
     */
    public function getEsimpreso()
    {
        return $this->esimpreso;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\GradoTipo
     */
    private $gradoTipo;


    /**
     * Set gradoTipo
     *
     * @param \Sie\AppWebBundle\Entity\GradoTipo $gradoTipo
     * @return InstitucioneducativaHumanisticoTecnico
     */
    public function setGradoTipo(\Sie\AppWebBundle\Entity\GradoTipo $gradoTipo = null)
    {
        $this->gradoTipo = $gradoTipo;
    
        return $this;
    }

    /**
     * Get gradoTipo
     *
     * @return \Sie\AppWebBundle\Entity\GradoTipo 
     */
    public function getGradoTipo()
    {
        return $this->gradoTipo;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnicoTipo
     */
    private $institucioneducativaHumanisticoTecnicoTipo;


    /**
     * Set institucioneducativaHumanisticoTecnicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnicoTipo $institucioneducativaHumanisticoTecnicoTipo
     * @return InstitucioneducativaHumanisticoTecnico
     */
    public function setInstitucioneducativaHumanisticoTecnicoTipo(\Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnicoTipo $institucioneducativaHumanisticoTecnicoTipo = null)
    {
        $this->institucioneducativaHumanisticoTecnicoTipo = $institucioneducativaHumanisticoTecnicoTipo;
    
        return $this;
    }

    /**
     * Get institucioneducativaHumanisticoTecnicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaHumanisticoTecnicoTipo 
     */
    public function getInstitucioneducativaHumanisticoTecnicoTipo()
    {
        return $this->institucioneducativaHumanisticoTecnicoTipo;
    }
}
