<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MenuSistemaRol
 */
class MenuSistemaRol
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $fechaInicio;

    /**
     * @var \DateTime
     */
    private $fechaFin;

    /**
     * @var boolean
     */
    private $esactivo;

    /**
     * @var \Sie\AppWebBundle\Entity\RolTipo
     */
    private $rolTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\MenuSistema
     */
    private $menuSistema;


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
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return MenuSistemaRol
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
    
        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime 
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     * @return MenuSistemaRol
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
    
        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime 
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set esactivo
     *
     * @param boolean $esactivo
     * @return MenuSistemaRol
     */
    public function setEsactivo($esactivo)
    {
        $this->esactivo = $esactivo;
    
        return $this;
    }

    /**
     * Get esactivo
     *
     * @return boolean 
     */
    public function getEsactivo()
    {
        return $this->esactivo;
    }

    /**
     * Set rolTipo
     *
     * @param \Sie\AppWebBundle\Entity\RolTipo $rolTipo
     * @return MenuSistemaRol
     */
    public function setRolTipo(\Sie\AppWebBundle\Entity\RolTipo $rolTipo = null)
    {
        $this->rolTipo = $rolTipo;
    
        return $this;
    }

    /**
     * Get rolTipo
     *
     * @return \Sie\AppWebBundle\Entity\RolTipo 
     */
    public function getRolTipo()
    {
        return $this->rolTipo;
    }

    /**
     * Set menuSistema
     *
     * @param \Sie\AppWebBundle\Entity\MenuSistema $menuSistema
     * @return MenuSistemaRol
     */
    public function setMenuSistema(\Sie\AppWebBundle\Entity\MenuSistema $menuSistema = null)
    {
        $this->menuSistema = $menuSistema;
    
        return $this;
    }

    /**
     * Get menuSistema
     *
     * @return \Sie\AppWebBundle\Entity\MenuSistema 
     */
    public function getMenuSistema()
    {
        return $this->menuSistema;
    }
    /**
     * @var \Sie\AppWebBundle\Entity\SistemaRol
     */
    private $sistemaRol;


    /**
     * Set sistemaRol
     *
     * @param \Sie\AppWebBundle\Entity\SistemaRol $sistemaRol
     * @return MenuSistemaRol
     */
    public function setSistemaRol(\Sie\AppWebBundle\Entity\SistemaRol $sistemaRol = null)
    {
        $this->sistemaRol = $sistemaRol;
    
        return $this;
    }

    /**
     * Get sistemaRol
     *
     * @return \Sie\AppWebBundle\Entity\SistemaRol 
     */
    public function getSistemaRol()
    {
        return $this->sistemaRol;
    }
}
