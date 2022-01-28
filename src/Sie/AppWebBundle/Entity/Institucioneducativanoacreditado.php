<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Institucioneducativanoacreditado
 */
class Institucioneducativanoacreditado
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $institucioneducativa;

    /**
     * @var integer
     */
    private $codDis;

    /**
     * @var string
     */
    private $codRue;

    /**
     * @var boolean
     */
    private $esimpreso;

    /**
     * @var \Sie\AppWebBundle\Entity\DependenciaTipo
     */
    private $dependenciaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\OrgcurricularTipo
     */
    private $orgcurricularTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\GestionTipo
     */
    private $gestion;


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
     * Set institucioneducativa
     *
     * @param string $institucioneducativa
     * @return Institucioneducativanoacreditado
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
     * Set codDis
     *
     * @param integer $codDis
     * @return Institucioneducativanoacreditado
     */
    public function setCodDis($codDis)
    {
        $this->codDis = $codDis;
    
        return $this;
    }

    /**
     * Get codDis
     *
     * @return integer 
     */
    public function getCodDis()
    {
        return $this->codDis;
    }

    /**
     * Set codRue
     *
     * @param string $codRue
     * @return Institucioneducativanoacreditado
     */
    public function setCodRue($codRue)
    {
        $this->codRue = $codRue;
    
        return $this;
    }

    /**
     * Get codRue
     *
     * @return string 
     */
    public function getCodRue()
    {
        return $this->codRue;
    }

    /**
     * Set esimpreso
     *
     * @param boolean $esimpreso
     * @return Institucioneducativanoacreditado
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
     * Set dependenciaTipo
     *
     * @param \Sie\AppWebBundle\Entity\DependenciaTipo $dependenciaTipo
     * @return Institucioneducativanoacreditado
     */
    public function setDependenciaTipo(\Sie\AppWebBundle\Entity\DependenciaTipo $dependenciaTipo = null)
    {
        $this->dependenciaTipo = $dependenciaTipo;
    
        return $this;
    }

    /**
     * Get dependenciaTipo
     *
     * @return \Sie\AppWebBundle\Entity\DependenciaTipo 
     */
    public function getDependenciaTipo()
    {
        return $this->dependenciaTipo;
    }

    /**
     * Set orgcurricularTipo
     *
     * @param \Sie\AppWebBundle\Entity\OrgcurricularTipo $orgcurricularTipo
     * @return Institucioneducativanoacreditado
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

    /**
     * Set gestion
     *
     * @param \Sie\AppWebBundle\Entity\GestionTipo $gestion
     * @return Institucioneducativanoacreditado
     */
    public function setGestion(\Sie\AppWebBundle\Entity\GestionTipo $gestion = null)
    {
        $this->gestion = $gestion;
    
        return $this;
    }

    /**
     * Get gestion
     *
     * @return \Sie\AppWebBundle\Entity\GestionTipo 
     */
    public function getGestion()
    {
        return $this->gestion;
    }
    /**
     * @var integer
     */
    private $gestionId;


    /**
     * Set gestionId
     *
     * @param integer $gestionId
     * @return Institucioneducativanoacreditado
     */
    public function setGestionId($gestionId)
    {
        $this->gestionId = $gestionId;
    
        return $this;
    }

    /**
     * Get gestionId
     *
     * @return integer 
     */
    public function getGestionId()
    {
        return $this->gestionId;
    }
}
