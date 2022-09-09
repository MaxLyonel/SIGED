<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UnivGradoTipo
 */
class UnivGradoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $gradoAcademico;

    /**
     * @var \Sie\AppWebBundle\Entity\UnivNivelAcademicoTipo
     */
    private $univNivelAcademicoTipo;


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
     * Set gradoAcademico
     *
     * @param string $gradoAcademico
     * @return UnivGradoTipo
     */
    public function setGradoAcademico($gradoAcademico)
    {
        $this->gradoAcademico = $gradoAcademico;
    
        return $this;
    }

    /**
     * Get gradoAcademico
     *
     * @return string 
     */
    public function getGradoAcademico()
    {
        return $this->gradoAcademico;
    }

    /**
     * Set univNivelAcademicoTipo
     *
     * @param \Sie\AppWebBundle\Entity\UnivNivelAcademicoTipo $univNivelAcademicoTipo
     * @return UnivGradoTipo
     */
    public function setUnivNivelAcademicoTipo(\Sie\AppWebBundle\Entity\UnivNivelAcademicoTipo $univNivelAcademicoTipo = null)
    {
        $this->univNivelAcademicoTipo = $univNivelAcademicoTipo;
    
        return $this;
    }

    /**
     * Get univNivelAcademicoTipo
     *
     * @return \Sie\AppWebBundle\Entity\UnivNivelAcademicoTipo 
     */
    public function getUnivNivelAcademicoTipo()
    {
        return $this->univNivelAcademicoTipo;
    }
}
