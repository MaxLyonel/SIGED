<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpDisciplinaParticipacionTipo
 */
class JdpDisciplinaParticipacionTipo
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $disciplinaParticipacion;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var integer
     */
    private $cantidad;


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
     * Set disciplinaParticipacion
     *
     * @param string $disciplinaParticipacion
     * @return JdpDisciplinaParticipacionTipo
     */
    public function setDisciplinaParticipacion($disciplinaParticipacion)
    {
        $this->disciplinaParticipacion = $disciplinaParticipacion;
    
        return $this;
    }

    /**
     * Get disciplinaParticipacion
     *
     * @return string 
     */
    public function getDisciplinaParticipacion()
    {
        return $this->disciplinaParticipacion;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return JdpDisciplinaParticipacionTipo
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
     * Set cantidad
     *
     * @param integer $cantidad
     * @return JdpDisciplinaParticipacionTipo
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;
    
        return $this;
    }

    /**
     * Get cantidad
     *
     * @return integer 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }
}
