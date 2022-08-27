<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EstTecGradoAcademicoTipo
 */
class EstTecGradoAcademicoTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descripcion;

    /**
     * @var \Sie\AppWebBundle\Entity\EstTecRegimenEstudioTipo
     */
    private $estTecRegimenEstudioTipo;


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
     * Set descripcion
     *
     * @param string $descripcion
     * @return EstTecGradoAcademicoTipo
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
    
        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set estTecRegimenEstudioTipo
     *
     * @param \Sie\AppWebBundle\Entity\EstTecRegimenEstudioTipo $estTecRegimenEstudioTipo
     * @return EstTecGradoAcademicoTipo
     */
    public function setEstTecRegimenEstudioTipo(\Sie\AppWebBundle\Entity\EstTecRegimenEstudioTipo $estTecRegimenEstudioTipo = null)
    {
        $this->estTecRegimenEstudioTipo = $estTecRegimenEstudioTipo;
    
        return $this;
    }

    /**
     * Get estTecRegimenEstudioTipo
     *
     * @return \Sie\AppWebBundle\Entity\EstTecRegimenEstudioTipo 
     */
    public function getEstTecRegimenEstudioTipo()
    {
        return $this->estTecRegimenEstudioTipo;
    }
}
