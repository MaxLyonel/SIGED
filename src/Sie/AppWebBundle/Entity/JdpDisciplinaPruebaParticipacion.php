<?php

namespace Sie\AppWebBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * JdpDisciplinaPruebaParticipacion
 */
class JdpDisciplinaPruebaParticipacion
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $cantidad;

    /**
     * @var string
     */
    private $obs;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpDisciplinaTipo
     */
    private $disciplinaTipo;

    /**
     * @var \Sie\AppWebBundle\Entity\JdpPruebaParticipacionTipo
     */
    private $pruebaParticipacionTipo;


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
     * Set cantidad
     *
     * @param integer $cantidad
     * @return JdpDisciplinaPruebaParticipacion
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

    /**
     * Set obs
     *
     * @param string $obs
     * @return JdpDisciplinaPruebaParticipacion
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
     * Set disciplinaTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpDisciplinaTipo $disciplinaTipo
     * @return JdpDisciplinaPruebaParticipacion
     */
    public function setDisciplinaTipo(\Sie\AppWebBundle\Entity\JdpDisciplinaTipo $disciplinaTipo = null)
    {
        $this->disciplinaTipo = $disciplinaTipo;
    
        return $this;
    }

    /**
     * Get disciplinaTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpDisciplinaTipo 
     */
    public function getDisciplinaTipo()
    {
        return $this->disciplinaTipo;
    }

    /**
     * Set pruebaParticipacionTipo
     *
     * @param \Sie\AppWebBundle\Entity\JdpPruebaParticipacionTipo $pruebaParticipacionTipo
     * @return JdpDisciplinaPruebaParticipacion
     */
    public function setPruebaParticipacionTipo(\Sie\AppWebBundle\Entity\JdpPruebaParticipacionTipo $pruebaParticipacionTipo = null)
    {
        $this->pruebaParticipacionTipo = $pruebaParticipacionTipo;
    
        return $this;
    }

    /**
     * Get pruebaParticipacionTipo
     *
     * @return \Sie\AppWebBundle\Entity\JdpPruebaParticipacionTipo 
     */
    public function getPruebaParticipacionTipo()
    {
        return $this->pruebaParticipacionTipo;
    }
}
