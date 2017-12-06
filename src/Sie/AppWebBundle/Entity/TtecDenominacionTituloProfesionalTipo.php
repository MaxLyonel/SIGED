<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TtecDenominacionTituloProfesionalTipo
 */
class TtecDenominacionTituloProfesionalTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $denominacion;

    /**
     * @var string
     */
    private $mencion;

    /**
     * @var \Sie\AppWebBundle\Entity\TtecCarreraTipo
     */
    private $ttecCarreraTipo;


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
     * Set denominacion
     *
     * @param string $denominacion
     * @return TtecDenominacionTituloProfesionalTipo
     */
    public function setDenominacion($denominacion)
    {
        $this->denominacion = $denominacion;
    
        return $this;
    }

    /**
     * Get denominacion
     *
     * @return string 
     */
    public function getDenominacion()
    {
        return $this->denominacion;
    }

    /**
     * Set mencion
     *
     * @param string $mencion
     * @return TtecDenominacionTituloProfesionalTipo
     */
    public function setMencion($mencion)
    {
        $this->mencion = $mencion;
    
        return $this;
    }

    /**
     * Get mencion
     *
     * @return string 
     */
    public function getMencion()
    {
        return $this->mencion;
    }

    /**
     * Set ttecCarreraTipo
     *
     * @param \Sie\AppWebBundle\Entity\TtecCarreraTipo $ttecCarreraTipo
     * @return TtecDenominacionTituloProfesionalTipo
     */
    public function setTtecCarreraTipo(\Sie\AppWebBundle\Entity\TtecCarreraTipo $ttecCarreraTipo = null)
    {
        $this->ttecCarreraTipo = $ttecCarreraTipo;
    
        return $this;
    }

    /**
     * Get ttecCarreraTipo
     *
     * @return \Sie\AppWebBundle\Entity\TtecCarreraTipo 
     */
    public function getTtecCarreraTipo()
    {
        return $this->ttecCarreraTipo;
    }
}
