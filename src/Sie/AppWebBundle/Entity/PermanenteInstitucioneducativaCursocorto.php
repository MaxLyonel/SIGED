<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PermanenteInstitucioneducativaCursocorto
 */
class PermanenteInstitucioneducativaCursocorto
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $esabierto;

    /**
     * @var \Sie\AppWebBundle\Entity\PermanenteAreaTematicaTipo
     */
    private $areatematicaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\PermanentePoblacionTipo
     */
    private $poblacionTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\InstitucioneducativaCurso
     */
    private $institucioneducativaCurso;

    /**
     * @var \Sie\AppWebBundle\Entity\PermanenteSubAreaTipo
     */
    private $subAreaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\PermanenteProgramaTipo
     */
    private $programaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\PermanenteCursocortoTipo
     */
    private $cursocortoTipo;


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
     * Set esabierto
     *
     * @param boolean $esabierto
     * @return PermanenteInstitucioneducativaCursocorto
     */
    public function setEsabierto($esabierto)
    {
        $this->esabierto = $esabierto;
    
        return $this;
    }

    /**
     * Get esabierto
     *
     * @return boolean 
     */
    public function getEsabierto()
    {
        return $this->esabierto;
    }

    /**
     * Set areatematicaTipo
     *
     * @param \Sie\AppWebBundle\Entity\PermanenteAreaTematicaTipo $areatematicaTipo
     * @return PermanenteInstitucioneducativaCursocorto
     */
    public function setAreatematicaTipo(\Sie\AppWebBundle\Entity\PermanenteAreaTematicaTipo $areatematicaTipo = null)
    {
        $this->areatematicaTipo = $areatematicaTipo;
    
        return $this;
    }

    /**
     * Get areatematicaTipo
     *
     * @return \Sie\AppWebBundle\Entity\PermanenteAreaTematicaTipo 
     */
    public function getAreatematicaTipo()
    {
        return $this->areatematicaTipo;
    }

    /**
     * Set poblacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\PermanentePoblacionTipo $poblacionTipo
     * @return PermanenteInstitucioneducativaCursocorto
     */
    public function setPoblacionTipo(\Sie\AppWebBundle\Entity\PermanentePoblacionTipo $poblacionTipo = null)
    {
        $this->poblacionTipo = $poblacionTipo;
    
        return $this;
    }

    /**
     * Get poblacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\PermanentePoblacionTipo 
     */
    public function getPoblacionTipo()
    {
        return $this->poblacionTipo;
    }

    /**
     * Set institucioneducativaCurso
     *
     * @param \Sie\AppWebBundle\Entity\InstitucioneducativaCurso $institucioneducativaCurso
     * @return PermanenteInstitucioneducativaCursocorto
     */
    public function setInstitucioneducativaCurso(\Sie\AppWebBundle\Entity\InstitucioneducativaCurso $institucioneducativaCurso = null)
    {
        $this->institucioneducativaCurso = $institucioneducativaCurso;
    
        return $this;
    }

    /**
     * Get institucioneducativaCurso
     *
     * @return \Sie\AppWebBundle\Entity\InstitucioneducativaCurso 
     */
    public function getInstitucioneducativaCurso()
    {
        return $this->institucioneducativaCurso;
    }

    /**
     * Set subAreaTipo
     *
     * @param \Sie\AppWebBundle\Entity\PermanenteSubAreaTipo $subAreaTipo
     * @return PermanenteInstitucioneducativaCursocorto
     */
    public function setSubAreaTipo(\Sie\AppWebBundle\Entity\PermanenteSubAreaTipo $subAreaTipo = null)
    {
        $this->subAreaTipo = $subAreaTipo;
    
        return $this;
    }

    /**
     * Get subAreaTipo
     *
     * @return \Sie\AppWebBundle\Entity\PermanenteSubAreaTipo 
     */
    public function getSubAreaTipo()
    {
        return $this->subAreaTipo;
    }

    /**
     * Set programaTipo
     *
     * @param \Sie\AppWebBundle\Entity\PermanenteProgramaTipo $programaTipo
     * @return PermanenteInstitucioneducativaCursocorto
     */
    public function setProgramaTipo(\Sie\AppWebBundle\Entity\PermanenteProgramaTipo $programaTipo = null)
    {
        $this->programaTipo = $programaTipo;
    
        return $this;
    }

    /**
     * Get programaTipo
     *
     * @return \Sie\AppWebBundle\Entity\PermanenteProgramaTipo 
     */
    public function getProgramaTipo()
    {
        return $this->programaTipo;
    }

    /**
     * Set cursocortoTipo
     *
     * @param \Sie\AppWebBundle\Entity\PermanenteCursocortoTipo $cursocortoTipo
     * @return PermanenteInstitucioneducativaCursocorto
     */
    public function setCursocortoTipo(\Sie\AppWebBundle\Entity\PermanenteCursocortoTipo $cursocortoTipo = null)
    {
        $this->cursocortoTipo = $cursocortoTipo;
    
        return $this;
    }

    /**
     * Get cursocortoTipo
     *
     * @return \Sie\AppWebBundle\Entity\PermanenteCursocortoTipo 
     */
    public function getCursocortoTipo()
    {
        return $this->cursocortoTipo;
    }
}
