<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecCatalogoMateria
 */
class TtecCatalogoMateria
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $codigo;

    /**
     * @var string
     */
    private $catalogoMateria;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo
     */
    private $ttecDenominacionTituloProfesionalTipo;


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
     * Set codigo
     *
     * @param string $codigo
     * @return TtecCatalogoMateria
     */
    public function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    
        return $this;
    }

    /**
     * Get codigo
     *
     * @return string 
     */
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * Set catalogoMateria
     *
     * @param string $catalogoMateria
     * @return TtecCatalogoMateria
     */
    public function setCatalogoMateria($catalogoMateria)
    {
        $this->catalogoMateria = $catalogoMateria;
    
        return $this;
    }

    /**
     * Get catalogoMateria
     *
     * @return string 
     */
    public function getCatalogoMateria()
    {
        return $this->catalogoMateria;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return TtecCatalogoMateria
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
     * Set ttecDenominacionTituloProfesionalTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo $ttecDenominacionTituloProfesionalTipo
     * @return TtecCatalogoMateria
     */
    public function setTtecDenominacionTituloProfesionalTipo(\Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo $ttecDenominacionTituloProfesionalTipo = null)
    {
        $this->ttecDenominacionTituloProfesionalTipo = $ttecDenominacionTituloProfesionalTipo;
    
        return $this;
    }

    /**
     * Get ttecDenominacionTituloProfesionalTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecDenominacionTituloProfesionalTipo 
     */
    public function getTtecDenominacionTituloProfesionalTipo()
    {
        return $this->ttecDenominacionTituloProfesionalTipo;
    }
}
